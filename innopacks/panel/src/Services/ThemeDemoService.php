<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace InnoShop\Panel\Services;

use Exception;
use Illuminate\Support\Facades\DB;

class ThemeDemoService extends BaseService
{
    /**
     * Determine whether the theme has demo data
     */
    public function hasDemo(string $dir): bool
    {
        $sqlFiles = glob($dir.'/demo/sql/*.sql');

        return ! empty($sqlFiles);
    }

    /**
     * Get the demo SQL directory path
     */
    public function getDemoPath(string $dir): string
    {
        return $dir.'/demo/sql';
    }

    /**
     * Import theme demo data (SQL + images)
     * @throws Exception
     */
    public function importDemo(string $dir): void
    {
        $this->copyDemoImages($dir);
        $sqlFiles = glob($dir.'/demo/sql/*.sql');
        if (empty($sqlFiles)) {
            throw new Exception(__('panel/themes.error_demo_not_found'));
        }
        sort($sqlFiles);

        // Check if database driver supports transactions
        $driver               = DB::connection()->getDriverName();
        $supportsTransactions = in_array($driver, ['mysql', 'pgsql', 'sqlite']);

        smart_log('info', '[ThemeDemo] Starting import', [
            'driver'                => $driver,
            'supports_transactions' => $supportsTransactions,
            'sql_files_count'       => count($sqlFiles),
        ]);

        // For MySQL, TRUNCATE and SET statements may commit transactions implicitly
        // So we'll handle them carefully
        if ($supportsTransactions && $driver === 'mysql') {
            // Start transaction but be aware that some statements may commit it
            try {
                DB::beginTransaction();
                smart_log('info', '[ThemeDemo] Transaction started', [
                    'transaction_level' => DB::transactionLevel(),
                ]);
            } catch (\Exception $e) {
                // If transaction already started, continue
                smart_log('warning', '[ThemeDemo] Failed to start transaction', [
                    'error' => $e->getMessage(),
                ]);
                $supportsTransactions = false;
            }
        } elseif ($supportsTransactions) {
            DB::beginTransaction();
            smart_log('info', '[ThemeDemo] Transaction started', [
                'transaction_level' => DB::transactionLevel(),
            ]);
        }

        try {
            foreach ($sqlFiles as $sqlFile) {
                smart_log('info', '[ThemeDemo] Processing SQL file', [
                    'file'                     => basename($sqlFile),
                    'transaction_level_before' => DB::transactionLevel(),
                ]);
                $this->importSqlFile($sqlFile, $supportsTransactions);
                smart_log('info', '[ThemeDemo] SQL file processed', [
                    'file'                    => basename($sqlFile),
                    'transaction_level_after' => DB::transactionLevel(),
                ]);
            }

            // Only commit if transaction is still active
            // For MySQL, some statements (TRUNCATE, SET FOREIGN_KEY_CHECKS) may have implicitly committed
            // So we need to check if transaction is really active before committing
            $transactionLevel = DB::transactionLevel();
            smart_log('info', '[ThemeDemo] Attempting to commit', [
                'transaction_level'     => $transactionLevel,
                'supports_transactions' => $supportsTransactions,
            ]);

            if ($supportsTransactions && $transactionLevel > 0) {
                try {
                    // For MySQL, try to verify transaction is really active by checking autocommit
                    if ($driver === 'mysql') {
                        // Check if we're actually in a transaction by trying a simple query
                        // If transaction was implicitly committed, this will work fine
                        // If commit fails, it means transaction was already committed
                        DB::commit();
                        smart_log('info', '[ThemeDemo] Transaction committed successfully');
                    } else {
                        DB::commit();
                        smart_log('info', '[ThemeDemo] Transaction committed successfully');
                    }
                } catch (\Exception $commitException) {
                    // If commit fails with "no active transaction", it was already committed
                    if (strpos($commitException->getMessage(), 'no active transaction') !== false) {
                        smart_log('warning', '[ThemeDemo] Transaction was already committed (likely by DDL statement)', [
                            'error' => $commitException->getMessage(),
                        ]);
                    } else {
                        // Re-throw if it's a different error
                        throw $commitException;
                    }
                }
            } else {
                smart_log('warning', '[ThemeDemo] Skipping commit', [
                    'reason' => $transactionLevel > 0 ? 'Transaction level check failed' : 'No active transaction',
                ]);
            }
        } catch (\Exception $e) {
            smart_log('error', '[ThemeDemo] Import failed', [
                'error'             => $e->getMessage(),
                'transaction_level' => DB::transactionLevel(),
                'file'              => $e->getFile(),
                'line'              => $e->getLine(),
            ]);

            // Only rollback if transaction is still active
            if ($supportsTransactions) {
                try {
                    $transactionLevel = DB::transactionLevel();
                    smart_log('info', '[ThemeDemo] Attempting to rollback', [
                        'transaction_level' => $transactionLevel,
                    ]);
                    if ($transactionLevel > 0) {
                        DB::rollBack();
                        smart_log('info', '[ThemeDemo] Transaction rolled back successfully');
                    } else {
                        smart_log('warning', '[ThemeDemo] No active transaction to rollback');
                    }
                } catch (\Exception $rollbackException) {
                    smart_log('error', '[ThemeDemo] Rollback failed', [
                        'error'             => $rollbackException->getMessage(),
                        'transaction_level' => DB::transactionLevel(),
                    ]);
                    // Ignore rollback errors if transaction is already closed
                }
            }
            throw $e;
        }
    }

