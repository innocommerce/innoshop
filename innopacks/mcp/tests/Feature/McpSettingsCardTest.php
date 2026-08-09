<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Mcp\Tests\Feature;

use InnoShop\Common\Repositories\SettingRepo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class McpSettingsCardTest extends TestCase
{
    #[Test]
    public function test_tools_settings_page_renders_mcp_card(): void
    {
        $html = view('mcp::settings._tools')->render();

        $this->assertStringContainsString('name="mcp_enabled"', $html);
        $this->assertStringContainsString(url('/mcp'), $html);
        $this->assertStringContainsString('mcpServers', $html);
        $this->assertStringContainsString('claude mcp add', $html);
    }

    #[Test]
    public function test_mcp_enabled_setting_persists(): void
    {
        SettingRepo::getInstance()->updateSystemValue('mcp_enabled', 1);
        $this->assertDatabaseHas('settings', ['space' => 'system', 'name' => 'mcp_enabled', 'value' => '1']);

        SettingRepo::getInstance()->updateSystemValue('mcp_enabled', 0);
        $this->assertDatabaseHas('settings', ['space' => 'system', 'name' => 'mcp_enabled', 'value' => '0']);
    }
}
