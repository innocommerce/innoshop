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
use InnoShop\RestAPI\Services\FileManagerService;

class FileManagerController extends BaseController
{
    protected mixed $fileManagerService;

    public function __construct()
    {
        parent::__construct();
        $this->fileManagerService = $this->getService();
    }

    /**
     * @return FileManagerService
     */
    private function getService(): mixed
    {
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
            'isIframe' => request()->header('X-Iframe') === '1',
            'multiple' => request()->query('multiple')  === '1',
            'type'     => request()->query('type', 'all'),
        ];

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
        $baseFolder = $request->get('base_folder', '');
        $keyword    = $request->get('keyword', '');
        $sort       = $request->get('sort', 'created');
        $order      = $request->get('order', 'desc');
        $page       = (int) $request->get('page');
        $perPage    = (int) $request->get('per_page');

        $data = $this->fileManagerService->getFiles($baseFolder, $keyword, $sort, $order, $page, $perPage);

        return fire_hook_filter('admin.file_manager.files.data', $data);
    }

    /**
     * Retrieve a list of directories.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getDirectories(Request $request): JsonResponse
    {
        $baseFolder = $request->get('base_folder', '/');
        $data       = $this->fileManagerService->getDirectories($baseFolder);

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
            $this->fileManagerService->createDirectory($folderName);

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

            $this->fileManagerService->updateName($originName, $newPath);

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

            $this->fileManagerService->deleteFiles($basePath, $files);

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
            $this->fileManagerService->deleteDirectoryOrFile($folderName);

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
            $this->fileManagerService->moveDirectory($sourcePath, $destPath);

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

            $this->fileManagerService->moveFiles($files, $destPath);

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
            $zipFile   = $this->fileManagerService->zipFolder($imagePath);

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
        $file     = $request->file('file');
        $savePath = $request->get('path');

        $originName = $file->getClientOriginalName();
        $fileUrl    = $this->fileManagerService->uploadFile($file, $savePath, $originName);

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

            $this->fileManagerService->copyFiles($files, $destPath);

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
