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
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;

class Checker
{
    /**
     * @return self
     */
    public static function getInstance(): Checker
    {
        return new self;
    }

    /**
     * Please see https://laravel.com/docs/11.x/deployment#server-requirements
     *
     * @return array
     */
    public function getEnvironment(): array
    {
        $phpVersion = phpversion();

        return [
            'php_version' => $phpVersion,
            'php_env'     => version_compare($phpVersion, '8.2.0') >= 0,
            'extensions'  => [
                'ctype'      => extension_loaded('ctype'),
                'curl'       => extension_loaded('curl'),
                'dom'        => extension_loaded('dom'),
                'fileinfo'   => extension_loaded('fileinfo'),
                'filter'     => extension_loaded('filter'),
                'hash'       => extension_loaded('hash'),
                'mbstring'   => extension_loaded('mbstring'),
                'pdo_mysql'  => extension_loaded('pdo_mysql'),
                'pdo_sqlite' => extension_loaded('pdo_sqlite'),
                'openssl'    => extension_loaded('openssl'),
                'session'    => extension_loaded('session'),
                'sqlite3'    => extension_loaded('sqlite3'),
                'tokenizer'  => extension_loaded('tokenizer'),
                'xml'        => extension_loaded('xml'),
            ],
            'permissions' => [
                '.env'            => $this->checkPermission('.env', 755),
                'storage'         => $this->checkPermission('storage', 755),
                'public/cache'    => $this->checkPermission('public/cache', 755),
                'bootstrap/cache' => $this->checkPermission('bootstrap/cache', 755),
            ],
            'driver_url' => route('install.install.driver_detect'),
        ];
    }

    /**
     * @param  $folder
     * @param  $permission
     * @return bool
     */
    public function checkPermission($folder, $permission): bool
    {
        if (! ($this->getPermission($folder) >= $permission) && php_uname('s') != 'Windows NT') {
            return false;
        }

        return true;
    }

    /**
     * @param  $folder
     * @return string
     */
    private function getPermission($folder): string
    {
        return substr(sprintf('%o', fileperms(base_path($folder))), -4);
    }

    /**
     * Check database connected.
     *
     * @param  $data
     * @return array
     */
    public function checkConnection($data): array
    {
        $type = strtolower($data['type']);
        if ($type == 'mysql') {
            $this->configMySQL($data);
        } elseif ($type == 'sqlite') {
            $this->configSQLite();
        }

        DB::purge();
        $result = [];
        try {
            $pdo     = DB::connection()->getPdo();
            $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            if ($type == 'mysql' && version_compare($version, '5.7', '<')) {
                $result['db_version'] = trans('install/common.invalid_version');

                return $result;
            }
            $result['db_success'] = true;
            Creator::getInstance()->saveEnv($data);

            return $result;
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case 1115:
                    $result['db_version'] = trans('install/common.invalid_version');
                    break;
                case 2002:
                    $result['db_hostname'] = trans('install/common.failed_host_port');
                    $result['db_port']     = trans('install/common.failed_host_port');
                    break;
                case 1045:
                    $result['db_username'] = trans('install/common.failed_user_password');
                    $result['db_password'] = trans('install/common.failed_user_password');
                    break;
                case 1049:
                    $result['db_name'] = trans('install/common.failed_db_name');
                    break;
                default:
                    $result['db_other'] = $e->getMessage();
            }
            $result['db_success'] = false;
        } catch (Exception $e) {
            $result['env_other'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * @param  $data
     * @return void
     */
    private function configMySQL($data): void
    {
        $settings = config('database.connections.mysql');
        config([
            'database' => [
                'default'     => 'mysql',
                'connections' => [
                    'mysql' => array_merge($settings, [
                        'driver'   => 'mysql',
                        'host'     => $data['db_hostname'],
                        'port'     => $data['db_port'],
                        'database' => $data['db_name'],
                        'username' => $data['db_username'],
                        'password' => $data['db_password'],
                        'options'  => [
                            PDO::ATTR_TIMEOUT => 1,
                        ],
                    ]),
                ],
            ],
        ]);
    }

    /**
     * @return void
     */
    private function configSQLite(): void
    {
        $databasePath = database_path('database.sqlite');
        if (! file_exists($databasePath)) {
            touch($databasePath);
        }

        $settings = config('database.connections.sqlite');
        config([
            'database' => [
                'default'     => 'sqlite',
                'connections' => [
                    'sqlite' => array_merge($settings, [
                        'driver'   => 'sqlite',
                        'url'      => null,
                        'database' => $databasePath,
                    ]),
                ],
            ],
        ]);
    }
}
