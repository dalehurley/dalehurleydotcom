<?php

namespace App\Jobs;

use App\Services\ImageCompressionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CompressImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $sourcePath,
        public string $outputPath,
        public ?string $targetFormat = null,
        public string $disk = 'public'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ImageCompressionService $compressor): void
    {
        try {
            $result = $compressor->compress($this->sourcePath, $this->targetFormat);
            
            // Store the compressed image
            Storage::disk($this->disk)->put($this->outputPath, $result['binary']);
            
            Log::info("Image compression job completed", [
                'source' => $this->sourcePath,
                'output' => $this->outputPath,
                'savings_pct' => $result['savings_pct'],
                'format' => $result['format']
            ]);

        } catch (\Exception $e) {
            Log::error("Image compression job failed", [
                'source' => $this->sourcePath,
                'output' => $this->outputPath,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Image compression job permanently failed", [
            'source' => $this->sourcePath,
            'output' => $this->outputPath,
            'error' => $exception->getMessage()
        ]);
    }
}
