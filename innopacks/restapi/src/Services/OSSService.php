<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Services;

use Aws\S3\S3Client;
use Exception;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Services\MediaUrlResolver;
use InnoShop\Common\Services\StorageService;

class OSSService implements MediaInterface
{
    protected S3Client $s3Client;

    protected string $bucket;

    protected string $cdnDomain;

    protected string $endpoint;

    /**
     * Cached plugin settings to avoid repeated DB queries.
     */
    protected array $config;

    /**
     * MIME type map for extension-based inference (avoids headObject N+1).
     */
    protected const MIME_MAP = [
        // Images
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/x-icon',
        // Documents
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        // Video
        'mp4' => 'video/mp4',
        'avi' => 'video/x-msvideo',
        'mov' => 'video/quicktime',
        'wmv' => 'video/x-ms-wmv',
        'flv' => 'video/x-flv',
        'mkv' => 'video/x-matroska',
        // Audio
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        // Archives
        'zip' => 'application/zip',
        'rar' => 'application/vnd.rar',
        'gz'  => 'application/gzip',
        'tar' => 'application/x-tar',
        // Text
        'txt'  => 'text/plain',
        'csv'  => 'text/csv',
        'html' => 'text/html',
        'json' => 'application/json',
        'xml'  => 'application/xml',
    ];

    public function __construct()
    {
        $this->loadConfig();
        $this->validateConfig();
        $this->initializeS3Client();
    }

    /**
     * Load all plugin settings into instance property once.
     */
    protected function loadConfig(): void
    {
        $driver = system_setting('media_driver', 'local');
        $prefix = "storage_{$driver}_";

        $this->config = [
            'driver'     => $driver,
            'key'        => system_setting($prefix.'key', system_setting('storage_key', '')),
            'secret'     => system_setting($prefix.'secret', system_setting('storage_secret', '')),
            'endpoint'   => system_setting($prefix.'endpoint', system_setting('storage_endpoint', '')),
            'bucket'     => system_setting($prefix.'bucket', system_setting('storage_bucket', '')),
            'region'     => system_setting($prefix.'region', system_setting('storage_region', '')),
            'cdn_domain' => system_setting($prefix.'cdn_domain', system_setting('storage_cdn_domain', '')),
        ];

        $this->bucket    = $this->config['bucket'];
        $this->cdnDomain = $this->config['cdn_domain'];
        $this->endpoint  = $this->config['endpoint'];
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
            if (empty($this->config[$field])) {
                $missing[] = $label;
            }
        }

