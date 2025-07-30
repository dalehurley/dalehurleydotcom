<?php

use Illuminate\Support\Facades\Route;
use App\Services\BlogService;
use App\Http\Controllers\SitemapController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/posts', function (BlogService $blogService) {
    $posts = $blogService->getAllPosts();
    return view('posts.index', compact('posts'));
});

Route::get('/posts/{slug}', function (string $slug, BlogService $blogService) {
    $post = $blogService->getPostContentForRender($slug);

    if (!$post) {
        abort(404);
    }

    return view('posts.show', compact('post'));
});

// Sitemap routes
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/sitemap-main.xml', [SitemapController::class, 'main']);
Route::get('/sitemap-posts.xml', [SitemapController::class, 'posts']);
