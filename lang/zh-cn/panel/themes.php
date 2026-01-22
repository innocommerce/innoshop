<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'no_custom_theme'          => '系统目录下无自定义模板, 使用 innopacks/front/resources 下默认模板',
    'error_config_not_found'   => '主题配置文件不存在: :file',
    'error_config_invalid'     => '主题配置文件格式错误: :file',
    'error_missing_field'      => '主题配置缺少必要字段: :field',
    'error_code_mismatch'      => '主题文件夹名（:folder）与 config.json 中 code（:code）不一致，且必须全小写',
    'error_code_not_lowercase' => 'config.json 中 code 字段必须全小写，当前为：:code',
    'current_theme'            => '当前主题',

    // Theme detail modal translations
    'theme_description' => '主题描述',
    'version'           => '版本',
    'author'            => '作者',
    'demo_data_notice'  => '此主题包含演示数据',
    'demo_data_warning' => '导入演示数据将覆盖您现有的数据，请确保您已经备份了重要数据。',
    'import_demo_data'  => '导入演示数据',

    // Import confirmation modal translations
    'confirm_import'                => '确认导入',
    'confirm_import_warning'        => '导入演示数据将覆盖您现有的数据，此操作不可撤销。是否继续？',
    'confirm_import_button'         => '确认导入',
    'import_failed'                 => '导入失败',
    'import_export_data'            => 'Demo数据',
    'no_demo_data'                  => '暂无演示数据',
    'no_demo_data_description'      => '系统会在主题目录 themes/:code/demo/sql/ 中检测 SQL 文件。如果该目录存在且包含 *.sql 文件，则视为有演示数据。',
    'export_sql'                    => '导出SQL',
    'export_started'                => '导出已开始，文件将自动下载',
    'error_theme_not_found'         => '主题不存在',
    'error_demo_not_found'          => '未找到演示数据',
    'error_demo_sql_empty'          => '演示数据 SQL 文件为空',
    'error_demo_sql_not_found'      => 'SQL 文件不存在: :file',
    'error_demo_sql_not_readable'   => 'SQL 文件无法读取: :file',
    'error_demo_sql_execute_failed' => '执行 SQL 失败 (文件: :file, 错误: :error)',
    'error_demo_sql_no_queries'     => 'SQL 文件中没有有效的查询语句: :file',
    'error_demo_image_dir_failed'   => '创建图片目录失败: :dir',
    'error_demo_image_copy_failed'  => '复制图片失败: :file',
    'error_export_failed'           => '导出失败',
    'demo_installed'                => '演示数据安装成功',
];
