<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ConvertMdxToBlade extends Command
{
    protected $signature = 'blog:convert-mdx {file} {--output=}';
    protected $description = 'Convert MDX file to Blade PHP file';

    public function handle()
    {
        $filePath = $this->argument('file');
        $outputPath = $this->option('output');

        if (!File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $content = File::get($filePath);
        $bladeContent = $this->convertMdxToBlade($content, $filePath);

        if ($outputPath) {
            File::put($outputPath, $bladeContent);
            $this->info("Converted file saved to: {$outputPath}");
        } else {
            // Auto-generate output path
            $directory = dirname($filePath);
            $outputPath = $directory . '/index.blade.php';
            File::put($outputPath, $bladeContent);
            $this->info("Converted file saved to: {$outputPath}");
        }

        return 0;
    }

    private function convertMdxToBlade(string $content, string $filePath): string
    {
        // Extract frontmatter
        $frontmatter = $this->extractFrontmatter($content);
        $bodyContent = $this->removeFrontmatter($content);

        // Get post slug from file path
        $postSlug = $this->getPostSlug($filePath);

        // Generate the Blade header
        $bladeHeader = $this->generateBladeHeader($postSlug);

        // Convert MDX content to Blade
        $bladeBody = $this->convertContentToBlade($bodyContent);

        // Wrap in blog layout
        $bladeContent = $bladeHeader . "\n\n<x-blog-layout :post=\"\$post\">\n" . $bladeBody . "\n</x-blog-layout>";

        return $bladeContent;
    }

    private function extractFrontmatter(string $content): array
    {
        $frontmatter = [];

        if (preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
            $yamlContent = $matches[1];
            $lines = explode("\n", $yamlContent);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (strpos($line, ':') !== false) {
                    [$key, $value] = explode(':', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    // Handle quoted values
                    if (preg_match('/^[\'"](.*)[\'"]\s*$/', $value, $valueMatches)) {
                        $value = $valueMatches[1];
                    }

                    $frontmatter[$key] = $value;
                }
            }
        }

        return $frontmatter;
    }

    private function removeFrontmatter(string $content): string
    {
        return preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
    }

    private function getPostSlug(string $filePath): string
    {
        $pathParts = explode('/', $filePath);
        $postsIndex = array_search('posts', $pathParts);

        if ($postsIndex !== false && isset($pathParts[$postsIndex + 1])) {
            return $pathParts[$postsIndex + 1];
        }

        return 'unknown';
    }

    private function generateBladeHeader(string $postSlug): string
    {
        return "<?php

use App\Services\BlogService;

\$blogService = new BlogService();
\$post = \$blogService->getPost('{$postSlug}');

if (!\$post) {
    abort(404);
}

?>";
    }

    private function convertContentToBlade(string $content): string
    {
        // Remove import statements
        $content = preg_replace('/^import\s+.*$/m', '', $content);

        // Convert code blocks first (before markdown conversion)
        $content = $this->convertCodeBlocks($content);

        // Convert MDX components to Blade components
        $content = $this->convertCallouts($content);

        // Convert remaining markdown to HTML
        $content = $this->convertMarkdownToHtml($content);

        // Clean up extra whitespace
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = trim($content);

        // Indent content for better formatting
        $lines = explode("\n", $content);
        $indentedLines = array_map(function ($line) {
            return empty(trim($line)) ? $line : '    ' . $line;
        }, $lines);

        return implode("\n", $indentedLines);
    }

    private function convertCallouts(string $content): string
    {
        // Convert  to <x-blog-callout type="info">
        $content = preg_replace_callback(
            '/<Callout\s+type="([^"]+)">\s*(.*?)\s*<\/Callout>/s',
            function ($matches) {
                $type = $matches[1];
                $calloutContent = trim($matches[2]);

                // Convert markdown within callout to HTML, but preserve structure
                $calloutContent = $this->convertMarkdownToHtmlSimple($calloutContent);

                return "<x-blog-callout type=\"{$type}\">\n        {$calloutContent}\n    </x-blog-callout>";
            },
            $content
        );

        return $content;
    }

    private function convertCodeBlocks(string $content): string
    {
        // Handle mermaid code blocks specially
        $content = preg_replace_callback(
            '/```mermaid\s*\n(.*?)\n```/s',
            function ($matches) {
                $mermaidContent = trim($matches[1]);
                return "<div class=\"my-6\">\n        <pre class=\"mermaid bg-gray-50 p-4 rounded\">\n{$mermaidContent}\n        </pre>\n    </div>";
            },
            $content
        );

        // Handle other code blocks - be careful with PHP code
        $content = preg_replace_callback(
            '/```(\w+)?\s*\n(.*?)\n```/s',
            function ($matches) {
                $language = $matches[1] ?? '';
                $codeContent = $matches[2];

                // Check if this contains PHP code that might interfere
                if (strpos($codeContent, '<?php') !== false) {
                    // Escape PHP tags in code blocks to prevent execution
                    $codeContent = str_replace('<?php', '&lt;?php', $codeContent);
                    $codeContent = str_replace('<?=', '&lt;?=', $codeContent);
                    $codeContent = str_replace('?>', '?&gt;', $codeContent);
                }

                return "<pre><code class=\"language-{$language}\">{$codeContent}</code></pre>";
            },
            $content
        );

        return $content;
    }

    private function convertMarkdownToHtml(string $content): string
    {
        // First handle code blocks to protect them
        $protectedBlocks = [];
        $blockIndex = 0;

        // Protect mermaid and code blocks
        $content = preg_replace_callback(
            '/(<div class="my-6">.*?<\/div>|<pre><code.*?<\/code><\/pre>)/s',
            function ($matches) use (&$protectedBlocks, &$blockIndex) {
                $placeholder = "___PROTECTED_BLOCK_{$blockIndex}___";
                $protectedBlocks[$placeholder] = $matches[0];
                $blockIndex++;
                return $placeholder;
            },
            $content
        );

        // Convert headers
        $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);

        // Convert bold text
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);

        // Convert italic text
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);

        // Convert links
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $content);

        // Convert unordered lists
        $content = preg_replace_callback(
            '/^(\* .*(?:\n\* .*)*)/m',
            function ($matches) {
                $listItems = explode("\n", trim($matches[1]));
                $htmlItems = array_map(function ($item) {
                    return '        <li>' . ltrim($item, '* ') . '</li>';
                }, $listItems);
                return "    <ul>\n" . implode("\n", $htmlItems) . "\n    </ul>";
            },
            $content
        );

        // Convert ordered lists
        $content = preg_replace_callback(
            '/^(\d+\. .*(?:\n\d+\. .*)*)/m',
            function ($matches) {
                $listItems = explode("\n", trim($matches[1]));
                $htmlItems = array_map(function ($item) {
                    return '        <li>' . preg_replace('/^\d+\. /', '', $item) . '</li>';
                }, $listItems);
                return "    <ol>\n" . implode("\n", $htmlItems) . "\n    </ol>";
            },
            $content
        );

        // Convert paragraphs (lines that aren't already HTML tags)
        $lines = explode("\n", $content);
        $result = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (empty($trimmed)) {
                $result[] = '';
                continue;
            }

            // Check if line is already an HTML tag, component, or protected block
            if (
                preg_match('/^<(\/?)([a-zA-Z\-]+)/', $trimmed) ||
                preg_match('/^___PROTECTED_BLOCK_\d+___$/', $trimmed)
            ) {
                $result[] = $line;
                continue;
            }

            // If it's a regular line of text, wrap in paragraph
            if (!preg_match('/^</', $trimmed)) {
                $result[] = '    <p>' . $trimmed . '</p>';
            } else {
                $result[] = $line;
            }
        }

        $content = implode("\n", $result);

        // Restore protected blocks
        foreach ($protectedBlocks as $placeholder => $block) {
            $content = str_replace($placeholder, $block, $content);
        }

        return $content;
    }

    private function convertMarkdownToHtmlSimple(string $content): string
    {
        // Convert bold text
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);

        // Convert italic text
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);

        // Convert links
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $content);

        // Convert unordered lists within callouts
        $content = preg_replace_callback(
            '/^(- .*(?:\n- .*)*)/m',
            function ($matches) {
                $listItems = explode("\n", trim($matches[1]));
                $htmlItems = array_map(function ($item) {
                    return '            <li>' . ltrim($item, '- ') . '</li>';
                }, $listItems);
                return "        <ul>\n" . implode("\n", $htmlItems) . "\n        </ul>";
            },
            $content
        );

        return $content;
    }
}
