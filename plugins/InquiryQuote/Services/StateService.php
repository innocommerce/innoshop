<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Plugin\InquiryQuote\Models\InquiryQuote;
use Plugin\InquiryQuote\Models\InquiryQuoteHistory;
use Throwable;

class StateService
{
    private InquiryQuote $quote;

    private int $quoteID;

    private string $comment = '';

    private bool $notify = false;

    public const CUSTOMER_CREATED = 'customer_created';

    public const CUSTOMER_UPDATED = 'customer_updated';

    public const CUSTOMER_CLOSED = 'customer_closed';

    public const ADMIN_UPDATED = 'admin_updated';

    public const ADMIN_CLOSED = 'admin_closed';

    public const COMPLETED = 'completed';

    public const QUOTE_STATUS = [
        self::CUSTOMER_CREATED,
        self::CUSTOMER_UPDATED,
        self::CUSTOMER_CLOSED,
        self::ADMIN_UPDATED,
        self::ADMIN_CLOSED,
        self::COMPLETED,
    ];

    public const MACHINES = [
        self::CUSTOMER_CREATED => [
            self::CUSTOMER_UPDATED => ['updateStatus', 'addHistory'],
            self::CUSTOMER_CLOSED  => ['updateStatus', 'addHistory'],
            self::ADMIN_UPDATED    => ['updateStatus', 'addHistory'],
            self::ADMIN_CLOSED     => ['updateStatus', 'addHistory'],
        ],
        self::CUSTOMER_UPDATED => [
            self::CUSTOMER_UPDATED => ['updateStatus', 'addHistory'],
            self::ADMIN_UPDATED    => ['updateStatus', 'addHistory'],
            self::ADMIN_CLOSED     => ['updateStatus', 'addHistory'],
        ],
        self::ADMIN_UPDATED => [
            self::ADMIN_UPDATED    => ['updateStatus', 'addHistory'],
            self::ADMIN_CLOSED     => ['updateStatus', 'addHistory'],
            self::CUSTOMER_UPDATED => ['updateStatus', 'addHistory'],
            self::CUSTOMER_CLOSED  => ['updateStatus', 'addHistory'],
            self::COMPLETED        => ['updateStatus', 'addHistory'],
        ],
    ];

    /**
     * @param  InquiryQuote  $quote
     */
    public function __construct(InquiryQuote $quote)
    {
        $this->quote   = $quote;
        $this->quoteID = $quote->id;
    }

    /**
     * @param  InquiryQuote  $quote
     * @return self
     */
    public static function getInstance(InquiryQuote $quote): self
    {
        return new self($quote);
    }

    /**
     * Get all order statuses.
     *
     * @return array
     * @throws Exception
     */
    public static function getAllStatuses(): array
    {
        $result   = [];
        $statuses = self::QUOTE_STATUS;
        foreach ($statuses as $status) {
            $result[] = [
                'status' => $status,
                'name'   => trans("InquiryQuote::quote.$status"),
            ];
        }

        return fire_hook_filter('service.quote.state.all_statuses', $result);
    }

    /**
     * Set order comment.
     *
     * @param  $comment
     * @return $this
     */
    public function setComment($comment): self
    {
        $this->comment = (string) $comment;

        return $this;
    }

    /**
     * Set order notify or not.
     *
     * @param  $flag
     * @return $this
     */
    public function setNotify($flag): self
    {
        $this->notify = (bool) $flag;

        return $this;
    }

    /**
     * Retrieve the possible states that the current quote can transition to.
     *
     * @return array
     * @throws Exception
     */
    public function nextBackendStatuses(): array
    {
        $machines = $this->getMachines();

        $currentStatusCode = $this->quote->status;
        $nextStatus        = $machines[$currentStatusCode] ?? [];

        if (empty($nextStatus)) {
            return [];
        }
        $nextStatusCodes = array_keys($nextStatus);
        $result          = [];
        foreach ($nextStatusCodes as $status) {
            $result[] = [
                'status' => $status,
                'name'   => trans("InquiryQuote::quote.{$status}"),
                'action' => trans("InquiryQuote::quote.{$status}_action"),
            ];
        }

        return $result;
    }

    /**
     * External method invocation to modify the order status and process others.
     *
     * @param  $status
     * @param  string|null  $comment
     * @param  bool  $notify
     * @throws Exception
     */
    public function changeStatus($status, ?string $comment = '', bool $notify = false): void
    {
        $oldStatusCode = $this->quote->status;
        $newStatusCode = $status;

        $this->validStatusCode($status);

        DB::beginTransaction();
        try {
            $functions = $this->getFunctions($oldStatusCode, $newStatusCode);
            if (empty($functions)) {
                return;
            }

            foreach ($functions as $function) {
                if ($function instanceof \Closure) {
                    $function();

                    continue;
                }

                if (! method_exists($this, $function)) {
                    throw new Exception("{$function} not exist in StateMachine!");
                }
                $this->{$function}($oldStatusCode, $status);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update order status.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return void
     * @throws Throwable
     */
    private function updateStatus($oldCode, $newCode): void
    {
        $this->quote->status = $newCode;
        $this->quote->saveOrFail();
    }

    /**
     * Add an order modification record.
     *
     * @param  $oldCode
     * @param  $newCode
     * @return mixed
     * @throws Throwable
     */
    public function addHistory($oldCode, $newCode): mixed
    {
        $history = new InquiryQuoteHistory([
            'inquiry_quote_id' => $this->quoteID,
            'status'           => $newCode,
            'notify'           => (int) $this->notify,
            'comment'          => $this->comment,
        ]);
        $history->saveOrFail();

        return $history;
    }

    /**
     * Retrieve the state machine process, which can be modified by external plugins through a filter hook.
     *
     * @return mixed
     */
    private function getMachines(): mixed
    {
        $data = [
            'quote'    => $this->quote,
            'machines' => self::MACHINES,
        ];

        $data = fire_hook_filter('service.quote.state.machines', $data);

        return $data['machines'] ?? [];
    }

    /**
     * Retrieve the events that need to be triggered based on the current order status,
     * and the status it is about to transition to.
     *
     * @param  $oldStatus
     * @param  $newStatus
     * @return array
     */
    private function getFunctions($oldStatus, $newStatus): array
    {
        $machines = $this->getMachines();

        return $machines[$oldStatus][$newStatus] ?? [];
    }

    /**
     * Check if the current order can be transitioned to a specific status.
     *
     * @param  $statusCode
     * @throws Exception
     */
    private function validStatusCode($statusCode): void
    {
        $quoteID           = $this->quoteID;
        $quoteNumber       = $this->quote->number;
        $currentStatusCode = $this->quote->status;

        $nextStatusCodes = collect($this->nextBackendStatuses())->pluck('status')->toArray();
        if (! in_array($statusCode, $nextStatusCodes)) {
            throw new Exception("Quote {$quoteID}({$quoteNumber}) is {$currentStatusCode}, cannot be changed to $statusCode");
        }
    }
}
