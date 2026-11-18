<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Services\EventTrackingService;
use InnoShop\Common\Tests\TestCase;

/**
 * The page_url / referrer columns are varchar(1000). An over-long value
 * made MySQL raise SQLSTATE 22001, which was swallowed by the catch block
 * and silently dropped the whole event.
 */
class EventTrackingUrlTruncationTest extends TestCase
{
    public function test_long_page_url_is_truncated_and_the_event_is_stored(): void
    {
        $request = $this->makeRequest('https://example.com/products/foo?'.str_repeat('a', 3000));

        $event = app(EventTrackingService::class)->track('product_view', [], $request);

        $this->assertNotNull($event, 'the event must be recorded, not dropped');

        $row = DB::table('visit_events')->first();
        $this->assertNotNull($row);
        $this->assertSame('product_view', $row->event_type);
        $this->assertSame(1000, mb_strlen($row->page_url), 'the url must fit into the column');
        $this->assertStringStartsWith('https://example.com/products/foo?', $row->page_url);
    }

    public function test_long_referrer_is_truncated_and_the_event_is_stored(): void
    {
        $request = $this->makeRequest('https://example.com/products/foo');
        $request->headers->set('referer', 'https://referrer.example.com/?'.str_repeat('b', 3000));

        $event = app(EventTrackingService::class)->track('add_to_cart', [], $request);

        $this->assertNotNull($event, 'the event must be recorded, not dropped');

        $row = DB::table('visit_events')->first();
        $this->assertNotNull($row);
        $this->assertSame(1000, mb_strlen($row->referrer), 'the referrer must fit into the column');
    }

    public function test_short_urls_are_stored_unchanged(): void
    {
        $request = $this->makeRequest('https://example.com/products/foo');
        $request->headers->set('referer', 'https://referrer.example.com/');

        app(EventTrackingService::class)->track('page_view', [], $request);

        $row = DB::table('visit_events')->first();
        $this->assertNotNull($row);
        $this->assertSame('https://example.com/products/foo', $row->page_url);
        $this->assertSame('https://referrer.example.com/', $row->referrer);
    }

    private function makeRequest(string $url): Request
    {
        $request = Request::create($url, 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36');
        $request->server->set('REMOTE_ADDR', '203.0.113.90');

        return $request;
    }
}
