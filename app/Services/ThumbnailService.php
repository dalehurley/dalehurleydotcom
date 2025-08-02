<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Services\BlogService;

class ThumbnailService
{
    protected $imageCompressionService;
    protected $blogService;

    public function __construct(ImageCompressionService $imageCompressionService, BlogService $blogService)
    {
        $this->imageCompressionService = $imageCompressionService;
        $this->blogService = $blogService;
    }

    /**
     * Create and optimize a thumbnail for an image
     *
     * @param string $sourcePath Full path to the source image
     * @param string $thumbnailPath Full path where the thumbnail should be saved
     * @param int $width Target width for the thumbnail
     * @param int $height Target height for the thumbnail
     * @param int $quality Quality for thumbnail compression (0-100)
     * @param string|null $slug Optional slug for logging purposes
     * @return array ['success' => bool, 'path' => string, 'error' => string|null]
     */
    public function createThumbnail(
        string $sourcePath,
        string $thumbnailPath,
        int $width = 384,
        int $height = 256,
        int $quality = 85,
        ?string $slug = null
    ): array {
        try {
            // Ensure source file exists
            if (!File::exists($sourcePath)) {
                throw new \Exception("Source image not found: {$sourcePath}");
            }

            // Ensure thumbnail directory exists
            File::ensureDirectoryExists(dirname($thumbnailPath));

            // Create thumbnail using ImageProcessor
            ImageProcessor::createThumbnail($sourcePath, $thumbnailPath, $width, $height, $quality);

            // Compress the thumbnail using CLI tools
            try {
                $thumbnailOptimized = $this->imageCompressionService->compress($thumbnailPath, 'webp');
                file_put_contents($thumbnailPath, $thumbnailOptimized['binary']);

                $logData = [
                    'source' => basename($sourcePath),
                    'thumbnail' => basename($thumbnailPath),
                    'original_kb' => $thumbnailOptimized['original_kb'],
                    'compressed_kb' => $thumbnailOptimized['compressed_kb'],
                    'savings_pct' => $thumbnailOptimized['savings_pct']
                ];

                if ($slug) {
                    $logData['slug'] = $slug;
                }

                Log::info("Thumbnail created and compressed", $logData);

                return [
                    'success' => true,
                    'path' => $thumbnailPath,
                    'error' => null,
                    'stats' => $thumbnailOptimized
                ];
            } catch (\Exception $e) {
                $errorMsg = "Failed to compress thumbnail: " . $e->getMessage();
                Log::warning($errorMsg, [
                    'slug' => $slug,
                    'source' => $sourcePath,
                    'thumbnail' => $thumbnailPath
                ]);

                // Continue with uncompressed thumbnail
                return [
                    'success' => true,
                    'path' => $thumbnailPath,
                    'error' => $errorMsg,
                    'stats' => null
                ];
            }
        } catch (\Exception $e) {
            $errorMsg = "Failed to create thumbnail: " . $e->getMessage();
            Log::error($errorMsg, [
                'slug' => $slug,
                'source' => $sourcePath,
                'thumbnail' => $thumbnailPath
            ]);

            return [
                'success' => false,
                'path' => null,
                'error' => $errorMsg,
                'stats' => null
            ];
        }
    }

    /**
     * Regenerate thumbnail for a specific image file
     *
     * @param string $imagePath Path to the main image (can be relative to public or absolute)
     * @param int $width Target width for the thumbnail
     * @param int $height Target height for the thumbnail
     * @param int $quality Quality for thumbnail compression (0-100)
     * @return array Result of thumbnail creation
     */
    public function regenerateThumbnail(
        string $imagePath,
        int $width = 384,
        int $height = 256,
        int $quality = 85
    ): array {
        // Normalize the path
        $fullImagePath = $this->normalizeImagePath($imagePath);

        if (!$fullImagePath) {
            return [
                'success' => false,
                'path' => null,
                'error' => "Image not found: {$imagePath}",
                'stats' => null
            ];
        }

        // Generate thumbnail path
        $thumbnailPath = $this->generateThumbnailPath($fullImagePath);

        // Extract slug from filename for logging
        $slug = pathinfo($fullImagePath, PATHINFO_FILENAME);
        $slug = str_replace(['-thumbnail', '_thumbnail'], '', $slug);

        return $this->createThumbnail($fullImagePath, $thumbnailPath, $width, $height, $quality, $slug);
    }

    /**
     * Batch regenerate thumbnails for multiple images
     *
     * @param array $imagePaths Array of image paths
     * @param int $width Target width for thumbnails
     * @param int $height Target height for thumbnails
     * @param int $quality Quality for thumbnail compression
     * @return array Summary of results
     */
    public function batchRegenerateThumbnails(
        array $imagePaths,
        int $width = 384,
        int $height = 256,
        int $quality = 85
    ): array {
        $results = [
            'total' => count($imagePaths),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($imagePaths as $imagePath) {
            $result = $this->regenerateThumbnail($imagePath, $width, $height, $quality);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'image' => $imagePath,
                'success' => $result['success'],
                'thumbnail' => $result['path'],
                'error' => $result['error'],
                'stats' => $result['stats']
            ];
        }

        return $results;
    }

