<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Enterprise\PanelControllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Enterprise\Services\FileManagerService;
use InnoShop\Panel\Controllers\BaseController;
use InnoShop\Panel\Requests\UploadFileRequest;

class FileManagerController extends BaseController
{
    protected FileManagerService $fileManagerService;

    public function __construct()
    {
        parent::__construct();
        $this->fileManagerService = new FileManagerService;
    }

    /**
     * Display the file manager index view.
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $data = $this->fileManagerService->getDirectories();

        return inno_view('enterprise::file_manager.index', $data);
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
     * @return mixed
     */
    public function getDirectories(Request $request): mixed
    {
        $baseFolder = $request->get('base_folder');

        $data = $this->fileManagerService->getDirectories($baseFolder);

        return fire_hook_filter('admin.file_manager.directories.data', $data);
    }

    /**
     * Create a new directory.
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function createDirectory(Request $request): JsonResponse
    {
        try {
            $folderName = $request->get('name');
            $this->fileManagerService->createDirectory($folderName);

            return json_success(trans('common.created_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Rename a file or folder.
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function rename(Request $request): JsonResponse
    {
        try {
            $originPath = $request->get('origin_name');
            $newPath    = $request->get('new_name');
            $this->fileManagerService->updateName($originPath, $newPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Delete specified files in a directory.
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function destroyFiles(Request $request): JsonResponse
    {
        try {
            $requestData = json_decode($request->getContent(), true);
            $basePath    = $requestData['path']  ?? '';
            $files       = $requestData['files'] ?? [];
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
            $images   = $request->get('images');
            $destPath = $request->get('dest_path');
            $this->fileManagerService->moveFiles($images, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
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
}
