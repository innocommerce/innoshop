<?php

namespace InnoShop\RestAPI\Services;

use Illuminate\Http\UploadedFile;

interface FileManagerInterface
{
    /**
     * Get files list
     */
    public function getFiles(string $baseFolder, string $keyword = '', string $sort = 'created', string $order = 'desc', int $page = 1, int $perPage = 20): array;

    /**
     * Get directories list
     */
    public function getDirectories(string $baseFolder = '/'): array;

    /**
     * Create directory
     */
    public function createDirectory(string $path): bool;

    /**
     * Upload file
     */
    public function uploadFile(UploadedFile $file, string $savePath, string $originName): string;

    /**
     * Delete files
     */
    public function deleteFiles(string $basePath, array $files): bool;

    /**
     * Delete directory or file
     */
    public function deleteDirectoryOrFile(string $path): bool;

    /**
     * Move directory
     */
    public function moveDirectory(string $sourcePath, string $destPath): bool;

    /**
     * Move files
     */
    public function moveFiles(array $files, string $destPath): bool;

    /**
     * Copy files
     */
    public function copyFiles(array $files, string $destPath): bool;

    /**
     * Rename file or directory
     */
    public function updateName(string $originPath, string $newPath): bool;
}