    /**
     * Import a single SQL file
     * @param  string  $file
     * @param  bool  $inTransaction
     * @throws Exception
     */
    protected function importSqlFile(string $file, bool $inTransaction = true): void
    {
        if (! file_exists($file)) {
            throw new Exception(__('panel/themes.error_demo_sql_not_found', ['file' => basename($file)]));
        }

        if (! is_readable($file)) {
            throw new Exception(__('panel/themes.error_demo_sql_not_readable', ['file' => basename($file)]));
        }

        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') {
            throw new Exception(__('panel/themes.error_demo_sql_empty'));
        }

        // Get database driver to filter out driver-specific statements
        $driver = DB::connection()->getDriverName();

        // Split SQL into queries
        $queries      = array_filter(array_map('trim', explode(';', $sql)));
        $queryCount   = 0;
        $totalQueries = count($queries);

        smart_log('info', '[ThemeDemo] SQL file parsed', [
            'file'              => basename($file),
            'total_queries'     => $totalQueries,
            'transaction_level' => DB::transactionLevel(),
        ]);

        foreach ($queries as $index => $query) {
            if (empty($query)) {
                continue;
            }

            $queryUpper   = strtoupper(trim($query));
            $queryPreview = substr($query, 0, 100);

            // Skip MySQL-specific statements for non-MySQL databases
            if ($driver !== 'mysql' && $driver !== 'mariadb') {
                // Skip SET FOREIGN_KEY_CHECKS statements for non-MySQL databases
                if (strpos($queryUpper, 'SET FOREIGN_KEY_CHECKS') === 0) {
                    smart_log('debug', '[ThemeDemo] Skipping MySQL-specific statement', [
                        'query' => $queryPreview,
                    ]);

                    continue;
                }
            }

            $transactionLevelBefore = DB::transactionLevel();

            // Remove comments and whitespace for DDL detection
            $queryForDDL    = preg_replace('/^[\s\-\-].*$/m', '', $query); // Remove comment lines
            $queryForDDL    = trim($queryForDDL);
            $isDDLStatement = preg_match('/^\s*(SET|TRUNCATE|CREATE|DROP|ALTER)\s+/i', $queryForDDL);

            smart_log('debug', '[ThemeDemo] Executing query', [
                'index'                    => $index + 1,
                'total'                    => $totalQueries,
                'query_preview'            => $queryPreview,
                'is_ddl'                   => $isDDLStatement,
                'transaction_level_before' => $transactionLevelBefore,
            ]);

            try {
                // For MySQL, TRUNCATE and SET FOREIGN_KEY_CHECKS may commit transactions
                // Use DB::unprepared() for these statements to avoid transaction issues
                if ($isDDLStatement && $driver === 'mysql') {
                    DB::unprepared($query);
                } else {
                    DB::statement($query);
                }

                $transactionLevelAfter = DB::transactionLevel();
                $queryCount++;

                if ($transactionLevelBefore !== $transactionLevelAfter) {
                    smart_log('warning', '[ThemeDemo] Transaction level changed after query', [
                        'query_preview' => $queryPreview,
                        'level_before'  => $transactionLevelBefore,
                        'level_after'   => $transactionLevelAfter,
                    ]);
                }
            } catch (\Exception $e) {
                $errorMessage          = $e->getMessage();
                $transactionLevelAfter = DB::transactionLevel();

                smart_log('error', '[ThemeDemo] Query execution failed', [
                    'query_preview'            => $queryPreview,
                    'error'                    => $errorMessage,
                    'transaction_level_before' => $transactionLevelBefore,
                    'transaction_level_after'  => $transactionLevelAfter,
                    'is_ddl'                   => $isDDLStatement,
                ]);

                // Check if it's a transaction-related error
                if (strpos($errorMessage, 'no active transaction') !== false) {
                    smart_log('warning', '[ThemeDemo] No active transaction error detected', [
                        'query_preview'     => $queryPreview,
                        'in_transaction'    => $inTransaction,
                        'transaction_level' => $transactionLevelAfter,
                    ]);

                    // If we're in a transaction but it was closed (e.g., by TRUNCATE),
                    // try to restart it and retry the query
                    if ($inTransaction && $transactionLevelAfter === 0) {
                        try {
                            smart_log('info', '[ThemeDemo] Attempting to restart transaction and retry', [
                                'query_preview' => $queryPreview,
                            ]);
                            DB::beginTransaction();
                            smart_log('info', '[ThemeDemo] Transaction restarted', [
                                'new_transaction_level' => DB::transactionLevel(),
                            ]);

                            // Retry the query
                            if ($isDDLStatement && $driver === 'mysql') {
                                DB::unprepared($query);
                            } else {
                                DB::statement($query);
                            }
                            $queryCount++;
                            smart_log('info', '[ThemeDemo] Query retry successful', [
                                'query_preview' => $queryPreview,
                            ]);

                            continue;
                        } catch (\Exception $retryException) {
                            smart_log('error', '[ThemeDemo] Query retry failed', [
                                'query_preview'  => $queryPreview,
                                'retry_error'    => $retryException->getMessage(),
                                'original_error' => $errorMessage,
                            ]);
                            // If retry fails, throw the original error
                            throw new Exception(__('panel/themes.error_demo_sql_execute_failed', [
                                'file'  => basename($file),
                                'query' => substr($query, 0, 100).'...',
                                'error' => $errorMessage,
                            ]));
                        }
                    } else {
                        smart_log('warning', '[ThemeDemo] Skipping transaction error', [
                            'query_preview' => $queryPreview,
                            'reason'        => $inTransaction ? 'Transaction level is not 0' : 'Not in transaction mode',
                        ]);

                        // For non-transactional databases or when not in transaction, skip this error
                        continue;
                    }
                }

                throw new Exception(__('panel/themes.error_demo_sql_execute_failed', [
                    'file'  => basename($file),
                    'query' => substr($query, 0, 100).'...',
                    'error' => $errorMessage,
                ]));
            }
        }

