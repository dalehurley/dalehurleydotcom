# MDX to Blade Converter

This tool converts MDX files (commonly used with Next.js and similar frameworks) to Laravel Blade PHP files.

## Features

- Converts MDX frontmatter to Laravel BlogService integration
- Handles Mermaid diagrams
- Preserves code blocks (including PHP code samples)
- Converts React/Next.js components to Blade components
- Handles Markdown syntax (headers, lists, links, etc.)
- Escapes PHP tags in code blocks to prevent execution issues

## Usage

### Option 1: Laravel Artisan Command

```bash
php artisan blog:convert-mdx path/to/file.mdx [--output=custom/output/path.blade.php]
```

Example:

```bash
php artisan blog:convert-mdx resources/views/pages/posts/mypost/page.mdx
```

This will create `resources/views/pages/posts/mypost/index.blade.php`

### Option 2: Standalone Script

```bash
php convert-mdx.php path/to/file.mdx [output-path]
```

Example:

```bash
php convert-mdx.php resources/views/pages/posts/mypost/page.mdx
php convert-mdx.php resources/views/pages/posts/mypost/page.mdx custom-output.blade.php
```

## Conversions

### Components

- ``→`<x-blog-callout type="info">`

### Code Blocks

- Mermaid diagrams are wrapped in appropriate div containers
- PHP tags in code blocks are escaped (`<?php` → `&lt;?php`)
- Language-specific syntax highlighting is preserved

### Markdown

- Headers (`#`, `##`, `###`) → `<h1>`, `<h2>`, `<h3>`
- Bold (`**text**`) → `<strong>text</strong>`
- Italic (`*text*`) → `<em>text</em>`
- Links (`[text](url)`) → `<a href="url">text</a>`
- Unordered lists (`* item`) → `<ul><li>item</li></ul>`
- Ordered lists (`1. item`) → `<ol><li>item</li></ol>`

### Frontmatter

The YAML frontmatter is removed and the post slug is extracted from the file path for BlogService integration.

## Generated Blade Structure

```php
<?php

use App\Services\BlogService;

$blogService = new BlogService();
$post = $blogService->getPost('post-slug');

if (!$post) {
    abort(404);
}

?>

<x-blog-layout :post="$post">
    <!-- Converted content here -->
</x-blog-layout>
```

## File Structure Expected

The tool expects your MDX files to be organized like:

```
resources/views/pages/posts/
├── post-slug-1/
│   └── page.mdx
├── post-slug-2/
│   └── page.mdx
```

And will generate:

```
resources/views/pages/posts/
├── post-slug-1/
│   ├── page.mdx
│   └── index.blade.php
├── post-slug-2/
│   ├── page.mdx
│   └── index.blade.php
```

## Known Limitations

- Complex React components are not automatically converted
- Custom MDX components need manual conversion rules
- Advanced JSX syntax may need manual adjustment
- Multi-dimensional frontmatter arrays are simplified

## Example

**Input (page.mdx):**

````mdx
---
title: "My Blog Post"
date: 2025-01-01
---

# My Blog Post

This is a **bold** statement with a [link](https://example.com).

This is important information.

```mermaid
graph TD
    A --> B
```
````

**Output (index.blade.php):**

```php
<?php

use App\Services\BlogService;

$blogService = new BlogService();
$post = $blogService->getPost('mypost');

if (!$post) {
    abort(404);
}

?>

<x-blog-layout :post="$post">
    <h1>My Blog Post</h1>

    <p>This is a <strong>bold</strong> statement with a <a href="https://example.com">link</a>.</p>

    <x-blog-callout type="info">
        This is important information.
    </x-blog-callout>

    <div class="my-6">
        <pre class="mermaid bg-gray-50 p-4 rounded">
graph TD
    A --> B
        </pre>
    </div>
</x-blog-layout>
```
