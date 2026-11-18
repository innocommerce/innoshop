<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Stripe\Tests\Feature;

use Database\Factories\CustomerFactory;
use Database\Factories\OrderFactory;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Services\StateMachineService;
use Plugin\Stripe\Controllers\StripeController;
use Tests\TestCase;

class WebhookSecurityTest extends TestCase
{
    private const SECRET = 'whsec_test_secret';

    public function test_callback_fails_closed_when_webhook_secret_is_not_configured(): void
    {
        config()->set('inno.stripe.webhook_secret', null);

        $response = (new StripeController)->callback($this->request($this->payload('missing'), null));

        $this->assertSame(503, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);
    }

    public function test_forged_callback_without_a_valid_signature_cannot_mark_an_order_paid(): void
    {
        $order = $this->order();
        config()->set('inno.stripe.webhook_secret', self::SECRET);

        $response = (new StripeController)->callback($this->request($this->payload($order->number), 't=1,v1=forged'));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(StateMachineService::UNPAID, $order->fresh()->status);
    }

    public function test_signed_charge_with_wrong_amount_is_rejected(): void
    {
        $order = $this->order(['total' => 20.00]);
        config()->set('inno.stripe.webhook_secret', self::SECRET);
        $payload = $this->payload($order->number, ['amount' => 100, 'amount_captured' => 100]);

        $response = (new StripeController)->callback($this->request($payload, $this->signature($payload)));

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(StateMachineService::UNPAID, $order->fresh()->status);
    }

    public function test_valid_signed_charge_marks_matching_order_paid_and_is_idempotent(): void
    {
        $order = $this->order(['total' => 20.00]);
        config()->set('inno.stripe.webhook_secret', self::SECRET);
        $payload = $this->payload($order->number);

        $controller = new StripeController;
        $response   = $controller->callback($this->request($payload, $this->signature($payload)));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['success']);
        $this->assertSame(StateMachineService::PAID, $order->fresh()->status);

        $duplicate = $controller->callback($this->request($payload, $this->signature($payload)));
        $this->assertSame(200, $duplicate->getStatusCode());
        $this->assertSame(1, Order\History::query()->where('order_id', $order->id)->where('status', StateMachineService::PAID)->count());
    }

    private function order(array $attributes = []): Order
    {
        $customer = CustomerFactory::new()->create();

        return OrderFactory::new()->forCustomer($customer)->create($attributes + [
            'currency_code'  => 'USD',
            'currency_value' => 1.0,
        ]);
    }

    private function payload(string $orderNumber, array $charge = []): string
    {
        return json_encode([
            'id'   => 'evt_test_'.uniqid(),
            'type' => 'charge.succeeded',
            'data' => [
                'object' => $charge + [
                    'amount'          => 2000,
                    'amount_captured' => 2000,
                    'currency'        => 'usd',
                    'paid'            => true,
                    'captured'        => true,
                    'status'          => 'succeeded',
                    'metadata'        => ['order_number' => $orderNumber],
                ],
            ],
        ], JSON_THROW_ON_ERROR);
    }

    private function signature(string $payload): string
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, self::SECRET);

        return "t=$timestamp,v1=$signature";
    }

    private function request(string $payload, ?string $signature): Request
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        if ($signature !== null) {
            $server['HTTP_STRIPE_SIGNATURE'] = $signature;
        }

        return Request::create('/callback/stripe', 'POST', server: $server, content: $payload);
    }
}
