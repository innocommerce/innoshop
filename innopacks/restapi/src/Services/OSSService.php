<?php

namespace InnoShop\RestAPI\Services;

use Aws\S3\S3Client;
use Exception;
use Illuminate\Support\Facades\Log;

class OSSService implements FileManagerInterface
{
    protected S3Client $s3Client;

    protected string $bucket;

    protected string $cdnDomain;

    public function __construct()
    {
        $this->refreshConfig();
        $this->validateConfig();
        $this->initializeS3Client();
        $this->bucket    = plugin_setting('file_manager', 'bucket', '');
        $this->cdnDomain = plugin_setting('file_manager', 'cdn_domain', '');

        Log::info('OSS Service initialized with:', [
            'bucket'    => $this->bucket,
            'cdnDomain' => $this->cdnDomain,
            'endpoint'  => plugin_setting('file_manager', 'endpoint', ''),
        ]);
    }

    /**
     * Refresh config
     */
    protected function refreshConfig(): void
    {
        config([
            'filesystems.file_manager.driver' => plugin_setting('file_manager', 'driver', 'local'),
            'filesystems.disks.s3.key'        => plugin_setting('file_manager', 'key', ''),
            'filesystems.disks.s3.secret'     => plugin_setting('file_manager', 'secret', ''),
            'filesystems.disks.s3.endpoint'   => plugin_setting('file_manager', 'endpoint', ''),
            'filesystems.disks.s3.bucket'     => plugin_setting('file_manager', 'bucket', ''),
            'filesystems.disks.s3.region'     => plugin_setting('file_manager', 'region', ''),
            'filesystems.disks.s3.cdn_domain' => plugin_setting('file_manager', 'cdn_domain', ''),
        ]);
    }

    protected function validateConfig(): void
    {
        $required = [
            'key'      => 'Access Key',
            'secret'   => 'Secret Key',
            'region'   => 'Region',
            'bucket'   => 'Bucket',
            'endpoint' => 'Endpoint',
        ];

        $missing = [];
        foreach ($required as $field => $label) {
            $value = plugin_setting('file_manager', $field, '');
            if (empty($value)) {
                $missing[] = $label;
            }
        }

        if (! empty($missing)) {
            Log::warning('OSS configuration incomplete:', ['missing' => $missing]);
            throw new Exception('OSS 配置不完整，请检查以下配置：'.PHP_EOL.
                implode(PHP_EOL, array_map(fn ($key) => "- {$key}", $missing)));
        }
    }

