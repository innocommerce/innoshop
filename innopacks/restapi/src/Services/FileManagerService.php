<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Services\FileSecurityValidator;

class FileManagerService implements FileManagerInterface
{
    protected string $fileBasePath = '';

    protected string $mediaDir = 'static/media';

    protected string $basePath = '';

    /**
     * Excluded files from listing
     */
    protected const EXCLUDED_FILES = ['index.html'];

    /**
     * Default sort field
     */
    protected const SORT_FIELD_CREATED = 'created';

    /**
     * Default sort order
     */
    protected const SORT_ORDER_DESC = 'desc';

    public function __construct()
    {
        $this->basePath     = '/'.$this->mediaDir;
        $this->fileBasePath = public_path().$this->basePath;
    }

    /**
     * Retrieves directories within a base folder.
     *
     * @param  string  $baseFolder  Path to the base folder
     * @return array Array of directories with their details
     */
    public function getDirectories(string $baseFolder = '/'): array
    {
        $baseFolder   = FileSecurityValidator::validateDirectoryPath($baseFolder);
        $realBasePath = $this->getRealBasePath();
        if ($realBasePath === false) {
            return [];
        }

        $currentBasePath = rtrim($this->fileBasePath.$baseFolder, '/');
        $directories     = glob("$currentBasePath/*", GLOB_ONLYDIR) ?: [];

        $result = [];
        foreach ($directories as $directory) {
            $processed = $this->processDirectory($directory, $realBasePath);
            if ($processed !== null) {
                $result[] = $processed;
            }
        }

        return $result;
    }

    /**
     * Get files list with pagination and filtering
     *
     * @param  string  $baseFolder  Base folder path
     * @param  string  $keyword  Search keyword
     * @param  string  $sort  Sort field (created or name)
     * @param  string  $order  Sort order (asc or desc)
     * @param  int  $page  Current page number
     * @param  int  $perPage  Items per page
     * @return array Paginated file list with metadata
     * @throws Exception If an error occurs during retrieval
     */
    public function getFiles(string $baseFolder, string $keyword = '', string $sort = self::SORT_FIELD_CREATED, string $order = self::SORT_ORDER_DESC, int $page = 1, int $perPage = 20): array
    {
        $baseFolder   = FileSecurityValidator::validateDirectoryPath($baseFolder);
        $realBasePath = $this->getRealBasePath();
        if ($realBasePath === false) {
            return $this->getEmptyFileList($page);
        }

        $currentBasePath = rtrim($this->fileBasePath.$baseFolder, '/');
        $folders         = $this->collectFolders($currentBasePath, $realBasePath);
        $images          = $this->collectFiles($currentBasePath, $realBasePath, $keyword);

        $allItems = array_merge($folders, $images);
        $allItems = $this->sortItems($allItems, $sort, $order);
        $allItems = $this->removeTemporaryFields($allItems);

        return $this->paginateItems($allItems, $page, $perPage);
    }

