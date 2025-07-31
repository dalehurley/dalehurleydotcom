<?php

namespace App\Services\Agents;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use OpenAI\Laravel\Facades\OpenAI;
use App\Services\ImageProcessor;
use App\Services\ImageCompressionService;

class ImageAgent
{
    /**
     * Generate an image for a blog post and save it.
     *
     * @param array $blogPost
     * @return array ['image_path' => string, 'thumbnail_path' => string]
     */
    public static function generateImage(array $blogPost): array
    {
        // Validate required fields
        if (!isset($blogPost['title']) || !isset($blogPost['slug'])) {
            throw new \InvalidArgumentException('Blog post must have title and slug fields');
        }

        $imagePrompt = self::generateImagePrompt($blogPost);
        Log::info("Generated Image Prompt for '{$blogPost['title']}':\n{$imagePrompt}");

        $response = OpenAI::images()->create([
            'model' => 'gpt-image-1',
            'prompt' => $imagePrompt,
            'size' => '1536x1024',
            'output_format' => 'webp'
        ]);

        $imageBase64 = $response->data[0]->b64_json;
        $contents = base64_decode($imageBase64);

        // Save to public/images directory
        $imagePath = 'images/' . $blogPost['slug'] . '.webp';
        $thumbnailPath = 'images/' . $blogPost['slug'] . '-thumbnail.webp';

        $publicImagePath = public_path($imagePath);
        $publicThumbnailPath = public_path($thumbnailPath);

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($publicImagePath));

        // Save original image
        file_put_contents($publicImagePath, $contents);

