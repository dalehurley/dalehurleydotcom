<?php

namespace App\Console\Commands;

use App\Services\BlogService;
use App\Services\Agents\ImageAgent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateBlogImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:generate-images {--post= : Generate image for specific post slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate images for blog posts using AI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $blogService = new BlogService();
        $specificPost = $this->option('post');

        if ($specificPost) {
            // Generate image for specific post
            $post = $blogService->getPost($specificPost);
            if (!$post) {
                $this->error("Post '{$specificPost}' not found.");
                return 1;
            }

            $this->generateImageForPost($post);
        } else {
            // Show all posts and let user select
            $posts = $blogService->getAllPosts();

            if (empty($posts)) {
                $this->error('No blog posts found.');
                return 1;
            }

            $choices = ['All posts'];
            foreach ($posts as $post) {
                $choices[] = $post['title'] . ' (' . $post['slug'] . ')';
            }

            $selected = $this->choice('Which posts would you like to generate images for?', $choices, 0);

            if ($selected === 'All posts') {
                $this->info('Generating images for all posts...');
                $bar = $this->output->createProgressBar(count($posts));
                $bar->start();

                foreach ($posts as $post) {
                    $this->generateImageForPost($post);
                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
                $this->info('Completed generating images for all posts!');
            } else {
                // Find the selected post
                $selectedIndex = array_search($selected, $choices) - 1; // -1 because "All posts" is at index 0
                $selectedPost = $posts[$selectedIndex];
                $this->generateImageForPost($selectedPost);
            }
        }

        return 0;
    }

    /**
     * Generate image for a specific post
     */
    private function generateImageForPost(array $post): void
    {
        $this->info("Generating image for: {$post['title']}");

        try {
            // Generate image and thumbnail
            $imagePaths = ImageAgent::generateImage($post);

            // Update the MDX file with image metadata
            $this->updateMdxWithImage($post, $imagePaths);

            $this->info("✅ Generated image and thumbnail for '{$post['title']}'");
            $this->line("   Image: {$imagePaths['image_path']}");
            $this->line("   Thumbnail: {$imagePaths['thumbnail_path']}");
        } catch (\Exception $e) {
            $this->error("❌ Failed to generate image for '{$post['title']}': " . $e->getMessage());
        }
    }

    /**
     * Update the MDX file with image metadata
     */
    private function updateMdxWithImage(array $post, array $imagePaths): void
    {
        $mdxPath = resource_path('views/posts/' . $post['slug'] . '/page.mdx');

        if (!File::exists($mdxPath)) {
            $this->warn("MDX file not found: {$mdxPath}");
            return;
        }

        $content = File::get($mdxPath);

        // Parse frontmatter
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
            $frontmatter = $matches[1];
            $body = $matches[2];

            // Add or update image fields in frontmatter
            $lines = explode("\n", $frontmatter);
            $updatedLines = [];
            $imageAdded = false;
            $thumbnailAdded = false;

            foreach ($lines as $line) {
                if (str_starts_with($line, 'image:')) {
                    $updatedLines[] = 'image: ' . $imagePaths['image_path'];
                    $imageAdded = true;
                } elseif (str_starts_with($line, 'thumbnail:')) {
                    $updatedLines[] = 'thumbnail: ' . $imagePaths['thumbnail_path'];
                    $thumbnailAdded = true;
                } else {
                    $updatedLines[] = $line;
                }
            }

            // Add image and thumbnail fields if they don't exist
            if (!$imageAdded) {
                $updatedLines[] = 'image: ' . $imagePaths['image_path'];
            }
            if (!$thumbnailAdded) {
                $updatedLines[] = 'thumbnail: ' . $imagePaths['thumbnail_path'];
            }

            $updatedContent = "---\n" . implode("\n", $updatedLines) . "\n---\n" . $body;
            File::put($mdxPath, $updatedContent);
        }
    }
}
