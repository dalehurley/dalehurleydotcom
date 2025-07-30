<?php

namespace App\Http\Controllers;

use App\Services\BlogService;
use Illuminate\Http\Response;
use Carbon\Carbon;

class SitemapController extends Controller
{
    protected BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    /**
     * Generate the main sitemap index
     */
    public function index(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Main sitemap
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>' . url('/sitemap-main.xml') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . Carbon::now()->toISOString() . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";
        
        // Blog sitemap
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>' . url('/sitemap-posts.xml') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . Carbon::now()->toISOString() . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";
        
        $xml .= '</sitemapindex>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate the main pages sitemap
     */
    public function main(): Response
    {
        $urls = [
            ['url' => url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => url('/posts'), 'priority' => '0.8', 'changefreq' => 'daily'],
        ];

        return $this->generateSitemap($urls);
    }

    /**
     * Generate the blog posts sitemap
     */
    public function posts(): Response
    {
        $posts = $this->blogService->getAllPosts();
        $urls = [];

        foreach ($posts as $post) {
            $urls[] = [
                'url' => url($post['url']),
                'lastmod' => isset($post['date']) ? Carbon::parse($post['date'])->toISOString() : Carbon::now()->toISOString(),
                'priority' => '0.7',
                'changefreq' => 'monthly'
            ];
        }

        return $this->generateSitemap($urls);
    }

    /**
     * Generate XML sitemap for given URLs
     */
    protected function generateSitemap(array $urls): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['url']) . '</loc>' . "\n";
            
            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            }
            
            if (isset($url['changefreq'])) {
                $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            }
            
            if (isset($url['priority'])) {
                $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            }
            
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