    protected function initializeS3Client(): void
    {
        $this->s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => plugin_setting('file_manager', 'region', ''),
            'credentials' => [
                'key'    => plugin_setting('file_manager', 'key', ''),
                'secret' => plugin_setting('file_manager', 'secret', ''),
            ],
            'endpoint'                => plugin_setting('file_manager', 'endpoint', ''),
            'use_path_style_endpoint' => false,
            'bucket_endpoint'         => true,
        ]);

        Log::info('S3 Client initialized with:', [
            'region'   => plugin_setting('file_manager', 'region', ''),
            'endpoint' => plugin_setting('file_manager', 'endpoint', ''),
            'key'      => plugin_setting('file_manager', 'key', '') ? '(set)' : '(not set)',
        ]);
    }

    public function uploadFile($file, $savePath, $originName): string
    {
        try {
            // Security validation is handled by UploadService
            // This method is for internal OSS operations only
            $key = $this->getObjectKey($savePath, $originName);

            $this->s3Client->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $key,
                'Body'        => fopen($file->getRealPath(), 'rb'),
                'ACL'         => 'public-read',
                'ContentType' => $file->getMimeType(),
            ]);

            return $this->getFileUrl($key);
        } catch (Exception $e) {
            Log::error('OSS upload failed:', [
                'error' => $e->getMessage(),
                'file'  => $originName,
            ]);
            throw new Exception(trans('panel::file_manager.upload_failed'));
        }
    }

    /**
     * Get files list
     */
    public function getFiles(string $baseFolder, ?string $keyword = '', string $sort = 'name', string $order = 'asc', int $page = 1, int $perPage = 20): array
    {
        try {
            Log::info('OSS getFiles:', [
                'baseFolder' => $baseFolder,
                'bucket'     => $this->bucket,
            ]);

            $prefix = trim($baseFolder, '/');
            $prefix = $prefix ? $prefix.'/' : '';

            // 获取所有对象
            $result = $this->s3Client->listObjectsV2([
                'Bucket'    => $this->bucket,
                'Prefix'    => $prefix,
                'Delimiter' => '/',
            ]);

            // 格式化目录
            $directories = array_map(function ($prefix) {
                $name = basename(rtrim($prefix['Prefix'], '/'));

                return [
                    'name'          => $name,
                    'path'          => $prefix['Prefix'],
                    'is_dir'        => true,
                    'thumb'         => url('/images/icons/folder.png'),
                    'url'           => '',
                    'mime'          => 'directory',
                    'size'          => 0,
                    'last_modified' => null,
                ];
            }, $result['CommonPrefixes'] ?? []);

            // 格式化文件
            $files = array_map(function ($object) {
                if (substr($object['Key'], -1) === '/') {
                    return null;
                }
                $name = basename($object['Key']);
                $url  = $this->getFileUrl($object['Key']);  // 使用 OSS 的 URL

                return [
                    'name'          => $name,
                    'path'          => $object['Key'],  // 这里返回的是相对路径
                    'is_dir'        => false,
                    'thumb'         => $this->isImagePath($object['Key']) ? $url : url('/images/icons/file.png'),
                    'url'           => $url,  // 这里应该返回完整的 URL
                    'mime'          => $this->getMimeType($object['Key']) ?? 'application/octet-stream',
                    'size'          => $object['Size']                    ?? 0,
                    'last_modified' => $object['LastModified']            ?? null,
                ];
            }, $result['Contents'] ?? []);

            // 过滤掉 null 值并合并目录和文件
            $items = array_merge($directories, $files);

            // 应用搜索过滤
            if ($keyword) {
                $items = array_filter($items, function ($item) use ($keyword) {
                    return stripos($item['name'], $keyword) !== false;
                });
            }

            // 排序
            usort($items, function ($a, $b) use ($sort, $order) {
                // 目录始终排在文件前面
                if ($a['is_dir'] && ! $b['is_dir']) {
                    return -1;
                }
                if (! $a['is_dir'] && $b['is_dir']) {
                    return 1;
                }

                $result = 0;
                if ($sort === 'name') {
                    $result = strcmp($a['name'], $b['name']);
                } elseif ($sort === 'size') {
                    $result = ($a['size'] ?? 0) <=> ($b['size'] ?? 0);
                } elseif ($sort === 'created') {
                    // 处理 null 值的情况
                    $timeA  = $a['last_modified'] ?? 0;
                    $timeB  = $b['last_modified'] ?? 0;
                    $result = $timeA <=> $timeB;
                }

                return $order === 'desc' ? -$result : $result;
            });

            // 计算总数（在分页之前）
            $total = count($items);

            // 分页
            $offset = ($page - 1) * $perPage;
            $items  = array_slice($items, $offset, $perPage);

            // 添加调试日志
            Log::info('File pagination:', [
                'total_items'    => $total,
                'page'           => $page,
                'per_page'       => $perPage,
                'offset'         => $offset,
                'returned_items' => count($items),
            ]);

            // 返回前端期望的格式
            return [
                'images'         => $items,
                'image_total'    => $total,
                'image_page'     => $page,
                'image_per_page' => $perPage,
                'success'        => true,
            ];
        } catch (Exception $e) {
            Log::error('OSS get files failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function getObjectKey($path, $filename): string
    {
        $path = trim($path, '/');

        return $path ? "{$path}/{$filename}" : $filename;
    }

    protected function getFileUrl(string $key): string
    {
        // 如果配置了 CDN 域名，优先使用
        if ($this->cdnDomain) {
            return rtrim($this->cdnDomain, '/').'/'.ltrim($key, '/');
        }

        // 否则使用 OSS 域名
        $endpoint = config('filesystems.disks.s3.endpoint');

        // 移除 endpoint 中可能存在的协议前缀
        $endpoint = preg_replace('#^https?://#', '', $endpoint);

        // 直接拼接 bucket.endpoint/key 的格式
        return sprintf('https://%s/%s', $endpoint, ltrim($key, '/'));
    }

    public function getDirectories(string $baseFolder = '/'): array
    {
        try {
            $prefix = trim($baseFolder, '/');
            $prefix = $prefix ? $prefix.'/' : '';

            $result = $this->s3Client->listObjectsV2([
                'Bucket'    => $this->bucket,
                'Prefix'    => $prefix,
                'Delimiter' => '/',
            ]);

            // 添加根目录
            $directories = [
                [
                    'id'     => '/',
                    'name'   => '/',
                    'path'   => '/',
                    'parent' => null,
                    'isRoot' => true,
                ],
            ];

            // 处理子目录
            foreach ($result['CommonPrefixes'] ?? [] as $prefix) {
                $path   = rtrim($prefix['Prefix'], '/');
                $name   = basename($path);
                $parent = dirname($path);
                $parent = $parent === '.' ? '/' : $parent;

                $directories[] = [
                    'id'     => $path,
                    'name'   => $name,
                    'path'   => $path,
                    'parent' => $parent,
                    'isRoot' => false,
                ];
            }

            // 添加调试日志
            Log::info('OSS directories:', [
                'baseFolder'  => $baseFolder,
                'directories' => $directories,
            ]);

            return $directories;
        } catch (Exception $e) {
            Log::error('OSS get directories failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function createDirectory($path): bool
    {
        try {
            // In S3, directories are just objects with trailing slashes
            $path = trim($path, '/').'/';

            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $path,
                'Body'   => '',
                'ACL'    => 'public-read',
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('OSS create directory failed:', [
                'error' => $e->getMessage(),
                'path'  => $path,
            ]);
            throw new Exception(trans('panel::file_manager.create_directory_failed'));
        }
    }

    public function moveFiles(array $files, string $destPath): bool
    {
        try {
            foreach ($files as $filePath) {
                $fileName = basename($filePath);
                $newKey   = trim($destPath, '/').'/'.$fileName;

                // Copy the object to the new location
                $this->s3Client->copyObject([
                    'Bucket'     => $this->bucket,
                    'CopySource' => $this->bucket.'/'.ltrim($filePath, '/'),
                    'Key'        => $newKey,
                    'ACL'        => 'public-read',
                ]);

                // Delete the original object
                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => ltrim($filePath, '/'),
                ]);
            }

            return true;
        } catch (Exception $e) {
            Log::error('OSS move files failed:', [
                'error'    => $e->getMessage(),
                'files'    => $files,
                'destPath' => $destPath,
            ]);
            throw new Exception(trans('panel::file_manager.move_failed'));
        }
    }

    public function copyFiles(array $files, string $destPath): bool
    {
        try {
            foreach ($files as $filePath) {
                $fileName = basename($filePath);
                $newKey   = trim($destPath, '/').'/'.$fileName;

                $this->s3Client->copyObject([
                    'Bucket'     => $this->bucket,
                    'CopySource' => $this->bucket.'/'.ltrim($filePath, '/'),
                    'Key'        => $newKey,
                    'ACL'        => 'public-read',
                ]);
            }

            return true;
        } catch (Exception $e) {
            Log::error('OSS copy files failed:', [
                'error'    => $e->getMessage(),
                'files'    => $files,
                'destPath' => $destPath,
            ]);
            throw new Exception(trans('panel::file_manager.copy_failed'));
        }
    }

    public function deleteFiles(string $basePath, array $files): bool
    {
        try {
            $objects = [];
            foreach ($files as $file) {
                $objects[] = [
                    'Key' => ltrim($basePath.'/'.$file, '/'),
                ];
            }

            if (! empty($objects)) {
                $this->s3Client->deleteObjects([
                    'Bucket' => $this->bucket,
                    'Delete' => [
                        'Objects' => $objects,
                    ],
                ]);
            }

            return true;
        } catch (Exception $e) {
            Log::error('OSS delete files failed:', [
                'error' => $e->getMessage(),
                'files' => $files,
            ]);
            throw new Exception(trans('panel::file_manager.delete_failed'));
        }
    }

    public function deleteDirectoryOrFile(string $path): bool
    {
        try {
            // List all objects in this directory
            $objects = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => trim($path, '/').'/',
            ]);

            // Prepare objects for deletion
            $toDelete = [];
            foreach ($objects['Contents'] ?? [] as $object) {
                $toDelete[] = ['Key' => $object['Key']];
            }

            // Delete all objects if any exist
            if (! empty($toDelete)) {
                $this->s3Client->deleteObjects([
                    'Bucket' => $this->bucket,
                    'Delete' => [
                        'Objects' => $toDelete,
                    ],
                ]);
            }

            return true;
        } catch (Exception $e) {
            Log::error('OSS delete directory failed:', [
                'error' => $e->getMessage(),
                'path'  => $path,
            ]);
            throw new Exception(trans('panel::file_manager.delete_failed'));
        }
    }

    public function moveDirectory(string $sourcePath, string $destPath): bool
    {
        try {
            // List all objects in source directory
            $objects = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => trim($sourcePath, '/').'/',
            ]);

            foreach ($objects['Contents'] ?? [] as $object) {
                $sourceKey = $object['Key'];
                $fileName  = substr($sourceKey, strlen(trim($sourcePath, '/').'/'));
                $newKey    = trim($destPath, '/').'/'.$fileName;

                // Skip if source and destination are the same
                if ($sourceKey === $newKey) {
                    continue;
                }

                // Copy object to new location
                $this->s3Client->copyObject([
                    'Bucket'     => $this->bucket,
                    'CopySource' => $this->bucket.'/'.$sourceKey,
                    'Key'        => $newKey,
                    'ACL'        => 'public-read',
                ]);

                // Delete original object
                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $sourceKey,
                ]);
            }

            return true;
        } catch (Exception $e) {
            Log::error('OSS move directory failed:', [
                'error'      => $e->getMessage(),
                'sourcePath' => $sourcePath,
                'destPath'   => $destPath,
            ]);
            throw new Exception(trans('panel::file_manager.move_failed'));
        }
    }

    public function updateName(string $originPath, string $newPath): bool
    {
        try {
            if (substr($originPath, -1) === '/') {
                // Handle directory rename
                return $this->moveDirectory($originPath, $newPath);
            }

            // Handle file rename
            $this->s3Client->copyObject([
                'Bucket'     => $this->bucket,
                'CopySource' => $this->bucket.'/'.ltrim($originPath, '/'),
                'Key'        => ltrim($newPath, '/'),
                'ACL'        => 'public-read',
            ]);

            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => ltrim($originPath, '/'),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('OSS rename failed:', [
                'error'      => $e->getMessage(),
                'originPath' => $originPath,
                'newPath'    => $newPath,
            ]);
            throw new Exception(trans('panel::file_manager.rename_failed'));
        }
    }

    public function search(string $keyword, string $type = 'all'): array
    {
        try {
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
            ]);

            $files = [];
            foreach ($result['Contents'] ?? [] as $object) {
                $key = $object['Key'];

                // Skip directories
                if (substr($key, -1) === '/') {
                    continue;
                }

                // Check if filename matches keyword
                $fileName = basename($key);
                if (stripos($fileName, $keyword) === false) {
                    continue;
                }

                // Filter by type if specified
                if ($type !== 'all') {
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $isImage   = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                    if ($type === 'image' && ! $isImage) {
                        continue;
                    }
                }

                $files[] = [
                    'name'          => $fileName,
                    'path'          => $key,
                    'is_dir'        => false,
                    'size'          => $object['Size'],
                    'last_modified' => $object['LastModified'],
                    'url'           => $this->getFileUrl($key),
                ];
            }

            return $files;
        } catch (Exception $e) {
            Log::error('OSS search failed:', [
                'error'   => $e->getMessage(),
                'keyword' => $keyword,
            ]);
            throw new Exception(trans('panel::file_manager.search_failed'));
        }
    }

    public function getFileInfo(string $path): array
    {
        try {
            $result = $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key'    => ltrim($path, '/'),
            ]);

            return [
                'name'          => basename($path),
                'path'          => $path,
                'size'          => $result['ContentLength'],
                'mime_type'     => $result['ContentType'],
                'last_modified' => $result['LastModified'],
                'url'           => $this->getFileUrl($path),
            ];
        } catch (Exception $e) {
            Log::error('OSS get file info failed:', [
                'error' => $e->getMessage(),
                'path'  => $path,
            ]);
            throw new Exception(trans('panel::file_manager.get_file_info_failed'));
        }
    }

    protected function isImage(string $mimeType): bool
    {
        return strpos($mimeType, 'image/') === 0;
    }

    /**
     * 检查文件是否是图片
     */
    protected function isImagePath(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * 获取文件的MIME类型
     */
    protected function getMimeType(string $path): ?string
    {
        try {
            $result = $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key'    => ltrim($path, '/'),
            ]);

            return $result['ContentType'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * 解析大小字符串（如 "2M", "500K"）
     */
    protected function parseSize(string $size): int
    {
        $unit  = strtoupper(substr($size, -1));
        $value = (int) substr($size, 0, -1);

        switch ($unit) {
            case 'K':
                return $value * 1024;
            case 'M':
                return $value * 1024 * 1024;
            case 'G':
                return $value * 1024 * 1024 * 1024;
            default:
                return (int) $size;
        }
    }
}
