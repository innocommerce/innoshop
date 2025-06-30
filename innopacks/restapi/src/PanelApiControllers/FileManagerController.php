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
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Requests\UploadFileRequest;
use InnoShop\Panel\Controllers\BaseController;
use InnoShop\RestAPI\Requests\FileRequest;
use InnoShop\RestAPI\Services\FileManagerInterface;
use InnoShop\RestAPI\Services\FileManagerService;
use InnoShop\RestAPI\Services\OSSService;

class FileManagerController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    private function getService(): FileManagerInterface
    {
        try {
            $driver = plugin_setting('file_manager', 'driver');
            Log::info('Getting file manager service:', [
                'driver'     => $driver,
                'key_exists' => ! empty(plugin_setting('file_manager', 'key')),
                'endpoint'   => plugin_setting('file_manager', 'endpoint'),
                'bucket'     => plugin_setting('file_manager', 'bucket'),
            ]);

            if ($driver === 'oss') {
                $service = new OSSService;
                Log::info('Created OSS service');

                return fire_hook_filter('file_manager.service', $service);
            }
        } catch (Exception $e) {
            Log::warning('Failed to initialize OSS service, falling back to local:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // default local file service
        Log::info('Created local file service');

        return fire_hook_filter('file_manager.service', new FileManagerService);
    }

    /**
     * Display the file manager index view.
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $data = [
            'isIframe'    => request()->header('X-Iframe') === '1',
            'multiple'    => request()->query('multiple')  === '1',
            'type'        => request()->query('type', 'all'),
            'base_folder' => '/',
            'driver'      => plugin_setting('file_manager', 'driver', 'local'),
            'title'       => plugin_setting('file_manager', 'driver') === 'oss' ? 'OSS 文件管理' : '图片空间',
            'config'      => [
                'driver'   => plugin_setting('file_manager', 'driver', 'local'),
                'endpoint' => plugin_setting('file_manager', 'endpoint', ''),
                'bucket'   => plugin_setting('file_manager', 'bucket', ''),
                'baseUrl'  => config('app.url'),
            ],
        ];

        Log::info('File manager index:', [
            'data'   => $data,
            'config' => [
                'driver'   => plugin_setting('file_manager', 'driver'),
                'bucket'   => plugin_setting('file_manager', 'bucket'),
                'endpoint' => plugin_setting('file_manager', 'endpoint'),
            ],
        ]);

        return inno_view('panel::file_manager.index', $data);
    }

    /**
     * Display the file manager iframe view.
     *
     * @return mixed
     */
    public function iframe(): mixed
    {
        $data = [
            'isIframe' => true,
            'multiple' => request()->query('multiple') === '1',
            'type'     => request()->query('type', 'all'),
            'config'   => [
                'driver'   => plugin_setting('file_manager', 'driver', 'local'),
                'endpoint' => plugin_setting('file_manager', 'endpoint', ''),
                'bucket'   => plugin_setting('file_manager', 'bucket', ''),
                'baseUrl'  => config('app.url'),
            ],
        ];

        return inno_view('panel::file_manager.iframe', $data);
    }

    /**
     * Retrieve a list of files in a folder based on filters.
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function getFiles(Request $request): mixed
    {
        try {
            $baseFolder = (string) $request->input('base_folder', '/');
            $page       = (int) $request->input('page', 1);
            $perPage    = (int) $request->input('per_page', 20);
            $keyword    = (string) $request->input('keyword', '');
            $sort       = (string) $request->input('sort', 'name');
            $order      = (string) $request->input('order', 'asc');

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
    public function createDirectory(FileRequest $request): mixed
    {
        try {
            $folderName = $request->get('name');
            $service    = $this->getService();
            $service->createDirectory($folderName);

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
    public function rename(Request $request): mixed
    {
        try {
            $originName = $request->get('origin_name');
            $newName    = $request->get('new_name');

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
    public function destroyFiles(Request $request): mixed
    {
        try {
            $requestData = json_decode($request->getContent(), true);
            $basePath    = $requestData['path']  ?? '/';
            $files       = $requestData['files'] ?? [];

            if (empty($files)) {
                throw new Exception(trans('panel::file_manager.no_files_selected'));
            }

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
    public function moveFiles(Request $request): mixed
    {
        try {
            $requestData = json_decode($request->getContent(), true);
            $files       = $requestData['files']     ?? [];
            $destPath    = $requestData['dest_path'] ?? '';

            if (empty($files) || empty($destPath)) {
                throw new Exception(trans('panel::file_manager.invalid_params'));
            }

            Log::info('Move files request:', [
                'files'    => $files,
                'destPath' => $destPath,
            ]);

            $service = $this->getService();
            $service->moveFiles($files, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            Log::error('Move files failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
    public function uploadFiles(UploadFileRequest $request): mixed
    {
        $service  = $this->getService();
        $file     = $request->file('file');
        $savePath = $request->get('path');

        $originName = $file->getClientOriginalName();
        $fileUrl    = $service->uploadFile($file, $savePath, $originName);

        $data = [
            'name' => $originName,
            'url'  => $fileUrl,
        ];

        return json_success('success', $data);
    }

    /**
     * Copy multiple files to a new directory.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function copyFiles(Request $request): mixed
    {
        try {
            $requestData = json_decode($request->getContent(), true);
            $files       = $requestData['files']     ?? [];
            $destPath    = $requestData['dest_path'] ?? '';

            if (empty($files) || empty($destPath)) {
                throw new Exception(trans('panel::file_manager.invalid_params'));
            }

            Log::info('Copy files request:', [
                'files'    => $files,
                'destPath' => $destPath,
            ]);

            $service = $this->getService();
            $service->copyFiles($files, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            Log::error('Copy files failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Get storage configs
     *
     * @return mixed
     */
    public function getStorageConfig(): mixed
    {
        try {
            $config = [
                'driver'     => plugin_setting('file_manager', 'driver', 'local'),
                'key'        => plugin_setting('file_manager', 'key', ''),
                'secret'     => plugin_setting('file_manager', 'secret', ''),
                'endpoint'   => plugin_setting('file_manager', 'endpoint', ''),
                'bucket'     => plugin_setting('file_manager', 'bucket', ''),
                'region'     => plugin_setting('file_manager', 'region', ''),
                'cdn_domain' => plugin_setting('file_manager', 'cdn_domain', ''),
            ];

            Log::info('Get storage configs:', [
                'config'   => array_merge($config, ['secret' => '***']),
                'settings' => [
                    'driver'     => plugin_setting('file_manager', 'driver'),
                    'key'        => plugin_setting('file_manager', 'key'),
                    'endpoint'   => plugin_setting('file_manager', 'endpoint'),
                    'bucket'     => plugin_setting('file_manager', 'bucket'),
                    'region'     => plugin_setting('file_manager', 'region'),
                    'cdn_domain' => plugin_setting('file_manager', 'cdn_domain'),
                ],
            ]);

            return json_success('获取存储配置成功', $config);
        } catch (\Exception $e) {
            Log::error('获取存储配置失败:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail('获取存储配置失败: '.$e->getMessage());
        }
    }

    /**
     * Save storage configs
     *
     * @param  Request  $request
     * @return mixed
     * @throws \Throwable
     */
    public function saveStorageConfig(Request $request): mixed
    {
        try {
            $driver     = $request->input('driver', 'local');
            $key        = $request->input('key', '');
            $secret     = $request->input('secret', '');
            $endpoint   = $request->input('endpoint', '');
            $bucket     = $request->input('bucket', '');
            $region     = $request->input('region', '');
            $cdn_domain = $request->input('cdn_domain', '');

            Log::info('Save storage configs:', [
                'request' => [
                    'driver'     => $driver,
                    'key'        => $key,
                    'secret'     => '***',
                    'endpoint'   => $endpoint,
                    'bucket'     => $bucket,
                    'region'     => $region,
                    'cdn_domain' => $cdn_domain,
                ],
            ]);

            $settingRepo = \InnoShop\Common\Repositories\SettingRepo::getInstance();
            $settingRepo->updatePluginValue('file_manager', 'driver', $driver);
            $settingRepo->updatePluginValue('file_manager', 'key', $key);
            $settingRepo->updatePluginValue('file_manager', 'secret', $secret);
            $settingRepo->updatePluginValue('file_manager', 'endpoint', $endpoint);
            $settingRepo->updatePluginValue('file_manager', 'bucket', $bucket);
            $settingRepo->updatePluginValue('file_manager', 'region', $region);
            $settingRepo->updatePluginValue('file_manager', 'cdn_domain', $cdn_domain);

            \Illuminate\Support\Facades\Artisan::call('config:clear');

            load_settings();

            config([
                'filesystems.file_manager.driver' => $driver,
            ]);

            // 是OSS
            if ($driver == 'oss') {
                config([
                    'filesystems.disks.s3.key'        => $key,
                    'filesystems.disks.s3.secret'     => $secret,
                    'filesystems.disks.s3.region'     => $region,
                    'filesystems.disks.s3.bucket'     => $bucket,
                    'filesystems.disks.s3.endpoint'   => $endpoint,
                    'filesystems.disks.s3.cdn_domain' => $cdn_domain,
                ]);
            }

            $configData = [
                'driver'     => $driver,
                'key'        => $key,
                'secret'     => $secret,
                'endpoint'   => $endpoint,
                'bucket'     => $bucket,
                'region'     => $region,
                'cdn_domain' => $cdn_domain,
            ];

            return json_success('存储配置保存成功', $configData);
        } catch (\Exception $e) {
            Log::error('存储配置保存失败:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail('存储配置保存失败: '.$e->getMessage());
        }
    }
}
