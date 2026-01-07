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
use Illuminate\Support\Facades\Http;

class InitPluginGit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:init-git {plugin : Plugin folder name} {--gitea-url=https://innoshop.work : Gitea server URL} {--gitea-token= : Gitea API token (required for creating repository)} {--org=splugins : Organization name, default is splugins} {--private : Create private repository} {--public : Create public repository} {--commit-message=Initial commit : Commit message} {--force : Force reinitialize if Git repo exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Git repository for a plugin and push to innoshop.work';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $pluginName = $this->argument('plugin');
        $pluginPath = base_path("plugins/{$pluginName}");

        if (! File::exists($pluginPath)) {
            $this->error("Plugin directory does not exist: {$pluginPath}");

            return Command::FAILURE;
        }

        // Get token and URL from various sources
        $giteaUrl   = $this->option('gitea-url');
        $giteaToken = $this->option('gitea-token');

        // If not provided via option, try to get from environment or storage
        if (empty($giteaToken)) {
            // Try .env first
            $giteaToken = env('GITEA_TOKEN');
        }

        if (empty($giteaToken)) {
            // Try storage file
            $storagePath = storage_path('app/.gitea_token');
            if (File::exists($storagePath)) {
                $data       = json_decode(File::get($storagePath), true);
                $giteaToken = $data['token'] ?? null;
            }
        }

        // Get URL from environment or storage
        if (empty($giteaUrl)) {
            $giteaUrl = env('GITEA_URL', 'https://innoshop.work');

            // Try storage file for URL
            $storagePath = storage_path('app/.gitea_token');
            if (File::exists($storagePath)) {
                $data     = json_decode(File::get($storagePath), true);
                $giteaUrl = $data['url'] ?? $giteaUrl;
            }
        }

        $org = $this->option('org');
        // Default to private repository unless --public is explicitly set
        $isPrivate     = ! $this->option('public');
        $commitMessage = $this->option('commit-message');
        $force         = $this->option('force');

        // If token is still empty, prompt user
        if (empty($giteaToken)) {
            $this->warn('⚠️  Gitea token not found.');
            $this->line('You can either:');
            $this->line('1. Use --gitea-token option: php artisan dev:init-git PluginName --gitea-token=your_token');
            $this->line('2. Save token first: php artisan dev:set-gitea-token your_token');
            $this->newLine();

            if (! $this->confirm('Continue without token? (Repository will not be created automatically)', false)) {
                return Command::FAILURE;
            }
        }

        $this->info("Initializing Git repository for plugin: {$pluginName}");

