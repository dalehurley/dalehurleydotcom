const tagLabels: Record<string, string> = {
  ai: 'AI',
  api: 'API',
  apis: 'APIs',
  ar: 'AR',
  aws: 'AWS',
  cli: 'CLI',
  css: 'CSS',
  cx: 'CX',
  genai: 'GenAI',
  github: 'GitHub',
  gpt: 'GPT',
  html: 'HTML',
  ios: 'iOS',
  javascript: 'JavaScript',
  json: 'JSON',
  llm: 'LLM',
  llms: 'LLMs',
  macos: 'macOS',
  mcp: 'MCP',
  ml: 'ML',
  openai: 'OpenAI',
  php: 'PHP',
  rag: 'RAG',
  'r&d': 'R&D',
  saas: 'SaaS',
  sdk: 'SDK',
  sdks: 'SDKs',
  seo: 'SEO',
  sql: 'SQL',
  typescript: 'TypeScript',
  ui: 'UI',
  ux: 'UX',
};

/** Keep date-only content dates stable across reader and build time zones. */
export function formatPostDate(
  date: Date | string,
  options: Intl.DateTimeFormatOptions = {},
): string {
  const value = typeof date === 'string' ? new Date(date) : date;
  return new Intl.DateTimeFormat('en-AU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    ...options,
    timeZone: 'UTC',
  }).format(value);
}

export function estimateReadingTime(body?: string): number {
  const words = body?.trim().match(/\S+/g)?.length ?? 0;
  return Math.max(1, Math.round(words / 200));
}

/** Preserve the existing tag URL convention while normalizing casing and space. */
export function normalizeTagSlug(tag: string): string {
  return tag.trim().toLowerCase().replace(/\s+/g, '-');
}

export function formatTagLabel(tag: string): string {
  return tag.trim().split(/[\s-]+/).map(word => {
    const normalized = word.toLowerCase();
    return tagLabels[normalized] ?? normalized.charAt(0).toUpperCase() + normalized.slice(1);
  }).join(' ');
}
