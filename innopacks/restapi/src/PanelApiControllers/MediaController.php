<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Common\Requests\UploadFileRequest;
use InnoShop\Panel\Controllers\BaseController;
use InnoShop\RestAPI\Criteria\FileListCriteria;
use InnoShop\RestAPI\Requests\DeleteFilesRequest;
use InnoShop\RestAPI\Requests\FileRequest;
use InnoShop\RestAPI\Requests\MoveFilesRequest;
use InnoShop\RestAPI\Requests\RenameFileRequest;
use InnoShop\RestAPI\Services\MediaInterface;
use InnoShop\RestAPI\Services\MediaService;
use InnoShop\RestAPI\Services\OSSService;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;

#[Group('Panel - File Manager')]
class MediaController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getService(): MediaInterface
    {
        $service = app(MediaInterface::class);

        return fire_hook_filter('media.service', $service);
    }

    /**
     * 获取文件管理器的基础配置数据
     * Get basic configuration data for file manager
     *
     * @return array
     */
    protected function getMediaData(): array
    {
        $uploadMaxFileSize = ini_get('upload_max_filesize');
        $postMaxSize       = ini_get('post_max_size');

        // Ensure we have valid values, provide defaults if empty
        if (empty($uploadMaxFileSize) || $uploadMaxFileSize === false) {
            $uploadMaxFileSize = '2M'; // Default fallback
        }
        if (empty($postMaxSize) || $postMaxSize === false) {
            $postMaxSize = '8M'; // Default fallback
        }

        $request = request();

        $fmDriver = system_setting('media_driver', 'local');

        return [
            'isIframe'        => $request->header('X-Iframe') === '1',
            'multiple'        => $request->query('multiple') === '1',
            'type'            => $request->query('type', 'all'),
            'base_folder'     => '/',
            'driver'          => $fmDriver,
            'title'           => $fmDriver !== 'local' ? trans('panel/media.oss_title') : trans('panel/media.root_name'),
            'enabled_drivers' => $this->getEnabledDrivers(),
            'config'          => [
                'driver'   => $fmDriver,
                'endpoint' => system_setting("storage_{$fmDriver}_endpoint", system_setting('storage_endpoint', '')),
                'bucket'   => system_setting("storage_{$fmDriver}_bucket", system_setting('storage_bucket', '')),
                'baseUrl'  => config('app.url'),
            ],
            'uploadMaxFileSize' => $uploadMaxFileSize,
            'postMaxSize'       => $postMaxSize,
        ];
    }

    /**
     * Display the file manager index view.
     *
     * @return mixed
     */
    #[Endpoint('File manager index page')]
    public function index(): mixed
    {
        $data = $this->getMediaData();

        return response(inno_view('panel::media.index', $data))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Display the file manager iframe view.
     *
     * @return mixed
     */
    #[Endpoint('File manager iframe view')]
    public function iframe(): mixed
    {
        $data = $this->getMediaData();

        // Override isIframe to true for iframe view
        $data['isIframe'] = true;

        return response(inno_view('panel::media.iframe', $data))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Retrieve a list of files in a folder based on filters.
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('List files')]
    #[QueryParam('base_folder', type: 'string', required: false, example: '/')]
    #[QueryParam('page', type: 'integer', required: false, example: 1)]
    #[QueryParam('per_page', type: 'integer', required: false, example: 20)]
    #[QueryParam('keyword', type: 'string', required: false, description: 'Search keyword')]
    #[QueryParam('sort', type: 'string', required: false, example: 'created')]
    #[QueryParam('order', type: 'string', required: false, example: 'desc')]
    public function getFiles(Request $request): mixed
    {
        try {
            $service = $this->getService();

            return $service->getFiles(FileListCriteria::fromRequest($request));

        } catch (Exception $e) {
            Log::error('Get files failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Retrieve a list of directories.
     *
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('List directories')]
    #[QueryParam('base_folder', type: 'string', required: false, example: '/')]
    public function getDirectories(Request $request): mixed
    {
        $service    = $this->getService();
        $baseFolder = $request->get('base_folder', '/');
        $data       = $service->getDirectories($baseFolder);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Create a new directory.
     *
     * @param  FileRequest  $request
     * @return mixed
     */
    #[Endpoint('Create directory')]
    #[BodyParam('name', type: 'string', required: true, description: 'Directory name')]
    #[BodyParam('parent_id', type: 'string', required: false, example: '/')]
    public function createDirectory(FileRequest $request): mixed
    {
        try {
            $folderName = $request->get('name');
            $parentId   = $request->get('parent_id', '/');

            $fullPath = $parentId === '/' ? "/{$folderName}" : "{$parentId}/{$folderName}";

            $service = $this->getService();
            $service->createDirectory($fullPath);

            return create_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Rename a file or folder.
     *
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Rename file or folder')]
    public function rename(RenameFileRequest $request): mixed
    {
        try {
            $originName = $request->input('origin_name');
            $newName    = $request->input('new_name');

            // Prevent path traversal in new name
            if (str_contains($newName, '/') || str_contains($newName, '\\')) {
                throw new Exception(trans('panel/media.invalid_params'));
            }

            $originName = $this->normalizePath($originName);

            $dirPath = dirname($originName);
            $newPath = $dirPath === '/' ? "/{$newName}" : "{$dirPath}/{$newName}";

            $service = $this->getService();
            $service->updateName($originName, $newPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Normalize file path
     *
     * @param  string  $path
     * @return string
     */
    private function normalizePath(string $path): string
    {
        $path = preg_replace('#/+#', '/', $path);

        return '/'.ltrim($path, '/');
    }

    /**
     * Delete specified files in a directory.
     *
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Delete files')]
    public function destroyFiles(DeleteFilesRequest $request): mixed
    {
        try {
            $basePath = $request->input('path');
            $files    = $request->input('files');

            $service = $this->getService();
            $service->deleteFiles($basePath, $files);

            return json_success(trans('common.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Delete a specified directory.
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('Delete directory')]
    #[BodyParam('name', type: 'string', required: true, description: 'Directory path to delete')]
    public function destroyDirectories(Request $request): mixed
    {
        try {
            $folderName = $request->get('name');
            $service    = $this->getService();
            $service->deleteDirectoryOrFile($folderName);

            return json_success(trans('common.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Move a directory to a new location.
     *
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Move directory')]
    #[BodyParam('source_path', type: 'string', required: true, description: 'Source directory path')]
    #[BodyParam('dest_path', type: 'string', required: true, description: 'Destination directory path')]
    public function moveDirectories(Request $request): mixed
    {
        try {
            $sourcePath = $request->get('source_path');
            $destPath   = $request->get('dest_path');
            $service    = $this->getService();
            $service->moveDirectory($sourcePath, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Move multiple image files to a new directory.
     *
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Move files')]
    public function moveFiles(MoveFilesRequest $request): mixed
    {
        try {
            $files    = $request->input('files');
            $destPath = $request->input('dest_path');

            $service = $this->getService();
            $service->moveFiles($files, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            Log::error('Move files failed:', [
                'error' => $e->getMessage(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Upload a file to the specified directory.
     *
     * @param  UploadFileRequest  $request
     * @return mixed
     */
    #[Endpoint('Upload file')]
    #[BodyParam('file', type: 'file', required: true, description: 'File to upload')]
    #[BodyParam('path', type: 'string', required: false, description: 'Target directory path')]
    public function uploadFiles(UploadFileRequest $request): mixed
    {
        $service  = $this->getService();
        $file     = $request->file('file');
        $savePath = $request->get('path', '');

        $originName = $file->getClientOriginalName();
        $storageKey = $service->uploadFile($file, $savePath, $originName);

        $data = [
            'name'       => $originName,
            'path'       => $storageKey,
            'url'        => storage_url($storageKey),
            'origin_url' => storage_url($storageKey),
        ];

        return json_success('success', $data);
    }

    /**
     * Copy multiple files to a new directory.
     *
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Copy files')]
    public function copyFiles(MoveFilesRequest $request): mixed
    {
        try {
            $files    = $request->input('files');
            $destPath = $request->input('dest_path');

            $service = $this->getService();
            $service->copyFiles($files, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            Log::error('Copy files failed:', [
                'error' => $e->getMessage(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Get storage configs (driver only, credentials are in system settings)
     *
     * @return mixed
     */
    #[Endpoint('Get storage configuration')]
    public function getStorageConfig(): mixed
    {
        try {
            $config = [
                'driver' => system_setting('media_driver', 'local'),
            ];

            return json_success(trans('panel/media.storage_config_loaded'), $config);
        } catch (Exception $e) {
            Log::error('Get storage config failed:', [
                'error' => $e->getMessage(),
            ]);

            return json_fail(trans('panel/media.storage_config_load_failed').': '.$e->getMessage());
        }
    }

    /**
     * Save storage driver (credentials are managed in system settings)
     *
     * @param  Request  $request
     * @return mixed
     * @throws \Throwable
     */
    #[Endpoint('Save storage configuration')]
    #[BodyParam('driver', type: 'string', required: true, example: 'local')]
    public function saveStorageConfig(Request $request): mixed
    {
        try {
            $driver = $request->input('driver', 'local');

            SettingRepo::getInstance()->updateSystemValue('media_driver', $driver);

            Artisan::call('config:clear');
            load_settings();

            // Rebind the MediaInterface singleton with the new driver
            $s3Drivers = ['oss', 'cos', 'qiniu', 's3', 'obs', 'r2', 'minio'];
            app()->forgetInstance(MediaInterface::class);
            if (in_array($driver, $s3Drivers)) {
                app()->singleton(MediaInterface::class, function () {
                    return new OSSService;
                });
            } else {
                app()->singleton(MediaInterface::class, function () {
                    return new MediaService;
                });
            }

            return json_success(trans('panel/media.storage_config_saved'), ['driver' => $driver]);
        } catch (Exception $e) {
            Log::error('Save storage config failed:', [
                'error' => $e->getMessage(),
            ]);

            return json_fail(trans('panel/media.storage_config_save_failed').': '.$e->getMessage());
        }
    }

    /**
     * Mask a secret value, showing only the last 4 characters.
     */
    private function maskSecret(string $value): string
    {
        if (empty($value)) {
            return '';
        }
        if (strlen($value) <= 4) {
            return str_repeat('*', strlen($value));
        }

        return str_repeat('*', strlen($value) - 4).substr($value, -4);
    }

    /**
     * Check if a value is a masked placeholder (all asterisks except last 4 chars).
     */
    private function isMasked(string $value): bool
    {
        if (empty($value) || strlen($value) <= 4) {
            return false;
        }

        return str_repeat('*', strlen($value) - 4) === substr($value, 0, strlen($value) - 4);
    }

    /**
     * Get list of enabled cloud drivers from settings.
     * Always includes 'local'.
     */
    private function getEnabledDrivers(): array
    {
        $valid   = ['oss', 'cos', 'qiniu', 's3', 'obs', 'r2', 'minio'];
        $drivers = ['local'];

        foreach ($valid as $driver) {
            if (system_setting("storage_{$driver}_enabled", '0') === '1') {
                $drivers[] = $driver;
            }
        }

        return $drivers;
    }

    /**
     * Get a media record with full metadata (original_name, checksum, mime, size, dimensions, alt, usage).
     *
     * @param  int  $id
     * @return mixed
     */
    #[Endpoint('Get media detail')]
    public function getMediaDetail(int $id): mixed
    {
        try {
            $media = MediaFile::find($id);
            if (! $media) {
                Log::warning('Media detail requested but not found', ['id' => $id]);

                return json_fail(trans('panel/media.media_not_found'));
            }

            $usage = $media->usageCount();

            return json_success(trans('panel/media.media_detail_loaded'), [
                'id'            => $media->id,
                'disk'          => $media->disk,
                'storage_key'   => $media->storage_key,
                'original_name' => $media->original_name,
                'checksum'      => $media->checksum,
                'mime'          => $media->mime,
                'size'          => $media->size,
                'size_readable' => $this->formatBytes((int) $media->size),
                'width'         => $media->width,
                'height'        => $media->height,
                'alt'           => $media->alt,
                'source'        => $media->source,
                'created_at'    => $media->created_at?->toDateTimeString(),
                'updated_at'    => $media->updated_at?->toDateTimeString(),
                'url'           => $media->url(),
                'usage'         => $usage,
                'total_usage'   => array_sum($usage),
            ]);
        } catch (Exception $e) {
            Log::error('Get media detail failed:', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Update editable fields on a media record (currently alt; extensible).
     *
     * @param  int  $id
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Update media')]
    #[BodyParam('alt', type: 'string', required: false, example: 'Galaxy Glow evening gown front view')]
    public function updateMedia(int $id, Request $request): mixed
    {
        try {
            $media = MediaFile::find($id);
            if (! $media) {
                return json_fail(trans('panel/media.media_not_found'));
            }

            if ($request->has('alt')) {
                $media->alt = $request->input('alt') ?: null;
            }
            $media->save();

            return json_success(trans('panel/media.media_updated'), [
                'id'  => $media->id,
                'alt' => $media->alt,
            ]);
        } catch (Exception $e) {
            Log::error('Update media failed:', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Aggregate media library statistics: total count, total size, by disk, by mime.
     *
     * @return mixed
     */
    #[Endpoint('Get media stats')]
    public function getMediaStats(): mixed
    {
        try {
            $baseQuery = MediaFile::query();

            $totalFiles = (clone $baseQuery)->count();
            $totalSize  = (clone $baseQuery)->sum('size');

            $byDisk = (clone $baseQuery)
                ->select('disk', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(size), 0) as size'))
                ->groupBy('disk')
                ->get()
                ->mapWithKeys(fn ($row) => [$row->disk => [
                    'count' => (int) $row->count,
                    'size'  => (int) $row->size,
                ]])
                ->all();

            $byMime = (clone $baseQuery)
                ->select('mime', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(size), 0) as size'))
                ->groupBy('mime')
                ->orderByDesc('size')
                ->limit(10)
                ->get()
                ->map(fn ($row) => [
                    'mime'  => $row->mime ?: 'unknown',
                    'count' => (int) $row->count,
                    'size'  => (int) $row->size,
                ])
                ->all();

            return json_success(trans('panel/media.media_stats_loaded'), [
                'total_files'   => $totalFiles,
                'total_size'    => (int) $totalSize,
                'size_readable' => $this->formatBytes((int) $totalSize),
                'by_disk'       => $byDisk,
                'by_mime'       => $byMime,
            ]);
        } catch (Exception $e) {
            Log::error('Get media stats failed:', [
                'error' => $e->getMessage(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Convert a byte count into a human-readable string (e.g. 4532 -> "4.4 KB").
     */
    private function formatBytes(int $bytes, int $precision = 1): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $exp   = (int) floor(log($bytes, 1024));
        $exp   = min($exp, count($units) - 1);

        return round($bytes / (1024 ** $exp), $precision).' '.$units[$exp];
    }
}
