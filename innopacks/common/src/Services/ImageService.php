<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    private string $image;

    private string $imagePath;

    const PLACEHOLDER_IMAGE = 'images/placeholder.png';

    /**
     * @throws Exception
     */
    public function __construct($image)
    {
        $this->image     = $image ?: self::PLACEHOLDER_IMAGE;
        $this->imagePath = public_path($this->image);
        if (! is_file($this->imagePath)) {
            $this->image     = self::PLACEHOLDER_IMAGE;
            $this->imagePath = public_path(self::PLACEHOLDER_IMAGE);
        }
    }

    /**
     * 生成并获取缩略图
     */
    public function resize(int $width = 100, int $height = 100): string
    {
        try {
            $extension = pathinfo($this->imagePath, PATHINFO_EXTENSION);
            $newImage  = 'cache/'.mb_substr($this->image, 0, mb_strrpos($this->image, '.')).'-'.$width.'x'.$height.'.'.$extension;

            $newImagePath = public_path($newImage);
            if (! is_file($newImagePath) || (filemtime($this->imagePath) > filemtime($newImagePath))) {
                create_directories(dirname($newImage));

                $manager = new ImageManager(new Driver());
                $image   = $manager->read($this->imagePath);
                $image->scale($width, $height);
                $image->save($newImagePath);
            }

            return asset($newImage);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return $this->originUrl();
        }
    }

    /**
     * 获取原图地址
     */
    public function originUrl(): string
    {
        return asset($this->image);
    }
}
