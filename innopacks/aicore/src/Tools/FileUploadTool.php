<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use Illuminate\Http\UploadedFile;
use InnoShop\Common\Services\StorageService;
use InnoShop\Restapi\Services\UploadService;
use InvalidArgumentException;

class FileUploadTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'file_upload';
    }

    public function description(): string
    {
        return '⚠️ WRITE: Upload a file from a URL or base64 data to the media library. Returns the stored file path and URL.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'source'   => ['type' => 'string', 'description' => 'Public URL of the file to download and store'],
                'base64'   => ['type' => 'string', 'description' => 'Base64-encoded file content (alternative to source URL)'],
                'filename' => ['type' => 'string', 'description' => 'Target file name, auto-detected from URL if omitted'],
                'path'     => ['type' => 'string', 'description' => 'Target directory under storage, default /media'],
            ],
            'required' => [],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'files_create';
    }

    public function execute(array $arguments): mixed
    {
        $source   = (string) ($arguments['source'] ?? '');
        $base64   = (string) ($arguments['base64'] ?? '');
        $filename = (string) ($arguments['filename'] ?? '');
        $dir      = (string) ($arguments['path'] ?? 'media');

        if ($source === '' && $base64 === '') {
            throw new InvalidArgumentException('Either source URL or base64 content is required.');
        }

        if ($source !== '') {
            $content  = @file_get_contents($source);
            $filename = $filename ?: basename(parse_url($source, PHP_URL_PATH) ?: 'file');
        } else {
            $content  = base64_decode($base64, true);
            $filename = $filename ?: 'upload_'.date('YmdHis');
        }

        if (empty($content)) {
            throw new InvalidArgumentException('Failed to read file content.');
        }

        // Write raw content to a temp file, then hand off to the standard
        // UploadService pipeline (security validation → media disk → DB record).
        $tempPath = tempnam(sys_get_temp_dir(), 'mcp_');
        file_put_contents($tempPath, $content);

        $mimeType = mime_content_type($tempPath) ?: 'application/octet-stream';

        $uploadedFile = new UploadedFile($tempPath, $filename, $mimeType, null, true);
        $result       = UploadService::getInstance()->uploadFile($uploadedFile, $dir);

        $storageKey = $result['value'] ?? '';

        return [
            'id'          => null,
            'name'        => $filename,
            'storage_key' => $storageKey,
            'url'         => StorageService::getInstance()->url($storageKey),
            'mime_type'   => $mimeType,
            'size'        => strlen($content),
        ];
    }
}
