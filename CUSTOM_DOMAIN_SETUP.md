# Custom Domain Setup — dalehurley.com

This document describes every manual step required to serve the site from `dalehurley.com` via GitHub Pages.

---

## 1. Enable GitHub Pages on the Repository

1. Go to **Settings → Pages** in your GitHub repository.
2. Under **Source**, select **GitHub Actions** (not a branch — the workflow deploys via the `actions/deploy-pages` action).
3. Leave **Custom domain** blank for now; you will add it after DNS propagates.

---

## 2. DNS Records at Your Domain Registrar

Log in to wherever `dalehurley.com` is registered (e.g. Namecheap, GoDaddy, Cloudflare, Route 53).

### Apex domain (`dalehurley.com`)

Add four **A records** pointing to GitHub Pages' IP addresses:

| Type | Host  | Value          | TTL  |
|------|-------|----------------|------|
| A    | @     | 185.199.108.153 | 3600 |
| A    | @     | 185.199.109.153 | 3600 |
| A    | @     | 185.199.110.153 | 3600 |
| A    | @     | 185.199.111.153 | 3600 |

### www subdomain (optional redirect)

Add a **CNAME record** so `www.dalehurley.com` also resolves:

| Type  | Host | Value                        | TTL  |
|-------|------|------------------------------|------|
| CNAME | www  | dalehurley.github.io         | 3600 |

> Replace `dalehurley` with your actual GitHub username if different.

### IPv6 (optional but recommended)

Add four **AAAA records**:

| Type  | Host | Value                   | TTL  |
|-------|------|-------------------------|------|
| AAAA  | @    | 2606:50c0:8000::153     | 3600 |
| AAAA  | @    | 2606:50c0:8001::153     | 3600 |
| AAAA  | @    | 2606:50c0:8002::153     | 3600 |
| AAAA  | @    | 2606:50c0:8003::153     | 3600 |

---

## 3. CNAME File (already committed)

The file `public/CNAME` contains `dalehurley.com`. Astro copies every file in `public/` into `dist/` at build time, so GitHub Pages will find it automatically. **Do not delete this file.**

---

## 4. Add the Custom Domain in GitHub Settings

Once DNS has propagated (usually 5–30 minutes, up to 24 hours):

1. Go to **Settings → Pages**.
2. Under **Custom domain**, enter `dalehurley.com` and click **Save**.
3. GitHub will run a DNS check. When it passes, tick **Enforce HTTPS** (requires the TLS certificate to have been issued first — wait a few minutes if the checkbox is greyed out).

---

## 5. Verify HTTPS Is Working

```bash
curl -I https://dalehurley.com
```

Expected: `HTTP/2 200` with `server: GitHub.com`.

---

## 6. Remove the Old Laravel Hosting

Once the static site is confirmed live:

- Point your old server's domain away or shut down the Laravel deployment.
- The static site has no PHP runtime dependency, so there is no server to maintain.

---

## Notes

- **Astro config** — `astro.config.mjs` already sets `site: 'https://dalehurley.com'` and `base: '/'`. These values drive canonical URLs, the sitemap, and OG image absolute URLs.
- **Sitemap** — `@astrojs/sitemap` auto-generates `/sitemap-index.xml` and `/sitemap-0.xml` from all pages. Submit `https://dalehurley.com/sitemap-index.xml` to Google Search Console.
- **robots.txt** — The existing `public/robots.txt` is kept as-is; update the `Sitemap:` line if needed to point at `https://dalehurley.com/sitemap-index.xml`.
