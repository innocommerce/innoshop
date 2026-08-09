<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Mcp\Tests\Feature;

use InnoShop\Common\Models\Admin;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesAdmin;

class McpEndpointTest extends TestCase
{
    use CreatesAdmin;

    private array $mcpHeaders = [
        'Accept'       => 'application/json, text/event-stream',
        'Content-Type' => 'application/json',
    ];

    private function initializePayload(): array
    {
        return [
            'jsonrpc' => '2.0',
            'id'      => 1,
            'method'  => 'initialize',
            'params'  => [
                'protocolVersion' => '2025-06-18',
                'capabilities'    => new \stdClass,
                'clientInfo'      => ['name' => 'test-client', 'version' => '1.0.0'],
            ],
        ];
    }

    private function enableMcp(): void
    {
        config(['inno.system.mcp_enabled' => true]);
    }

    private function actingAsAdminApi(): Admin
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        return $admin;
    }

    #[Test]
    public function test_get_returns_welcome_page(): void
    {
        $this->enableMcp();

        $response = $this->get('/mcp');

        $response->assertOk();
        $response->assertSee('class="sidebar"', false);
        $response->assertSee('logo-icon-light.svg', false);
        $response->assertSee('product_list', false);
        $response->assertSee(__('mcp::welcome.title'), false);
    }

    #[Test]
    public function test_welcome_page_404_when_mcp_disabled(): void
    {
        config(['inno.system.mcp_enabled' => false]);

        $this->get('/mcp')->assertNotFound();
    }

    #[Test]
    public function test_welcome_follows_lang_query_and_persists_in_session(): void
    {
        $this->enableMcp();

        $zhOverview = __('mcp::welcome.nav_overview', [], 'zh-cn');
        $this->get('/mcp?lang=zh-cn')->assertOk()->assertSee($zhOverview, false);
        $this->get('/mcp')->assertOk()->assertSee($zhOverview, false);
    }

    #[Test]
    public function test_welcome_ignores_unknown_lang_param(): void
    {
        $this->enableMcp();

        $this->get('/mcp?lang=xx')->assertOk();
    }

    #[Test]
    public function test_welcome_supports_locale_not_active_in_backend(): void
    {
        $this->enableMcp();

        // ja is available as a language pack but not enabled in the backend —
        // the MCP welcome page still renders it, unlike the frontend.
        $this->get('/mcp?lang=ja')->assertOk()
            ->assertSee(__('mcp::welcome.nav_overview', [], 'ja'), false);
    }

    #[Test]
    public function test_locale_switch_route_changes_welcome_locale(): void
    {
        $this->enableMcp();

        $this->get('/mcp/locale/zh-cn')->assertRedirect('/mcp');

        $this->get('/mcp')->assertOk()
            ->assertSee(__('mcp::welcome.nav_overview', [], 'zh-cn'), false);
    }

    #[Test]
    public function test_endpoint_returns_404_when_mcp_disabled(): void
    {
        config(['inno.system.mcp_enabled' => false]);

        $this->postJson('/mcp', $this->initializePayload(), $this->mcpHeaders)
            ->assertNotFound();
    }

    #[Test]
    public function test_endpoint_requires_authentication(): void
    {
        $this->enableMcp();

        $this->postJson('/mcp', $this->initializePayload(), $this->mcpHeaders)
            ->assertUnauthorized();
    }

    #[Test]
    public function test_endpoint_rejects_foreign_origin(): void
    {
        $this->enableMcp();
        $this->actingAsAdminApi();

        $this->postJson('/mcp', $this->initializePayload(), $this->mcpHeaders + [
            'Origin' => 'https://evil.example.com',
        ])->assertForbidden();
    }

    #[Test]
    public function test_endpoint_allows_app_origin(): void
    {
        $this->enableMcp();
        $this->actingAsAdminApi();

        $response = $this->postJson('/mcp', $this->initializePayload(), $this->mcpHeaders + [
            'Origin' => config('app.url'),
        ]);

        $response->assertOk();
        $this->assertStringContainsString('serverInfo', $response->getContent());
    }

    #[Test]
    public function test_initialize_handshake_without_origin(): void
    {
        $this->enableMcp();
        $this->actingAsAdminApi();

        $response = $this->postJson('/mcp', $this->initializePayload(), $this->mcpHeaders);

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('serverInfo', $content);
        $this->assertStringContainsString('InnoShop', $content);
    }

    #[Test]
    public function test_initialize_negotiates_newer_protocol_version(): void
    {
        $this->enableMcp();
        $this->actingAsAdminApi();

        $payload                              = $this->initializePayload();
        $payload['params']['protocolVersion'] = '2025-11-25';

        $response = $this->postJson('/mcp', $payload, $this->mcpHeaders);

        $response->assertOk();
        $this->assertStringContainsString('2025-11-25', $response->getContent());
        $this->assertStringContainsString('serverInfo', $response->getContent());
    }

    private function listToolsContent(): string
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id'      => 2,
            'method'  => 'tools/list',
            'params'  => new \stdClass,
        ], $this->mcpHeaders);

        $response->assertOk();

        return $response->getContent();
    }

    private function callToolContent(string $name, array $arguments = []): string
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id'      => 3,
            'method'  => 'tools/call',
            'params'  => ['name' => $name, 'arguments' => $arguments],
        ], $this->mcpHeaders);

        $response->assertOk();

        return $response->getContent();
    }

    #[Test]
    public function test_tools_list_returns_registered_tools(): void
    {
        $this->enableMcp();
        $this->actingAsAdminApi();

        $content = $this->listToolsContent();
        $this->assertStringContainsString('tools', $content);
        $this->assertStringContainsString('product_list', $content);
    }

    #[Test]
    public function test_tools_list_hides_write_tools_by_default(): void
    {
        $this->enableMcp();
        $this->actingAsAdminApi();

        $content = $this->listToolsContent();
        $this->assertStringContainsString('product_list', $content);
        $this->assertStringNotContainsString('product_update', $content);
        $this->assertStringNotContainsString('order_update_status', $content);
        $this->assertStringNotContainsString('shipment_create', $content);
    }

    #[Test]
    public function test_write_tool_call_rejected_by_default(): void
    {
        $this->enableMcp();
        $this->actingAsAdminApi();

        $this->assertStringContainsString('not found', $this->callToolContent('order_update_status'));
    }

    #[Test]
    public function test_tools_list_includes_write_tools_when_enabled(): void
    {
        $this->enableMcp();
        config(['inno.system.mcp_write_enabled' => true]);
        $this->actingAsAdminApi();

        $content = $this->listToolsContent();
        $this->assertStringContainsString('product_update', $content);
        $this->assertStringContainsString('shipment_create', $content);
    }

    #[Test]
    public function test_endpoint_accepts_real_plain_text_token(): void
    {
        $this->enableMcp();
        $token = $this->createAdmin()->createToken('admin-token')->plainTextToken;

        $response = $this->postJson('/mcp', $this->initializePayload(), $this->mcpHeaders + [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk();
        $this->assertStringContainsString('serverInfo', $response->getContent());
    }

    #[Test]
    public function test_endpoint_accepts_token_without_id_prefix(): void
    {
        $this->enableMcp();
        $token     = $this->createAdmin()->createToken('admin-token')->plainTextToken;
        $tokenPart = explode('|', $token)[1];

        $this->postJson('/mcp', $this->initializePayload(), $this->mcpHeaders + [
            'Authorization' => 'Bearer '.$tokenPart,
        ])->assertOk();
    }
}