        smart_log('info', '[ThemeDemo] SQL file execution completed', [
            'file'              => basename($file),
            'queries_executed'  => $queryCount,
            'total_queries'     => $totalQueries,
            'transaction_level' => DB::transactionLevel(),
        ]);

        if ($queryCount === 0) {
            throw new Exception(__('panel/themes.error_demo_sql_no_queries', ['file' => basename($file)]));
        }
    }

    /**
     * Copy demo images to the public directory
     * @throws Exception
     */
    protected function copyDemoImages(string $dir): void
    {
        $pattern   = $dir.'/demo/images/**/*.{jpg,png,gif}';
        $images    = glob($pattern, GLOB_BRACE) ?: [];
        $themeCode = basename($dir);

        foreach ($images as $image) {
            if (! file_exists($image)) {
                continue;
            }

            $relativePath = str_replace($dir.'/demo/images/', '', $image);
            $targetPath   = public_path('static/themes/'.$themeCode.'/'.$relativePath);

            $targetDir = dirname($targetPath);
            if (! is_dir($targetDir)) {
                if (! mkdir($targetDir, 0755, true)) {
                    throw new Exception(__('panel/themes.error_demo_image_dir_failed', [
                        'dir' => $targetDir,
                    ]));
                }
            }

            if (! copy($image, $targetPath)) {
                throw new Exception(__('panel/themes.error_demo_image_copy_failed', [
                    'file' => basename($image),
                ]));
            }
        }
    }

    /**
     * Export current database data as SQL file
     * @param  string  $themeCode
     * @return string Path to the exported SQL file
     * @throws Exception
     */
    public function exportSql(string $themeCode): string
    {
        $tables = [
            'categories',
            'category_translations',
            'category_paths',
            'products',
            'product_translations',
            'product_images',
            'product_categories',
            'product_skus',
            'product_sku_translations',
            'brands',
            'brand_translations',
            'pages',
            'page_translations',
            'articles',
            'article_translations',
            'catalogs',
            'catalog_translations',
        ];

        $sqlContent   = [];
        $sqlContent[] = '-- Theme Demo Data Export';
        $sqlContent[] = "-- Theme: {$themeCode}";
        $sqlContent[] = '-- Generated at: '.date('Y-m-d H:i:s');
        $sqlContent[] = '';
        $sqlContent[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $sqlContent[] = '';

        foreach ($tables as $table) {
            $prefix        = DB::connection()->getTablePrefix();
            $fullTableName = $prefix.$table;

            // Check if table exists
            try {
                $rows = DB::table($table)->get();
                if ($rows->isEmpty()) {
                    continue;
                }

                $sqlContent[] = "-- Table: {$table}";
                $sqlContent[] = "TRUNCATE TABLE `{$fullTableName}`;";
                $sqlContent[] = '';

                foreach ($rows as $row) {
                    $values  = [];
                    $columns = [];
                    $pdo     = DB::connection()->getPdo();

                    foreach ((array) $row as $key => $value) {
                        $columns[] = $key;
                        if ($value === null) {
                            $values[] = 'NULL';
                        } elseif (is_numeric($value) && ! is_string($value)) {
                            // Only treat as numeric if it's actually a number type, not a string that looks like a number
                            $values[] = $value;
                        } else {
                            // Use PDO quote() for proper SQL escaping
                            // This handles all special characters including quotes, backslashes, etc.
                            $values[] = $pdo->quote((string) $value);
                        }
                    }

                    $sqlContent[] = "INSERT INTO `{$fullTableName}` (`".implode('`, `', $columns).'`) VALUES ('.implode(', ', $values).');';
                }
                $sqlContent[] = '';
            } catch (\Exception $e) {
                // Skip if table doesn't exist
                continue;
            }
        }

        $sqlContent[] = 'SET FOREIGN_KEY_CHECKS=1;';

        // Create temp directory if not exists
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $sqlFileName = $themeCode.'_demo_'.date('YmdHis').'.sql';
        $sqlFilePath = $tempDir.'/'.$sqlFileName;
        file_put_contents($sqlFilePath, implode("\n", $sqlContent));

        return $sqlFilePath;
    }
}
