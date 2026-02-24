import { defineCollection, z } from 'astro:content';

const blog = defineCollection({
  type: 'content',
  schema: z.object({
    title: z.string(),
    date: z.coerce.date(),
    description: z.string().optional(),
    tags: z.array(z.string()).optional().default([]),
    author: z.string().optional().default('Dale Hurley'),
    image: z.string().optional(),
    thumbnail: z.string().optional(),
  }),
});

export const collections = { blog };
