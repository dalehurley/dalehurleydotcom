<?php

namespace App\Http\Controllers;

use App\Jobs\CompressImageJob;
use App\Services\ImageCompressionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function __construct(
        private ImageCompressionService $compressor
    ) {}

    /**
     * Upload and compress an image synchronously
     */
    public function uploadSync(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
            'format' => 'nullable|in:png,jpeg,webp,avif',
            'quality_preset' => 'nullable|in:high_quality,web_optimized',
        ]);

        $uploadedFile = $request->file('image');
        $targetFormat = $request->input('format');
        
        try {
            // Compress the image
            $result = $this->compressor->compress($uploadedFile, $targetFormat);
            
            // Generate filename
            $filename = $this->generateFilename($uploadedFile->getClientOriginalName(), $result['format']);
            
            // Store compressed image
            $path = Storage::put('images/' . $filename, $result['binary']);
            
            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::url($path),
                'compression' => [
                    'original_size_kb' => $result['original_kb'],
                    'compressed_size_kb' => $result['compressed_kb'],
                    'savings_percent' => $result['savings_pct'],
                    'format' => $result['format'],
                    'method' => $result['compression_method'] ?? 'cli'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Image compression failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload and queue compression for background processing
     */
    public function uploadAsync(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240',
            'format' => 'nullable|in:png,jpeg,webp,avif',
        ]);

        $uploadedFile = $request->file('image');
        $targetFormat = $request->input('format', 'webp');
        
        // Store original image temporarily
        $tempPath = $uploadedFile->store('temp/uploads');
        $absolutePath = Storage::path($tempPath);
        
        // Generate final path
        $filename = $this->generateFilename($uploadedFile->getClientOriginalName(), $targetFormat);
        $finalPath = 'images/' . $filename;
        
        // Dispatch compression job
        CompressImageJob::dispatch($absolutePath, $finalPath, $targetFormat);
        
        return response()->json([
            'success' => true,
            'message' => 'Image uploaded and queued for compression',
            'final_path' => $finalPath,
            'estimated_url' => Storage::url($finalPath),
            'status' => 'processing'
        ]);
    }

    /**
     * Get compression tool status
     */
    public function toolsStatus(): JsonResponse
    {
        $tools = $this->compressor->checkTools();
        $formats = $this->compressor->getAvailableFormats();
        
        return response()->json([
            'tools' => $tools,
            'available_formats' => $formats,
            'fallback_enabled' => config('image-compression.fallback.enabled'),
            'recommended_setup' => empty($formats) ? 
                'Install CLI tools for optimal compression: pngquant, mozjpeg, cwebp, avifenc' : 
                'Compression tools properly configured'
        ]);
    }

    /**
     * Batch compress multiple images
     */
    public function batchCompress(Request $request): JsonResponse
    {
        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|max:10240',
            'format' => 'nullable|in:png,jpeg,webp,avif',
        ]);

        $results = [];
        $targetFormat = $request->input('format', 'webp');
        
        foreach ($request->file('images') as $index => $file) {
            try {
                $result = $this->compressor->compress($file, $targetFormat);
                $filename = $this->generateFilename($file->getClientOriginalName(), $result['format']);
                $path = Storage::put('images/' . $filename, $result['binary']);
                
                $results[] = [
                    'index' => $index,
                    'success' => true,
                    'path' => $path,
                    'url' => Storage::url($path),
                    'savings_percent' => $result['savings_pct'],
                    'format' => $result['format']
                ];
                
            } catch (\Exception $e) {
                $results[] = [
                    'index' => $index,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        $successful = count(array_filter($results, fn($r) => $r['success']));
        
        return response()->json([
            'total' => count($results),
            'successful' => $successful,
            'failed' => count($results) - $successful,
            'results' => $results
        ]);
    }

    private function generateFilename(string $originalName, string $format): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = Str::slug($name);
        $timestamp = time();
        
        return "{$safeName}_{$timestamp}.{$format}";
    }
}
