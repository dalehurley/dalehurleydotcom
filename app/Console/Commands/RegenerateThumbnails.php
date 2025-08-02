<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ThumbnailService;
use App\Services\BlogService;

class RegenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thumbnails:regenerate
                            {--post= : Specific blog post slug to process}
                            {--all : Process all blog posts, including those that already have thumbnails}
                            {--missing : Only process blog posts that don\'t have thumbnails (default)}
                            {--width=384 : Thumbnail width}
                            {--height=256 : Thumbnail height}
                            {--quality=85 : Compression quality (0-100)}
                            {--dry-run : Show what would be processed without actually creating thumbnails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate thumbnails for blog post images';

    protected $thumbnailService;
    protected $blogService;

    public function __construct(ThumbnailService $thumbnailService, BlogService $blogService)
    {
        parent::__construct();
        $this->thumbnailService = $thumbnailService;
        $this->blogService = $blogService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🖼️  Blog Thumbnail Regeneration Tool');
        $this->newLine();

        // Get options
        $specificPost = $this->option('post');
        $processAll = $this->option('all');
        $processMissing = $this->option('missing') || (!$processAll && !$specificPost);
        $width = (int) $this->option('width');
        $height = (int) $this->option('height');
        $quality = (int) $this->option('quality');
        $dryRun = $this->option('dry-run');

        // Validate dimensions and quality
        if ($width <= 0 || $height <= 0) {
            $this->error('Width and height must be positive integers');
            return 1;
        }

        if ($quality < 0 || $quality > 100) {
            $this->error('Quality must be between 0 and 100');
            return 1;
        }

        $this->info("Settings:");
        $this->line("  • Dimensions: {$width}x{$height}");
        $this->line("  • Quality: {$quality}%");
        $this->line("  • Mode: " . ($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->newLine();

        // Process specific blog post
        if ($specificPost) {
            return $this->processSpecificPost($specificPost, $width, $height, $quality, $dryRun);
        }

        // Get blog posts to process
        if ($processAll) {
            $posts = $this->thumbnailService->getAllBlogPostsWithImages();
            $this->info("📁 Processing ALL blog posts with images");
        } else {
            $posts = $this->thumbnailService->getBlogPostsWithoutThumbnails();
            $this->info("📁 Processing blog posts WITHOUT thumbnails");
        }

        if (empty($posts)) {
            $this->info('✅ No blog posts to process!');
            return 0;
        }

        $this->info("Found " . count($posts) . " blog post(s) to process:");
        foreach ($posts as $post) {
            $this->line("  • {$post['title']} ({$post['slug']})");
        }
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No thumbnails will be created');
            return 0;
        }

        // Confirm before proceeding
        if (!$this->confirm('Proceed with thumbnail generation?', true)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Process blog posts
        return $this->processBatch($posts, $width, $height, $quality);
    }

    /**
     * Process a specific blog post
     */
    protected function processSpecificPost(string $postSlug, int $width, int $height, int $quality, bool $dryRun): int
    {
        $this->info("🎯 Processing specific blog post: {$postSlug}");

        $post = $this->blogService->getPost($postSlug);

        if (!$post) {
            $this->error("❌ Blog post not found: {$postSlug}");

            // Show available posts
            $allPosts = $this->blogService->getAllPosts();
            if (!empty($allPosts)) {
                $this->line("Available blog posts:");
                foreach ($allPosts as $availablePost) {
                    $this->line("  • {$availablePost['slug']} - {$availablePost['title']}");
                }
            }
            return 1;
        }

        if ($dryRun) {
            $this->warn('🔍 DRY RUN - Would process: ' . $post['title'] . ' (' . $post['slug'] . ')');
            return 0;
        }

        $this->info("Processing: {$post['title']}");

        $result = $this->thumbnailService->createThumbnailForBlogPost($post, $width, $height, $quality);

        if ($result['success']) {
            $this->info("✅ Success: " . basename($result['path']));
            if ($result['stats']) {
                $this->line("  📊 {$result['stats']['original_kb']}KB → {$result['stats']['compressed_kb']}KB ({$result['stats']['savings_pct']}% savings)");
            }
        } else {
            $this->error("❌ Failed: " . $result['error']);
            return 1;
        }

        return 0;
    }

    /**
     * Process multiple blog posts
     */
    protected function processBatch(array $posts, int $width, int $height, int $quality): int
    {
        $this->info('🚀 Starting batch processing...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar(count($posts));
        $progressBar->setFormat('verbose');

        $results = $this->thumbnailService->batchCreateThumbnailsForBlogPosts($posts, $width, $height, $quality);

        $processed = 0;
        foreach ($results['details'] as $detail) {
            $progressBar->setMessage("Processing: " . $detail['post']);
            $progressBar->advance();
            $processed++;
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->displayResults($results);

        return $results['failed'] > 0 ? 1 : 0;
    }

    /**
     * Display processing results
     */
    protected function displayResults(array $results): void
    {
        $this->info('📊 Processing Complete!');
        $this->newLine();

        $this->info("Results:");
        $this->line("  • Total: {$results['total']}");
        $this->line("  • <fg=green>Success: {$results['success']}</>");
        $this->line("  • <fg=red>Failed: {$results['failed']}</>");

        if ($results['failed'] > 0) {
            $this->newLine();
            $this->error('❌ Errors encountered:');
            foreach ($results['details'] as $detail) {
                if (!$detail['success']) {
                    $this->line("  • {$detail['post']}: {$detail['error']}");
                }
            }
        }

        if ($results['success'] > 0) {
            $this->newLine();
            $this->info('✅ Thumbnails have been regenerated successfully!');
            $this->line('Check the public/images directory for the new thumbnail files.');
        }
    }
}
