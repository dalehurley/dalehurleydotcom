<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ImageProcessor
{
    /**
     * Create a thumbnail from an image using native PHP
     *
     * @param string $sourcePath
     * @param string $thumbnailPath
     * @param int $width
     * @param int $height
     * @param int $quality Quality for WebP compression (0-100)
     * @return void
     * @throws \Exception
     */
    public static function createThumbnail(string $sourcePath, string $thumbnailPath, int $width, int $height, int $quality = 90): void
    {
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            throw new \Exception("Could not get image information for: {$sourcePath}");
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Create source image resource based on mime type
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \Exception("Unsupported image type: {$mimeType}");
        }

        if (!$sourceImage) {
            throw new \Exception("Could not create image resource from: {$sourcePath}");
        }

        // Calculate thumbnail dimensions maintaining aspect ratio
        $aspectRatio = $sourceWidth / $sourceHeight;
        if ($aspectRatio > 1) {
            // Landscape
            $thumbnailWidth = $width;
            $thumbnailHeight = (int) ($width / $aspectRatio);
        } else {
            // Portrait or square
            $thumbnailHeight = $height;
            $thumbnailWidth = (int) ($height * $aspectRatio);
        }

        // Create thumbnail image
        $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

        // Preserve transparency for PNG and WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefill($thumbnail, 0, 0, $transparent);
        }

        // Resize image
        imagecopyresampled(
            $thumbnail,
            $sourceImage,
            0,
            0,
            0,
            0,
            $thumbnailWidth,
            $thumbnailHeight,
            $sourceWidth,
            $sourceHeight
        );

        // Save thumbnail as WebP
        imagewebp($thumbnail, $thumbnailPath, $quality);

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
    }

    /**
     * Optimize an image by compressing it
     *
     * @param string $sourcePath
     * @param string $outputPath
     * @param int $quality Quality for compression (0-100)
     * @param int|null $maxWidth Maximum width to resize to (optional)
     * @param int|null $maxHeight Maximum height to resize to (optional)
     * @return array Image info after optimization
     * @throws \Exception
     */
    public static function optimizeImage(string $sourcePath, string $outputPath = null, int $quality = 85, int $maxWidth = null, int $maxHeight = null): array
    {
        $outputPath = $outputPath ?: $sourcePath;

        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            throw new \Exception("Could not get image information for: {$sourcePath}");
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Create source image resource
        $sourceImage = self::createImageResource($sourcePath, $mimeType);

        // Calculate new dimensions if max width/height specified
        $newWidth = $sourceWidth;
        $newHeight = $sourceHeight;

        if ($maxWidth || $maxHeight) {
            $aspectRatio = $sourceWidth / $sourceHeight;

            if ($maxWidth && $maxHeight) {
                // Both constraints - fit within bounds
                if ($sourceWidth > $maxWidth || $sourceHeight > $maxHeight) {
                    if ($aspectRatio > 1) {
                        $newWidth = min($maxWidth, $sourceWidth);
                        $newHeight = (int) ($newWidth / $aspectRatio);
                    } else {
                        $newHeight = min($maxHeight, $sourceHeight);
                        $newWidth = (int) ($newHeight * $aspectRatio);
                    }
                }
            } elseif ($maxWidth && $sourceWidth > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int) ($newWidth / $aspectRatio);
            } elseif ($maxHeight && $sourceHeight > $maxHeight) {
                $newHeight = $maxHeight;
                $newWidth = (int) ($newHeight * $aspectRatio);
            }
        }

        // Create optimized image
        $optimizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($optimizedImage, false);
            imagesavealpha($optimizedImage, true);
            $transparent = imagecolorallocatealpha($optimizedImage, 255, 255, 255, 127);
            imagefill($optimizedImage, 0, 0, $transparent);
        }

        // Resize/copy image
        imagecopyresampled(
            $optimizedImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $sourceWidth,
            $sourceHeight
        );

        // Save optimized image as WebP (best compression)
        $success = imagewebp($optimizedImage, $outputPath, $quality);

        if (!$success) {
            throw new \Exception("Failed to save optimized image to: {$outputPath}");
        }

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($optimizedImage);

        // Return optimization info
        $originalSize = filesize($sourcePath);
        $optimizedSize = filesize($outputPath);
        $compressionRatio = round((1 - ($optimizedSize / $originalSize)) * 100, 2);

        Log::info("Image optimized: {$sourcePath} -> {$outputPath}", [
            'original_size' => self::formatBytes($originalSize),
            'optimized_size' => self::formatBytes($optimizedSize),
            'compression_ratio' => "{$compressionRatio}%",
            'original_dimensions' => "{$sourceWidth}x{$sourceHeight}",
            'new_dimensions' => "{$newWidth}x{$newHeight}"
        ]);

        return [
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'compression_ratio' => $compressionRatio,
            'original_dimensions' => [$sourceWidth, $sourceHeight],
            'new_dimensions' => [$newWidth, $newHeight]
        ];
    }

    /**
     * Create multiple sizes of an image (responsive images)
     *
     * @param string $sourcePath
     * @param string $baseOutputPath Path without extension
     * @param array $sizes Array of ['width' => int, 'height' => int, 'suffix' => string]
     * @param int $quality
     * @return array Paths of created images
     * @throws \Exception
     */
    public static function createResponsiveImages(string $sourcePath, string $baseOutputPath, array $sizes, int $quality = 85): array
    {
        $createdImages = [];

        foreach ($sizes as $size) {
            $outputPath = $baseOutputPath . '-' . $size['suffix'] . '.webp';

            self::createThumbnail(
                $sourcePath,
                $outputPath,
                $size['width'],
                $size['height'],
                $quality
            );

            $createdImages[$size['suffix']] = $outputPath;
        }

        return $createdImages;
    }

    /**
     * Convert an image to WebP format
     *
     * @param string $sourcePath
     * @param string $outputPath
     * @param int $quality
     * @return bool
     * @throws \Exception
     */
    public static function convertToWebP(string $sourcePath, string $outputPath, int $quality = 85): bool
    {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            throw new \Exception("Could not get image information for: {$sourcePath}");
        }

        $mimeType = $imageInfo['mime'];
        $sourceImage = self::createImageResource($sourcePath, $mimeType);

        $success = imagewebp($sourceImage, $outputPath, $quality);
        imagedestroy($sourceImage);

        if (!$success) {
            throw new \Exception("Failed to convert image to WebP: {$sourcePath}");
        }

        return true;
    }

    /**
     * Create image resource from file based on mime type
     *
     * @param string $sourcePath
     * @param string $mimeType
     * @return \GdImage
     * @throws \Exception
     */
    private static function createImageResource(string $sourcePath, string $mimeType): \GdImage
    {
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($sourcePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new \Exception("Unsupported image type: {$mimeType}");
        }

        if (!$image) {
            throw new \Exception("Could not create image resource from: {$sourcePath}");
        }

        return $image;
    }

    /**
     * Format bytes into human readable format
     *
     * @param int $bytes
     * @return string
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Get optimized image settings based on image type and use case
     *
     * @param string $useCase 'thumbnail', 'hero', 'gallery', 'icon'
     * @return array
     */
    public static function getOptimizedSettings(string $useCase): array
    {
        $settings = [
            'thumbnail' => [
                'quality' => 85,
                'maxWidth' => 300,
                'maxHeight' => 200
            ],
            'hero' => [
                'quality' => 90,
                'maxWidth' => 1920,
                'maxHeight' => 1080
            ],
            'gallery' => [
                'quality' => 88,
                'maxWidth' => 1200,
                'maxHeight' => 800
            ],
            'icon' => [
                'quality' => 95,
                'maxWidth' => 64,
                'maxHeight' => 64
            ]
        ];

        return $settings[$useCase] ?? $settings['hero'];
    }
}
