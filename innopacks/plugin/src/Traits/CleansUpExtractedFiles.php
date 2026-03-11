<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Traits;

trait CleansUpExtractedFiles
{
    /**
     * Clean up unnecessary files from extracted plugin/theme
     * Remove macOS artifacts, hidden files, etc.
     *
     * @param  string  $baseDir
     * @return void
     */
    protected function cleanupExtractedFiles(string $baseDir): void
    {
        $patternsToRemove = [
            '__MACOSX',     // macOS archive metadata
            '.DS_Store',    // macOS folder metadata
            '.git',         // Git directory
            '.github',      // GitHub config directory
            '.idea',        // JetBrains IDE config
            '.vscode',      // VS Code config
            'Thumbs.db',    // Windows thumbnail cache
            'desktop.ini',  // Windows folder settings
        ];

        foreach ($patternsToRemove as $pattern) {
            $this->removeByPattern($baseDir, $pattern);
        }
    }

    /**
     * Remove files/directories matching a pattern recursively
     *
     * @param  string  $dir
     * @param  string  $pattern
     * @return void
     */
    protected function removeByPattern(string $dir, string $pattern): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $basename = $item->getBasename();
            if ($basename === $pattern) {
                $path = $item->getPathname();
                if ($item->isDir()) {
                    $this->removeDirectory($path);
                } else {
                    @unlink($path);
                }
            }
        }
    }

    /**
     * Recursively remove a directory
     *
     * @param  string  $dir
     * @return void
     */
    protected function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $path = $item->getPathname();
            if ($item->isDir()) {
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
