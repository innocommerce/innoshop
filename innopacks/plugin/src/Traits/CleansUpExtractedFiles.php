<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Traits;

use Exception;
use Illuminate\Support\Str;
use PhpZip\ZipFile;

trait CleansUpExtractedFiles
{
    /**
     * Extract a zip into a temp directory, run {@see cleanupExtractedFiles} only there, then move
     * top-level entries into <code>$destinationRoot</code>. Avoids running cleanup on the entire
     * <code>plugins/</code> or <code>themes/</code> tree (which would delete sibling <code>.git</code> dirs).
     *
     * If a top-level path already exists (upgrade / re-install), it is removed first, then replaced
     * by the archive version (full directory replace, not file-level merge).
     *
     * @throws Exception
     */
    protected function extractZipAndMergeIntoRoot(string $zipAbsolutePath, string $destinationRoot): void
    {
        if (! is_file($zipAbsolutePath)) {
            throw new Exception('Zip file not found.');
        }

        $tmp = storage_path('app/tmp/zip_install_'.Str::lower(Str::random(16)));
        if (! is_dir($tmp) && ! mkdir($tmp, 0755, true) && ! is_dir($tmp)) {
            throw new Exception('Cannot create temporary directory for extraction.');
        }

        try {
            $zipFile = new ZipFile;
            $zipFile->openFile($zipAbsolutePath)->extractTo($tmp);
            $this->cleanupExtractedFiles($tmp);

            $entries = array_diff(scandir($tmp) ?: [], ['.', '..']);
            if ($entries === []) {
                throw new Exception('Archive is empty.');
            }

            foreach ($entries as $entry) {
                $from = $tmp.DIRECTORY_SEPARATOR.$entry;
                $to   = $destinationRoot.DIRECTORY_SEPARATOR.$entry;

                if (file_exists($to) || is_link($to)) {
                    if (is_dir($to) && ! is_link($to)) {
                        $this->removeDirectory($to);
                    } else {
                        @unlink($to);
                    }
                }

                if (! rename($from, $to)) {
                    throw new Exception("Failed to move \"{$entry}\" into the install directory.");
                }
            }
        } catch (Exception $e) {
            if (is_dir($tmp)) {
                $this->removeDirectory($tmp);
            }
            throw $e;
        }

        if (is_dir($tmp)) {
            @rmdir($tmp);
        }
    }

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
