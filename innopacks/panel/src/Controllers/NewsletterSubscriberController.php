<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\NewsletterSubscriber;
use InnoShop\Common\Repositories\NewsletterRepo;

class NewsletterSubscriberController extends BaseController
{
    protected string $modelClass = NewsletterSubscriber::class;

    /**
     * List newsletter subscribers.
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'searchFields'  => NewsletterRepo::getSearchFieldOptions(),
            'filterButtons' => NewsletterRepo::getFilterButtonOptions(),
            'subscribers'   => NewsletterRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::newsletter_subscribers.index', $data);
    }

    /**
     * Show subscriber details.
     *
     * @param  NewsletterSubscriber  $newsletterSubscriber
     * @return NewsletterSubscriber
     */
    public function show(NewsletterSubscriber $newsletterSubscriber): NewsletterSubscriber
    {
        return $newsletterSubscriber->load('customer');
    }

    /**
     * Delete subscriber.
     *
     * @param  NewsletterSubscriber  $newsletterSubscriber
     * @return RedirectResponse
     */
    public function destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        try {
            NewsletterRepo::getInstance()->destroy($newsletterSubscriber);

            return redirect(panel_route('newsletter_subscribers.index'))
                ->with('success', common_trans('base.deleted_success'));
        } catch (Exception $e) {
            return redirect(panel_route('newsletter_subscribers.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Unsubscribe a subscriber.
     *
     * @param  NewsletterSubscriber  $newsletterSubscriber
     * @return RedirectResponse
     */
    public function unsubscribe(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        try {
            $newsletterSubscriber->unsubscribe();

            return redirect(panel_route('newsletter_subscribers.index'))
                ->with('success', panel_trans('newsletter.unsubscribe_success'));
        } catch (Exception $e) {
            return redirect(panel_route('newsletter_subscribers.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Resubscribe a subscriber.
     *
     * @param  NewsletterSubscriber  $newsletterSubscriber
     * @return RedirectResponse
     */
    public function resubscribe(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        try {
            $newsletterSubscriber->subscribe();

            return redirect(panel_route('newsletter_subscribers.index'))
                ->with('success', panel_trans('newsletter.resubscribe_success'));
        } catch (Exception $e) {
            return redirect(panel_route('newsletter_subscribers.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
