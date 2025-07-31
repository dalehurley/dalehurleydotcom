<?php

namespace App\Console\Commands;

use App\Services\ImageCompressionService;
use Illuminate\Console\Command;

class TestImageCompression extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:test-compression
                          {image? : Path to test image (optional, will download one if not provided)}
                          {--format= : Target format (png, jpeg, webp, avif)}
                          {--check-tools : Only check available tools}
                          {--download : Force download new test image even if one exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test image compression with available CLI tools using real sample images';

    /**
     * Execute the console command.
     */
    public function handle(ImageCompressionService $compressor): int
    {
        if ($this->option('check-tools')) {
            return $this->checkTools($compressor);
        }

        $imagePath = $this->argument('image');
        $format = $this->option('format');

        // Download or use existing test image if none provided
        if (!$imagePath) {
            $imagePath = $this->getTestImage($format);
            $this->info("Using test image: {$imagePath}");
        }

        if (!file_exists($imagePath)) {
            $this->error("Image file does not exist: {$imagePath}");
            return 1;
        }

        try {
            $this->info("Compressing image: {$imagePath}");
            if ($format) {
                $this->info("Target format: {$format}");
            } else {
                $this->info("Keeping original format");
            }

            $result = $compressor->compress($imagePath, $format);

            $this->table(['Metric', 'Value'], [
                ['Original Size', $result['original_kb'] . ' KB'],
                ['Compressed Size', $result['compressed_kb'] . ' KB'],
                ['Savings', $result['savings_pct'] . '%'],
                ['Format', $result['format']],
                ['Method', $result['compression_method'] ?? 'cli'],
            ]);

            // Save compressed image
            $outputPath = dirname($imagePath) . '/compressed_' . basename($imagePath, '.jpg') . '.' . $result['format'];
            file_put_contents($outputPath, $result['binary']);
            $this->info("Compressed image saved to: {$outputPath}");

            return 0;

        } catch (\Exception $e) {
            $this->error("Compression failed: " . $e->getMessage());
            return 1;
        }
    }

    private function checkTools(ImageCompressionService $compressor): int
    {
        $this->info('Checking available compression tools...');

        $tools = $compressor->checkTools();
        $formats = $compressor->getAvailableFormats();

        $this->table(['Tool', 'Available'], array_map(function ($tool, $available) {
            return [$tool, $available ? '✅ Yes' : '❌ No'];
        }, array_keys($tools), $tools));

        $this->info('Available formats: ' . implode(', ', $formats));

        if (empty($formats)) {
            $this->warn('No compression tools available. Install pngquant, mozjpeg, cwebp, or avifenc.');
            return 1;
        }

        return 0;
    }

    private function getTestImage(?string $targetFormat = null): string
    {
        $testImages = [
            'jpeg' => [
                'url' => 'https://picsum.photos/800/600.jpg',
                'filename' => 'test_sample.jpg',
                'description' => 'Random JPEG photo from Lorem Picsum'
            ],
            'png' => [
                'url' => 'https://httpbin.org/image/png',
                'filename' => 'test_sample.png',
                'description' => 'PNG test image'
            ],
            'webp' => [
                'url' => 'https://www.gstatic.com/webp/gallery/1.webp',
                'filename' => 'test_sample.webp',
                'description' => 'WebP sample image from Google'
            ],
            'avif' => [
                'url' => 'https://github.com/AOMediaCodec/av1-avif/raw/master/testFiles/Link-U/kimono.avif',
                'filename' => 'test_sample.avif',
                'description' => 'AVIF sample image'
            ]
        ];

        // Default to JPEG if no format specified
        $format = $targetFormat ?? 'jpeg';

        // Use JPEG for unknown formats
        if (!isset($testImages[$format])) {
            $format = 'jpeg';
        }

        $imageInfo = $testImages[$format];
        $cachePath = storage_path('app/' . $imageInfo['filename']);

        // Download if file doesn't exist or if forced download
        if (!file_exists($cachePath) || $this->option('download')) {
            $this->info("Downloading {$imageInfo['description']}...");

            try {
                $imageData = file_get_contents($imageInfo['url']);

                if ($imageData === false) {
                    $this->warn("Failed to download {$format} image, creating fallback...");
                    return $this->createFallbackImage($format);
                }

                if (file_put_contents($cachePath, $imageData) === false) {
                    $this->error("Failed to save downloaded image");
                    return $this->createFallbackImage($format);
                }

                $this->info("Downloaded: {$cachePath} (" . number_format(strlen($imageData) / 1024, 1) . " KB)");

            } catch (\Exception $e) {
                $this->warn("Download failed ({$e->getMessage()}), creating fallback...");
                return $this->createFallbackImage($format);
            }
        }

        return $cachePath;
    }

    private function createFallbackImage(string $format = 'jpeg'): string
    {
        $extension = $format === 'jpeg' ? 'jpg' : $format;
        $path = storage_path("app/fallback_test_{$format}_" . time() . ".{$extension}");

        $image = imagecreatetruecolor(800, 600);

        // Create a colorful gradient pattern
        for ($y = 0; $y < 600; $y++) {
            for ($x = 0; $x < 800; $x++) {
                $red = (int) (128 + 127 * sin($x / 100) * cos($y / 100));
                $green = (int) (128 + 127 * sin($x / 80) * sin($y / 80));
                $blue = (int) (128 + 127 * cos($x / 120) * cos($y / 60));

                $color = imagecolorallocate($image, $red, $green, $blue);
                imagesetpixel($image, $x, $y, $color);
            }
        }

        // Add format-specific text
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagestring($image, 5, 250, 250, "FALLBACK {$format} TEST", $white);
        imagestring($image, 3, 300, 300, 'Generated locally', $black);

        // Save in the appropriate format
        switch ($format) {
            case 'png':
                imagepng($image, $path);
                break;
            case 'webp':
                imagewebp($image, $path);
                break;
            default: // jpeg and others
                imagejpeg($image, $path, 95);
                break;
        }

        imagedestroy($image);
        return $path;
    }
}
