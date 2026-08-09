<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'             => 'MCP 서비스',
    'subtitle'          => ':name 은(는) MCP 프로토콜을 통해 스토어 데이터 조회·운영 기능을 외부 AI Agent에 공개합니다.',
    'active'            => '실행 중',
    'tools_available'   => '개 도구 사용 가능',
    'nav_overview'      => '개요',
    'nav_architecture'  => '아키텍처',
    'nav_connect'       => '연결 가이드',
    'nav_auth'          => '인증',
    'nav_tools'         => '모든 도구',
    'overview_title'    => '개요',
    'overview_desc'     => '이 엔드포인트는 <strong>Model Context Protocol</strong>(Streamable HTTP, JSON-RPC 2.0)을 구현합니다. Claude Code, Cursor, Cline 등 AI 도구는 관리자 인증을 거쳐 스토어 데이터를 안전하게 조회할 수 있습니다. 모든 도구는 백오피스 권한 체계를 상속하며 기본적으로 읽기 전용입니다.',
    'write_mode_on'     => '쓰기 작업: 활성화(외부 AI가 데이터 생성·수정 가능)',
    'write_mode_off'    => '쓰기 작업: 비활성화(현재 읽기 전용)',
    'endpoint_label'    => '엔드포인트:',
    'arch_desc'         => 'MCP는 innopacks/mcp가 제공하는 프로토콜 어댑터 계층이며, 모든 도구(Tool)는 innopacks/ai에서 정의·관리됩니다. 둘은 ToolInterface 계약으로 느슨하게 결합됩니다.',
    'arch_ai'           => 'innopacks/ai',
    'arch_tools'        => '개 AI Tool<br>(데이터 조회/운영)',
    'arch_mcp'          => 'innopacks/mcp',
    'arch_protocol'     => '프로토콜 어댑터 계층<br>(JSON-RPC + 인증)',
    'arch_bridge'       => 'ToolInterface',
    'arch_note_badge'   => '팁',
    'arch_note'         => '백오피스 안에서 AI 대화를 사용하려면 <strong>ShopBot 플러그인</strong>(AI 어시스턴트)을 설치하세요. ShopBot도 innopacks/ai의 Tool 체계를 기반으로 하지만 MCP 프로토콜 없이 Web UI로 직접 대화합니다.',
    'connect_title'     => '연결 가이드',
    'your_token'        => '당신의 관리자 토큰',
    'no_token'          => '현재 페이지에서 API 토큰이 감지되지 않았습니다. 백오피스에 로그인한 후 시스템 설정 → AI → MCP로 이 페이지에 들어오면 자동 입력된 토큰을 받을 수 있습니다.',
    'auth_title'        => '인증',
    'auth_desc'         => '관리자 계정으로 Bearer Token을 발급받습니다:',
    'auth_token_hint'   => '토큰 발급 후 백오피스의',
    'system_settings'   => '시스템 설정 → 도구 → AI',
    'auth_mcp_card'     => 'MCP 서비스 카드에서 확인·관리할 수 있습니다.',
    'tools_title'       => '모든 도구',
    'tools_write_hint'  => '「쓰기」표시 도구는 현재 호출할 수 없습니다. 백오피스의 시스템 설정 → 도구 → AI에서 「쓰기 작업 허용」을 켜야 사용할 수 있습니다.',
    'tool_write_badge'  => '쓰기',
    'tools_plugin_hint' => '플러그인은 ai.tools 훅으로 커스텀 도구를 등록할 수 있으며, 이 목록과 MCP 프로토콜에 자동 반영됩니다:',
    'cat_product'       => '상품',
    'cat_order'         => '주문 / 반품',
    'cat_customer'      => '고객',
    'cat_catalog'       => '카테고리 / 브랜드',
    'cat_content'       => '콘텐츠 (CMS)',
    'cat_shipping'      => '물류',
    'cat_analytics'     => '통계 / 리포트',
    'cat_config'        => '설정 / 시스템',
    'cat_other'         => '기타',
];