    /**
     * Get all blog posts that don't have thumbnails or need thumbnail regeneration
     *
     * @return array Array of blog posts that need thumbnails
     */
    public function getBlogPostsWithoutThumbnails(): array
    {
        $posts = $this->blogService->getAllPosts();
        $postsNeedingThumbnails = [];

        foreach ($posts as $post) {
            // Check if the main image exists
            $imagePath = isset($post['image']) ? public_path($post['image']) : public_path('images/' . $post['slug'] . '.webp');

            if (!File::exists($imagePath)) {
                continue; // Skip if main image doesn't exist
            }

            // Check if thumbnail exists
            $thumbnailPath = isset($post['thumbnail']) ? public_path($post['thumbnail']) : public_path('images/' . $post['slug'] . '-thumbnail.webp');

            if (!File::exists($thumbnailPath)) {
                $postsNeedingThumbnails[] = $post;
            }
        }

        return $postsNeedingThumbnails;
    }

    /**
     * Get all blog posts that have images (for regenerating all thumbnails)
     *
     * @return array Array of all blog posts with images
     */
    public function getAllBlogPostsWithImages(): array
    {
        $posts = $this->blogService->getAllPosts();
        $postsWithImages = [];

        foreach ($posts as $post) {
            // Check if the main image exists
            $imagePath = isset($post['image']) ? public_path($post['image']) : public_path('images/' . $post['slug'] . '.webp');

            if (File::exists($imagePath)) {
                $postsWithImages[] = $post;
            }
        }

        return $postsWithImages;
    }

    /**
     * Create thumbnail for a specific blog post
     *
     * @param array $blogPost Blog post data
     * @param int $width Target width for the thumbnail
     * @param int $height Target height for the thumbnail
     * @param int $quality Quality for thumbnail compression (0-100)
     * @return array Result of thumbnail creation
     */
    public function createThumbnailForBlogPost(
        array $blogPost,
        int $width = 384,
        int $height = 256,
        int $quality = 85
    ): array {
        // Determine source image path
        $imagePath = isset($blogPost['image']) ? public_path($blogPost['image']) : public_path('images/' . $blogPost['slug'] . '.webp');

        if (!File::exists($imagePath)) {
            return [
                'success' => false,
                'path' => null,
                'error' => "Source image not found for blog post: {$blogPost['title']}",
                'stats' => null
            ];
        }

        // Determine thumbnail path
        $thumbnailPath = isset($blogPost['thumbnail']) ? public_path($blogPost['thumbnail']) : public_path('images/' . $blogPost['slug'] . '-thumbnail.webp');

        return $this->createThumbnail($imagePath, $thumbnailPath, $width, $height, $quality, $blogPost['slug']);
    }

    /**
     * Batch create thumbnails for multiple blog posts
     *
     * @param array $blogPosts Array of blog post data
     * @param int $width Target width for thumbnails
     * @param int $height Target height for thumbnails
     * @param int $quality Quality for thumbnail compression
     * @return array Summary of results
     */
    public function batchCreateThumbnailsForBlogPosts(
        array $blogPosts,
        int $width = 384,
        int $height = 256,
        int $quality = 85
    ): array {
        $results = [
            'total' => count($blogPosts),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($blogPosts as $blogPost) {
            $result = $this->createThumbnailForBlogPost($blogPost, $width, $height, $quality);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'post' => $blogPost['title'] . ' (' . $blogPost['slug'] . ')',
                'success' => $result['success'],
                'thumbnail' => $result['path'] ? basename($result['path']) : null,
                'error' => $result['error'],
                'stats' => $result['stats']
            ];
        }

        return $results;
    }

    /**
     * Normalize image path to absolute path
     *
     * @param string $imagePath
     * @return string|null
     */
    protected function normalizeImagePath(string $imagePath): ?string
    {
        // If it's already an absolute path and exists, return it
        if (File::exists($imagePath)) {
            return $imagePath;
        }

        // If it's a relative path starting with 'images/', convert to public path
        if (str_starts_with($imagePath, 'images/')) {
            $fullPath = public_path($imagePath);
            return File::exists($fullPath) ? $fullPath : null;
        }

        // Try treating it as relative to public/images
        $fullPath = public_path('images/' . ltrim($imagePath, '/'));
        return File::exists($fullPath) ? $fullPath : null;
    }

    /**
     * Generate thumbnail path from image path
     *
     * @param string $imagePath
     * @return string
     */
    protected function generateThumbnailPath(string $imagePath): string
    {
        $pathInfo = pathinfo($imagePath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];

        return $directory . '/' . $filename . '-thumbnail.webp';
    }
}
