<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Libraries;

use Exception;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InnoShop\Common\Models\Admin;
use Symfony\Component\Console\Output\BufferedOutput;

class Creator
{
    private BufferedOutput $outputLog;

    public function __construct()
    {
        $this->outputLog = new BufferedOutput;
    }

    /**
     * @return self
     */
    public static function getInstance(): Creator
    {
        return new self;
    }

    /**
     * @param  $data
     * @return Creator
     * @throws Exception|\Throwable
     */
    public function setup($data): static
    {
        $this->migrate();
        $this->seedData();
        $this->setAdmin($data);
        $this->touchLockFile();

        return $this;
    }

    /**
     * @return BufferedOutput
     */
    public function getOutputLog(): BufferedOutput
    {
        return $this->outputLog;
    }

    /**
     * @param  $data
     * @return void
     * @throws Exception
     */
    public function saveEnv($data): void
    {
        $scheme = is_secure() ? 'https' : 'http';
        $appUrl = $scheme.'://'.$_SERVER['HTTP_HOST'];
        $dbType = strtolower($data['type']);

        $envFileData = 'APP_NAME='.($data['app_name'] ?? 'InnoShop')."\n".
            'APP_ENV='.($data['environment'] ?? 'local')."\n".
            'APP_KEY='.'base64:'.base64_encode(Str::random(32))."\n".
            'APP_DEBUG=false'."\n".
            'APP_TIMEZONE=UTC'."\n".
            'APP_URL='.$appUrl."\n\n".
            'APP_LOCALE=en'."\n\n";
        if ($dbType == 'mysql') {
            $envFileData .= 'DB_CONNECTION='.$data['type']."\n".
                'DB_PREFIX='.($data['db_prefix'] ?: 'inno_')."\n".
                'DB_HOST='.$data['db_hostname']."\n".
                'DB_PORT='.$data['db_port']."\n".
                'DB_DATABASE='.$data['db_name']."\n".
                'DB_USERNAME='.$data['db_username']."\n".
                'DB_PASSWORD=\''.$data['db_password']."'\n";
        } elseif ($dbType == 'sqlite') {
            $envFileData .= 'DB_CONNECTION='.$data['type']."\n".
                'DB_PREFIX='.($data['db_prefix'] ?: 'inno_')."\n";
        }

        file_put_contents(base_path('.env'), $envFileData);
    }

    /**
     * @return void
     * @throws Exception
     */
    private function migrate(): void
    {
        $this->sqlite();

        try {
            Artisan::call('migrate:fresh', ['--force' => true], $this->outputLog);
        } catch (Exception $e) {
            $this->outputLog->write($e);
            throw $e;
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function seedData(): void
    {
        try {
            Artisan::call('db:seed', ['--force' => true], $this->outputLog);
            Artisan::call('storage:link', [], $this->outputLog);
        } catch (Exception $e) {
            $this->outputLog->write($e);
            throw $e;
        }

        $this->outputLog->write(trans('install/common.finished'));
    }

    /**
     * @param  $data
     * @return void
     * @throws \Throwable
     */
    private function setAdmin($data): void
    {
        $email    = $data['admin_email'];
        $password = $data['admin_password'];
        $admin    = Admin::query()->first();
        if (empty($admin)) {
            $admin = new Admin;
        }

        $admin->fill([
            'email'    => $email,
            'password' => bcrypt($password),
        ]);
        $admin->saveOrFail();
    }

    /**
     * @return void
     */
    private function sqlite(): void
    {
        if (DB::connection() instanceof SQLiteConnection) {
            $database = DB::connection()->getDatabaseName();
            if (! file_exists($database)) {
                touch($database);
                DB::reconnect(Config::get('database.default'));
            }
            $this->outputLog->write('Using SqlLite database: '.$database, 1);
        }
    }

    /**
     * @return void
     */
    private function touchLockFile(): void
    {
        touch(storage_path('installed'));
    }
}
