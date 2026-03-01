<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use InnoShop\Common\Models\NewsletterSubscriber;
use InnoShop\Common\Repositories\NewsletterRepo;
use Throwable;

class NewsletterService
{
    private NewsletterRepo $newsletterRepo;

    public function __construct()
    {
        $this->newsletterRepo = NewsletterRepo::getInstance();
    }

    /**
     * Get service instance.
     *
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Subscribe email to newsletter.
     *
     * @param  array  $data
     * @return NewsletterSubscriber
     * @throws Throwable
     */
    public function subscribe(array $data): NewsletterSubscriber
    {
        // Get current customer if logged in
        $customer = current_customer();
        if ($customer) {
            $data['customer_id'] = $customer->id;
            if (empty($data['name']) && $customer->name) {
                $data['name'] = $customer->name;
            }
        }

        return $this->newsletterRepo->subscribe($data);
    }

    /**
     * Unsubscribe email from newsletter.
     *
     * @param  string  $email
     * @return bool
     */
    public function unsubscribe(string $email): bool
    {
        return $this->newsletterRepo->unsubscribe($email);
    }

    /**
     * Check if email is already subscribed.
     *
     * @param  string  $email
     * @return bool
     */
    public function isSubscribed(string $email): bool
    {
        $subscriber = $this->newsletterRepo->findByEmail($email);

        return $subscriber && $subscriber->isActive();
    }

    /**
     * Get active subscribers count.
     *
     * @return int
     */
    public function getActiveCount(): int
    {
        return $this->newsletterRepo->getActiveCount();
    }
}
