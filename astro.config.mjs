import { defineConfig } from 'astro/config';
import mdx from '@astrojs/mdx';
import sitemap from '@astrojs/sitemap';
import tailwind from '@astrojs/tailwind';

// https://astro.build/config
export default defineConfig({
  site: 'https://dalehurley.com',
  integrations: [
    mdx(),
    sitemap(),
    tailwind({ applyBaseStyles: false }),
  ],
  // Static output for GitHub Pages
  output: 'static',
  // GitHub Pages serves from root when custom domain is used
  base: '/',
  markdown: {
    // Use Shiki for syntax highlighting (built into Astro)
    shikiConfig: {
      theme: 'github-dark',
      wrap: false,
    },
  },
});
