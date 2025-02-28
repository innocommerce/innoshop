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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Panel\Controllers\BaseController;
use InnoShop\Panel\Requests\UploadFileRequest;
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
        // 根据驱动类型创建对应的服务
        try {
            if (config('filesystems.file_manager.driver') === 'oss') {
                $service = new OSSService;
                \Log::info('Created OSS service');

                return fire_hook_filter('file_manager.service', $service);
            }
        } catch (Exception $e) {
            // 如果 OSS 配置验证失败，记录日志
            \Log::warning('Failed to initialize OSS service, falling back to local:', [
                'error' => $e->getMessage(),
            ]);
        }

        // 默认使用本地存储服务
        \Log::info('Created local file service');

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
            'base_folder' => '/',  // 设置默认根目录
            'driver'      => config('filesystems.file_manager.driver'),  // 添加驱动类型
            'title'       => config('filesystems.file_manager.driver') === 'oss' ? 'OSS 文件管理' : '图片空间',
            'config'      => [
                'driver'   => config('filesystems.file_manager.driver'),
                'endpoint' => config('filesystems.disks.s3.endpoint'),
                'bucket'   => config('filesystems.disks.s3.bucket'),
                'baseUrl'  => config('app.url'),
            ],
        ];

        // 添加调试日志
        \Log::info('File manager index:', [
            'data'   => $data,
            'config' => [
                'driver'   => config('filesystems.file_manager.driver'),
                'bucket'   => config('filesystems.disks.s3.bucket'),
                'endpoint' => config('filesystems.disks.s3.endpoint'),
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
                'driver'   => config('filesystems.file_manager.driver'),
                'endpoint' => config('filesystems.disks.s3.endpoint'),
                'bucket'   => config('filesystems.disks.s3.bucket'),
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
            \Log::error('Get files failed:', [
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
     * @return JsonResponse
     */
    public function getDirectories(Request $request): JsonResponse
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
     * @return JsonResponse
     */
    public function createDirectory(FileRequest $request): JsonResponse
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
     * @return JsonResponse
     */
    public function rename(Request $request): JsonResponse
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
     * @return JsonResponse
     */
    public function destroyFiles(Request $request): JsonResponse
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
     * @return JsonResponse
     * @throws Exception
     */
    public function destroyDirectories(Request $request): JsonResponse
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
     * @return JsonResponse
     */
    public function moveDirectories(Request $request): JsonResponse
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
     * @return JsonResponse
     */
    public function moveFiles(Request $request): JsonResponse
    {
        try {
            $requestData = json_decode($request->getContent(), true);
            $files       = $requestData['files']     ?? [];
            $destPath    = $requestData['dest_path'] ?? '';

            if (empty($files) || empty($destPath)) {
                throw new Exception(trans('panel::file_manager.invalid_params'));
            }

            \Log::info('Move files request:', [
                'files'    => $files,
                'destPath' => $destPath,
            ]);

            $service = $this->getService();
            $service->moveFiles($files, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            \Log::error('Move files failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Export a folder as a zip file for download.
     *
     * @param  Request  $request
     */
    public function exportZip(Request $request): void
    {
        try {
            $imagePath = $request->get('path');
            $service   = $this->getService();
            $zipFile   = $service->zipFolder($imagePath);

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.basename($zipFile).'"');
            header('Content-Length: '.filesize($zipFile));
            readfile($zipFile);
            unlink($zipFile);

        } catch (Exception $e) {
            echo $e->getMessage();
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
     * @return JsonResponse
     */
    public function copyFiles(Request $request): JsonResponse
    {
        try {
            $requestData = json_decode($request->getContent(), true);
            $files       = $requestData['files']     ?? [];
            $destPath    = $requestData['dest_path'] ?? '';

            if (empty($files) || empty($destPath)) {
                throw new Exception(trans('panel::file_manager.invalid_params'));
            }

            \Log::info('Copy files request:', [
                'files'    => $files,
                'destPath' => $destPath,
            ]);

            $service = $this->getService();
            $service->copyFiles($files, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            \Log::error('Copy files failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail($e->getMessage());
        }
    }
}