    /**
     * Creates a new directory.
     *
     * @param  string  $path  Path where the directory should be created
     * @return bool True if directory was created successfully
     * @throws Exception If directory already exists or creation fails
     */
    public function createDirectory(string $path): bool
    {
        try {
            // Validate path security
            $path = FileSecurityValidator::validateDirectoryPath($path);

            $folderPath = $this->getFullPath($path);
            if (is_dir($folderPath)) {
                throw new Exception(trans('panel/file_manager.directory_already_exist'));
            }

            create_directories("$this->mediaDir/$path");

            return true;
        } catch (Exception $e) {
            Log::error('Create directory failed:', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Moves a directory to a new path.
     *
     * @param  string  $sourcePath  Source directory path
     * @param  string  $destPath  Destination directory path
     * @return bool True if directory was moved successfully
     * @throws Exception If source or destination is invalid, or move operation fails
     */
    public function moveDirectory(string $sourcePath, string $destPath): bool
    {
        try {
            $this->validatePathsNotEmpty($sourcePath, $destPath);
            $this->validateNotMovingToSubdirectory($sourcePath, $destPath);

            $sourceDirPath = $this->getFullPath($sourcePath);
            $destDirPath   = $this->getFullPath($destPath);
            $destFullPath  = rtrim($destDirPath, '/').'/'.basename($sourcePath);

            $this->ensureDirectoryExists($sourceDirPath);
            $this->ensureDirectoryExists($destDirPath);
            $this->ensurePathDoesNotExist($destFullPath);

            Log::info('Moving directory:', [
                'from' => $sourceDirPath,
                'to'   => $destFullPath,
            ]);

            if (! @rename($sourceDirPath, $destFullPath)) {
                Log::error('Failed to move directory:', [
                    'error' => error_get_last(),
                ]);
                throw new Exception(trans('panel/file_manager.move_failed'));
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Move directory failed', $e, [
                'source'      => $sourcePath,
                'destination' => $destPath,
            ]);
            throw $e;
        }
    }

    /**
     * Moves multiple files to a new directory.
     *
     * @param  array  $files  Array of file paths to move
     * @param  string  $destPath  Destination directory path
     * @return bool True if all files were moved successfully
     * @throws Exception If files cannot be moved or other error occurs
     */
    public function moveFiles(array $files, string $destPath): bool
    {
        try {
            $this->validateFilesNotEmpty($files);
            $destPath = FileSecurityValidator::validateDirectoryPath($destPath);
            $files    = $this->validateFilePaths($files);

            $destFullPath = $this->getFullPath($destPath);
            $this->ensureDirectoryExists($destFullPath);

            foreach ($files as $fileName) {
                $this->moveSingleFile($fileName, $destFullPath, $destPath);
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Move files failed', $e, ['files' => $files, 'destination' => $destPath]);
            throw $e;
        }
    }

    /**
     * Deletes a file or folder.
     *
     * @param  string  $path  Path to the file or folder to delete
     * @return bool True if deletion was successful
     * @throws Exception If deletion fails or path is invalid
     */
    public function deleteDirectoryOrFile(string $path): bool
    {
        try {
            $path     = FileSecurityValidator::validateDirectoryPath($path);
            $fullPath = $this->getFullPath($path);

            Log::info('Deleting path:', [
                'path'   => $fullPath,
                'is_dir' => is_dir($fullPath),
            ]);

            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } elseif (file_exists($fullPath)) {
                $this->deleteFile($fullPath);
            } else {
                Log::warning('Path not found:', ['path' => $fullPath]);
                throw new Exception(trans('panel/file_manager.file_not_exist'));
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Delete path failed', $e, ['path' => $path]);
            throw $e;
        }
    }

    /**
     * Delete multiple files.
     *
     * @param  string  $basePath  Base directory path
     * @param  array  $files  Array of filenames to delete
     * @return bool True if all files were deleted successfully
     * @throws Exception If deletion fails or files are not found
     */
    public function deleteFiles(string $basePath, array $files): bool
    {
        try {
            $this->validateFilesNotEmpty($files);

            foreach ($files as $file) {
                $filePath = $this->getFullPath("$basePath/$file");

                Log::info('Deleting file:', ['path' => $filePath]);

                if (file_exists($filePath)) {
                    $this->deleteFile($filePath);
                } else {
                    Log::warning('File not found:', ['path' => $filePath]);
                }
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Delete files failed', $e, ['files' => $files]);
            throw $e;
        }
    }

    /**
     * Renames a file or folder.
     *
     * @param  string  $originPath  Original path
     * @param  string  $newPath  New path
     * @return bool True if renaming was successful
     * @throws Exception If renaming fails or paths are invalid
     */
    public function updateName(string $originPath, string $newPath): bool
    {
        try {
            // Validate path security (防止路径遍历)
            $originPath = FileSecurityValidator::validateDirectoryPath($originPath);
            $newPath    = FileSecurityValidator::validateDirectoryPath($newPath);

            // Validate new file name security (防止文件名包含路径分隔符等)
            $newFileName = basename($newPath);
            FileSecurityValidator::validateFileName($newFileName);

            // Validate file extension if it's a file rename (防止危险扩展名)
            if (pathinfo($newFileName, PATHINFO_EXTENSION)) {
                FileSecurityValidator::validateFileExtension($newFileName);
            }

            $originFullPath = $this->getFullPath($originPath);
            $newFullPath    = $this->getFullPath($newPath);

            if (! is_dir($originFullPath) && ! file_exists($originFullPath)) {
                throw new Exception(trans('panel/file_manager.target_not_exist'));
            }

            if (file_exists($newFullPath)) {
                $dirPath     = dirname($newPath);
                $newName     = $this->getUniqueFileName($dirPath, basename($newPath));
                $newPath     = $dirPath === '/' ? "/$newName" : "$dirPath/$newName";
                $newFullPath = $this->getFullPath($newPath);
            }

            if (! @rename($originFullPath, $newFullPath)) {
                Log::error('Failed to rename:', [
                    'from'  => $originFullPath,
                    'to'    => $newFullPath,
                    'error' => error_get_last(),
                ]);
                throw new Exception(trans('panel/file_manager.rename_failed'));
            }

            return true;
        } catch (Exception $e) {
            Log::error('Rename failed:', [
                'error'       => $e->getMessage(),
                'origin_path' => $originPath,
                'new_path'    => $newPath,
            ]);
            throw $e;
        }
    }

    /**
     * Uploads a file to a specified path.
     *
     * @param  UploadedFile  $file  The uploaded file
     * @param  string  $savePath  Path where the file should be saved
     * @param  string  $originName  Original filename
     * @return string URL to the uploaded file
     */
    public function uploadFile(UploadedFile $file, string $savePath, string $originName): string
    {
        // Validate file security (包括文件名和扩展名安全性)
        FileSecurityValidator::validateFile($originName);

        // Validate save path security
        $savePath = FileSecurityValidator::validateDirectoryPath($savePath);

        $originName = $this->getUniqueFileName($savePath, $originName);
        $filePath   = $file->storeAs($savePath, $originName, 'media');

        return asset($this->mediaDir.'/'.$filePath);
    }

    /**
     * Generates a unique file name to avoid conflicts.
     *
     * @param  string  $savePath  Directory path
     * @param  string  $originName  Original filename
     * @return string Unique filename
     */
    public function getUniqueFileName(string $savePath, string $originName): string
    {
        $fullPath = $this->getFullPath("$savePath/$originName");
        if (file_exists($fullPath)) {
            $originName = $this->getNewFileName($originName);

            return $this->getUniqueFileName($savePath, $originName);
        }

        return $originName;
    }

    /**
     * Generates a new file name by appending an incremented index.
     *
     * @param  string  $originName  Original filename
     * @return string New filename with index
     */
    public function getNewFileName(string $originName): string
    {
        $extension = pathinfo($originName, PATHINFO_EXTENSION);
        $name      = pathinfo($originName, PATHINFO_FILENAME);

        if (preg_match('/(.+?)\((\d+)\)$/', $name, $matches)) {
            $index = (int) $matches[2] + 1;
            $name  = "{$matches[1]}({$index})";
        } else {
            $name .= '(1)';
        }

        return "{$name}.{$extension}";
    }

    /**
     * Processes an image file and returns its metadata.
     *
     * @param  string  $filePath  Path to the image file
     * @param  string  $baseName  Base filename
     * @return array Image metadata
     */
    protected function handleImage(string $filePath, string $baseName): array
    {
        $thumbPath = $path = "$this->mediaDir$filePath";
        $realPath  = str_replace($this->fileBasePath.$this->basePath, $this->fileBasePath, $this->fileBasePath.$filePath);

        $mime = '';
        if (file_exists($realPath)) {
            $mime = mime_content_type($realPath);
            if (str_starts_with($mime, 'application/')) {
                $thumbPath = 'images/panel/doc.png';
            } elseif (str_starts_with($mime, 'video/')) {
                $thumbPath = 'images/panel/video.png';
            }
        }

        return [
            'id'         => $filePath,
            'path'       => '/'.$path,
            'name'       => $baseName,
            'origin_url' => image_origin($path),
            'url'        => image_resize($thumbPath),
            'mime'       => $mime,
            'selected'   => false,
        ];
    }

    /**
     * Processes a folder and returns its metadata.
     *
     * @param  string  $folderPath  Path to the folder
     * @param  string  $folderName  Folder name
     * @return array Folder metadata
     */
    protected function handleFolder(string $folderPath, string $folderName): array
    {
        return [
            'name' => $folderName,
            'path' => $folderPath,
        ];
    }

    /**
     * Copies multiple files to a new directory.
     *
     * @param  array  $files  Array of file paths to copy
     * @param  string  $destPath  Destination directory path
     * @return bool True if all files were copied successfully
     * @throws Exception If copying fails or files are not found
     */
    public function copyFiles(array $files, string $destPath): bool
    {
        try {
            $this->validateFilesNotEmpty($files);
            $destPath = FileSecurityValidator::validateDirectoryPath($destPath);
            $files    = $this->validateFilePaths($files);

            $destFullPath = $this->getFullPath($destPath);
            $this->ensureDirectoryExists($destFullPath);

            foreach ($files as $fileName) {
                $this->copySingleFile($fileName, $destFullPath, $destPath);
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Copy files failed', $e, ['files' => $files, 'destination' => $destPath]);
            throw $e;
        }
    }

    /**
     * Gets the full system path for a relative path.
     *
     * @param  string  $path  Relative path
     * @return string Full system path
     */
    protected function getFullPath(string $path): string
    {
        $normalizedPath = ltrim($path, '/');
        $fullPath       = public_path("$this->basePath/$normalizedPath");

        $realPath = realpath($fullPath);
        if ($realPath !== false) {
            $realBasePath = realpath($this->fileBasePath);
            if ($realBasePath !== false && str_starts_with($realPath, $realBasePath)) {
                return $realPath;
            }
        }

        return rtrim($fullPath, '/');
    }

    // ==================== Helper Methods ====================

    /**
     * Get the real base path, ensuring it's accessible.
     *
     * @return string|false The real base path or false if not accessible
     */
    protected function getRealBasePath()
    {
        return realpath($this->fileBasePath);
    }

    /**
     * Normalize a relative path to ensure it starts with '/'.
     *
     * @param  string  $path  Path to normalize
     * @return string Normalized path
     */
    protected function normalizeRelativePath(string $path): string
    {
        return str_starts_with($path, '/') ? $path : '/'.$path;
    }

    /**
     * Process a directory entry and return folder metadata if valid.
     *
     * @param  string  $directory  Directory path
     * @param  string  $realBasePath  Real base path for validation
     * @return array|null Folder metadata or null if invalid
     */
    protected function processDirectory(string $directory, string $realBasePath): ?array
    {
        $realDirectory = realpath($directory);
        if ($realDirectory === false) {
            return null;
        }

        if (! str_starts_with($realDirectory, $realBasePath)) {
            return null;
        }

        $baseName = basename($directory);
        $dirName  = str_replace($this->fileBasePath, '', $directory);

        if (! str_starts_with($dirName, '/')) {
            $dirName = '/'.$dirName;
        }

        try {
            if (! is_dir($realDirectory)) {
                return null;
            }

            $item           = $this->handleFolder($dirName, $baseName);
            $subDirectories = $this->getDirectories($dirName);
            if (! empty($subDirectories)) {
                $item['children'] = $subDirectories;
            }

            return $item;
        } catch (\Exception $e) {
            Log::warning('Skipping directory due to access restriction:', [
                'directory' => $directory,
                'error'     => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Collect folders from a directory path.
     *
     * @param  string  $currentBasePath  Current base path
     * @param  string  $realBasePath  Real base path for validation
     * @return array Array of folder metadata
     */
    protected function collectFolders(string $currentBasePath, string $realBasePath): array
    {
        $directories = glob("$currentBasePath/*", GLOB_ONLYDIR) ?: [];
        $folders     = [];

        foreach ($directories as $directory) {
            $realDirectory = realpath($directory);
            if ($realDirectory === false || ! str_starts_with($realDirectory, $realBasePath)) {
                continue;
            }

            try {
                $baseName = basename($directory);
                $dirPath  = $this->normalizeRelativePath(str_replace($this->fileBasePath, '', $directory));

                $folders[] = [
                    'id'           => $dirPath,
                    'name'         => $baseName,
                    'path'         => $dirPath,
                    'is_dir'       => true,
                    'thumb'        => asset('images/icons/folder.png'),
                    'url'          => '',
                    'mime'         => 'directory',
                    'created_time' => @filemtime($realDirectory) ?: time(),
                ];
            } catch (\Exception $e) {
                Log::warning('Skipping directory due to access restriction:', [
                    'directory' => $directory,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return $folders;
    }

    /**
     * Collect files from a directory path.
     *
     * @param  string  $currentBasePath  Current base path
     * @param  string  $realBasePath  Real base path for validation
     * @param  string  $keyword  Search keyword
     * @return array Array of file metadata
     */
    protected function collectFiles(string $currentBasePath, string $realBasePath, string $keyword = ''): array
    {
        $files  = glob($currentBasePath.'/*') ?: [];
        $images = [];

        foreach ($files as $file) {
            $realFile = realpath($file);
            if ($realFile === false || ! str_starts_with($realFile, $realBasePath)) {
                continue;
            }

            try {
                if (! is_file($realFile)) {
                    continue;
                }

                $baseName = basename($file);
                if ($this->shouldSkipFile($baseName, $keyword)) {
                    continue;
                }

                $fileName = $this->normalizeRelativePath(str_replace($this->fileBasePath, '', $file));

                $fileInfo                 = $this->handleImage($fileName, $baseName);
                $fileInfo['created_time'] = @filemtime($realFile) ?: time();
                $images[]                 = $fileInfo;
            } catch (\Exception $e) {
                Log::warning('Skipping file due to access restriction:', [
                    'file'  => $file,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $images;
    }

    /**
     * Check if a file should be skipped based on exclusion rules or keyword filter.
     *
     * @param  string  $baseName  Base filename
     * @param  string  $keyword  Search keyword
     * @return bool True if file should be skipped
     */
    protected function shouldSkipFile(string $baseName, string $keyword): bool
    {
        if (in_array($baseName, self::EXCLUDED_FILES, true)) {
            return true;
        }

        return $keyword !== '' && ! str_contains($baseName, $keyword);
    }

    /**
     * Sort items by specified field and order.
     *
     * @param  array  $items  Items to sort
     * @param  string  $sort  Sort field (created or name)
     * @param  string  $order  Sort order (asc or desc)
     * @return array Sorted items
     */
    protected function sortItems(array $items, string $sort, string $order): array
    {
        if ($sort === self::SORT_FIELD_CREATED) {
            usort($items, function ($a, $b) use ($order) {
                $timeA = $a['created_time'] ?? 0;
                $timeB = $b['created_time'] ?? 0;

                return ($order === self::SORT_ORDER_DESC) ? $timeB - $timeA : $timeA - $timeB;
            });
        } else {
            usort($items, function ($a, $b) use ($order) {
                if (($a['is_dir'] ?? false) && ! ($b['is_dir'] ?? false)) {
                    return -1;
                }
                if (! ($a['is_dir'] ?? false) && ($b['is_dir'] ?? false)) {
                    return 1;
                }

                return ($order === self::SORT_ORDER_DESC) ?
                    strcasecmp($b['name'], $a['name']) :
                    strcasecmp($a['name'], $b['name']);
            });
        }

        return $items;
    }

    /**
     * Remove temporary fields from items.
     *
     * @param  array  $items  Items to process
     * @return array Items without temporary fields
     */
    protected function removeTemporaryFields(array $items): array
    {
        return array_map(function ($item) {
            unset($item['created_time']);

            return $item;
        }, $items);
    }

    /**
     * Paginate items and return formatted result.
     *
     * @param  array  $items  Items to paginate
     * @param  int  $page  Current page number
     * @param  int  $perPage  Items per page
     * @return array Paginated result
     */
    protected function paginateItems(array $items, int $page, int $perPage): array
    {
        $collection   = collect($items);
        $currentItems = $collection->forPage($page, $perPage);

        return [
            'images'      => $currentItems->values(),
            'image_total' => $collection->count(),
            'image_page'  => $page,
        ];
    }

    /**
     * Get empty file list result.
     *
     * @param  int  $page  Current page number
     * @return array Empty file list structure
     */
    protected function getEmptyFileList(int $page): array
    {
        return [
            'images'      => [],
            'image_total' => 0,
            'image_page'  => $page,
        ];
    }

    /**
     * Validate that files array is not empty.
     *
     * @param  array  $files  Files array
     * @return void
     * @throws Exception If files array is empty
     */
    protected function validateFilesNotEmpty(array $files): void
    {
        if (empty($files)) {
            throw new Exception(trans('panel/file_manager.no_files_selected'));
        }
    }

    /**
     * Validate and normalize file paths.
     *
     * @param  array  $files  Array of file paths
     * @return array Validated file paths
     */
    protected function validateFilePaths(array $files): array
    {
        $validatedFiles = [];
        foreach ($files as $file) {
            $validatedFiles[] = FileSecurityValidator::validateDirectoryPath($file);
        }

        return $validatedFiles;
    }

    /**
     * Ensure directory exists, throw exception if not.
     *
     * @param  string  $dirPath  Directory path
     * @return void
     * @throws Exception If directory does not exist
     */
    protected function ensureDirectoryExists(string $dirPath): void
    {
        if (! is_dir($dirPath)) {
            throw new Exception(trans('panel/file_manager.target_dir_not_exist'));
        }
    }

    /**
     * Move a single file to destination directory.
     *
     * @param  string  $fileName  Source file name/path
     * @param  string  $destFullPath  Destination full path
     * @param  string  $destPath  Destination relative path (for logging)
     * @return void
     * @throws Exception If move operation fails
     */
    protected function moveSingleFile(string $fileName, string $destFullPath, string $destPath): void
    {
        $sourcePath   = $this->getFullPath($fileName);
        $destFilePath = rtrim($destFullPath, '/').'/'.basename($fileName);

        Log::info('Moving file:', [
            'source'      => $sourcePath,
            'destination' => $destFilePath,
            'fileName'    => $fileName,
            'destPath'    => $destPath,
        ]);

        if (! file_exists($sourcePath)) {
            Log::warning('Source file not found:', ['path' => $sourcePath]);
            throw new Exception(trans('panel/file_manager.source_file_not_exist'));
        }

        if (file_exists($destFilePath)) {
            @unlink($destFilePath);
        }

        if (! @rename($sourcePath, $destFilePath)) {
            Log::error('Failed to move file:', [
                'source'      => $sourcePath,
                'destination' => $destFilePath,
                'error'       => error_get_last(),
            ]);
            throw new Exception(trans('panel/file_manager.move_failed'));
        }

        Log::info('File moved successfully:', [
            'from' => $sourcePath,
            'to'   => $destFilePath,
        ]);
    }

    /**
     * Copy a single file to destination directory.
     *
     * @param  string  $fileName  Source file name/path
     * @param  string  $destFullPath  Destination full path
     * @param  string  $destPath  Destination relative path
     * @return void
     * @throws Exception If copy operation fails
     */
    protected function copySingleFile(string $fileName, string $destFullPath, string $destPath): void
    {
        $sourcePath   = $this->getFullPath($fileName);
        $destFilePath = rtrim($destFullPath, '/').'/'.basename($fileName);

        Log::info('Copying file:', [
            'source'      => $sourcePath,
            'destination' => $destFilePath,
        ]);

        if (! file_exists($sourcePath)) {
            Log::warning('Source file not found:', ['path' => $sourcePath]);
            throw new Exception(trans('panel/file_manager.source_file_not_exist'));
        }

        if (file_exists($destFilePath)) {
            $newName      = $this->getUniqueFileName($destPath, basename($fileName));
            $destFilePath = rtrim($destFullPath, '/').'/'.$newName;
        }

        if (! @copy($sourcePath, $destFilePath)) {
            Log::error('Failed to copy file:', [
                'source'      => $sourcePath,
                'destination' => $destFilePath,
                'error'       => error_get_last(),
            ]);
            throw new Exception(trans('panel/file_manager.copy_failed'));
        }

        Log::info('File copied successfully:', [
            'from' => $sourcePath,
            'to'   => $destFilePath,
        ]);
    }

    /**
     * Validate that paths are not empty.
     *
     * @param  string  $sourcePath  Source path
     * @param  string  $destPath  Destination path
     * @return void
     * @throws Exception If any path is empty
     */
    protected function validatePathsNotEmpty(string $sourcePath, string $destPath): void
    {
        if (empty($sourcePath) || empty($destPath)) {
            throw new Exception(trans('panel/file_manager.empty_path'));
        }
    }

    /**
     * Validate that destination is not a subdirectory of source.
     *
     * @param  string  $sourcePath  Source path
     * @param  string  $destPath  Destination path
     * @return void
     * @throws Exception If destination is a subdirectory of source
     */
    protected function validateNotMovingToSubdirectory(string $sourcePath, string $destPath): void
    {
        if (str_starts_with($destPath, $sourcePath.'/')) {
            throw new Exception(trans('panel/file_manager.cannot_move_to_subdirectory'));
        }
    }

    /**
     * Ensure path does not exist, throw exception if it does.
     *
     * @param  string  $path  Path to check
     * @return void
     * @throws Exception If path exists
     */
    protected function ensurePathDoesNotExist(string $path): void
    {
        if (is_dir($path) || file_exists($path)) {
            throw new Exception(trans('panel/file_manager.target_dir_exist'));
        }
    }

    /**
     * Delete a directory.
     *
     * @param  string  $dirPath  Directory path
     * @return void
     * @throws Exception If directory is not empty or deletion fails
     */
    protected function deleteDirectory(string $dirPath): void
    {
        $files = glob($dirPath.'/*');
        if ($files) {
            throw new Exception(trans('panel/file_manager.directory_not_empty'));
        }

        if (! @rmdir($dirPath)) {
            Log::error('Failed to delete directory:', [
                'path'  => $dirPath,
                'error' => error_get_last(),
            ]);
            throw new Exception(trans('panel/file_manager.delete_failed'));
        }
    }

    /**
     * Delete a file.
     *
     * @param  string  $filePath  File path
     * @return void
     * @throws Exception If deletion fails
     */
    protected function deleteFile(string $filePath): void
    {
        if (! @unlink($filePath)) {
            Log::error('Failed to delete file:', [
                'path'  => $filePath,
                'error' => error_get_last(),
            ]);
            throw new Exception(trans('panel/file_manager.delete_failed'));
        }
    }

    /**
     * Log error with context.
     *
     * @param  string  $message  Error message
     * @param  Exception  $exception  Exception object
     * @param  array  $context  Additional context
     * @return void
     */
    protected function logError(string $message, Exception $exception, array $context = []): void
    {
        Log::error($message, array_merge([
            'error' => $exception->getMessage(),
        ], $context));
    }
}
