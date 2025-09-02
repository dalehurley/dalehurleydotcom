<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BlogService
{
    protected $postsPath;
    protected $iframeProtection = [];

    public function __construct()
    {
        $this->postsPath = resource_path('views/posts');
    }

    /**
     * Get all blog posts with metadata
     */
    public function getAllPosts(): array
    {
        $posts = [];
        $directories = File::directories($this->postsPath);

        foreach ($directories as $directory) {
            $slug = basename($directory);

            // Skip the main posts directory
            if ($slug === 'posts') {
                continue;
            }

            $postFile = $directory . '/page.mdx';

            if (File::exists($postFile)) {
                $post = $this->parsePost($postFile, $slug);
                if ($post) {
                    $posts[] = $post;
                }
            }
        }

        // Sort posts by date (newest first)
        usort($posts, function ($a, $b) {
            $dateA = isset($a['date']) ? Carbon::parse($a['date']) : Carbon::now();
            $dateB = isset($b['date']) ? Carbon::parse($b['date']) : Carbon::now();
            return $dateB->getTimestamp() - $dateA->getTimestamp();
        });

        return $posts;
    }

    /**
     * Get a single post by slug
     */
    public function getPost(string $slug): ?array
    {
        $postFile = $this->postsPath . '/' . $slug . '/page.mdx';

        if (!File::exists($postFile)) {
            return null;
        }

        return $this->parsePost($postFile, $slug);
    }

    /**
     * Parse a post file to extract metadata and content
     */
    protected function parsePost(string $filePath, string $slug): ?array
    {
        $content = File::get($filePath);

        // Extract frontmatter
        $pattern = '/^---\s*\n(.*?)\n---\s*\n(.*)$/s';
        if (!preg_match($pattern, $content, $matches)) {
            return null;
        }

        $frontmatter = $this->parseFrontmatter($matches[1]);
        $body = trim($matches[2]);

        return array_merge($frontmatter, [
            'slug' => $slug,
            'content' => $body,
            'url' => '/posts/' . $slug,
            'reading_time' => $this->calculateReadingTime($body),
            'word_count' => str_word_count(strip_tags($body)),
        ]);
    }

    /**
     * Parse YAML frontmatter
     */
    protected function parseFrontmatter(string $yaml): array
    {
        $data = [];
        $lines = explode("\n", $yaml);
        $currentKey = null;
        $collectingArray = false;
        $arrayItems = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Handle array continuation (lines starting with -)
            if ($collectingArray && str_starts_with($line, '-')) {
                $item = trim(substr($line, 1));
                $item = trim($item, '"\'[]');
                if (!empty($item)) {
                    $arrayItems[] = $item;
                }
                continue;
            }

            // End array collection if we hit a new key
            if ($collectingArray && strpos($line, ':') !== false) {
                $data[$currentKey] = $arrayItems;
                $collectingArray = false;
                $arrayItems = [];
                $currentKey = null;
            }

            // Handle key: value pairs
            if (strpos($line, ':') !== false) {
                [$key, $value] = array_map('trim', explode(':', $line, 2));
                $currentKey = $key;

                // Clean up the value
                $value = trim($value, '"\'');

                // Handle inline arrays (tags: ['AI', 'Technology'])
                if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
                    $value = trim($value, '[]');
                    $items = explode(',', $value);
                    $data[$key] = array_map(function ($item) {
                        return trim($item, '"\'');
                    }, $items);
                }
                // Handle start of multi-line array
                elseif (empty($value) || $value === '[') {
                    $collectingArray = true;
                    $arrayItems = [];
                }
                // Handle regular values
                else {
                    $data[$key] = $value;
                }
            }
        }

        // Handle any remaining array
        if ($collectingArray && $currentKey) {
            $data[$currentKey] = $arrayItems;
        }

        // Ensure tags is always an array
        if (isset($data['tags']) && !is_array($data['tags'])) {
            $data['tags'] = [];
        }

        // Handle image and thumbnail paths
        if (isset($data['image']) && !str_starts_with($data['image'], '/')) {
            $data['image'] = '/' . $data['image'];
        }
        if (isset($data['thumbnail']) && !str_starts_with($data['thumbnail'], '/')) {
            $data['thumbnail'] = '/' . $data['thumbnail'];
        }

        return $data;
    }

    /**
     * Get featured posts (latest 3)
     */
    public function getFeaturedPosts(int $limit = 3): array
    {
        return array_slice($this->getAllPosts(), 0, $limit);
    }

    /**
     * Search posts by title, description, or tags
     */
    public function searchPosts(string $query): array
    {
        $posts = $this->getAllPosts();
        $query = strtolower($query);

        return array_filter($posts, function ($post) use ($query) {
            $searchFields = [
                strtolower($post['title'] ?? ''),
                strtolower($post['description'] ?? ''),
                strtolower(implode(' ', $post['tags'] ?? [])),
            ];

            foreach ($searchFields as $field) {
                if (str_contains($field, $query)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Get posts by tag
     */
    public function getPostsByTag(string $tag): array
    {
        $posts = $this->getAllPosts();

        return array_filter($posts, function ($post) use ($tag) {
            return in_array($tag, $post['tags'] ?? []);
        });
    }

    /**
     * Get post content without frontmatter for rendering
     */
    public function getPostContentForRender(string $slug): ?array
    {
        $post = $this->getPost($slug);

        if (!$post) {
            return null;
        }

        // Reset iframe protection for this request
        $this->iframeProtection = [];

        // Convert MDX components to Blade components before markdown conversion
        $processedContent = $this->convertMdxComponentsToBlade($post['content']);

        // Convert MDX content to HTML using Str::markdown
        $htmlContent = Str::markdown($processedContent);

        // Restore protected iframe tags
        foreach ($this->iframeProtection as $placeholder => $iframe) {
            $htmlContent = str_replace($placeholder, $iframe, $htmlContent);
        }

        return array_merge($post, [
            'html_content' => $htmlContent
        ]);
    }

    /**
     * Convert MDX components to Blade components
     */
    protected function convertMdxComponentsToBlade(string $content): string
    {
        // First, protect iframe tags from markdown processing by replacing them with placeholders
        $iframeProtection = [];
        $content = preg_replace_callback(
            '/<iframe[^>]*>.*?<\/iframe>/s',
            function ($matches) use (&$iframeProtection) {
                $placeholder = '<!-- IFRAME_PLACEHOLDER_' . count($iframeProtection) . ' -->';
                // Add responsive wrapper and styling to iframe
                $iframe = $matches[0];
                // Add responsive classes to the iframe itself
                $iframe = preg_replace('/(<iframe[^>]*)(>)/', '$1 class="absolute inset-0 w-full h-full"$2', $iframe);
                $styledIframe = '<div class="my-8 relative aspect-video overflow-hidden rounded-lg shadow-lg">' . $iframe . '</div>';
                $iframeProtection[$placeholder] = $styledIframe;
                return $placeholder;
            },
            $content
        );

        // Convert Callout components (handle both self-closing and regular)
        $content = preg_replace_callback(
            '/<Callout(?:\s+type="([^"]*)")?[^>]*>(?:(.*?)<\/Callout>)?/s',
            function ($matches) {
                $type = $matches[1] ?? 'info';
                $content = isset($matches[2]) ? trim($matches[2]) : '';
                return "<x-blog-callout type=\"{$type}\">\n{$content}\n</x-blog-callout>";
            },
            $content
        );

        // Convert Steps components
        $content = preg_replace_callback(
            '/<Steps[^>]*>(.*?)<\/Steps>/s',
            function ($matches) {
                $content = trim($matches[1]);

                // Process steps content to add proper structure
                $steps = $this->processStepsContent($content);

                return "<x-blog-steps>\n<div class=\"steps-container\">\n{$steps}\n</div>\n</x-blog-steps>";
            },
            $content
        );

        // Convert Image components (handle various formats)
        $content = preg_replace_callback(
            '/<img\s+([^>]*)\/?>/',
            function ($matches) {
                $attributes = $matches[1];

                // Extract src
                preg_match('/src="([^"]*)"/', $attributes, $srcMatch);
                $src = $srcMatch[1] ?? '';

                // Extract alt
                preg_match('/alt="([^"]*)"/', $attributes, $altMatch);
                $alt = $altMatch[1] ?? '';

                // Extract width
                preg_match('/width="?(\d+)"?/', $attributes, $widthMatch);
                $width = $widthMatch[1] ?? '';

                // Extract height
                preg_match('/height="?(\d+)"?/', $attributes, $heightMatch);
                $height = $heightMatch[1] ?? '';

                $imgAttributes = "src=\"{$src}\" alt=\"{$alt}\" class=\"mx-auto rounded-lg shadow-lg\"";
                if ($width) $imgAttributes .= " width=\"{$width}\"";
                if ($height) $imgAttributes .= " height=\"{$height}\"";

                return "<img {$imgAttributes} />";
            },
            $content
        );

        // Store iframe protection for later restoration
        $this->iframeProtection = $iframeProtection;

        return $content;
    }

    /**
     * Process steps content to add proper HTML structure
     */
    protected function processStepsContent(string $content): string
    {
        // Split content by ### headers which typically denote individual steps
        $lines = explode("\n", $content);
        $steps = [];
        $currentStep = [];

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '### ')) {
                // If we have a current step, save it
                if (!empty($currentStep)) {
                    $steps[] = implode("\n", $currentStep);
                }
                // Start new step
                $currentStep = [$line];
            } else {
                // Add line to current step
                $currentStep[] = $line;
            }
        }

        // Don't forget the last step
        if (!empty($currentStep)) {
            $steps[] = implode("\n", $currentStep);
        }

        // If no ### headers found, treat the whole content as one step
        if (empty($steps)) {
            $steps = [$content];
        }

        $processedSteps = [];
        foreach ($steps as $step) {
            $step = trim($step);
            if (empty($step)) continue;

            $processedSteps[] = "<div class=\"step-item\">\n{$step}\n</div>";
        }

        return implode("\n", $processedSteps);
    }

    /**
     * Calculate estimated reading time for content
     */
    protected function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $wordsPerMinute = 200; // Average reading speed

        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Generate keywords from post content
     */
    protected function generateKeywords(array $post): string
    {
        $baseKeywords = ['Dale Hurley'];

        // Add tags if available
        if (isset($post['tags']) && is_array($post['tags'])) {
            $baseKeywords = array_merge($baseKeywords, $post['tags']);
        }

        // Add category-specific keywords based on content
        $content = strtolower($post['content'] ?? '');

        if (str_contains($content, 'ai') || str_contains($content, 'artificial intelligence')) {
            $baseKeywords[] = 'AI';
            $baseKeywords[] = 'artificial intelligence';
        }

        if (str_contains($content, 'fintech') || str_contains($content, 'banking')) {
            $baseKeywords[] = 'fintech';
            $baseKeywords[] = 'banking innovation';
        }

        if (str_contains($content, 'entrepreneur') || str_contains($content, 'startup')) {
            $baseKeywords[] = 'entrepreneurship';
            $baseKeywords[] = 'startup';
        }

        if (str_contains($content, 'laravel') || str_contains($content, 'php')) {
            $baseKeywords[] = 'Laravel';
            $baseKeywords[] = 'PHP';
        }

        return implode(', ', array_unique($baseKeywords));
    }
}
