<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'image_settings'                      => '图片设置',
    'image_settings_desc'                 => '配置图片缩放模式和其他图片相关设置',
    'image_resize_mode'                   => '图片缩放模式',
    'image_resize_mode_cover'             => '覆盖模式 (Cover)',
    'image_resize_mode_cover_desc'        => '保持宽高比，缩放以完全填充目标尺寸，可能会裁剪图片的部分内容',
    'image_resize_mode_contain'           => '包含模式 (Contain)',
    'image_resize_mode_contain_desc'      => '保持宽高比，缩放以完全包含在目标尺寸内，可能会有留白',
    'image_resize_mode_pad'               => '填充模式 (Pad)',
    'image_resize_mode_pad_desc'          => '保持宽高比，缩放以适合尺寸，用配置的背景色填充剩余空间 - 非常适合产品图片',
    'image_pad_color'                     => '图片填充背景颜色',
    'image_pad_color_desc'                => '使用填充模式时的背景颜色（十六进制颜色，例如 #ffffff 表示白色）',
    'image_resize_mode_resize'            => '直接缩放 (Resize)',
    'image_resize_mode_resize_desc'       => '不保持宽高比，强制缩放到目标尺寸，可能会变形',
    'image_resize_mode_width_cover'       => '宽度覆盖 (Width Cover)',
    'image_resize_mode_width_cover_desc'  => '拉伸到目标宽度，保持宽高比，高度裁剪或填充以适配',
    'image_resize_mode_height_cover'      => '高度覆盖 (Height Cover)',
    'image_resize_mode_height_cover_desc' => '拉伸到目标高度，保持宽高比，宽度裁剪或填充以适配',
    'upload_max_file_size'                => '上传文件大小限制',
    'upload_max_file_size_desc'           => '普通用户上传文件的最大大小（单位：KB），默认 2048（2MB）。注意：实际上传还受限于 PHP 配置（upload_max_filesize: :upload_max_filesize, post_max_size: :post_max_size）',
];
