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
use Plugin\InquiryQuote\Models\InquiryQuote;
use Plugin\InquiryQuote\Models\InquiryQuoteItem;

class CommentService
{
    private mixed $admin;

    private ?InquiryQuoteItem $inquiry = null;

    private ?InquiryQuote $quote = null;

    public function __construct()
    {
        $this->admin = token_customer();
    }

    /**
     * @return self
     */
    public static function getInstance(): CommentService
    {
        return new self;
    }

    /**
     * @param  InquiryQuoteItem  $inquiry
     * @return CommentService
     */
    public function setInquiry(InquiryQuoteItem $inquiry): static
    {
        $this->inquiry = $inquiry;

        return $this;
    }

    /**
     * @param  InquiryQuote  $quote
     * @return CommentService
     */
    public function setQuote(InquiryQuote $quote): static
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function getMessage($data): string
    {
        if (empty($this->inquiry) && empty($this->quote)) {
            throw new Exception('Both Inquiry and Quote are empty!');
        }

        if (! is_string($data)) {
            $data = json_encode($data);
        }

        if ($this->inquiry) {
            return "{$this->admin->name}: Inquiry {$this->inquiry->sku_code} - {$data}";
        } elseif ($this->quote) {
            return "{$this->admin->name}: Quote {$this->quote->number} - {$data}";
        }

        return '';
    }
}
