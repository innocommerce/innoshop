<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use InnoShop\Common\Services\NewsletterService;
use InnoShop\Front\Requests\NewsletterRequest;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter.
     *
     * @param  NewsletterRequest  $request
     * @return mixed
     */
    public function subscribe(NewsletterRequest $request): mixed
    {
        try {
            $data = [
                'email'  => $request->input('email'),
                'name'   => $request->input('name'),
                'source' => $request->input('source', 'footer'),
            ];

            $service = NewsletterService::getInstance();

            // Check if already subscribed
            if ($service->isSubscribed($data['email'])) {
                return json_fail(front_trans('newsletter.already_subscribed'));
            }

            $subscriber = $service->subscribe($data);

            return json_success(front_trans('newsletter.subscribe_success'), [
                'subscriber' => [
                    'id'    => $subscriber->id,
                    'email' => $subscriber->email,
                ],
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Unsubscribe from newsletter.
     *
     * @param  NewsletterRequest  $request
     * @return mixed
     */
    public function unsubscribe(NewsletterRequest $request): mixed
    {
        try {
            $email = $request->input('email');

            if (empty($email)) {
                return json_fail(front_trans('newsletter.email_required'));
            }

            $service = NewsletterService::getInstance();
            $result  = $service->unsubscribe($email);

            if ($result) {
                return json_success(front_trans('newsletter.unsubscribe_success'));
            }

            return json_fail(front_trans('newsletter.unsubscribe_failed'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
