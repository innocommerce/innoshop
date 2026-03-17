<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'image_settings'                      => 'Image Settings',
    'image_settings_desc'                 => 'Configure image resize mode and other image-related settings',
    'image_resize_mode'                   => 'Image Resize Mode',
    'image_resize_mode_cover'             => 'Cover',
    'image_resize_mode_cover_desc'        => 'Maintain aspect ratio, scale to fill the target size, may crop parts of the image',
    'image_resize_mode_contain'           => 'Contain',
    'image_resize_mode_contain_desc'      => 'Maintain aspect ratio, scale to fit within the target size, may have blank spaces',
    'image_resize_mode_pad'               => 'Pad (Fill Background)',
    'image_resize_mode_pad_desc'          => 'Maintain aspect ratio, scale to fit, fill remaining space with configured background color - perfect for product images',
    'image_pad_color'                     => 'Image Pad Background Color',
    'image_pad_color_desc'                => 'Background color used when padding mode is selected (hex color, e.g., #ffffff for white)',
    'image_resize_mode_resize'            => 'Resize',
    'image_resize_mode_resize_desc'       => 'Force resize to target size without maintaining aspect ratio, may cause distortion',
    'image_resize_mode_width_cover'       => 'Width Cover',
    'image_resize_mode_width_cover_desc'  => 'Stretch to target width while maintaining aspect ratio, crop or pad height to fit',
    'image_resize_mode_height_cover'      => 'Height Cover',
    'image_resize_mode_height_cover_desc' => 'Stretch to target height while maintaining aspect ratio, crop or pad width to fit',
    'upload_max_file_size'                => 'Upload File Size Limit',
    'upload_max_file_size_desc'           => 'Maximum file size for regular user uploads (in KB), default 2048 (2MB). Note: actual upload is also limited by PHP settings (upload_max_filesize: :upload_max_filesize, post_max_size: :post_max_size)',
];
