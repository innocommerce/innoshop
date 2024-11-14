<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Enterprise\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManagerService
{
    protected string $fileBasePath = '';

    protected string $basePath = '';

    public function __construct()
    {
        $this->fileBasePath = public_path('catalog').$this->basePath;
    }

    /**
     * Retrieves directories within a base folder.
     *
     * @param  string  $baseFolder
     * @return array
     */
    public function getDirectories(string $baseFolder = '/'): array
    {
        $currentBasePath = rtrim($this->fileBasePath.$baseFolder, '/');
        $directories     = glob("$currentBasePath/*", GLOB_ONLYDIR);

        $result = [];
        foreach ($directories as $directory) {
            $baseName = basename($directory);
            $dirName  = str_replace($this->fileBasePath, '', $directory);
            if (is_dir($directory)) {
                $item           = $this->handleFolder($dirName, $baseName);
                $subDirectories = $this->getDirectories($dirName);
                if ($subDirectories) {
                    $item['children'] = $subDirectories;
                }
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Fetches files in a directory based on filters.
     *
     * @param  string  $baseFolder
     * @param  string  $keyword
     * @param  string  $sort
     * @param  string  $order
     * @param  int  $page
     * @param  int  $perPage
     * @return array
     * @throws Exception
     */
    public function getFiles(string $baseFolder, string $keyword, string $sort, string $order, int $page = 1, int $perPage = 20): array
    {
        $currentBasePath = rtrim($this->fileBasePath.$baseFolder, '/');
        $files           = glob($currentBasePath.'/*');

        if ($sort === 'created') {
            usort($files, function ($a, $b) use ($order) {
                return ($order === 'desc') ? filemtime($b) - filemtime($a) : filemtime($a) - filemtime($b);
            });
        } else {
            natcasesort($files);
            if ($order === 'desc') {
                $files = array_reverse($files);
            }
        }

        $images = [];
        foreach ($files as $file) {
            $baseName = basename($file);
            if ($baseName === 'index.html' || ($keyword && ! str_contains($baseName, $keyword))) {
                continue;
            }
            $fileName = str_replace(public_path('catalog'), '', $file);
            if (is_file($file)) {
                $images[] = $this->handleImage($fileName, $baseName);
            }
        }

        $imageCollection = collect($images);
        $currentImages   = $imageCollection->forPage($page, $perPage);

        return [
            'images'      => $currentImages->values(),
            'image_total' => $imageCollection->count(),
            'image_page'  => $page,
        ];
    }

    /**
     * Creates a new directory.
     *
     * @param  string  $folderName
     * @throws Exception
     */
    public function createDirectory(string $folderName): void
    {
        $folderPath = public_path("catalog{$this->basePath}/{$folderName}");
        if (is_dir($folderPath)) {
            throw new Exception(trans('admin/file_manager.directory_already_exist'));
        }
        create_directories("catalog{$this->basePath}/{$folderName}");
    }

    /**
     * Moves a directory to a new path.
     *
     * @param  string  $sourcePath
     * @param  string  $destPath
     * @throws Exception
     */
    public function moveDirectory(string $sourcePath, string $destPath): void
    {
        if (empty($sourcePath) || empty($destPath)) {
            throw new Exception(trans('admin/file_manager.empty_path'));
        }

        $folderName    = basename($sourcePath);
        $sourceDirPath = public_path("catalog{$this->basePath}{$sourcePath}/");
        $destDirPath   = public_path("catalog{$this->basePath}{$destPath}");

        $destFullPath = "{$destDirPath}/{$folderName}";
        if (! File::exists($destFullPath)) {
            move_dir($sourceDirPath, $destDirPath);
        } else {
            throw new Exception(trans('admin/file_manager.target_dir_exist'));
        }
    }

    /**
     * Moves multiple files to a new directory.
     *
     * @param  array  $images
     * @param  string  $destPath
     */
    public function moveFiles(array $images, string $destPath): void
    {
        $destDirPath = public_path("catalog{$this->basePath}{$destPath}");

        foreach ($images as $image) {
            $sourceDirPath = public_path($image);
            File::move($sourceDirPath, "{$destDirPath}/".basename($sourceDirPath));
        }
    }

    /**
     * Zips a folder and returns the zip path.
     *
     * @param  string  $imagePath
     * @return string
     */
    public function zipFolder(string $imagePath): string
    {
        $realPath = $this->fileBasePath.$imagePath;
        $zipName  = basename($realPath).'-'.date('Ymd').'.zip';
        $zipPath  = public_path($zipName);
        zip_folder($realPath, $zipPath);

        return $zipPath;
    }

    /**
     * Deletes a file or folder.
     *
     * @param  string  $filePath
     * @throws Exception
     */
    public function deleteDirectoryOrFile(string $filePath): void
    {
        $fullPath = public_path("catalog{$this->basePath}/{$filePath}");
        if (is_dir($fullPath)) {
            $files = glob($fullPath.'/*');
            if ($files) {
                throw new Exception(trans('admin/file_manager.directory_not_empty'));
            }
            @rmdir($fullPath);
        } elseif (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }

    /**
     * Deletes multiple files within a base path.
     *
     * @param  string  $basePath
     * @param  array  $files
     */
    public function deleteFiles(string $basePath, array $files): void
    {
        foreach ($files as $file) {
            $filePath = public_path("catalog{$this->basePath}/{$basePath}/$file");
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    /**
     * Renames a file or folder.
     *
     * @param  string  $originPath
     * @param  string  $newPath
     * @throws Exception
     */
    public function updateName(string $originPath, string $newPath): void
    {
        $originPath = public_path("catalog{$this->basePath}/{$originPath}");
        if (! is_dir($originPath) && ! file_exists($originPath)) {
            throw new Exception(trans('admin/file_manager.target_not_exist'));
        }

        $newPath = dirname($originPath).'/'.$newPath;
        if ($originPath === $newPath || file_exists($newPath)) {
            throw new Exception(trans('admin/file_manager.rename_failed'));
        }

        if (! @rename($originPath, $newPath)) {
            throw new Exception(trans('admin/file_manager.rename_failed'));
        }
    }

    /**
     * Uploads a file to a specified path.
     *
     * @param  UploadedFile  $file
     * @param  string  $savePath
     * @param  string  $originName
     * @return string
     */
    public function uploadFile(UploadedFile $file, string $savePath, string $originName): string
    {
        $originName = $this->getUniqueFileName($savePath, $originName);
        $filePath   = $file->storeAs($this->basePath.$savePath, $originName, 'catalog');

        return asset('catalog/'.$filePath);
    }

    /**
     * Generates a unique file name to avoid conflicts.
     *
     * @param  string  $savePath
     * @param  string  $originName
     * @return string
     */
    public function getUniqueFileName(string $savePath, string $originName): string
    {
        if (is_file(public_path('catalog'.$this->basePath.$savePath.'/'.$originName))) {
            $originName = $this->getNewFileName($originName);

            return $this->getUniqueFileName($savePath, $originName);
        }

        return $originName;
    }

    /**
     * Generates a new file name by appending an incremented index.
     *
     * @param  string  $originName
     * @return string
     */
    public function getNewFileName(string $originName): string
    {
        $extension = pathinfo($originName, PATHINFO_EXTENSION);
        $name      = pathinfo($originName, PATHINFO_FILENAME);
        if (preg_match('/(.+?)-(\d+)$/', $name, $matches)) {
            $index = (int) $matches[2] + 1;
            $name  = "{$matches[1]}-{$index}";
        } else {
            $name .= '-1';
        }

        return "{$name}.{$extension}";
    }

    /**
     * @param  $imagePath
     * @param  $baseName
     * @return array
     */
    protected function handleImage($filePath, $baseName): array
    {
        $path     = "catalog{$filePath}";
        $realPath = str_replace($this->fileBasePath.$this->basePath, $this->fileBasePath, $this->fileBasePath.$filePath);

        $mime = '';
        if (file_exists($realPath)) {
            $mime = mime_content_type($realPath);
        }

        return [
            'path'       => '/'.$path,
            'name'       => $baseName,
            'origin_url' => image_origin($path),
            'url'        => image_resize($path),
            'mime'       => $mime,
            'selected'   => false,
        ];
    }

    /**
     * @param  $folderPath
     * @param  $folderName
     * @return array
     */
    protected function handleFolder($folderPath, $folderName): array
    {
        return [
            'name' => $folderName,
            'path' => $folderPath,
        ];
    }
}
