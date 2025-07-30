# Blog Image Generation

This project includes an AI-powered image generation system for blog posts using OpenAI's DALL-E API.

## Features

- **AI Image Generation**: Uses OpenAI's DALL-E 3 model to generate unique hero images for blog posts based on the post title and content
- **Automatic Thumbnails**: Creates 300x300 thumbnail versions for blog post listings
- **MDX Integration**: Automatically updates MDX frontmatter with image paths
- **Responsive Display**: Shows hero images in blog posts and thumbnails in the blog index

## Usage

### Generate Images for All Posts

```bash
php artisan blog:generate-images
```

This will show you a list of all blog posts and let you choose to generate images for all posts or select specific ones.

### Generate Image for Specific Post

```bash
php artisan blog:generate-images --post=your-post-slug
```

## Image Storage

- **Original Images**: Saved to `public/images/{slug}.webp`
- **Thumbnails**: Saved to `public/images/{slug}-thumbnail.webp`

## MDX Frontmatter

The command automatically adds these fields to your MDX files:

```yaml
---
image: images/your-post-slug.webp
thumbnail: images/your-post-slug-thumbnail.webp
---
```

## Image Display

### Blog Index (Thumbnails)

Thumbnails are automatically displayed in the blog post grid when available.

### Blog Post View (Hero Images)

Hero images are displayed prominently at the top of individual blog posts.

## Image Style

The generated images follow a retro 1950s comic-style aesthetic with:

- Mid-century advertising art style
- Mustard yellow, teal, deep red, and cream color palette
- Technology and AI innovation themes
- Square (1:1) aspect ratio for social media compatibility

## Requirements

- OpenAI API key configured in your environment
- PHP GD extension for thumbnail generation
- WebP support in your server/browser environment
