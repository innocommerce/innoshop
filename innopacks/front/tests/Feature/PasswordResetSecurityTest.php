<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use Database\Factories\CustomerFactory;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\VerifyCode;
use InnoShop\Common\Notifications\ForgottenNotification;
use InnoShop\Front\Services\AccountService;
use ReflectionProperty;
use Tests\TestCase;

class PasswordResetSecurityTest extends TestCase
{
    public function test_reset_codes_are_hashed_and_successfully_reset_once(): void
    {
        Notification::fake();
        $customer = CustomerFactory::new()->create(['email' => 'reset@example.com']);
        $service  = AccountService::getInstance();
        $service->sendVerifyCode($customer->email);

        $row = VerifyCode::query()->where('account', $customer->email)->firstOrFail();
        $this->assertDoesNotMatchRegularExpression('/^\d{6}$/', $row->code);

        $code = $this->sentCode($customer);
        $this->assertTrue(Hash::check($code, $row->code));
        $service->verifyUpdatePassword($code, $customer->email, 'new-password');

        $this->assertTrue(Hash::check('new-password', $customer->fresh()->password));
        $this->assertNull(VerifyCode::query()->where('account', $customer->email)->first());

        $this->expectException(Exception::class);
        $service->verifyUpdatePassword($code, $customer->email, 'another-password');
    }

    public function test_five_wrong_guesses_invalidate_the_reset_code(): void
    {
        Notification::fake();
        $customer = CustomerFactory::new()->create(['email' => 'brute-force@example.com']);
        $service  = AccountService::getInstance();
        $service->sendVerifyCode($customer->email);
        $code      = $this->sentCode($customer);
        $wrongCode = $code === '000000' ? '000001' : '000000';

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                $service->verifyUpdatePassword($wrongCode, $customer->email, 'attacker-password');
                $this->fail('An invalid reset code was accepted.');
            } catch (Exception $e) {
                $this->assertSame(front_trans('account.verify_code_error'), $e->getMessage());
            }

            $row = VerifyCode::query()->where('account', $customer->email)->first();
            if ($attempt < 5) {
                $this->assertSame($attempt, $row->attempts);
            } else {
                $this->assertNull($row);
            }
        }

        $this->expectException(Exception::class);
        $service->verifyUpdatePassword($code, $customer->email, 'attacker-password');
    }

    public function test_legacy_plaintext_code_still_works_and_is_invalidated(): void
    {
        $customer = CustomerFactory::new()->create(['email' => 'legacy@example.com']);
        VerifyCode::query()->create([
            'account' => $customer->email,
            'code'    => '123456',
            'type'    => 'reset',
        ]);

        AccountService::getInstance()->verifyUpdatePassword('123456', $customer->email, 'new-password');

        $this->assertTrue(Hash::check('new-password', $customer->fresh()->password));
        $this->assertNull(VerifyCode::query()->where('account', $customer->email)->first());
    }

    private function sentCode(Customer $customer): string
    {
        $code = null;
        Notification::assertSentTo($customer, ForgottenNotification::class, function ($notification) use (&$code) {
            $property = new ReflectionProperty($notification, 'code');
            $code     = $property->getValue($notification);

            return true;
        });

        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $code);

        return (string) $code;
    }
}
