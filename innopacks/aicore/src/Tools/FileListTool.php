<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\MediaFile;

class FileListTool extends BaseTool
{
    public function name(): string
    {
        return 'file_list';
    }

    public function description(): string
    {
        return 'List media files with pagination. Supports filtering by file type (image, video, document) and keyword search on file name.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'type'     => ['type' => 'string', 'description' => 'Filter by MIME type prefix: image, video, application (documents)'],
                'keyword'  => ['type' => 'string', 'description' => 'Search keyword matched against file name'],
                'page'     => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page' => ['type' => 'integer', 'description' => 'Items per page, default 20, max 100'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'files_index';
    }

    public function execute(array $arguments): mixed
    {
        $query = MediaFile::query();

        if ($type = $arguments['type'] ?? '') {
            $query->where('mime', 'like', "{$type}/%");
        }
        if ($keyword = $arguments['keyword'] ?? '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $page    = max(1, (int) ($arguments['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($arguments['per_page'] ?? 20)));

        $paginator = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($file) => [
                'id'         => $file->id,
                'name'       => $file->name,
                'path'       => $file->path,
                'mime_type'  => $file->mime,
                'size'       => $file->size,
                'url'        => $file->url(),
                'created_at' => (string) $file->created_at,
            ])->values()->all(),
        ];
    }
}
