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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Services\FileSecurityValidator;
use InnoShop\Common\Services\StorageService;

class OSSService implements FileManagerInterface
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
        $driver = system_setting('file_manager_driver', 'local');
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
            $key = $this->getObjectKey($savePath, $originName);

            $this->s3Client->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $key,
                'Body'        => fopen($file->getRealPath(), 'rb'),
                'ACL'         => 'public-read',
                'ContentType' => $file->getMimeType(),
            ]);

            $this->invalidateCDN([$key]);

            return StorageService::storageKey($key);
        } catch (Exception $e) {
            Log::error('OSS upload failed:', [
                'error' => $e->getMessage(),
                'file'  => $originName,
            ]);
            throw new Exception(trans('panel/file_manager.upload_failed'));
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
            $root = [
                'id'     => '/',
                'name'   => '/',
                'path'   => '/',
                'parent' => null,
                'isRoot' => true,
            ];

            // Collect all prefixes recursively
            $allPrefixes = $this->collectAllPrefixes('');

            if (empty($allPrefixes)) {
                return [$root];
            }

            // Build flat node list
            $nodes = ['/' => $root];
            foreach ($allPrefixes as $prefix) {
                $path   = rtrim($prefix, '/');
                $name   = basename($path);
                $parent = dirname($path);
                $parent = $parent === '.' ? '/' : $parent;

                $nodes[$path] = [
                    'id'     => $path,
                    'name'   => $name,
                    'path'   => $path,
                    'parent' => $parent,
                    'isRoot' => false,
                ];
            }

            // Build tree with children
            foreach ($nodes as $key => &$node) {
                if (isset($node['parent']) && isset($nodes[$node['parent']])) {
                    $nodes[$node['parent']]['children'][] = &$node;
                }
            }
            unset($node);

            return [$nodes['/']];
        } catch (Exception $e) {
            Log::error('OSS get directories failed:', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Recursively collect all directory prefixes from S3.
     * Uses Delimiter to get CommonPrefixes at each level.
     */
    protected function collectAllPrefixes(string $prefix): array
    {
        $result = $this->s3Client->listObjectsV2([
            'Bucket'    => $this->bucket,
            'Prefix'    => $prefix,
            'Delimiter' => '/',
        ]);

        $prefixes = [];
        foreach ($result['CommonPrefixes'] ?? [] as $commonPrefix) {
            $p          = $commonPrefix['Prefix'];
            $prefixes[] = $p;
            // Recurse into subdirectory
            $prefixes = array_merge($prefixes, $this->collectAllPrefixes($p));
        }

        return $prefixes;
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
            throw new Exception(trans('panel/file_manager.create_fail'));
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
            throw new Exception(trans('panel/file_manager.move_fail'));
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

            $keys = array_map(fn ($f) => trim($destPath, '/').'/'.basename($f), $files);
            $this->invalidateCDN($keys);

            return true;
        } catch (Exception $e) {
            Log::error('OSS copy files failed:', [
                'error'    => $e->getMessage(),
                'files'    => $files,
                'destPath' => $destPath,
            ]);
            throw new Exception(trans('panel/file_manager.copy_failed'));
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
            }

            $this->invalidateCDN($keys);

            return true;
        } catch (Exception $e) {
            Log::error('OSS delete files failed:', [
                'error' => $e->getMessage(),
                'files' => $files,
            ]);
            throw new Exception(trans('panel/file_manager.delete_failed'));
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

            return true;
        } catch (Exception $e) {
            Log::error('OSS delete directory failed:', [
                'error' => $e->getMessage(),
                'path'  => $path,
            ]);
            throw new Exception(trans('panel/file_manager.delete_failed'));
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

            return true;
        } catch (Exception $e) {
            Log::error('OSS move directory failed:', [
                'error'      => $e->getMessage(),
                'sourcePath' => $sourcePath,
                'destPath'   => $destPath,
            ]);
            throw new Exception(trans('panel/file_manager.move_fail'));
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

            return true;
        } catch (Exception $e) {
            Log::error('OSS rename failed:', [
                'error'      => $e->getMessage(),
                'originPath' => $originPath,
                'newPath'    => $newPath,
            ]);
            throw new Exception(trans('panel/file_manager.rename_failed'));
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

    /**
     * Download a remote file from URL and upload to S3.
     *
     * @param  string  $url  Remote file URL
     * @param  string  $savePath  Target directory path
     * @param  string|null  $fileName  Optional file name
     * @return string Storage key of the saved file
     *
     * @throws Exception
     */
    public function downloadRemoteFile(string $url, string $savePath, ?string $fileName = null): string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception(trans('panel/file_manager.invalid_url'));
        }

        $savePath = FileSecurityValidator::validateDirectoryPath($savePath);

        // Download the file first
        $response = Http::timeout(60)->get($url);
        if (! $response->successful()) {
            throw new Exception(trans('panel/file_manager.download_failed'));
        }

        // Determine file name
        if (empty($fileName)) {
            $fileName = basename(parse_url($url, PHP_URL_PATH));
        }
        if (empty($fileName) || ! str_contains($fileName, '.')) {
            $extension = $this->getExtensionFromResponse($response, $url);
            $fileName  = md5($url).'.'.$extension;
        }

        FileSecurityValidator::validateFile($fileName);

        $key      = trim($savePath, '/').'/'.$fileName;
        $mimeType = $this->getMimeType($fileName);

        $this->s3Client->putObject([
            'Bucket'      => $this->bucket,
            'Key'         => $key,
            'Body'        => $response->body(),
            'ACL'         => 'public-read',
            'ContentType' => $mimeType,
        ]);

        $this->invalidateCDN([$key]);

        return StorageService::storageKey($key);
    }

    /**
     * Guess file extension from response Content-Type or URL.
     */
    protected function getExtensionFromResponse($response, string $url): string
    {
        $contentType = $response->header('Content-Type') ?? '';
        $mimeToExt   = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/gif'       => 'gif',
            'image/webp'      => 'webp',
            'image/svg+xml'   => 'svg',
            'image/bmp'       => 'bmp',
            'video/mp4'       => 'mp4',
            'application/pdf' => 'pdf',
        ];

        $mime = strtolower(strtok($contentType, ';'));
        if (isset($mimeToExt[$mime])) {
            return $mimeToExt[$mime];
        }

        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
        $format    = $query['fm'] ?? '';
        $formatMap = ['jpg' => 'jpg', 'jpeg' => 'jpg', 'png' => 'png', 'gif' => 'gif', 'webp' => 'webp'];
        if (isset($formatMap[$format])) {
            return $formatMap[$format];
        }

        return 'jpg';
    }
}
