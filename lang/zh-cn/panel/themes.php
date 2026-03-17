<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'no_custom_theme'               => '系统目录下无自定义模板, 使用 innopacks/front/resources 下默认模板',
    'error_config_not_found'        => '主题配置文件不存在: :file',
    'error_config_invalid'          => '主题配置文件格式错误: :file',
    'error_missing_field'           => '主题配置缺少必要字段: :field',
    'error_code_mismatch'           => '主题文件夹名（:folder）与 config.json 中 code（:code）不一致，且必须全小写',
    'error_code_not_lowercase'      => 'config.json 中 code 字段必须全小写，当前为：:code',
    'current_theme'                 => '当前主题',

    // Theme detail modal translations
    'theme_description'             => '主题描述',
    'version'                       => '版本',
    'author'                        => '作者',
    'demo_data_notice'              => '此主题包含演示数据',
    'demo_data_warning'             => '导入演示数据将覆盖您现有的数据，请确保您已经备份了重要数据。',
    'import_demo_data'              => '导入演示数据',
    'import_export_data'            => 'Demo数据',
    'import_failed'                 => '导入失败',
    'no_demo_data'                  => '暂无演示数据',
    'no_demo_data_description'      => '此主题没有演示数据，如需添加演示数据，请创建 <code>demo/Seeder.php</code> 文件。',
    'confirm_import'                => '确认导入',
    'confirm_import_button'         => '确认导入',
    'confirm_import_warning'        => '导入演示数据将覆盖您现有的数据，此操作不可撤销。是否继续？',
    'demo_installed'                => '演示数据安装成功',

    // Error messages
    'error_theme_not_found'         => '主题不存在',
    'error_demo_not_found'          => '未找到演示数据',
    'error_demo_seeder_invalid'     => '演示数据 Seeder 文件无效，必须返回闭包',
    'error_demo_seeder_failed'      => '演示数据 Seeder 执行失败: :error',
    'error_demo_image_dir_failed'   => '创建图片目录失败: :dir',
    'error_demo_image_copy_failed'  => '复制图片失败: :file',
    'error_theme_validation'        => '以下主题配置有误，已被跳过',

    // Theme development guide
    'theme_guide_title'             => '主题开发指南',
    'theme_guide_desc'              => '开发自定义主题时，请遵循以下规范：',
    'theme_guide_preview'           => '建议尺寸 900×600 像素，3:2 比例，放置于 public/images/ 目录',
    'theme_guide_preview_title'     => '预览图 (preview.png/jpg)',
    'theme_guide_icon'              => '建议尺寸 60×60 像素，正方形',
    'theme_guide_icon_title'        => '主题图标 (icon.png)',
    'theme_guide_config'            => 'code 必须与文件夹名一致，且全部小写',
    'theme_guide_config_title'      => 'config.json 配置',
];
