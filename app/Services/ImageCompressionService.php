<?php

namespace App\Services;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use RuntimeException;

class ImageCompressionService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('image-compression');
    }

    public function compress(File|UploadedFile|string $source, ?string $targetFormat = null): array
    {
        $path = $this->getSourcePath($source);
        $originalSize = filesize($path);
        $originalMime = mime_content_type($path);

        if (!$originalMime) {
            throw new RuntimeException("Could not determine mime type for: {$path}");
        }

        $format = $targetFormat ?? $this->detectFormat($originalMime);

        if (!$format) {
            throw new RuntimeException("Unsupported image format: {$originalMime}");
        }

        try {
            $binary = $this->runEncoder($format, $path);
            $newSize = strlen($binary);

            $result = [
                'binary' => $binary,
                'original_kb' => round($originalSize / 1024, 2),
                'compressed_kb' => round($newSize / 1024, 2),
                'savings_pct' => round(100 * (1 - $newSize / $originalSize), 1),
                'format' => $format,
                'original_format' => $this->detectFormat($originalMime),
                'compression_method' => 'cli'
            ];

            $this->logCompressionResult($path, $result);

            return $result;
        } catch (\Exception $e) {
            if ($this->config['fallback']['enabled']) {
                Log::warning("CLI compression failed, falling back to GD: " . $e->getMessage());
                return $this->fallbackToGd($path, $format, $originalSize);
            }

            throw new RuntimeException("Image compression failed: " . $e->getMessage(), 0, $e);
        }
    }

    public function checkTools(): array
    {
        $tools = ['pngquant', 'mozjpeg', 'cwebp', 'avifenc'];
        $status = [];

        foreach ($tools as $tool) {
            try {
                $this->bin($tool);
                $status[$tool] = true;
            } catch (RuntimeException) {
                $status[$tool] = false;
            }
        }

        return $status;
    }

    public function getAvailableFormats(): array
    {
        $formats = [];
        $tools = $this->checkTools();

        if ($tools['pngquant']) $formats[] = 'png';
        if ($tools['mozjpeg']) $formats[] = 'jpeg';
        if ($tools['cwebp']) $formats[] = 'webp';
        if ($tools['avifenc']) $formats[] = 'avif';

        return $formats;
    }

    protected function getSourcePath(File|UploadedFile|string $source): string
    {
        if (is_string($source)) {
            if (!file_exists($source)) {
                throw new RuntimeException("Source file does not exist: {$source}");
            }
            return $source;
        }

        return $source->getRealPath();
    }

    protected function detectFormat(string $mimeType): ?string
    {
        return match (true) {
            str_contains($mimeType, 'png') => 'png',
            str_contains($mimeType, 'jpeg') || str_contains($mimeType, 'jpg') => 'jpeg',
            str_contains($mimeType, 'webp') => 'webp',
            str_contains($mimeType, 'avif') => 'avif',
            default => null,
        };
    }

    protected function runEncoder(string $format, string $path): string
    {
        if ($format === 'avif') {
            return $this->runAvifEncoder($path);
        }

        $process = match ($format) {
            'png' => $this->buildPngquant($path),
            'jpeg' => $this->buildMozjpeg($path),
            'webp' => $this->buildCwebp($path),
            default => throw new RuntimeException("Unknown format: {$format}"),
        };

        $process->setTimeout($this->config['timeout']);
        $process->mustRun();

        $output = $process->getOutput();
        if (empty($output)) {
            throw new RuntimeException("No output from {$format} encoder");
        }

        return $output;
    }

    protected function runAvifEncoder(string $path): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'avif_') . '.avif';

        try {
            $process = $this->buildAvifenc($path, $tempFile);
            $process->setTimeout($this->config['timeout']);
            $process->mustRun();

            if (!file_exists($tempFile)) {
                throw new RuntimeException("AVIF encoder did not create output file");
            }

            $content = file_get_contents($tempFile);
            if ($content === false) {
                throw new RuntimeException("Failed to read AVIF output file");
            }

            return $content;
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    protected function buildPngquant(string $path): Process
    {
        return new Process([
            $this->bin('pngquant'),
            "--quality={$this->config['quality']['png']}",
            '--strip',
            '--output',
            '-',
            $path
        ]);
    }

    protected function buildMozjpeg(string $path): Process
    {
        return new Process([
            'jpegtran',
            '-optimize',
            '-progressive',
            $path
        ]);
    }

    protected function buildCwebp(string $path): Process
    {
        return new Process([
            $this->bin('cwebp'),
            '-q',
            (string) $this->config['quality']['webp'],
            $path,
            '-o',
            '-'
        ]);
    }

    protected function buildAvifenc(string $path, string $outputPath): Process
    {
        $quality = $this->config['quality']['avif'] ?? 60;

        return new Process([
            $this->bin('avifenc'),
            '-q',
            (string) $quality,
            $path,
            $outputPath
        ]);
    }

    protected function bin(string $key): string
    {
        if (!empty($this->config['cli'][$key])) {
            $path = $this->config['cli'][$key];
            if (is_executable($path)) {
                return $path;
            }
        }

        $binaryName = $key === 'mozjpeg' ? 'cjpeg' : $key;
        $result = trim(shell_exec("which {$binaryName} 2>/dev/null") ?: '');

        if (empty($result) || !is_executable($result)) {
            throw new RuntimeException("{$key} binary not found in PATH or config");
        }

        return $result;
    }

    protected function fallbackToGd(string $path, string $format, int $originalSize): array
    {
        $tempOutput = tempnam(sys_get_temp_dir(), 'img_fallback_') . '.webp';

        try {
            if ($format === 'webp' || $format === 'avif') {
                $quality = is_numeric($this->config['quality']['webp']) ?
                    (int) $this->config['quality']['webp'] : 80;
                ImageProcessor::convertToWebP($path, $tempOutput, $quality);
            } else {
                $quality = $this->config['quality'][$format] ?? 85;
                if (is_string($quality)) {
                    if (str_contains($quality, '-')) {
                        $parts = explode('-', $quality);
                        $quality = (int) end($parts);
                    } else {
                        $quality = (int) $quality;
                    }
                }

                ImageProcessor::optimizeImage($path, $tempOutput, $quality);
            }

            $binary = file_get_contents($tempOutput);
            $newSize = strlen($binary);

            return [
                'binary' => $binary,
                'original_kb' => round($originalSize / 1024, 2),
                'compressed_kb' => round($newSize / 1024, 2),
                'savings_pct' => round(100 * (1 - $newSize / $originalSize), 1),
                'format' => 'webp',
                'compression_method' => 'gd_fallback'
            ];
        } finally {
            if (file_exists($tempOutput)) {
                unlink($tempOutput);
            }
        }
    }

    protected function logCompressionResult(string $path, array $result): void
    {
        Log::info("Image compressed successfully", [
            'source' => basename($path),
            'original_kb' => $result['original_kb'],
            'compressed_kb' => $result['compressed_kb'],
            'savings_pct' => $result['savings_pct'],
            'format' => $result['format'],
            'method' => $result['compression_method']
        ]);
    }
}
