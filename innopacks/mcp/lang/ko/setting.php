<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'MCP 서비스',
    'mcp_service_desc'  => '스토어 데이터(상품, 주문, 재고, 매출 통계)를 MCP 프로토콜로 Claude, Cursor 등 클라이언트에 공개합니다. 기본은 읽기 전용이며 관리자 토큰이 필요합니다.',
    'enable_mcp'        => 'MCP 엔드포인트 활성화',
    'enable_mcp_write'  => '쓰기 작업 허용',
    'write_hint'        => '끄면 외부 AI가 조회만 가능합니다. 켜면 상품 생성/수정, 주문 상태 변경, 발송 등 쓰기 도구가 노출됩니다.',
    'endpoint_url'      => '엔드포인트 URL',
    'auth_header'       => '인증 방식',
    'token_hint'        => '관리자 계정으로 :url에 POST하여 Token을 받아 Bearer로 전송합니다.',
    'usage_title'       => '사용 방법',
    'usage_cursor'      => 'Cursor: 다음 설정을 ~/.cursor/mcp.json에 추가합니다(또는 설정 → MCP → 서버 추가):',
    'usage_claude_code' => 'Claude Code: 다음 명령을 실행합니다:',
];
