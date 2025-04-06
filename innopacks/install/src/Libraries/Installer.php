<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Libraries;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InnoShop\Install\Libraries\Database\MySQLDatabase;
use InnoShop\Install\Libraries\Database\SQLiteDatabase;
use InnoShop\Install\Libraries\Environment\EnvironmentChecker;

/**
 * Installer class
 *
 * Handles the installation process of InnoShop
 */
class Installer
{
    private EnvironmentChecker $environmentChecker;

    private MySQLDatabase $mysqlDatabase;

    private SQLiteDatabase $sqliteDatabase;

    private string $basePath;

    private string $installedFile;

    public function __construct(?string $basePath = null)
    {
        $this->basePath           = $basePath ?? dirname(dirname(dirname(dirname(__DIR__))));
        $this->installedFile      = $this->basePath.'/storage/installed';
        $this->environmentChecker = new EnvironmentChecker($this->basePath);
        $this->mysqlDatabase      = new MySQLDatabase;
        $this->sqliteDatabase     = new SQLiteDatabase;
    }

    public function setDatabases(MySQLDatabase $mysqlDatabase, SQLiteDatabase $sqliteDatabase): void
    {
        $this->mysqlDatabase  = $mysqlDatabase;
        $this->sqliteDatabase = $sqliteDatabase;
    }

    public function setEnvironmentChecker(EnvironmentChecker $checker): void
    {
        $this->environmentChecker = $checker;
    }

    public function install(array $data): array
    {
        $result = ['success' => false];

        try {
            // Validate data
            $validation = $this->validateData($data);
            if (! $validation['success']) {
                return array_merge($result, ['errors' => $validation['errors']]);
            }

            // Check environment
            $environment = $this->checkEnvironment();
            if (! $environment['success']) {
                return array_merge($result, ['errors' => $environment['errors']]);
            }

            // Check database connection
            $database = $this->checkDatabase($data);
            if (! $database['success']) {
                return array_merge($result, ['errors' => $database['errors']]);
            }

            // Save environment configuration
            $this->saveEnv($data);

            // Run database migrations
            $this->migrate();

            // Create admin user
            $this->createAdmin($data);

            // Mark installation as complete
            $this->markAsInstalled();

            $result['success'] = true;
        } catch (\Exception $e) {
            $result['errors'] = ['installation' => $e->getMessage()];
        }

        return $result;
    }

    public function isInstalled(): bool
    {
        if (! file_exists($this->installedFile)) {
            return false;
        }

        if (! file_exists($this->basePath.'/.env')) {
            return false;
        }

        return true;
    }

    private function validateData(array $data): array
    {
        $errors = [];

        if (empty($data['db_type'])) {
            $errors['db_type'] = 'Database type is required';
        }

        if ($data['db_type'] === 'mysql') {
            $required = ['db_hostname', 'db_port', 'db_name', 'db_username', 'db_password'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)).' is required';
                }
            }
        }

        if (empty($data['admin_email'])) {
            $errors['admin_email'] = 'Admin email is required';
        } elseif (! filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['admin_email'] = 'Invalid email format';
        }

        if (empty($data['admin_password'])) {
            $errors['admin_password'] = 'Admin password is required';
        } elseif (strlen($data['admin_password']) < 8) {
            $errors['admin_password'] = 'Password must be at least 8 characters';
        }

        return [
            'success' => empty($errors),
            'errors'  => $errors,
        ];
    }

    private function checkEnvironment(): array
    {
        $errors = [];

        // Check PHP version
        $phpVersion = $this->environmentChecker->checkPhpVersion();
        if (! $phpVersion['success']) {
            $errors['php_version'] = "PHP version {$phpVersion['version']} is not supported. Required: {$phpVersion['required']}";
        }

        // Check required extensions
        $extensions = $this->environmentChecker->checkExtensions();
        if (! $extensions['success']) {
            $missing              = array_keys(array_filter($extensions['extensions'], fn ($loaded) => ! $loaded));
            $errors['extensions'] = 'Missing required extensions: '.implode(', ', $missing);
        }

        // Check directory permissions
        $permissions = $this->environmentChecker->checkDirectoryPermissions();
        if (! $permissions['success']) {
            $unwritable            = array_keys(array_filter($permissions['directories'], fn ($writable) => ! $writable));
            $errors['permissions'] = 'Cannot write to directories: '.implode(', ', $unwritable);
        }

        return [
            'success' => empty($errors),
            'errors'  => $errors,
        ];
    }

    private function checkDatabase(array $data): array
    {
        $errors = [];

        if ($data['db_type'] === 'mysql') {
            $result = $this->mysqlDatabase->checkConnection($data);
        } else {
            $result = $this->sqliteDatabase->checkConnection($data);
        }

        if (! $result['db_success']) {
            unset($result['db_success']);
            $errors = array_merge($errors, $result);
        }

        return [
            'success' => empty($errors),
            'errors'  => $errors,
        ];
    }

    private function saveEnv(array $data): void
    {
        $env = [
            'APP_NAME'  => $data['app_name'] ?? 'InnoShop',
            'APP_ENV'   => 'production',
            'APP_KEY'   => 'base64:'.base64_encode($this->generateRandomKey()),
            'APP_DEBUG' => 'false',
            'APP_URL'   => $data['app_url'] ?? $this->getAppUrl(),
        ];

        if ($data['db_type'] === 'mysql') {
            $env = array_merge($env, $this->mysqlDatabase->getConfig($data));
        } else {
            $env = array_merge($env, $this->sqliteDatabase->getConfig($data));
        }

        $envContent = '';
        foreach ($env as $key => $value) {
            $envContent .= "{$key}={$value}\n";
        }

        if (! is_dir(dirname($this->basePath.'/.env'))) {
            mkdir(dirname($this->basePath.'/.env'), 0755, true);
        }
        file_put_contents($this->basePath.'/.env', $envContent);
    }

    private function migrate(): void
    {
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    protected function createAdmin(array $data): void
    {
        DB::table('admins')->insert([
            'email'      => $data['admin_email'],
            'password'   => $this->hashPassword($data['admin_password']),
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ]);
    }

    private function markAsInstalled(): void
    {
        if (! is_dir(dirname($this->installedFile))) {
            mkdir(dirname($this->installedFile), 0755, true);
        }
        touch($this->installedFile);
    }

    protected function generateRandomKey(): string
    {
        return Str::random(32);
    }

    protected function getAppUrl(): string
    {
        return url('/');
    }

    protected function hashPassword(string $password): string
    {
        return Hash::make($password);
    }

    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
