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
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Common\Requests\UploadFileRequest;
use InnoShop\Panel\Controllers\BaseController;
use InnoShop\RestAPI\Requests\DeleteFilesRequest;
use InnoShop\RestAPI\Requests\FileRequest;
use InnoShop\RestAPI\Requests\MoveFilesRequest;
use InnoShop\RestAPI\Requests\RenameFileRequest;
use InnoShop\RestAPI\Services\FileManagerInterface;
use InnoShop\RestAPI\Services\FileManagerService;
use InnoShop\RestAPI\Services\OSSService;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;

#[Group('Panel - File Manager')]
class FileManagerController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getService(): FileManagerInterface
    {
        $service = app(FileManagerInterface::class);

        return fire_hook_filter('file_manager.service', $service);
    }

    /**
     * 获取文件管理器的基础配置数据
     * Get basic configuration data for file manager
     *
     * @return array
     */
    protected function getFileManagerData(): array
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

        $fmDriver = system_setting('file_manager_driver', 'local');

        return [
            'isIframe'        => $request->header('X-Iframe') === '1',
            'multiple'        => $request->query('multiple') === '1',
            'type'            => $request->query('type', 'all'),
            'base_folder'     => '/',
            'driver'          => $fmDriver,
            'title'           => $fmDriver !== 'local' ? trans('panel/file_manager.oss_title') : trans('panel/file_manager.root_name'),
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
        $data = $this->getFileManagerData();

        return inno_view('panel::file_manager.index', $data);
    }

    /**
     * Display the file manager iframe view.
     *
     * @return mixed
     */
    #[Endpoint('File manager iframe view')]
    public function iframe(): mixed
    {
        $data = $this->getFileManagerData();

        // Override isIframe to true for iframe view
        $data['isIframe'] = true;

        return inno_view('panel::file_manager.iframe', $data);
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
            $baseFolder = (string) $request->input('base_folder', '/');
            $page       = (int) $request->input('page', 1);
            $perPage    = (int) $request->input('per_page', 20);
            $keyword    = (string) $request->input('keyword', '');
            $sort       = (string) $request->input('sort', 'created');  // 默认按创建时间排序
            $order      = (string) $request->input('order', 'desc');    // 默认降序，最新的在前面

            $service = $this->getService();

            return $service->getFiles($baseFolder, $keyword, $sort, $order, $page, $perPage);

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
                throw new Exception(trans('panel/file_manager.invalid_params'));
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
        $savePath = $request->get('path');

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
                'driver' => system_setting('file_manager_driver', 'local'),
            ];

            return json_success(trans('panel/file_manager.storage_config_loaded'), $config);
        } catch (Exception $e) {
            Log::error('Get storage config failed:', [
                'error' => $e->getMessage(),
            ]);

            return json_fail(trans('panel/file_manager.storage_config_load_failed').': '.$e->getMessage());
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

            SettingRepo::getInstance()->updateSystemValue('file_manager_driver', $driver);

            Artisan::call('config:clear');
            load_settings();

            // Rebind the FileManagerInterface singleton with the new driver
            $s3Drivers = ['oss', 'cos', 'qiniu', 's3', 'obs', 'r2', 'minio'];
            app()->forgetInstance(FileManagerInterface::class);
            if (in_array($driver, $s3Drivers)) {
                app()->singleton(FileManagerInterface::class, function () {
                    return new OSSService;
                });
            } else {
                app()->singleton(FileManagerInterface::class, function () {
                    return new FileManagerService;
                });
            }

            return json_success(trans('panel/file_manager.storage_config_saved'), ['driver' => $driver]);
        } catch (Exception $e) {
            Log::error('Save storage config failed:', [
                'error' => $e->getMessage(),
            ]);

            return json_fail(trans('panel/file_manager.storage_config_save_failed').': '.$e->getMessage());
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
     * Download a remote file and save to the file manager.
     *
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Download remote file')]
    #[BodyParam('url', type: 'string', required: true, description: 'Remote file URL')]
    #[BodyParam('path', type: 'string', required: false, description: 'Target directory path')]
    #[BodyParam('file_name', type: 'string', required: false, description: 'Custom file name')]
    public function downloadRemoteFile(Request $request): mixed
    {
        try {
            $url      = $request->input('url');
            $savePath = $request->input('path', '/');
            $fileName = $request->input('file_name');

            if (empty($url)) {
                throw new Exception(trans('panel/file_manager.invalid_url'));
            }

            $service    = $this->getService();
            $storageKey = $service->downloadRemoteFile($url, $savePath, $fileName);

            $data = [
                'name'       => $fileName ?? basename(parse_url($url, PHP_URL_PATH)),
                'path'       => $storageKey,
                'url'        => storage_url($storageKey),
                'origin_url' => storage_url($storageKey),
            ];

            return json_success(trans('panel/file_manager.download_success'), $data);
        } catch (Exception $e) {
            Log::error('Download remote file failed:', [
                'error' => $e->getMessage(),
                'url'   => $url ?? '',
            ]);

            return json_fail($e->getMessage());
        }
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
}
