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
        // Validate path security
        $baseFolder = FileSecurityValidator::validateDirectoryPath($baseFolder);

        $currentBasePath = rtrim($this->fileBasePath.$baseFolder, '/');
        $directories     = glob("$currentBasePath/*", GLOB_ONLYDIR);

        $result = [];
        foreach ($directories as $directory) {
            $baseName = basename($directory);
            $dirName  = str_replace($this->fileBasePath, '', $directory);
            if (is_dir($directory)) {
                $item           = $this->handleFolder($dirName, $baseName);
                $subDirectories = $this->getDirectories($dirName);
                if (! empty($subDirectories)) {
                    $item['children'] = $subDirectories;
                }
                $result[] = $item;
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
    public function getFiles(string $baseFolder, string $keyword = '', string $sort = 'created', string $order = 'desc', int $page = 1, int $perPage = 20): array
    {
        // Validate path security
        $baseFolder = FileSecurityValidator::validateDirectoryPath($baseFolder);

        $currentBasePath = rtrim($this->fileBasePath.$baseFolder, '/');

        $directories = glob("$currentBasePath/*", GLOB_ONLYDIR);
        $folders     = [];
        foreach ($directories as $directory) {
            $baseName  = basename($directory);
            $dirPath   = str_replace($this->fileBasePath, '', $directory);
            $folders[] = [
                'id'           => $dirPath,
                'name'         => $baseName,
                'path'         => $dirPath,
                'is_dir'       => true,
                'thumb'        => asset('images/icons/folder.png'),
                'url'          => '',
                'mime'         => 'directory',
                'created_time' => filemtime($directory),
            ];
        }

        $files  = glob($currentBasePath.'/*');
        $images = [];
        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }
            $baseName = basename($file);
            if ($baseName === 'index.html' || ($keyword && ! str_contains($baseName, $keyword))) {
                continue;
            }
            $fileName                 = str_replace($this->fileBasePath, '', $file);
            $fileInfo                 = $this->handleImage($fileName, $baseName);
            $fileInfo['created_time'] = filemtime($file);
            $images[]                 = $fileInfo;
        }

        $allItems = array_merge($folders, $images);

        if ($sort === 'created') {
            usort($allItems, function ($a, $b) use ($order) {
                $timeA = $a['created_time'] ?? 0;
                $timeB = $b['created_time'] ?? 0;

                return ($order === 'desc') ? $timeB - $timeA : $timeA - $timeB;
            });
        } else {
            // folders always in front of files
            usort($allItems, function ($a, $b) use ($order) {
                if (($a['is_dir'] ?? false) && ! ($b['is_dir'] ?? false)) {
                    return -1;
                }
                if (! ($a['is_dir'] ?? false) && ($b['is_dir'] ?? false)) {
                    return 1;
                }

                return ($order === 'desc') ?
                    strcasecmp($b['name'], $a['name']) :
                    strcasecmp($a['name'], $b['name']);
            });
        }

        $allItems = array_map(function ($item) {
            unset($item['created_time']);

            return $item;
        }, $allItems);

        $collection   = collect($allItems);
        $currentItems = $collection->forPage($page, $perPage);

        return [
            'images'      => $currentItems->values(),
            'image_total' => $collection->count(),
            'image_page'  => $page,
        ];
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
            if (empty($sourcePath) || empty($destPath)) {
                throw new Exception(trans('panel/file_manager.empty_path'));
            }

            $sourceDirPath = $this->getFullPath($sourcePath);
            $destDirPath   = $this->getFullPath($destPath);
            $folderName    = basename($sourcePath);
            $destFullPath  = rtrim($destDirPath, '/').'/'.$folderName;

            // confirm origin folder exists
            if (! is_dir($sourceDirPath)) {
                throw new Exception(trans('panel/file_manager.source_dir_not_exist'));
            }

            // confirm target folder exists
            if (! is_dir($destDirPath)) {
                throw new Exception(trans('panel/file_manager.target_dir_not_exist'));
            }

            if (is_dir($destFullPath)) {
                throw new Exception(trans('panel/file_manager.target_dir_exist'));
            }

            if (str_starts_with($destPath, $sourcePath.'/')) {
                throw new Exception(trans('panel/file_manager.cannot_move_to_subdirectory'));
            }

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
            Log::error('Move directory failed:', [
                'error'       => $e->getMessage(),
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
            if (empty($files)) {
                throw new Exception(trans('panel/file_manager.no_files_selected'));
            }

            // Validate destination path security
            $destPath = FileSecurityValidator::validateDirectoryPath($destPath);

            // Validate all source file paths
            $validatedFiles = [];
            foreach ($files as $file) {
                $validatedFiles[] = FileSecurityValidator::validateDirectoryPath($file);
            }
            $files = $validatedFiles;

            $destFullPath = $this->getFullPath($destPath);
            if (! is_dir($destFullPath)) {
                throw new Exception(trans('panel/file_manager.target_dir_not_exist'));
            }

            foreach ($files as $fileName) {
                $sourcePath   = $this->getFullPath($fileName);
                $destFilePath = rtrim($destFullPath, '/').'/'.basename($fileName);

                Log::info('Moving file:', [
                    'source'      => $sourcePath,
                    'destination' => $destFilePath,
                    'fileName'    => $fileName,
                    'destPath'    => $destPath,
                ]);

                if (file_exists($sourcePath)) {
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
                    } else {
                        Log::info('File moved successfully:', [
                            'from' => $sourcePath,
                            'to'   => $destFilePath,
                        ]);
                    }
                } else {
                    Log::warning('Source file not found:', ['path' => $sourcePath]);
                    throw new Exception(trans('panel/file_manager.source_file_not_exist'));
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error('Move files failed:', [
                'error'       => $e->getMessage(),
                'files'       => $files,
                'destination' => $destPath,
            ]);
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
            // Validate path security
            $path = FileSecurityValidator::validateDirectoryPath($path);

            $fullPath = $this->getFullPath($path);

            Log::info('Deleting path:', [
                'path'   => $fullPath,
                'is_dir' => is_dir($fullPath),
            ]);

            if (is_dir($fullPath)) {
                // Check if directory is empty
                $files = glob($fullPath.'/*');
                if ($files) {
                    throw new Exception(trans('panel/file_manager.directory_not_empty'));
                }

                // Delete directory
                if (! @rmdir($fullPath)) {
                    Log::error('Failed to delete directory:', [
                        'path'  => $fullPath,
                        'error' => error_get_last(),
                    ]);
                    throw new Exception(trans('panel/file_manager.delete_failed'));
                }
            } elseif (file_exists($fullPath)) {
                // Delete file
                if (! @unlink($fullPath)) {
                    Log::error('Failed to delete file:', [
                        'path'  => $fullPath,
                        'error' => error_get_last(),
                    ]);
                    throw new Exception(trans('panel/file_manager.delete_failed'));
                }
            } else {
                Log::warning('Path not found:', [
                    'path' => $fullPath,
                ]);
                throw new Exception(trans('panel/file_manager.file_not_exist'));
            }

            return true;
        } catch (Exception $e) {
            Log::error('Delete path failed:', [
                'error' => $e->getMessage(),
                'path'  => $path,
            ]);
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
            if (empty($files)) {
                throw new Exception(trans('panel/file_manager.no_files_selected'));
            }

            foreach ($files as $file) {
                $filePath = $this->getFullPath("$basePath/$file");

                Log::info('Deleting file:', [
                    'path' => $filePath,
                ]);

                if (file_exists($filePath)) {
                    if (! @unlink($filePath)) {
                        Log::error('Failed to delete file:', [
                            'path'  => $filePath,
                            'error' => error_get_last(),
                        ]);
                        throw new Exception(trans('panel/file_manager.delete_failed'));
                    }
                } else {
                    Log::warning('File not found:', [
                        'path' => $filePath,
                    ]);
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error('Delete files failed:', [
                'error' => $e->getMessage(),
                'files' => $files,
            ]);
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
     * @throws Exception If processing fails
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
            if (empty($files)) {
                throw new Exception(trans('panel/file_manager.no_files_selected'));
            }

            // Validate destination path security
            $destPath = FileSecurityValidator::validateDirectoryPath($destPath);

            // Validate all source file paths
            $validatedFiles = [];
            foreach ($files as $file) {
                $validatedFiles[] = FileSecurityValidator::validateDirectoryPath($file);
            }
            $files = $validatedFiles;

            $destFullPath = $this->getFullPath($destPath);
            if (! is_dir($destFullPath)) {
                throw new Exception(trans('panel/file_manager.target_dir_not_exist'));
            }

            foreach ($files as $fileName) {
                $sourcePath   = $this->getFullPath($fileName);
                $destFilePath = rtrim($destFullPath, '/').'/'.basename($fileName);

                Log::info('Copying file:', [
                    'source'      => $sourcePath,
                    'destination' => $destFilePath,
                ]);

                if (file_exists($sourcePath)) {
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
                    } else {
                        Log::info('File copied successfully:', [
                            'from' => $sourcePath,
                            'to'   => $destFilePath,
                        ]);
                    }
                } else {
                    Log::warning('Source file not found:', ['path' => $sourcePath]);
                    throw new Exception(trans('panel/file_manager.source_file_not_exist'));
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error('Copy files failed:', [
                'error'       => $e->getMessage(),
                'files'       => $files,
                'destination' => $destPath,
            ]);
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
        return public_path("$this->basePath/$path");
    }
}
