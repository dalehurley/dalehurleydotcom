#!/usr/bin/env php
<?php

/**
 * Simple MDX to Blade Converter Script
 *
 * Usage: php convert-mdx.php path/to/file.mdx [output-path]
 *
 * This script can be used independently of the Laravel artisan command
 */

require_once __DIR__ . '/vendor/autoload.php';

if ($argc < 2) {
    echo "Usage: php convert-mdx.php path/to/file.mdx [output-path]\n";
    exit(1);
}

$inputFile = $argv[1];
$outputFile = $argv[2] ?? null;

if (!file_exists($inputFile)) {
    echo "Error: File not found: {$inputFile}\n";
    exit(1);
}

// Get the post slug from the file path
function getPostSlug($filePath)
{
    $pathParts = explode('/', $filePath);
    $postsIndex = array_search('posts', $pathParts);

    if ($postsIndex !== false && isset($pathParts[$postsIndex + 1])) {
        return $pathParts[$postsIndex + 1];
    }

    // Fallback: use filename without extension
    return pathinfo($filePath, PATHINFO_FILENAME);
}

$postSlug = getPostSlug($inputFile);
$content = file_get_contents($inputFile);

// Remove frontmatter
$content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);

// Remove import statements
$content = preg_replace('/^import\s+.*$/m', '', $content);

// Convert code blocks first
$content = preg_replace_callback(
    '/```mermaid\s*\n(.*?)\n```/s',
    function ($matches) {
        $mermaidContent = trim($matches[1]);
        return "<div class=\"my-6\">\n        <pre class=\"mermaid bg-gray-50 p-4 rounded\">\n{$mermaidContent}\n        </pre>\n    </div>";
    },
    $content
);

// Handle other code blocks
$content = preg_replace_callback(
    '/```(\w+)?\s*\n(.*?)\n```/s',
    function ($matches) {
        $language = $matches[1] ?? '';
        $codeContent = $matches[2];

        // Escape PHP tags in code blocks
        if (strpos($codeContent, '<?php') !== false) {
            $codeContent = str_replace('<?php', '&lt;?php', $codeContent);
            $codeContent = str_replace('<?=', '&lt;?=', $codeContent);
            $codeContent = str_replace('?>', '?&gt;', $codeContent);
        }

        return "<pre><code class=\"language-{$language}\">{$codeContent}</code></pre>";
    },
    $content
);

// Convert callouts
$content = preg_replace_callback(
    '/<Callout\s+type="([^"]+)">\s*(.*?)\s*<\/Callout>/s',
    function ($matches) {
        $type = $matches[1];
        $calloutContent = trim($matches[2]);

        // Convert markdown within callout
        $calloutContent = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $calloutContent);
        $calloutContent = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $calloutContent);

        // Convert lists
        $calloutContent = preg_replace_callback(
            '/^(- .*(?:\n- .*)*)/m',
            function ($matches) {
                $listItems = explode("\n", trim($matches[1]));
                $htmlItems = array_map(function ($item) {
                    return '            <li>' . ltrim($item, '- ') . '</li>';
                }, $listItems);
                return "        <ul>\n" . implode("\n", $htmlItems) . "\n        </ul>";
            },
            $calloutContent
        );

        return "<x-blog-callout type=\"{$type}\">\n        {$calloutContent}\n    </x-blog-callout>";
    },
    $content
);

// Protect code blocks and callouts from paragraph conversion
$protectedBlocks = [];
$blockIndex = 0;

$content = preg_replace_callback(
    '/(<div class="my-6">.*?<\/div>|<pre><code.*?<\/code><\/pre>|<x-blog-callout.*?<\/x-blog-callout>)/s',
    function ($matches) use (&$protectedBlocks, &$blockIndex) {
        $placeholder = "___PROTECTED_BLOCK_{$blockIndex}___";
        $protectedBlocks[$placeholder] = $matches[0];
        $blockIndex++;
        return $placeholder;
    },
    $content
);

// Convert markdown
$content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
$content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
$content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);
$content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
$content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
$content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $content);

// Convert lists
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

// Convert paragraphs
$lines = explode("\n", $content);
$result = [];

foreach ($lines as $line) {
    $trimmed = trim($line);

    if (empty($trimmed)) {
        $result[] = '';
        continue;
    }

    if (
        preg_match('/^<(\/?)([a-zA-Z\-]+)/', $trimmed) ||
        preg_match('/^___PROTECTED_BLOCK_\d+___$/', $trimmed)
    ) {
        $result[] = $line;
        continue;
    }

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

// Clean up
$content = preg_replace('/\n{3,}/', "\n\n", $content);
$content = trim($content);

// Indent content
$lines = explode("\n", $content);
$indentedLines = array_map(function ($line) {
    return empty(trim($line)) ? $line : '    ' . $line;
}, $lines);

$content = implode("\n", $indentedLines);

// Generate Blade file
$bladeContent = "<?php

use App\Services\BlogService;

\$blogService = new BlogService();
\$post = \$blogService->getPost('{$postSlug}');

if (!\$post) {
    abort(404);
}

?>

<x-blog-layout :post=\"\$post\">
{$content}
</x-blog-layout>";

// Determine output file
if (!$outputFile) {
    $directory = dirname($inputFile);
    $outputFile = $directory . '/index.blade.php';
}

// Write file
file_put_contents($outputFile, $bladeContent);
echo "Converted file saved to: {$outputFile}\n";
