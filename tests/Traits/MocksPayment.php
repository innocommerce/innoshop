<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Traits;

use Mockery;
use Mockery\MockInterface;

trait MocksPayment
{
    protected MockInterface $paymentServiceMock;

    protected function mockPaymentService(): MockInterface
    {
        $this->paymentServiceMock = Mockery::mock('InnoShop\Common\Services\PaymentService');
        $this->app->instance('InnoShop\Common\Services\PaymentService', $this->paymentServiceMock);

        return $this->paymentServiceMock;
    }

    protected function mockSuccessfulPayment(float $amount = 100.00): void
    {
        $this->mockPaymentService()
            ->shouldReceive('processPayment')
            ->andReturn([
                'success'        => true,
                'transaction_id' => 'TXN-'.uniqid(),
                'amount'         => $amount,
            ]);
    }

    protected function mockFailedPayment(string $errorMessage = 'Payment failed'): void
    {
        $this->mockPaymentService()
            ->shouldReceive('processPayment')
            ->andReturn([
                'success' => false,
                'error'   => $errorMessage,
            ]);
    }

    protected function mockPaymentCallback(string $status = 'success'): void
    {
        $this->mockPaymentService()
            ->shouldReceive('handleCallback')
            ->andReturn([
                'status'         => $status,
                'transaction_id' => 'TXN-'.uniqid(),
            ]);
    }

    protected function mockRefund(float $amount = 100.00, bool $success = true): void
    {
        $this->mockPaymentService()
            ->shouldReceive('processRefund')
            ->andReturn([
                'success'   => $success,
                'refund_id' => $success ? 'REF-'.uniqid() : null,
                'amount'    => $amount,
                'error'     => $success ? null : 'Refund failed',
            ]);
    }
}
