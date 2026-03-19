<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use InnoShop\Common\Models\NewsletterSubscriber;
use Throwable;

class NewsletterRepo extends BaseRepo
{
    protected string $model = NewsletterSubscriber::class;

    /**
     * Get criteria for filtering.
     *
     * @return array
     */
    public static function getCriteria(): array
    {
        $statusOptions = [];
        foreach (NewsletterSubscriber::STATUSES as $status) {
            $statusOptions[] = [
                'value' => $status,
                'label' => trans('panel/newsletter.status_'.$status),
            ];
        }

        $sourceOptions = [];
        foreach (NewsletterSubscriber::SOURCES as $source) {
            $sourceOptions[] = [
                'value' => $source,
                'label' => trans('panel/newsletter.source_'.$source),
            ];
        }

        return [
            ['name' => 'email', 'type' => 'input', 'label' => trans('panel/newsletter.email')],
            ['name' => 'name', 'type' => 'input', 'label' => trans('panel/newsletter.name')],
            [
                'name'    => 'status',
                'type'    => 'select',
                'label'   => trans('panel/newsletter.status'),
                'options' => $statusOptions,
            ],
            [
                'name'    => 'source',
                'type'    => 'select',
                'label'   => trans('panel/newsletter.source'),
                'options' => $sourceOptions,
            ],
        ];
    }

    /**
     * Get search field options for data_search component
     *
     * @return array
     */
    public static function getSearchFieldOptions(): array
    {
        $options = [
            ['value' => '', 'label' => trans('panel/common.all_fields')],
            ['value' => 'email', 'label' => trans('panel/newsletter.email')],
            ['value' => 'name', 'label' => trans('panel/newsletter.name')],
        ];

        return fire_hook_filter('common.repo.newsletter.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $statusOptions = [
            ['value' => '', 'label' => trans('panel/common.all')],
        ];
        foreach (NewsletterSubscriber::STATUSES as $status) {
            $statusOptions[] = [
                'value' => $status,
                'label' => trans('panel/newsletter.status_'.$status),
            ];
        }

        $filters = [
            [
                'name'    => 'status',
                'label'   => trans('panel/newsletter.status'),
                'type'    => 'button',
                'options' => $statusOptions,
            ],
        ];

        return fire_hook_filter('common.repo.newsletter.filter_button_options', $filters);
    }

    /**
     * Build query with filters.
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = NewsletterSubscriber::query()->with(['customer']);

        $email = $filters['email'] ?? '';
        if ($email) {
            $builder->where('email', 'like', "%{$email}%");
        }

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', "%{$name}%");
        }

        $status = $filters['status'] ?? '';
        if ($status) {
            $builder->where('status', $status);
        }

        $source = $filters['source'] ?? '';
        if ($source) {
            $builder->where('source', $source);
        }

        return $builder;
    }

    /**
     * Find subscriber by email.
     *
     * @param  string  $email
     * @return NewsletterSubscriber|null
     */
    public function findByEmail(string $email): ?NewsletterSubscriber
    {
        return NewsletterSubscriber::where('email', $email)->first();
    }

    /**
     * Subscribe email.
     *
     * @param  array  $data
     * @return NewsletterSubscriber
     * @throws Throwable
     */
    public function subscribe(array $data): NewsletterSubscriber
    {
        $subscriber = $this->findByEmail($data['email']);

        if ($subscriber) {
            // If already exists, reactivate if unsubscribed
            if ($subscriber->status === NewsletterSubscriber::STATUS_UNSUBSCRIBED) {
                $subscriber->subscribe();
                if (isset($data['name'])) {
                    $subscriber->name = $data['name'];
                }
                if (isset($data['source'])) {
                    $subscriber->source = $data['source'];
                }
                if (isset($data['customer_id'])) {
                    $subscriber->customer_id = $data['customer_id'];
                }
                $subscriber->save();
            }
        } else {
            // Create new subscriber
            $subscriber = NewsletterSubscriber::create([
                'email'         => $data['email'],
                'name'          => $data['name'] ?? null,
                'customer_id'   => $data['customer_id'] ?? null,
                'status'        => NewsletterSubscriber::STATUS_ACTIVE,
                'source'        => $data['source'] ?? NewsletterSubscriber::SOURCE_FOOTER,
                'subscribed_at' => now(),
            ]);
        }

        return $subscriber;
    }

    /**
     * Unsubscribe email.
     *
     * @param  string  $email
     * @return bool
     */
    public function unsubscribe(string $email): bool
    {
        $subscriber = $this->findByEmail($email);

        if ($subscriber && $subscriber->isActive()) {
            $subscriber->unsubscribe();

            return true;
        }

        return false;
    }

    /**
     * Get active subscribers count.
     *
     * @return int
     */
    public function getActiveCount(): int
    {
        return NewsletterSubscriber::where('status', NewsletterSubscriber::STATUS_ACTIVE)->count();
    }

    /**
     * Get subscribers by status.
     *
     * @param  string  $status
     * @return Collection
     */
    public function getByStatus(string $status)
    {
        return NewsletterSubscriber::where('status', $status)->get();
    }
}
