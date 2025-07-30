<?php

namespace App\Services\Agents;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use OpenAI\Laravel\Facades\OpenAI;

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

        // Create thumbnail (300x200) using native PHP
        try {
            self::createThumbnail($publicImagePath, $publicThumbnailPath, 300, 200);
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
    • "The Secret Weapon Smart Businesses Use"
    • "Why Everyone's Talking About [Key Concept]"
    • "The Future Is Here and It's [Adjective]"
    • Or invent something completely original that stops scrollers dead

2. **Hero Visual (Center Stage)** – Create a dynamic, story-driven scene that embodies the blog's core message. Break free from tech clichés with unexpected approaches:
    • A confident figure surfing a tsunami of glowing data particles
    • Vintage pin-up style character wielding futuristic tools like they're magic wands
    • Retro diner scene where AI solutions are served like the special of the day
    • Mad scientist laboratory meets sleek boardroom aesthetic
    • Time-traveler from the 50s marveling at today's AI capabilities
    • Office worker transforming into a superhero via technology
    • Think film noir meets sci-fi meets advertising golden age

    Visual requirements: Thick comic-book ink lines, dramatic lighting, halftone dot patterns, paper texture overlay, dynamic angles, burst effects. Characters should be diverse, attractive, and exude confidence and competence.

3. **Power Tagline (Bottom Banner)** – Deliver the transformation promise in ≤ 8 punchy words:
    • "Where Innovation Meets Unstoppable Results"
    • "Turning Tomorrow's Dreams Into Today's Reality"
    • "Your Competitive Edge Starts Right Here"
    • "Making the Impossible Look Effortless"
    • Focus on outcomes, emotions, and aspirational transformation

### Creative License
Feel completely free to reimagine these frameworks. The goal is creating scroll-stopping art that makes viewers think "I NEED to read this." Blend nostalgia with future-forward thinking. Make it irresistible.

### Art Direction Checklist
• **Style**: Mid-century comic / pin-up / advertisement, halftone shading, slight distress texture.
• **Color palette**: Mustard yellow, teal, deep red, cream, charcoal accents.
• **Mood**: Fun, empowering, innovation-focused, future-forward.
• **Avoid**: Modern stock-photo aesthetics, clichés like generic computers/robots unless subverted, any content that diminishes the power of AI innovation.
• **Aspect Ratio**: Landscape (3:2) optimized for hero images and wide displays.

### Prompt Output Rules
• Start with a direct instruction to the generator (e.g. "Create a retro 1950s comic poster…").
• Include all stylistic directives above in concise, generator-friendly syntax.
• **Do NOT wrap your prompt in back-ticks or markdown.**
• Return ONLY the prompt—no commentary, pre-amble, or closing remarks.
• The image is landscape format, so ensure the composition works well in a 3:2 aspect ratio with room for text placement.
• Make sure the image represents the blog post content effectively, capturing its essence and themes.

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

    /**
     * Create a thumbnail from an image using native PHP
     */
    private static function createThumbnail(string $sourcePath, string $thumbnailPath, int $width, int $height): void
    {
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            throw new \Exception("Could not get image information for: {$sourcePath}");
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Create source image resource based on mime type
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \Exception("Unsupported image type: {$mimeType}");
        }

        if (!$sourceImage) {
            throw new \Exception("Could not create image resource from: {$sourcePath}");
        }

        // Calculate thumbnail dimensions maintaining aspect ratio
        $aspectRatio = $sourceWidth / $sourceHeight;
        if ($aspectRatio > 1) {
            // Landscape
            $thumbnailWidth = $width;
            $thumbnailHeight = (int) ($width / $aspectRatio);
        } else {
            // Portrait or square
            $thumbnailHeight = $height;
            $thumbnailWidth = (int) ($height * $aspectRatio);
        }

        // Create thumbnail image
        $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

        // Preserve transparency for PNG and WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefill($thumbnail, 0, 0, $transparent);
        }

        // Resize image
        imagecopyresampled(
            $thumbnail,
            $sourceImage,
            0,
            0,
            0,
            0,
            $thumbnailWidth,
            $thumbnailHeight,
            $sourceWidth,
            $sourceHeight
        );

        // Save thumbnail as WebP
        imagewebp($thumbnail, $thumbnailPath, 90);

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
    }
}
