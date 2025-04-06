<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Libraries\Database;

use PDO;
use PDOException;

/**
 * SQLiteDatabase class
 *
 * Handles SQLite database connection and configuration
 */
class SQLiteDatabase
{
    public function checkConnection(array $data): array
    {
        $result = ['db_success' => false];
        $dbPath = $data['db_path'] ?? database_path('database.sqlite');

        try {
            // 确保数据库目录存在
            $dbDir = dirname($dbPath);
            if (! is_dir($dbDir)) {
                // 尝试创建目录
                if (! @mkdir($dbDir, 0755, true)) {
                    // 如果创建失败,检查是否是权限问题
                    if (! is_writable(dirname($dbDir))) {
                        $result['db_path'] = trans('install/common.dir_permission_denied');

                        return $result;
                    }
                    $result['db_path'] = trans('install/common.dir_create_failed');

                    return $result;
                }
            }

            // 检查目录是否可写
            if (! is_writable($dbDir)) {
                $result['db_path'] = trans('install/common.dir_not_writable');

                return $result;
            }

            // 创建或检查数据库文件
            if (! file_exists($dbPath)) {
                if (! @touch($dbPath)) {
                    $result['db_path'] = trans('install/common.file_create_failed');

                    return $result;
                }
            }

            // 检查文件是否可写
            if (! is_writable($dbPath)) {
                $result['db_path'] = trans('install/common.file_not_writable');

                return $result;
            }

            // 设置正确的权限
            if (! @chmod($dbPath, 0666)) {
                $result['db_path'] = trans('install/common.file_permission_failed');

                return $result;
            }

            // 测试连接
            $pdo = new PDO(
                "sqlite:{$dbPath}",
                null,
                null,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $result['db_success'] = true;
        } catch (PDOException $e) {
            $result['db_error'] = $e->getMessage();
        }

        return $result;
    }

    public function getConfig(array $data): array
    {
        $dbPath = $data['db_path'] ?? database_path('database.sqlite');

        return [
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE'   => $dbPath,
        ];
    }
}
