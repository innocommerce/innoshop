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
use InnoShop\Common\Models\Order;
use Plugin\Stripe\Services\StripeService;
use Stripe\ApiRequestor;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Exception\UnexpectedValueException;
use Stripe\HttpClient\ClientInterface;
use Tests\TestCase;

/**
 * Verifies the parameters the plugin sends through stripe-php v23 without
 * touching the network: a fake HTTP client captures every request and the
 * tests assert none of them carry parameters removed in v22/v23.
 */
class StripeSdkCompatibilityTest extends TestCase
{
    private object $http;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('inno.stripe.secret_key', 'sk_test_fake');
        config()->set('inno.stripe.publishable_key', 'pk_test_fake');

        $this->http = new class implements ClientInterface
        {
            public array $requests = [];

            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
            {
                $this->requests[] = ['method' => $method, 'url' => $absUrl, 'params' => $params];

                $body = match (true) {
                    str_contains($absUrl, '/checkout/sessions') => [
                        'id'             => 'cs_test_fake',
                        'object'         => 'checkout.session',
                        'url'            => 'https://checkout.stripe.com/pay/cs_test_fake',
                        'mode'           => 'payment',
                        'payment_status' => 'unpaid',
                        'currency'       => 'usd',
                        'amount_total'   => 2000,
                    ],
                    str_contains($absUrl, '/payment_intents') => [
                        'id'            => 'pi_test_fake',
                        'object'        => 'payment_intent',
                        'client_secret' => 'pi_test_fake_secret',
                        'status'        => 'requires_payment_method',
                        'amount'        => 2000,
                        'currency'      => 'usd',
                    ],
                    str_contains($absUrl, '/customers') => [
                        'id'     => 'cus_test_fake',
                        'object' => 'customer',
                        'email'  => 'buyer@example.test',
                    ],
                    str_contains($absUrl, '/charges') => [
                        'id'       => 'ch_test_fake',
                        'object'   => 'charge',
                        'status'   => 'succeeded',
                        'paid'     => true,
                        'captured' => true,
                        'amount'   => 2000,
                        'currency' => 'usd',
                    ],
                    default => throw new UnexpectedValueException('Unexpected Stripe endpoint: '.$absUrl),
                };

                return [json_encode($body, JSON_THROW_ON_ERROR), 200, []];
            }
        };

        ApiRequestor::setHttpClient($this->http);
    }

    protected function tearDown(): void
    {
        ApiRequestor::setHttpClient(null);

        parent::tearDown();
    }

    public function test_checkout_session_uses_allowed_payment_method_types(): void
    {
        $order   = $this->order(['total' => 20.00]);
        $service = new StripeService($order);

        $session = $service->createCheckoutSession([
            'success_url' => 'https://shop.test/payment/success',
            'cancel_url'  => 'https://shop.test/payment/cancel',
        ]);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertSame('cs_test_fake', $session->id);

        $params = $this->paramsFor('/checkout/sessions');
        $this->assertArrayNotHasKey('payment_method_types', $params);
        $this->assertSame(StripeService::PAYMENT_METHODS, $params['allowed_payment_method_types']);
        $this->assertSame('payment', $params['mode']);
        $this->assertSame(2000, $params['line_items'][0]['price_data']['unit_amount']);
        $this->assertSame($order->number, $params['client_reference_id']);
        $this->assertSame($order->email, $params['customer_email']);
        $this->assertSame('https://shop.test/payment/success', $params['success_url']);
        $this->assertSame('https://shop.test/payment/cancel', $params['cancel_url']);
        $this->assertSame($order->number, $params['metadata']['order_number']);
    }

    public function test_checkout_session_defaults_can_be_overridden(): void
    {
        $service = new StripeService($this->order(['total' => 20.00]));

        $service->createCheckoutSession([
            'allowed_payment_method_types' => ['card'],
            'metadata'                     => ['order_number' => 'custom'],
        ]);

        $params = $this->paramsFor('/checkout/sessions');
        $this->assertSame(['card'], $params['allowed_payment_method_types']);
        $this->assertSame('custom', $params['metadata']['order_number']);
    }

    public function test_mobile_payment_data_uses_automatic_payment_methods(): void
    {
        $service = new StripeService($this->order(['total' => 20.00]));

        $data = $service->getMobilePaymentData();

        $this->assertSame('pi_test_fake_secret', $data['paymentIntent']);
        $this->assertSame('pk_test_fake', $data['publishKey']);

        $params = $this->paramsFor('/payment_intents');
        $this->assertArrayNotHasKey('payment_method_types', $params);
        // The SDK form-encodes booleans as 'true'/'false' strings.
        $this->assertSame('true', $params['automatic_payment_methods']['enabled']);
        $this->assertSame('cus_test_fake', $params['customer']);
        $this->assertSame(2000, $params['amount']);
    }

    public function test_capture_creates_customer_with_token_and_charge_without_removed_params(): void
    {
        $service = new StripeService($this->order(['total' => 20.00]));

        $charge = $service->capture(['token' => 'tok_test_fake']);

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertSame('ch_test_fake', $charge->id);

        $customerParams = $this->paramsFor('/customers');
        $this->assertSame('tok_test_fake', $customerParams['source']);

        $chargeParams = $this->paramsFor('/charges');
        $this->assertArrayNotHasKey('payment_method_types', $chargeParams);
        $this->assertSame(2000, $chargeParams['amount']);
        $this->assertSame('cus_test_fake', $chargeParams['customer']);
    }

    public function test_zero_decimal_currency_is_not_multiplied(): void
    {
        $service = new StripeService($this->order(['total' => 2000, 'currency_code' => 'JPY']));

        $service->createCheckoutSession();

        $params = $this->paramsFor('/checkout/sessions');
        $this->assertSame(2000, $params['line_items'][0]['price_data']['unit_amount']);
    }

    private function order(array $attributes = []): Order
    {
        $customer = CustomerFactory::new()->create();

        return OrderFactory::new()->forCustomer($customer)->create($attributes + [
            'currency_code'  => 'USD',
            'currency_value' => 1.0,
        ]);
    }

    private function paramsFor(string $path): array
    {
        foreach (array_reverse($this->http->requests) as $request) {
            if (str_contains($request['url'], $path)) {
                return $request['params'];
            }
        }

        $this->fail('No request was sent to '.$path);
    }
}
