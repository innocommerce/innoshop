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
    'author'                        => '저자',
    'confirm_import'                => '가져오기 확인',
    'confirm_import_button'         => '가져오기 확인',
    'confirm_import_warning'        => '프리젠테이션 데이터를 가져오면 현재 데이터를 덮어씁니다. 되돌릴 수 없습니다.계속하시겠습니까?',
    'current_theme'                 => '현재 테마',
    'demo_data_notice'              => '이 테마는 프리젠테이션 데이터를 포함합니다',
    'demo_data_warning'             => '프리젠테이션 데이터를 가져오면 현재 데이터를 덮어씁니다. 중요한 데이터를 백업했는지 확인하십시오.',
    'demo_installed'                => '데모 데이터를 성공적으로 설치했습니다',
    'error_code_mismatch'           => '테마 폴더 이름 (:folder)은 config.json의 code (:code)와 일치하지 않으며 모두 소문자로 써야 한다',
    'error_code_not_lowercase'      => 'config.json에서 code 필드는 소문자로 되어 있어야 하며, 현재::code이다',
    'error_config_invalid'          => '테마 설정 파일 형식 오류::file',
    'error_config_not_found'        => '테마 설정 파일이 존재하지 않습니다::file',
    'error_demo_image_copy_failed'  => '그림 복사할 수 없음::file',
    'error_demo_image_dir_failed'   => '그림 디렉터리를 만들 수 없습니다::dir',
    'error_demo_not_found'          => '프리젠테이션 데이터를 찾을 수 없습니다',
    'error_demo_sql_empty'          => '프리젠테이션 sql 파일이 비어 있다',
    'error_demo_sql_execute_failed' => 'sql 실행 실패 (파일::file, 오류::error)',
    'error_demo_sql_no_queries'     => 'sql 파일에 유효한 쿼리문이 없다::file',
    'error_demo_sql_not_found'      => 'sql 파일은 존재하지 않는다::file',
    'error_demo_sql_not_readable'   => 'sql 파일을 읽을 수 없음::file',
    'error_export_failed'           => '내보내기 실패',
    'error_missing_field'           => '테마 설정에는 필수 필드가 없습니다::field',
    'error_theme_not_found'         => '테마가 존재하지 않음',
    'export_sql'                    => 'sql 내보내기',
    'export_started'                => '내보내기가 시작되었습니다. 파일이 자동으로 다운로드됩니다',
    'import_demo_data'              => '프리젠테이션 데이터 가져오기',
    'import_export_data'            => '데모 데이터',
    'import_failed'                 => '가져오기 실패',
    'no_custom_theme'               => '시스템 디렉토리에 맞춤 템플릿이 없습니다. innopacks/front/resources 아래의 기본 템플릿을 사용합니다.',
    'no_demo_data'                  => '현재 프레젠테이션 데이터가 없습니다',
    'no_demo_data_description'      => '시스템은 테마 디렉터리 themes/:code/demo/sql/에서 sql 파일을 탐지한다.*.sql 파일을 포함하는 디렉터리가 존재하면 프리젠테이션 데이터가 있는 것으로 간주된다.',
    'theme_description'             => '테마 설명',
    'version'                       => '버전',
];
