<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Libraries\Environment;

/**
 * EnvironmentChecker class
 *
 * Checks system requirements and environment settings
 */
class EnvironmentChecker
{
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? dirname(dirname(dirname(dirname(__DIR__))));
    }

    public function checkPhpVersion(): array
    {
        $version = phpversion();
        $success = version_compare($version, '8.2.0', '>=');

        return [
            'success'  => $success,
            'version'  => $version,
            'required' => '8.2.0',
        ];
    }

    public function checkExtensions(): array
    {
        $requiredExtensions = [
            'pdo',
            'pdo_mysql',
            'pdo_sqlite',
            'mbstring',
            'openssl',
            'json',
            'fileinfo',
            'tokenizer',
            'ctype',
            'xml',
        ];

        $extensions = [];
        foreach ($requiredExtensions as $extension) {
            $extensions[$extension] = extension_loaded($extension);
        }

        $success = ! in_array(false, $extensions, true);

        return [
            'success'    => $success,
            'extensions' => $extensions,
        ];
    }

    public function checkDirectoryPermissions(): array
    {
        $directories = [
            'storage'         => $this->basePath.'/storage',
            'bootstrap/cache' => $this->basePath.'/bootstrap/cache',
            'public/cache'    => $this->basePath.'/public/cache',
        ];

        $results = [];
        foreach ($directories as $name => $path) {
            $results[$name] = $this->isWritable($path);
        }

        $success = ! in_array(false, $results, true);

        return [
            'success'     => $success,
            'directories' => $results,
        ];
    }

    private function isWritable(string $path): bool
    {
        if (! file_exists($path)) {
            return @mkdir($path, 0755, true);
        }

        return is_writable($path);
    }
}
