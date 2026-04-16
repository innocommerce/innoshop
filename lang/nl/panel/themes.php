<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'available_themes_count'        => 'Available themes: :count',
    'themes_stats_title'            => 'Overview',
    'themes_stat_available'         => 'Available',
    'themes_stat_demo'              => 'With demo data',
    'themes_stat_current'           => 'In use',
    'themes_stat_none'              => 'Not set',
    'theme_badge_demo'              => 'Demo data',
    'author'                        => 'Author',
    'confirm_import'                => 'Confirm import',
    'confirm_import_button'         => 'Confirm import',
    'confirm_import_warning'        => 'Importing demonstration data will overwrite your existing data, and this operation is irreversible. Shall we continue?',
    'current_theme'                 => 'Current topic',
    'demo_data_notice'              => 'This topic contains demonstration data',
    'demo_data_warning'             => 'Importing demonstration data will overwrite your existing data. Please ensure that you have backed up important data.',
    'demo_installed'                => 'The demonstration data installation was successful',
    'error_code_mismatch'           => 'The name of the theme folder (:folder) is inconsistent with the code (:code) in config.json and must be in all lowercase',
    'error_code_not_lowercase'      => 'The "code" field in config.json must be in all lowercase. Currently, it is: :code',
    'error_config_invalid'          => 'Theme configuration file format error: :file',
    'error_config_not_found'        => 'The theme configuration file does not exist: :file',
    'error_demo_image_copy_failed'  => 'Failed to copy the image: :file',
    'error_demo_image_dir_failed'   => 'Failed to create the image directory: :dir',
    'error_demo_not_found'          => 'No demonstration data was found',
    'error_demo_sql_empty'          => 'The demonstration data SQL file is empty',
    'error_demo_sql_execute_failed' => 'SQL execution failed (file: :file, error: :error)',
    'error_demo_sql_no_queries'     => 'There is no valid query statement in the SQL file: :file',
    'error_demo_sql_not_found'      => 'The SQL file does not exist: :file',
    'error_demo_sql_not_readable'   => 'The SQL file cannot be read: :file',
    'error_export_failed'           => 'Export failed',
    'error_missing_field'           => 'The theme configuration is missing necessary fields: :field',
    'error_theme_not_found'         => 'The theme does not exist.',
    'export_sql'                    => 'Export SQL',
    'export_started'                => 'The export has begun and the file will be downloaded automatically',
    'import_demo_data'              => 'Import demonstration data',
    'import_export_data'            => 'Demo data',
    'import_failed'                 => 'Import failed',
    'no_custom_theme'               => 'No custom templates system directory, use innopacks/front/resources under the default template',
    'no_demo_data'                  => 'There is no demonstration data available for the moment',
    'no_demo_data_description'      => 'The system will detect sql files in the theme directory themes/:code/demo/sql/. If the directory exists and contains *.SQL files, it is regarded as having demonstration data.',
    'theme_description'             => 'Theme description',
    'version'                       => 'Version',
];
