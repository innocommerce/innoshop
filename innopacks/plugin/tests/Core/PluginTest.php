<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Core;

use InnoShop\Plugin\Core\Plugin;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PluginTest extends TestCase
{
    #[Test]
    public function test_plugin_class_exists(): void
    {
        $this->assertTrue(class_exists(Plugin::class));
    }

    #[Test]
    public function test_has_types_constant(): void
    {
        $this->assertTrue(defined(Plugin::class.'::TYPES'));
        $this->assertIsArray(Plugin::TYPES);
        $this->assertContains('feature', Plugin::TYPES);
        $this->assertContains('payment', Plugin::TYPES);
        $this->assertContains('shipping', Plugin::TYPES);
        $this->assertContains('price', Plugin::TYPES);
        $this->assertContains('orderfee', Plugin::TYPES);
        $this->assertContains('marketing', Plugin::TYPES);
        $this->assertContains('service', Plugin::TYPES);
    }

    #[Test]
    public function test_has_type_mapping_constant(): void
    {
        $this->assertTrue(defined(Plugin::class.'::TYPE_MAPPING'));
        $this->assertIsArray(Plugin::TYPE_MAPPING);
        $this->assertArrayHasKey('billing', Plugin::TYPE_MAPPING);
        $this->assertEquals('payment', Plugin::TYPE_MAPPING['billing']);
        $this->assertEquals('orderfee', Plugin::TYPE_MAPPING['fee']);
        $this->assertEquals('orderfee', Plugin::TYPE_MAPPING['discount']);
        $this->assertEquals('service', Plugin::TYPE_MAPPING['translator']);
        $this->assertEquals('service', Plugin::TYPE_MAPPING['intelli']);
        $this->assertEquals('feature', Plugin::TYPE_MAPPING['social']);
        $this->assertEquals('feature', Plugin::TYPE_MAPPING['language']);
    }

    #[Test]
    public function test_has_set_type_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setType'));
    }

    #[Test]
    public function test_has_get_type_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getType'));
    }

    #[Test]
    public function test_has_set_code_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setCode'));
    }

    #[Test]
    public function test_has_get_code_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getCode'));
    }

    #[Test]
    public function test_has_set_name_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setName'));
    }

    #[Test]
    public function test_has_get_name_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getName'));
    }

    #[Test]
    public function test_has_set_description_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setDescription'));
    }

    #[Test]
    public function test_has_get_description_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getDescription'));
    }

    #[Test]
    public function test_has_set_author_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setAuthor'));
    }

    #[Test]
    public function test_has_get_author_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getAuthor'));
    }

    #[Test]
    public function test_has_get_author_name_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getAuthorName'));
    }

    #[Test]
    public function test_has_get_author_email_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getAuthorEmail'));
    }

    #[Test]
    public function test_has_set_icon_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setIcon'));
    }

    #[Test]
    public function test_has_get_icon_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getIcon'));
    }

    #[Test]
    public function test_has_set_version_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setVersion'));
    }

    #[Test]
    public function test_has_get_version_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getVersion'));
    }

    #[Test]
    public function test_has_set_dirname_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setDirname'));
    }

    #[Test]
    public function test_has_get_dirname_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getDirname'));
    }

    #[Test]
    public function test_has_get_path_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getPath'));
    }

    #[Test]
    public function test_has_set_installed_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setInstalled'));
    }

    #[Test]
    public function test_has_set_enabled_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setEnabled'));
    }

    #[Test]
    public function test_has_get_enabled_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getEnabled'));
    }

    #[Test]
    public function test_has_set_priority_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setPriority'));
    }

    #[Test]
    public function test_has_get_priority_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getPriority'));
    }

    #[Test]
    public function test_has_set_fields_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setFields'));
    }

    #[Test]
    public function test_has_get_fields_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getFields'));
    }

    #[Test]
    public function test_has_get_boot_file_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getBootFile'));
    }

    #[Test]
    public function test_has_get_field_view_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getFieldView'));
    }

    #[Test]
    public function test_has_get_field_view_path_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getFieldViewPath'));
    }

    #[Test]
    public function test_has_validate_config_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'validateConfig'));
    }

    #[Test]
    public function test_has_validate_fields_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'validateFields'));
    }

    #[Test]
    public function test_has_to_array_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'toArray'));
    }

    #[Test]
    public function test_has_handle_label_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'handleLabel'));
    }

    #[Test]
    public function test_has_get_readme_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getReadme'));
    }

    #[Test]
    public function test_has_get_readme_path_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getReadmePath'));
    }

    #[Test]
    public function test_has_get_readme_html_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getReadmeHtml'));
    }

    #[Test]
    public function test_has_get_menu_url_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getMenuUrl'));
    }

    #[Test]
    public function test_has_get_edit_url_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getEditUrl'));
    }

    #[Test]
    public function test_has_get_first_letter_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getFirstLetter'));
    }

    #[Test]
    public function test_implements_arrayable(): void
    {
        $interfaces = class_implements(Plugin::class);
        $this->assertContains('Illuminate\Contracts\Support\Arrayable', $interfaces);
    }

    #[Test]
    public function test_implements_array_access(): void
    {
        $interfaces = class_implements(Plugin::class);
        $this->assertContains('ArrayAccess', $interfaces);
    }
}