        // Compress the main image using CLI tools
        try {
            $compressor = app(ImageCompressionService::class);
            $optimized = $compressor->compress($publicImagePath, 'webp');
            file_put_contents($publicImagePath, $optimized['binary']);
            
            Log::info("Main image compressed", [
                'slug' => $blogPost['slug'],
                'original_kb' => $optimized['original_kb'],
                'compressed_kb' => $optimized['compressed_kb'],
                'savings_pct' => $optimized['savings_pct']
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to compress main image for {$blogPost['slug']}, falling back to ImageProcessor: " . $e->getMessage());
            // Fallback to ImageProcessor optimization
            try {
                ImageProcessor::optimizeImage($publicImagePath, $publicImagePath, 90, 1920, 1080);
            } catch (\Exception $fallbackError) {
                Log::warning("ImageProcessor fallback also failed for {$blogPost['slug']}: " . $fallbackError->getMessage());
            }
        }

        // Create thumbnail using ImageProcessor
        try {
            ImageProcessor::createThumbnail($publicImagePath, $publicThumbnailPath, 300, 200, 85);
            
            // Compress the thumbnail using CLI tools
            try {
                $compressor = app(ImageCompressionService::class);
                $thumbnailOptimized = $compressor->compress($publicThumbnailPath, 'webp');
                file_put_contents($publicThumbnailPath, $thumbnailOptimized['binary']);
                
                Log::info("Thumbnail compressed", [
                    'slug' => $blogPost['slug'],
                    'original_kb' => $thumbnailOptimized['original_kb'],
                    'compressed_kb' => $thumbnailOptimized['compressed_kb'],
                    'savings_pct' => $thumbnailOptimized['savings_pct']
                ]);
            } catch (\Exception $e) {
                Log::warning("Failed to compress thumbnail for {$blogPost['slug']}: " . $e->getMessage());
                // Continue with uncompressed thumbnail
            }
        } catch (\Exception $e) {
            Log::warning("Failed to create thumbnail for {$blogPost['slug']}: " . $e->getMessage());
            // Continue without thumbnail - we still have the main image
        }
        return [
            'image_path' => $imagePath,
            'thumbnail_path' => $thumbnailPath
        ];
    }

    /**
     * Generate a prompt for the image generation AI.
     *
     * @param array $blogPost
     * @return string
     */
    private static function generateImagePrompt(array $blogPost): string
    {
        $metaPrompt = <<<PROMPT
You are an elite visual prompt engineer with a flair for mid-century advertising art. Create a SINGLE, production-ready prompt that an AI image generator (e.g. Midjourney, DALL·E) can use to produce a striking hero/featured image for the following blog post title: "{$blogPost['title']}".

@use for inspiration
<blog-post-content>
{$blogPost['content']}
</blog-post-content>
@end use for inspiration

The finished artwork must feel like a 1950s comic-style poster while representing Dale Hurley's brand promise—delivering innovative AI solutions and empowering businesses through cutting-edge technology.

### Creative Vision Framework
1. **Bold Statement Banner (Top)** – Craft an attention-grabbing headline (≤ 8 words) that captures the blog's essence. Think provocative, memorable, action-oriented:
    • "Revolutionize Everything You Know About [Topic]"
    • "The Secret AI Weapon Nobody Talks About"
    • "Why Everyone's Obsessed With [Key Concept]"
    • "The Game-Changer That Breaks All Rules"
    • "Welcome to the [Topic] Revolution"
    • "This Changes Everything About [Subject]"
    • "The Underground Secret Smart Businesses Use"
    • Or create something completely original that demands attention

2. **Hero Visual (Center Stage)** – Create a dynamic, story-driven scene that embodies the blog's core message. Break free from predictable tech visuals with unexpected creative approaches:
    • A vintage pin-up scientist mixing glowing AI potions in test tubes that sparkle with code
    • Retro space explorer planting a flag on a digital planet made of flowing data streams
    • 1950s housewife casually commanding an army of helpful AI robots like kitchen appliances
    • Mad scientist's laboratory where beakers bubble with colorful algorithms and lightning bolts of innovation
    • Vintage superhero bursting through a wall of traditional business limitations
    • Time-traveling inventor from the 50s riding a rocket-powered computer through cyber-space
    • Noir detective solving mysteries with AI magnifying glasses that reveal hidden patterns
    • Retro diner where AI solutions are served as glowing milkshakes and electric burgers
    • Comic book hero transforming mundane office work into explosive productivity superpowers
    • Vintage pilot navigating through storm clouds of data with AI as their co-pilot
    • 1950s factory worker operating incredible AI-powered machinery that creates digital magic

    Visual requirements: Bold comic-book ink lines, dramatic shadows and highlights, Ben-Day dot patterns, dynamic action poses, explosion effects, speed lines, dramatic perspectives.

3. **Power Tagline (Bottom Banner)** – Deliver the transformation promise in ≤ 8 punchy words:
    • "Where Wild Ideas Become Winning Reality"
    • "Turning Chaos Into Your Competitive Advantage"
    • "Making Magic Out of Monday Morning"
    • "Your Secret Weapon for Impossible Results"
    • "Where the Future Meets Your Ambition"
    • "Transforming Problems Into Profit Machines"

### Creative License
Go wild! The goal is creating scroll-stopping art that makes viewers think "I absolutely MUST read this." Blend nostalgic charm with explosive innovation. Make it absolutely irresistible and unforgettable.

### Art Direction Checklist
• **Style**: Mid-century comic book advertisement with halftone shading, vintage poster aesthetic, slight paper texture overlay.
• **Color palette**: Electric blue, vibrant coral, bright lime green, deep purple, golden yellow, crisp white highlights.
• **Mood**: Explosive energy, empowering transformation, playful innovation, confident optimism.
• **Avoid**: Generic stock photography, boring corporate aesthetics, predictable tech clichés unless completely reimagined.
• **Aspect Ratio**: Landscape (3:2) optimized for hero images with dynamic composition.

### Prompt Output Rules
• Start with a direct instruction to the generator (e.g. "Create a vibrant retro 1950s comic poster…").
• Include all stylistic directives above in concise, generator-friendly syntax.
• **Do NOT wrap your prompt in back-ticks or markdown.**
• Return ONLY the prompt—no commentary, pre-amble, or closing remarks.
• The image is landscape format, so ensure the composition works well in a 3:2 aspect ratio with room for text placement.
• Make sure the image represents the blog post content effectively, capturing its essence and themes with maximum visual impact.

Generate the image prompt now.
PROMPT;

        $response = OpenAI::chat()->create([
            'model' => 'o3',
            'messages' => [
                ['role' => 'user', 'content' => $metaPrompt],
            ],
        ]);

        Log::info('Response: ' . json_encode($response->choices[0]->message->content));

        return $response->choices[0]->message->content;
    }
}
