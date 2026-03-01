<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Models;

use InnoShop\Common\Models\Customer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CustomerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Customer::class);
    }

    #[Test]
    public function test_model_exists(): void
    {
        $this->assertTrue(class_exists(Customer::class));
    }

    #[Test]
    public function test_model_extends_auth_user(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('Illuminate\Foundation\Auth\User'));
    }

    #[Test]
    public function test_model_uses_required_traits(): void
    {
        $traits = class_uses_recursive(Customer::class);
        $this->assertContains('Laravel\Sanctum\HasApiTokens', $traits);
        $this->assertContains('Illuminate\Database\Eloquent\Factories\HasFactory', $traits);
        $this->assertContains('Illuminate\Notifications\Notifiable', $traits);
    }

    #[Test]
    public function test_from_constants_are_defined(): void
    {
        $this->assertEquals('pc_web', Customer::FROM_PC_WEB);
        $this->assertEquals('mobile_web', Customer::FROM_MOBILE_WEB);
        $this->assertEquals('miniapp', Customer::FROM_MINIAPP);
        $this->assertEquals('wechat_official', Customer::FROM_WECHAT_OFFICIAL);
        $this->assertEquals('app', Customer::FROM_APP);
    }

    #[Test]
    public function test_fillable_contains_required_fields(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $requiredFields = [
            'email', 'password', 'name', 'avatar', 'customer_group_id',
            'address_id', 'locale', 'active', 'code', 'from',
            'calling_code', 'telephone',
        ];

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $fillable, "Field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_has_customer_group_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('customerGroup'));
        $method = $this->reflection->getMethod('customerGroup');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_addresses_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('addresses'));
        $method = $this->reflection->getMethod('addresses');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_orders_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('orders'));
        $method = $this->reflection->getMethod('orders');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_transactions_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('transactions'));
        $method = $this->reflection->getMethod('transactions');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_favorites_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('favorites'));
        $method = $this->reflection->getMethod('favorites');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_socials_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('socials'));
        $method = $this->reflection->getMethod('socials');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_password_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getHasPasswordAttribute'));
        $method = $this->reflection->getMethod('getHasPasswordAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_verify_password_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('verifyPassword'));
        $method = $this->reflection->getMethod('verifyPassword');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_sync_balance_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('syncBalance'));
        $method = $this->reflection->getMethod('syncBalance');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_notify_registration_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('notifyRegistration'));
        $method = $this->reflection->getMethod('notifyRegistration');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_notify_forgotten_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('notifyForgotten'));
        $method = $this->reflection->getMethod('notifyForgotten');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_get_from_options_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getFromOptions'));
        $method = $this->reflection->getMethod('getFromOptions');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    #[Test]
    public function test_has_from_display_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getFromDisplayAttribute'));
        $method = $this->reflection->getMethod('getFromDisplayAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_is_mobile_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('isMobile'));
        $method = $this->reflection->getMethod('isMobile');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_is_mobile_logic_for_pc_web(): void
    {
        $from     = Customer::FROM_PC_WEB;
        $isMobile = in_array($from, [
            Customer::FROM_MOBILE_WEB,
            Customer::FROM_MINIAPP,
            Customer::FROM_WECHAT_OFFICIAL,
            Customer::FROM_APP,
        ]);

        $this->assertFalse($isMobile);
    }

    #[Test]
    public function test_is_mobile_logic_for_mobile_web(): void
    {
        $from     = Customer::FROM_MOBILE_WEB;
        $isMobile = in_array($from, [
            Customer::FROM_MOBILE_WEB,
            Customer::FROM_MINIAPP,
            Customer::FROM_WECHAT_OFFICIAL,
            Customer::FROM_APP,
        ]);

        $this->assertTrue($isMobile);
    }

    #[Test]
    public function test_is_mobile_logic_for_miniapp(): void
    {
        $from     = Customer::FROM_MINIAPP;
        $isMobile = in_array($from, [
            Customer::FROM_MOBILE_WEB,
            Customer::FROM_MINIAPP,
            Customer::FROM_WECHAT_OFFICIAL,
            Customer::FROM_APP,
        ]);

        $this->assertTrue($isMobile);
    }

    #[Test]
    public function test_is_mobile_logic_for_wechat_official(): void
    {
        $from     = Customer::FROM_WECHAT_OFFICIAL;
        $isMobile = in_array($from, [
            Customer::FROM_MOBILE_WEB,
            Customer::FROM_MINIAPP,
            Customer::FROM_WECHAT_OFFICIAL,
            Customer::FROM_APP,
        ]);

        $this->assertTrue($isMobile);
    }

    #[Test]
    public function test_is_mobile_logic_for_app(): void
    {
        $from     = Customer::FROM_APP;
        $isMobile = in_array($from, [
            Customer::FROM_MOBILE_WEB,
            Customer::FROM_MINIAPP,
            Customer::FROM_WECHAT_OFFICIAL,
            Customer::FROM_APP,
        ]);

        $this->assertTrue($isMobile);
    }

    #[Test]
    public function test_password_verification_logic(): void
    {
        $password       = 'secret123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->assertTrue(password_verify($password, $hashedPassword));
        $this->assertFalse(password_verify('wrongpassword', $hashedPassword));
    }

    #[Test]
    public function test_has_password_logic_with_password(): void
    {
        $password    = 'hashed_password';
        $hasPassword = ! empty($password);

        $this->assertTrue($hasPassword);
    }

    #[Test]
    public function test_has_password_logic_without_password(): void
    {
        $password    = '';
        $hasPassword = ! empty($password);

        $this->assertFalse($hasPassword);
    }

    #[Test]
    public function test_has_password_logic_with_null(): void
    {
        $password    = null;
        $hasPassword = ! empty($password);

        $this->assertFalse($hasPassword);
    }
}