        if (! empty($missing)) {
            throw new Exception('OSS configuration incomplete: '.implode(', ', $missing));
        }
    }

    protected function initializeS3Client(): void
    {
        $this->s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => $this->config['region'],
            'credentials' => [
                'key'    => $this->config['key'],
                'secret' => $this->config['secret'],
            ],
            'endpoint'                => $this->config['endpoint'],
            'use_path_style_endpoint' => false,
        ]);
    }

    public function uploadFile($file, $savePath, $originName): string
    {
        try {
            $resolver = MediaUrlResolver::getInstance();
            $storeAs  = $resolver->shouldRenameToHash()
                ? $resolver->resolveStoreFileName($file)
                : $originName;
            $key = $this->getObjectKey($savePath, $storeAs);

            $this->s3Client->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $key,
                'Body'        => fopen($file->getRealPath(), 'rb'),
                'ACL'         => 'public-read',
                'ContentType' => $file->getMimeType(),
            ]);

            $this->invalidateCDN([$key]);

            $storageKey = StorageService::storageKey($key);

            try {
                $resolver->registerFromUploadedFile($file, $storageKey, $this->config['driver']);
            } catch (\Throwable $e) {
                Log::warning('Media register failed: '.$e->getMessage(), ['storage_key' => $storageKey]);
            }

            return $storageKey;
        } catch (Exception $e) {
            Log::error('OSS upload failed:', [
                'error' => $e->getMessage(),
                'file'  => $originName,
            ]);
            throw new Exception(trans('panel/media.upload_failed'));
        }
    }

    /**
     * Get files list with pagination.
     * For keyword search or non-name sort, falls back to full scan (required by S3 limitations).
     * Otherwise uses S3 MaxKeys for server-side limiting.
     */
    public function getFiles(string $baseFolder, ?string $keyword = '', string $sort = 'name', string $order = 'asc', int $page = 1, int $perPage = 20): array
    {
        try {
            $prefix = trim($baseFolder, '/');
            $prefix = $prefix ? $prefix.'/' : '';

            $result = $this->s3Client->listObjectsV2([
                'Bucket'    => $this->bucket,
                'Prefix'    => $prefix,
                'Delimiter' => '/',
            ]);

            // Format directories (always complete via CommonPrefixes)
            $directories = array_map(function ($prefix) {
                $name = basename(rtrim($prefix['Prefix'], '/'));

                return [
                    'name'          => $name,
                    'path'          => StorageService::storageKey(rtrim($prefix['Prefix'], '/')),
                    'is_dir'        => true,
                    'thumb'         => url('/images/icons/folder.png'),
                    'url'           => '',
                    'mime'          => 'directory',
                    'size'          => 0,
                    'last_modified' => null,
                ];
            }, $result['CommonPrefixes'] ?? []);

            // Format files — use extension-based MIME (no headObject)
            $files = array_map(function ($object) {
                if (substr($object['Key'], -1) === '/') {
                    return null;
                }
                $name = basename($object['Key']);
                $url  = $this->getFileUrl($object['Key']);

                return [
                    'name'          => $name,
                    'path'          => StorageService::storageKey($object['Key']),
                    'is_dir'        => false,
                    'thumb'         => $this->isImagePath($object['Key']) ? $url : url('/images/icons/file.png'),
                    'url'           => $url,
                    'mime'          => $this->getMimeType($object['Key']),
                    'size'          => $object['Size'] ?? 0,
                    'last_modified' => $object['LastModified'] ?? null,
                ];
            }, $result['Contents'] ?? []);

            // Filter nulls and merge
            $items = array_merge($directories, array_filter($files));

            // Batch-resolve media_ids for all file items (avoid N+1).
            $storageKeys = array_filter(array_map(fn ($i) => $i['path'] ?? null, $files));
            $mediaIdMap  = [];
            if (! empty($storageKeys)) {
                $rows = MediaFile::query()
                    ->whereIn('storage_key', array_unique(array_values($storageKeys)))
                    ->pluck('id', 'storage_key');
                foreach ($rows as $storageKey => $id) {
                    $mediaIdMap[$storageKey] = (int) $id;
                }
            }
            foreach ($items as &$item) {
                if ($item['is_dir'] ?? false) {
                    continue;
                }
                $mediaId = $mediaIdMap[$item['path']] ?? null;

                $item['media_id']        = $mediaId;
                $item['media_reference'] = $mediaId
                    ? MediaUrlResolver::buildReference($mediaId)
                    : null;
            }
            unset($item);

            // Apply search filter
            if ($keyword) {
                $items = array_filter($items, function ($item) use ($keyword) {
                    return stripos($item['name'], $keyword) !== false;
                });
                $items = array_values($items);
            }

            // Sort: directories first, then by specified field
            usort($items, function ($a, $b) use ($sort, $order) {
                if ($a['is_dir'] && ! $b['is_dir']) {
                    return -1;
                }
                if (! $a['is_dir'] && $b['is_dir']) {
                    return 1;
                }

                $cmp = 0;
                if ($sort === 'name') {
                    $cmp = strcmp($a['name'], $b['name']);
                } elseif ($sort === 'size') {
                    $cmp = ($a['size'] ?? 0) <=> ($b['size'] ?? 0);
                } elseif ($sort === 'created') {
                    $cmp = ($a['last_modified'] ?? 0) <=> ($b['last_modified'] ?? 0);
                }

                return $order === 'desc' ? -$cmp : $cmp;
            });

            $total  = count($items);
            $offset = ($page - 1) * $perPage;
            $items  = array_slice($items, $offset, $perPage);

            return [
                'items'    => $items,
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
                'success'  => true,
            ];
        } catch (Exception $e) {
            Log::error('OSS get files failed:', [
                'error' => $e->getMessage(),
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
        if ($this->cdnDomain) {
            $url = rtrim($this->cdnDomain, '/').'/'.ltrim($key, '/');

            if ($this->config['driver'] === 'qiniu') {
                return $this->signQiniuPrivateUrl($url);
            }

            return $url;
        }

        $endpoint = preg_replace('#^https?://#', '', $this->endpoint);

        return sprintf('https://%s.%s/%s', $this->bucket, $endpoint, ltrim($key, '/'));
    }

    /**
     * Generate Qiniu Cloud Kodo private download URL with token signature.
     * Keeps bucket private while allowing authenticated file access.
     */
    protected function signQiniuPrivateUrl(string $url, int $expires = 3600): string
    {
        $deadline    = time() + $expires;
        $urlToSign   = $url.'?e='.$deadline;
        $sign        = hash_hmac('sha1', $urlToSign, $this->config['secret'], true);
        $encodedSign = str_replace(['+', '/'], ['-', '_'], base64_encode($sign));
        $token       = $this->config['key'].':'.$encodedSign;

        return $urlToSign.'&token='.$token;
    }

    /**
     * Invalidate CDN cache for the given keys.
     * Silently logs failures — CDN refresh is best-effort.
     */
    protected function invalidateCDN(array $keys): void
    {
        if (empty($this->cdnDomain)) {
            return;
        }

        try {
            // Build full URLs for CDN invalidation
            $paths = array_map(fn ($key) => $this->getFileUrl($key), $keys);

            // Use S3Client to create a CloudFront-style invalidation if available.
            // For Alibaba Cloud OSS + CDN, use the CDN SDK or API directly.
            // This is a placeholder that logs the intent — actual CDN API integration
            // should be added based on the specific CDN provider.
            Log::debug('CDN invalidation requested:', ['paths' => $paths]);
        } catch (Exception $e) {
            Log::warning('CDN invalidation failed:', ['error' => $e->getMessage()]);
        }
    }

    public function getDirectories(string $baseFolder = '/'): array
    {
        try {
            // Root request: return tree root with top-level children for initial load
            if ($baseFolder === '/' || $baseFolder === '') {
                $root = [
                    'id'       => '/',
                    'name'     => '/',
                    'path'     => '/',
                    'parent'   => null,
                    'isRoot'   => true,
                    'children' => $this->fetchChildDirectories(''),
                ];

                return [$root];
            }

            // Sub-directory request: return flat list of immediate children (for lazy loading)
            return $this->fetchChildDirectories($baseFolder);
        } catch (Exception $e) {
            Log::error('OSS get directories failed:', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Fetch immediate child directories with hasChildren flag (parallel check).
     */
    protected function fetchChildDirectories(string $prefix): array
    {
        $params = [
            'Bucket'    => $this->bucket,
            'Delimiter' => '/',
        ];
        if ($prefix !== '') {
            $params['Prefix'] = rtrim($prefix, '/').'/';
        }

        $result = $this->s3Client->listObjectsV2($params);

        $children = [];
        foreach ($result['CommonPrefixes'] ?? [] as $commonPrefix) {
            $fullPath            = rtrim($commonPrefix['Prefix'], '/');
            $name                = basename($fullPath);
            $children[$fullPath] = [
                'id'     => $fullPath,
                'name'   => $name,
                'path'   => $fullPath,
                'parent' => $prefix === '' ? '/' : rtrim($prefix, '/'),
                'isRoot' => false,
            ];
        }

        // Parallel check hasChildren for all directories
        if (! empty($children)) {
            $hasChildrenMap = $this->batchCheckHasChildren(array_keys($children));
            foreach ($children as $path => &$child) {
                $child['hasChildren'] = $hasChildrenMap[$path] ?? false;
            }
            unset($child);
        }

        return array_values($children);
    }

    /**
     * Check multiple directories in parallel for sub-directory existence.
     * Uses async S3 calls via Guzzle promises — N checks take ~1 round-trip.
     *
     * @return array<string, bool> Map of path => hasChildren
     */
    protected function batchCheckHasChildren(array $paths): array
    {
        $promises = [];
        foreach ($paths as $path) {
            $prefix          = rtrim($path, '/').'/';
            $promises[$path] = $this->s3Client->listObjectsV2Async([
                'Bucket'    => $this->bucket,
                'Prefix'    => $prefix,
                'Delimiter' => '/',
                'MaxKeys'   => 1,
            ]);
        }

        $results = Utils::settle($promises)->wait();

        $map = [];
        foreach ($results as $path => $result) {
            if ($result['state'] === 'fulfilled') {
                $map[$path] = ! empty($result['value']['CommonPrefixes']);
            } else {
                $map[$path] = false;
            }
        }

        return $map;
    }

    public function createDirectory($path): bool
    {
        try {
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
            throw new Exception(trans('panel/media.create_fail'));
        }
    }

    public function moveFiles(array $files, string $destPath): bool
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

                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => ltrim($filePath, '/'),
                ]);

                $this->relocateMedia($filePath, $newKey);
            }

            $keys = array_map(fn ($f) => ltrim($f, '/'), $files);
            $this->invalidateCDN($keys);

            return true;
        } catch (Exception $e) {
            Log::error('OSS move files failed:', [
                'error'    => $e->getMessage(),
                'files'    => $files,
                'destPath' => $destPath,
            ]);
            throw new Exception(trans('panel/media.move_fail'));
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

                try {
                    $newStorageKey = $this->normalizeMediaKey($newKey);
                    MediaUrlResolver::getInstance()->registerCopy($newStorageKey, $this->config['driver'], [
                        'original_name' => $fileName,
                        'source'        => 'copy',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('OSS media copy register failed: '.$e->getMessage(), ['key' => $newKey]);
                }
            }

            $keys = array_map(fn ($f) => trim($destPath, '/').'/'.basename($f), $files);
            $this->invalidateCDN($keys);

            return true;
        } catch (Exception $e) {
            Log::error('OSS copy files failed:', [
                'error'    => $e->getMessage(),
                'files'    => $files,
                'destPath' => $destPath,
            ]);
            throw new Exception(trans('panel/media.copy_failed'));
        }
    }

    public function deleteFiles(string $basePath, array $files): bool
    {
        try {
            $keys = [];
            foreach ($files as $file) {
                $key = ltrim($basePath.'/'.$file, '/');

                // Delete the object itself
                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $key,
                ]);
                $keys[] = $key;

                // Also delete as a potential directory marker (key with trailing slash)
                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $key.'/',
                ]);

                $this->removeMedia($key);
            }

            $this->invalidateCDN($keys);

            return true;
        } catch (Exception $e) {
            Log::error('OSS delete files failed:', [
                'error' => $e->getMessage(),
                'files' => $files,
            ]);
            throw new Exception(trans('panel/media.delete_failed'));
        }
    }

    public function deleteDirectoryOrFile(string $path): bool
    {
        try {
            $prefix = trim($path, '/').'/';

            Log::info('OSS deleteDirectoryOrFile called', [
                'raw_path' => $path,
                'prefix'   => $prefix,
                'bucket'   => $this->bucket,
            ]);

            // Delete all objects under the prefix
            $objects = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
            ]);

            $keys = [];
            foreach ($objects['Contents'] ?? [] as $object) {
                $keys[] = $object['Key'];

                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $object['Key'],
                ]);
            }

            // Explicitly delete the directory marker object (0-byte key like "folder/")
            // Some S3-compatible stores (e.g. Tencent COS) may not include it in list results
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $prefix,
            ]);

            Log::info('OSS deleteDirectoryOrFile completed', [
                'deleted_keys'   => $keys,
                'deleted_marker' => $prefix,
            ]);

            $this->invalidateCDN($keys);
            $this->removeMediaUnderPrefix($prefix);

            return true;
        } catch (Exception $e) {
            Log::error('OSS delete directory failed:', [
                'error' => $e->getMessage(),
                'path'  => $path,
            ]);
            throw new Exception(trans('panel/media.delete_failed'));
        }
    }

    public function moveDirectory(string $sourcePath, string $destPath): bool
    {
        try {
            $objects = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => trim($sourcePath, '/').'/',
            ]);

            foreach ($objects['Contents'] ?? [] as $object) {
                $sourceKey = $object['Key'];
                $fileName  = substr($sourceKey, strlen(trim($sourcePath, '/').'/'));
                $newKey    = trim($destPath, '/').'/'.$fileName;

                if ($sourceKey === $newKey) {
                    continue;
                }

                $this->s3Client->copyObject([
                    'Bucket'     => $this->bucket,
                    'CopySource' => $this->bucket.'/'.$sourceKey,
                    'Key'        => $newKey,
                    'ACL'        => 'public-read',
                ]);

                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $sourceKey,
                ]);
            }

            $this->invalidateCDN([trim($sourcePath, '/').'/']);
            $this->relocateMediaUnderPrefix($sourcePath, $destPath);

            return true;
        } catch (Exception $e) {
            Log::error('OSS move directory failed:', [
                'error'      => $e->getMessage(),
                'sourcePath' => $sourcePath,
                'destPath'   => $destPath,
            ]);
            throw new Exception(trans('panel/media.move_fail'));
        }
    }

    public function updateName(string $originPath, string $newPath): bool
    {
        try {
            if (substr($originPath, -1) === '/') {
                return $this->moveDirectory($originPath, $newPath);
            }

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

            $this->invalidateCDN([ltrim($originPath, '/'), ltrim($newPath, '/')]);
            $this->relocateMedia($originPath, $newPath);

            return true;
        } catch (Exception $e) {
            Log::error('OSS rename failed:', [
                'error'      => $e->getMessage(),
                'originPath' => $originPath,
                'newPath'    => $newPath,
            ]);
            throw new Exception(trans('panel/media.rename_failed'));
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

                if (substr($key, -1) === '/') {
                    continue;
                }

                $fileName = basename($key);
                if (stripos($fileName, $keyword) === false) {
                    continue;
                }

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
            throw $e;
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
            throw $e;
        }
    }

    /**
     * Get MIME type from file extension (no HTTP request needed).
     */
    protected function getMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return self::MIME_MAP[$extension] ?? 'application/octet-stream';
    }

    protected function isImagePath(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    // ==================== Media Library Sync ====================

    /**
     * Normalize a key used in S3 operations to a storage_key (with static/media/ prefix).
     * S3 keys may or may not include the prefix; storage_key in DB always has it.
     */
    protected function normalizeMediaKey(string $key): string
    {
        $key = ltrim($key, '/');

        return StorageService::isStoragePath($key) ? $key : StorageService::storageKey($key);
    }

    /**
     * Sync media_files after renaming / moving a single object.
     */
    protected function relocateMedia(string $oldKey, string $newKey): void
    {
        $oldFull = $this->normalizeMediaKey($oldKey);
        $newFull = $this->normalizeMediaKey($newKey);
        try {
            MediaUrlResolver::getInstance()->relocateByKey($oldFull, $newFull, $this->config['driver']);
        } catch (\Throwable $e) {
            Log::warning('OSS media relocate failed: '.$e->getMessage(), ['old' => $oldFull, 'new' => $newFull]);
        }
    }

    /**
     * Soft delete a media record after deleting a single object.
     */
    protected function removeMedia(string $key): void
    {
        $fullKey = $this->normalizeMediaKey($key);
        try {
            MediaUrlResolver::getInstance()->removeByKey($fullKey);
        } catch (\Throwable $e) {
            Log::warning('OSS media remove failed: '.$e->getMessage(), ['key' => $fullKey]);
        }
    }

    /**
     * Soft delete all media records under a directory prefix.
     */
    protected function removeMediaUnderPrefix(string $prefix): void
    {
        $normalized = $this->normalizeMediaKey(rtrim($prefix, '/').'/');
        try {
            foreach (MediaFile::where('storage_key', 'like', $normalized.'%')->cursor() as $media) {
                $media->delete();
            }
        } catch (\Throwable $e) {
            Log::warning('OSS media remove under prefix failed: '.$e->getMessage(), ['prefix' => $normalized]);
        }
    }

    /**
     * Relocate all media records under a directory prefix (used by moveDirectory).
     */
    protected function relocateMediaUnderPrefix(string $oldPrefix, string $newPrefix): void
    {
        $oldFull = $this->normalizeMediaKey(rtrim($oldPrefix, '/').'/');
        $newFull = $this->normalizeMediaKey(rtrim($newPrefix, '/').'/');
        try {
            $resolver = MediaUrlResolver::getInstance();
            foreach (MediaFile::where('storage_key', 'like', $oldFull.'%')->cursor() as $media) {
                $newKey = $newFull.substr($media->storage_key, strlen($oldFull));
                $resolver->relocate($media->id, $newKey, $this->config['driver']);
            }
        } catch (\Throwable $e) {
            Log::warning('OSS media relocate under prefix failed: '.$e->getMessage(), ['old' => $oldFull, 'new' => $newFull]);
        }
    }
}
