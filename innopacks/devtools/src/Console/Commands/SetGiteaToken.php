<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetGiteaToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:set-gitea-token 
                            {token? : Gitea API token}
                            {--gitea-url=https://innoshop.work : Gitea server URL}
                            {--storage : Save to storage directory instead of .env}
                            {--clear : Clear saved token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save Gitea API token to .env or storage directory';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if ($this->option('clear')) {
            return $this->clearToken();
        }

        $token      = $this->argument('token');
        $giteaUrl   = $this->option('gitea-url');
        $useStorage = $this->option('storage');

        // If token not provided, prompt for it
        if (empty($token)) {
            $token = $this->secret('Enter Gitea API token');
            if (empty($token)) {
                $this->error('Token cannot be empty');

                return Command::FAILURE;
            }
        }

        if ($useStorage) {
            return $this->saveToStorage($token, $giteaUrl);
        }

        return $this->saveToEnv($token, $giteaUrl);
    }

    /**
     * Save token to .env file.
     *
     * @param  string  $token
     * @param  string  $giteaUrl
     * @return int
     */
    private function saveToEnv(string $token, string $giteaUrl): int
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            $this->error('.env file does not exist. Please create it first or use --storage option.');

            return Command::FAILURE;
        }

        $envContent = File::get($envPath);

        // Update or add GITEA_TOKEN
        if (preg_match('/^GITEA_TOKEN=.*$/m', $envContent)) {
            $envContent = preg_replace('/^GITEA_TOKEN=.*$/m', "GITEA_TOKEN={$token}", $envContent);
        } else {
            $envContent .= "\nGITEA_TOKEN={$token}\n";
        }

        // Update or add GITEA_URL
        if (preg_match('/^GITEA_URL=.*$/m', $envContent)) {
            $envContent = preg_replace('/^GITEA_URL=.*$/m', "GITEA_URL={$giteaUrl}", $envContent);
        } else {
            $envContent .= "GITEA_URL={$giteaUrl}\n";
        }

        File::put($envPath, $envContent);

        $this->info('✅ Gitea token saved to .env file!');
        $this->line("Token saved to: {$envPath}");
        $this->line("Gitea URL: {$giteaUrl}");

        return Command::SUCCESS;
    }

    /**
     * Save token to storage directory.
     *
     * @param  string  $token
     * @param  string  $giteaUrl
     * @return int
     */
    private function saveToStorage(string $token, string $giteaUrl): int
    {
        $storagePath = storage_path('app/.gitea_token');
        $storageDir  = dirname($storagePath);

        // Ensure storage directory exists
        if (! File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        $data = [
            'token'      => $token,
            'url'        => $giteaUrl,
            'updated_at' => now()->toDateTimeString(),
        ];

        File::put($storagePath, json_encode($data, JSON_PRETTY_PRINT));

        // Set secure permissions (readable only by owner)
        chmod($storagePath, 0600);

        $this->info('✅ Gitea token saved to storage directory!');
        $this->line("Token saved to: {$storagePath}");
        $this->line("Gitea URL: {$giteaUrl}");

        return Command::SUCCESS;
    }

    /**
     * Clear saved token.
     *
     * @return int
     */
    private function clearToken(): int
    {
        $cleared = false;

        // Clear from .env
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            if (preg_match('/^GITEA_TOKEN=.*$/m', $envContent)) {
                $envContent = preg_replace('/^GITEA_TOKEN=.*$/m', '', $envContent);
                $envContent = preg_replace('/^GITEA_URL=.*$/m', '', $envContent);
                // Remove empty lines
                $envContent = preg_replace('/\n\n+/', "\n", $envContent);
                File::put($envPath, trim($envContent)."\n");
                $cleared = true;
            }
        }

        // Clear from storage
        $storagePath = storage_path('app/.gitea_token');
        if (File::exists($storagePath)) {
            File::delete($storagePath);
            $cleared = true;
        }

        if ($cleared) {
            $this->info('✅ Gitea token cleared successfully!');
        } else {
            $this->info('No saved token found.');
        }

        return Command::SUCCESS;
    }
}
