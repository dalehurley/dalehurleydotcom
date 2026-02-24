# DaleHurley.com

Personal blog and portfolio of Dale Hurley — AI entrepreneur, co-founder of Avenue Bank, and founding CTO of CreditorWatch.

Built with [Astro](https://astro.build) and deployed to [GitHub Pages](https://pages.github.com) at **[dalehurley.com](https://dalehurley.com)**.

## Stack

| Layer | Technology |
|---|---|
| Framework | Astro 5 (static output) |
| Styling | Tailwind CSS 3 + @tailwindcss/typography |
| Content | MDX with Astro Content Collections |
| Syntax highlighting | Shiki (built-in to Astro) |
| Sitemap | @astrojs/sitemap |
| Hosting | GitHub Pages |
| CI/CD | GitHub Actions |
| Image generation | OpenAI gpt-image-1 + gpt-4o via Node.js script |

## Development

```bash
npm install
npm run dev       # http://localhost:4321
npm run build     # output → dist/
npm run preview   # preview the built site locally
```

## Adding a Blog Post

1. Create `src/content/blog/<slug>/index.mdx`
2. Add frontmatter:

```yaml
---
title: My Post Title
date: 2025-01-15
description: A short description for SEO and cards.
tags: [AI, Technology]
author: Dale Hurley
image: images/<slug>.webp
thumbnail: images/<slug>-thumbnail.webp
---
```

3. Write the post in Markdown/MDX below the frontmatter.

## Generating Hero Images

Uses the OpenAI API to generate 1950s propaganda-style hero images that match each post's content.

```bash
OPENAI_API_KEY=sk-... npm run generate-images
# or for a single post:
OPENAI_API_KEY=sk-... npm run generate-images -- --post=<slug>
```

Images are saved to `public/images/` and the MDX frontmatter is updated automatically.

## Deployment

Every push to `main` triggers the GitHub Actions workflow (`.github/workflows/deploy.yml`), which builds the site and deploys it to GitHub Pages.

See [CUSTOM_DOMAIN_SETUP.md](./CUSTOM_DOMAIN_SETUP.md) for the DNS and Pages configuration required for the `dalehurley.com` custom domain.
