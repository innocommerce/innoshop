<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'no_custom_theme' => 'There is no custom template in the system directory, use the default template in innopacks/front/resources',
    'current_theme'   => 'Current Theme',

    // Theme detail modal translations
    'theme_description' => 'Theme Description',
    'version'           => 'Version',
    'author'            => 'Author',
    'demo_data_notice'  => 'This theme contains demo data',
    'demo_data_warning' => 'Importing demo data will overwrite your existing data. Please make sure you have backed up important data.',
    'import_demo_data'  => 'Import Demo Data',

    // Import confirmation modal translations
    'confirm_import'                => 'Confirm Import',
    'confirm_import_warning'        => 'Importing demo data will overwrite your existing data. This operation cannot be undone. Continue?',
    'confirm_import_button'         => 'Confirm Import',
    'import_failed'                 => 'Import Failed',
    'import_export_data'            => 'Demo Data',
    'no_demo_data'                  => 'No Demo Data Available',
    'no_demo_data_description'      => 'The system checks for SQL files in the theme directory themes/:code/demo/sql/. If this directory exists and contains *.sql files, it is considered to have demo data.',
    'export_sql'                    => 'Export SQL',
    'export_started'                => 'Export started, file will be downloaded automatically',
    'error_theme_not_found'         => 'Theme not found',
    'error_demo_not_found'          => 'Demo data not found',
    'error_demo_sql_empty'          => 'Demo data SQL file is empty',
    'error_demo_sql_not_found'      => 'SQL file not found: :file',
    'error_demo_sql_not_readable'   => 'SQL file is not readable: :file',
    'error_demo_sql_execute_failed' => 'Failed to execute SQL (File: :file, Error: :error)',
    'error_demo_sql_no_queries'     => 'No valid queries found in SQL file: :file',
    'error_demo_image_dir_failed'   => 'Failed to create image directory: :dir',
    'error_demo_image_copy_failed'  => 'Failed to copy image: :file',
    'error_export_failed'           => 'Export failed',
    'demo_installed'                => 'Demo data installed successfully',
];