        try {
            // 1. Initialize Git repository
            $gitDir    = "{$pluginPath}/.git";
            $isGitRepo = File::exists($gitDir);

            if ($isGitRepo && $force) {
                $this->warn('Force reinitializing Git repository...');
                File::deleteDirectory($gitDir);
                $isGitRepo = false;
            }

            if (! $isGitRepo) {
                $this->info('  📦 Initializing Git repository...');
                $this->executeCommand("cd {$pluginPath} && git init", $pluginPath);
            } else {
                $this->info('  ℹ️  Git repository already exists');
            }

            // 2. Set remote URL
            $remoteUrl = "git@innoshop.work:splugins/{$pluginName}.git";
            $this->info("  🔗 Setting remote URL: {$remoteUrl}");

            $hasOrigin = $this->hasRemoteOrigin($pluginPath);
            if ($hasOrigin) {
                $this->executeCommand("cd {$pluginPath} && git remote set-url origin {$remoteUrl}", $pluginPath);
            } else {
                try {
                    $this->executeCommand("cd {$pluginPath} && git remote add origin {$remoteUrl}", $pluginPath);
                } catch (\Exception $e) {
                    if (str_contains($e->getMessage(), 'already exists')) {
                        $this->executeCommand("cd {$pluginPath} && git remote set-url origin {$remoteUrl}", $pluginPath);
                    } else {
                        throw $e;
                    }
                }
            }

            // 3. Create repository via API if token is provided
            if ($giteaToken) {
                $this->info('  🌐 Creating repository via API...');
                $this->createGiteaRepository($giteaUrl, $giteaToken, $org, $pluginName, $isPrivate);
                sleep(1); // Wait for repository creation
            }

            // 4. Add and commit files
            $this->info('  📝 Adding files to staging area...');
            $this->executeCommand("cd {$pluginPath} && git add .", $pluginPath);

            // Check if there are changes to commit
            $status = $this->executeCommand("cd {$pluginPath} && git status --porcelain", $pluginPath, false);
            if ($status && trim($status)) {
                $this->info("  💾 Creating commit: {$commitMessage}");
                $this->executeCommand("cd {$pluginPath} && git commit -m '{$commitMessage}'", $pluginPath);
            } else {
                $this->info('  ℹ️  No changes to commit');
            }

            // 5. Create or switch to main branch
            $currentBranch = $this->executeCommand("cd {$pluginPath} && git branch --show-current", $pluginPath, false);
            $currentBranch = trim($currentBranch);

            if (empty($currentBranch)) {
                $mainBranchExists = $this->executeCommand("cd {$pluginPath} && git show-ref --verify --quiet refs/heads/main 2>/dev/null; echo $?", $pluginPath, false);
                if ($mainBranchExists && trim($mainBranchExists) === '0') {
                    $this->info('  🌿 Switching to main branch...');
                    $this->executeCommand("cd {$pluginPath} && git checkout main", $pluginPath);
                } else {
                    $this->info('  🌿 Creating main branch...');
                    $this->executeCommand("cd {$pluginPath} && git checkout -b main", $pluginPath);
                }
                $currentBranch = 'main';
            }

            // 6. Push to remote
            $this->info('  📤 Pushing to remote repository...');
            $remoteExists = $this->checkRemoteRepositoryExists($pluginPath);

            if (! $remoteExists && $giteaToken) {
                $this->info('  🌐 Remote repository does not exist, creating via API...');
                $this->createGiteaRepository($giteaUrl, $giteaToken, $org, $pluginName, $isPrivate);
                sleep(1);
            }

            // Check if remote branch exists
            $remoteBranch = $this->executeCommand("cd {$pluginPath} && git ls-remote --heads origin {$currentBranch} 2>/dev/null", $pluginPath, false);
            $branchExists = ! empty(trim($remoteBranch));

            if (! $branchExists) {
                $this->executeCommand("cd {$pluginPath} && git push -u origin {$currentBranch}", $pluginPath);
            } else {
                $this->executeCommand("cd {$pluginPath} && git push origin {$currentBranch}", $pluginPath);
            }

            $this->info('  ✅ Git repository initialized and pushed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("  ❌ Error: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    /**
     * Execute shell command.
     *
     * @param  string  $command
     * @param  string  $workingDir
     * @param  bool  $throwOnError
     * @return string|null
     * @throws \Exception
     */
    private function executeCommand(string $command, string $workingDir, bool $throwOnError = true): ?string
    {
        $output    = [];
        $returnVar = 0;

        exec($command.' 2>&1', $output, $returnVar);

        if ($returnVar !== 0 && $throwOnError) {
            $errorMessage = implode("\n", $output);
            throw new \Exception("Command failed: {$errorMessage}");
        }

        return ! empty($output) ? implode("\n", $output) : null;
    }

    /**
     * Check if remote origin exists.
     *
     * @param  string  $directory
     * @return bool
     */
    private function hasRemoteOrigin(string $directory): bool
    {
        $output    = [];
        $returnVar = 0;

        exec("cd {$directory} && git remote show origin 2>/dev/null", $output, $returnVar);

        return $returnVar === 0;
    }

    /**
     * Check if remote repository exists.
     *
     * @param  string  $directory
     * @return bool
     */
    private function checkRemoteRepositoryExists(string $directory): bool
    {
        $output    = [];
        $returnVar = 0;

        exec("cd {$directory} && git ls-remote origin 2>/dev/null", $output, $returnVar);

        return $returnVar === 0;
    }

    /**
     * Create Gitea repository via API.
     *
     * @param  string  $giteaUrl
     * @param  string  $token
     * @param  string  $org
     * @param  string  $repoName
     * @param  bool  $isPrivate
     * @return void
     * @throws \Exception
     */
    private function createGiteaRepository(string $giteaUrl, string $token, string $org, string $repoName, bool $isPrivate = true): void
    {
        $giteaUrl = rtrim($giteaUrl, '/');

        // Verify token
        $userInfo = $this->getGiteaUserInfo($giteaUrl, $token);
        if ($userInfo === '无法获取用户信息') {
            throw new \Exception('Invalid or expired token. Please check your token.');
        }

        // Check if repository exists
        $checkEndpoint = "{$giteaUrl}/api/v1/repos/{$org}/{$repoName}";
        $checkResponse = Http::withHeaders([
            'Authorization' => "token {$token}",
        ])->get($checkEndpoint);

        if ($checkResponse->successful()) {
            $this->info("  ✓ Repository already exists: {$repoName}");

            return;
        }

        // Create repository
        $endpoint    = "{$giteaUrl}/api/v1/orgs/{$org}/repos";
        $requestData = [
            'name'        => $repoName,
            'description' => "InnoShop Plugin: {$repoName}",
            'private'     => $isPrivate,
            'auto_init'   => false,
        ];

        $response = Http::withHeaders([
            'Authorization' => "token {$token}",
            'Content-Type'  => 'application/json',
        ])->post($endpoint, $requestData);

        if ($response->successful()) {
            $this->info("  ✓ Repository created successfully: {$repoName}");
        } elseif ($response->status() === 409) {
            $this->info("  ✓ Repository already exists: {$repoName}");
        } else {
            $responseBody = $response->json();
            $errorMessage = $responseBody['message'] ?? $response->body();
            $statusCode   = $response->status();
            throw new \Exception("Failed to create repository: {$errorMessage} (Status: {$statusCode})");
        }
    }

    /**
     * Get Gitea user info.
     *
     * @param  string  $giteaUrl
     * @param  string  $token
     * @return string
     */
    private function getGiteaUserInfo(string $giteaUrl, string $token): string
    {
        $response = Http::withHeaders([
            'Authorization' => "token {$token}",
        ])->get(rtrim($giteaUrl, '/').'/api/v1/user');

        if ($response->successful()) {
            $user = $response->json();

            return $user['login'] ?? 'unknown';
        }

        return '无法获取用户信息';
    }
}
