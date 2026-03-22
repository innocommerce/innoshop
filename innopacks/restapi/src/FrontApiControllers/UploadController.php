<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use InnoShop\Common\Requests\UploadFileRequest;
use InnoShop\Common\Requests\UploadImageRequest;
use InnoShop\RestAPI\Services\UploadService;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Front - Upload')]
#[Authenticated]
class UploadController
{
    /**
     * Upload images.
     *
     * @param  UploadImageRequest  $request
     * @return mixed
     */
    #[Endpoint('Upload images')]
    #[BodyParam('images', type: 'file', required: true, description: 'Image files to upload')]
    public function images(UploadImageRequest $request): mixed
    {
        $data = UploadService::getInstance()->images($request);

        return create_json_success($data);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return mixed
     */
    #[Endpoint('Upload files')]
    #[BodyParam('files', type: 'file', required: true, description: 'Files to upload')]
    public function files(UploadFileRequest $request): mixed
    {
        $data = UploadService::getInstance()->files($request);

        return create_json_success($data);
    }
}
