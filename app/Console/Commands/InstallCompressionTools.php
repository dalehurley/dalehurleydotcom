<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class InstallCompressionTools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:install-tools 
                          {--check-only : Only check current installation status}
                          {--output-env : Output environment variables for .env file}
                          {--force : Force reinstallation even if tools exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install image compression tools (pngquant, mozjpeg, cwebp, avifenc) and output environment paths';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Image Compression Tools Installer');
        $this->line('');

        // Detect operating system
        $os = $this->detectOS();
        $this->info("Detected OS: {$os}");
        $this->line('');

        // Check current installation status
        $currentPaths = $this->checkCurrentInstallation();
        
        if ($this->option('check-only')) {
            return $this->showCurrentStatus($currentPaths);
        }

        if ($this->option('output-env')) {
            return $this->outputEnvironmentVariables($currentPaths);
        }

        // Check if tools are already installed and --force not used
        $allInstalled = array_filter($currentPaths);
        if (count($allInstalled) === 4 && !$this->option('force')) {
            $this->info('✅ All tools are already installed!');
            $this->line('');
            $this->showCurrentStatus($currentPaths);
            $this->line('');
            $this->info('Use --force to reinstall or --output-env to get environment variables.');
            return 0;
        }

        // Install tools based on OS
        try {
            switch ($os) {
                case 'macOS':
                    $paths = $this->installOnMacOS();
                    break;
                case 'Ubuntu':
                case 'Debian':
                    $paths = $this->installOnUbuntu();
                    break;
                default:
                    $this->error("Unsupported operating system: {$os}");
                    $this->info('Please install tools manually:');
                    $this->showManualInstallInstructions();
                    return 1;
            }

            $this->line('');
            $this->info('✅ Installation completed successfully!');
            $this->line('');
            
            // Show final status
            $finalPaths = $this->checkCurrentInstallation();
            $this->showCurrentStatus($finalPaths);
            
            $this->line('');
            $this->outputEnvironmentVariables($finalPaths);
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Installation failed: ' . $e->getMessage());
            $this->line('');
            $this->showManualInstallInstructions();
            return 1;
        }
    }

    private function detectOS(): string
    {
        $uname = trim(shell_exec('uname -s') ?? '');
        
        if ($uname === 'Darwin') {
            return 'macOS';
        }
        
        if ($uname === 'Linux') {
            // Check for specific distributions
            if (file_exists('/etc/os-release')) {
                $osRelease = file_get_contents('/etc/os-release');
                if (stripos($osRelease, 'ubuntu') !== false) {
                    return 'Ubuntu';
                }
                if (stripos($osRelease, 'debian') !== false) {
                    return 'Debian';
                }
            }
            return 'Linux';
        }
        
        return 'Unknown';
    }

    private function checkCurrentInstallation(): array
    {
        $tools = ['pngquant', 'mozjpeg', 'cwebp', 'avifenc'];
        $paths = [];

        foreach ($tools as $tool) {
            $path = $this->findToolPath($tool);
            $paths[$tool] = $path;
        }

        return $paths;
    }

    private function findToolPath(string $tool): ?string
    {
        // Check common locations first
        $commonPaths = [
            '/usr/local/bin/' . $tool,
            '/usr/bin/' . $tool,
            '/opt/homebrew/bin/' . $tool,
            '/usr/local/bin/cjpeg', // mozjpeg might be installed as cjpeg
        ];

        // Special case for mozjpeg
        if ($tool === 'mozjpeg') {
            $commonPaths[] = '/usr/local/bin/cjpeg';
            $commonPaths[] = '/opt/homebrew/bin/cjpeg';
            $commonPaths[] = '/usr/bin/cjpeg';
        }

        foreach ($commonPaths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        // Try using 'which' command
        $which = $tool === 'mozjpeg' ? 'cjpeg' : $tool;
        $result = trim(shell_exec("which {$which} 2>/dev/null") ?? '');
        
        if (!empty($result) && is_executable($result)) {
            return $result;
        }

        return null;
    }

    private function installOnMacOS(): array
    {
        $this->info('📦 Installing tools on macOS using Homebrew...');
        
        // Check if Homebrew is installed
        if (!$this->isBrewInstalled()) {
            throw new \Exception('Homebrew is not installed. Please install Homebrew first: https://brew.sh/');
        }

        $this->info('✅ Homebrew detected');
        
        // Update Homebrew
        $this->info('🔄 Updating Homebrew...');
        $this->executeCommand(['brew', 'update']);

        // Install tools
        $tools = [
            'pngquant' => 'pngquant',
            'mozjpeg' => 'mozjpeg', 
            'webp' => 'cwebp',
            'libavif' => 'avifenc'
        ];

        foreach ($tools as $package => $binary) {
            $this->info("📦 Installing {$package}...");
            try {
                $this->executeCommand(['brew', 'install', $package]);
                $this->info("✅ {$package} installed successfully");
            } catch (\Exception $e) {
                $this->warn("⚠️  {$package} installation failed, might already be installed: " . $e->getMessage());
            }
        }

        return $this->checkCurrentInstallation();
    }

    private function installOnUbuntu(): array
    {
        $this->info('📦 Installing tools on Ubuntu/Debian using apt...');
        
        // Update package list
        $this->info('🔄 Updating package list...');
        $this->executeCommand(['sudo', 'apt-get', 'update']);

        // Install tools
        $packages = ['pngquant', 'mozjpeg-tools', 'webp', 'libavif-bin'];
        
        $this->info('📦 Installing packages: ' . implode(', ', $packages));
        $this->executeCommand(['sudo', 'apt-get', 'install', '-y', ...$packages]);

        // Create symlink for mozjpeg if needed
        $cjpegPath = '/usr/bin/cjpeg';
        $mozjpegPath = '/usr/local/bin/mozjpeg';
        
        if (file_exists($cjpegPath) && !file_exists($mozjpegPath)) {
            $this->info('🔗 Creating mozjpeg symlink...');
            $this->executeCommand(['sudo', 'ln', '-sf', $cjpegPath, $mozjpegPath]);
        }

        return $this->checkCurrentInstallation();
    }

    private function isBrewInstalled(): bool
    {
        try {
            $this->executeCommand(['brew', '--version'], false);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function executeCommand(array $command, bool $showOutput = true): string
    {
        $process = new Process($command);
        $process->setTimeout(300); // 5 minutes timeout
        
        if ($showOutput) {
            $this->line('Running: ' . implode(' ', $command));
        }
        
        $process->run(function ($type, $buffer) use ($showOutput) {
            if ($showOutput && Process::OUT === $type) {
                $this->line($buffer);
            }
        });

        if (!$process->isSuccessful()) {
            throw new \Exception("Command failed: " . $process->getErrorOutput());
        }

        return $process->getOutput();
    }

    private function showCurrentStatus(array $paths): int
    {
        $this->info('📋 Current Installation Status:');
        $this->line('');

        $tableData = [];
        foreach ($paths as $tool => $path) {
            $status = $path ? '✅ Installed' : '❌ Not Found';
            $location = $path ?: 'N/A';
            $tableData[] = [$tool, $status, $location];
        }

        $this->table(['Tool', 'Status', 'Path'], $tableData);

        $installed = array_filter($paths);
        $this->line('');
        $this->info(sprintf('📊 Summary: %d/4 tools installed', count($installed)));

        return count($installed) === 4 ? 0 : 1;
    }

    private function outputEnvironmentVariables(array $paths): int
    {
        $this->info('📝 Environment Variables for .env file:');
        $this->line('');
        $this->line('# Image Compression Tool Paths');
        
        foreach ($paths as $tool => $path) {
            $envVar = 'IMG_COMP_' . strtoupper($tool) . '_PATH';
            $value = $path ?: '';
            $this->line("{$envVar}={$value}");
        }

        $this->line('');
        $this->info('💡 Copy the lines above to your .env file');
        $this->info('   Leave empty values for auto-discovery via PATH');

        return 0;
    }

    private function showManualInstallInstructions(): void
    {
        $this->info('📖 Manual Installation Instructions:');
        $this->line('');
        
        $this->info('🍎 macOS (Homebrew):');
        $this->line('  brew install pngquant mozjpeg webp libavif');
        $this->line('');
        
        $this->info('🐧 Ubuntu/Debian:');
        $this->line('  sudo apt-get update');
        $this->line('  sudo apt-get install pngquant mozjpeg-tools webp libavif-bin');
        $this->line('  sudo ln -sf /usr/bin/cjpeg /usr/local/bin/mozjpeg');
        $this->line('');
        
        $this->info('🐳 Docker:');
        $this->line('  RUN apt-get update && apt-get install -y \\');
        $this->line('      pngquant mozjpeg-tools webp libavif-bin \\');
        $this->line('      && ln -sf /usr/bin/cjpeg /usr/local/bin/mozjpeg');
        $this->line('');
        
        $this->info('After manual installation, run:');
        $this->line('  php artisan image:install-tools --output-env');
    }
}
