import rss from '@astrojs/rss';
import { getCollection } from 'astro:content';
import type { APIContext } from 'astro';

export async function GET(context: APIContext) {
  const posts = await getCollection('blog');
  const sortedPosts = posts.sort((a, b) => b.data.date.getTime() - a.data.date.getTime());

  return rss({
    title: 'Dale Hurley Blog',
    description: 'Thoughts on AI, technology, and entrepreneurship from Dale Hurley.',
    site: context.site!,
    items: sortedPosts.map(post => ({
      title: post.data.title,
      pubDate: post.data.date,
      description: post.data.description ?? '',
      link: `/posts/${post.slug}/`,
      categories: post.data.tags ?? [],
      author: post.data.author ?? 'Dale Hurley',
    })),
    customData: `<language>en-au</language>`,
  });
}
