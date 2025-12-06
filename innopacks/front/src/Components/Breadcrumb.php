<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Components;

use Exception;
use Illuminate\View\Component;
use InnoShop\Common\Libraries\Breadcrumb as BreadcrumbLib;

class Breadcrumb extends Component
{
    public array $breadcrumbs;

    public bool $showFilter = false;

    /**
     * @param  $type
     * @param  $value
     * @param  string  $title
     * @param  bool  $showFilter
     * @throws Exception
     */
    public function __construct($type, $value, string $title = '', bool $showFilter = false)
    {
        $this->breadcrumbs[] = $this->formatBreadcrumb([
            'title' => front_trans('common.home'),
            'url'   => front_route('home.index'),
        ]);

        $breadcrumbLib = BreadcrumbLib::getInstance();

        $accountRoutes = [
            'account.orders.index',
            'account.favorites.index',
            'account.reviews.index',
            'account.addresses.index',
            'account.order_returns.index',
            'account.edit.index',
            'account.password.index',
        ];

        $accountRoutes = fire_hook_filter('front.component.breadcrumb.account_routes', $accountRoutes);

        if (in_array($type, ['order', 'order_return']) || in_array($value, $accountRoutes)) {
            $this->breadcrumbs[] = $this->formatBreadcrumb($breadcrumbLib->getTrail('route', 'account.index', front_trans('account.account')));
        }

        if ($type == 'order') {
            $this->breadcrumbs[] = $this->formatBreadcrumb($breadcrumbLib->getTrail('route', 'account.orders.index', front_trans('account.orders')));
        } elseif ($type == 'order_return') {
            $this->breadcrumbs[] = $this->formatBreadcrumb($breadcrumbLib->getTrail('route', 'account.order_returns.index', front_trans('account.order_returns')));
        } elseif ($type == 'brand') {
            $this->breadcrumbs[] = $this->formatBreadcrumb($breadcrumbLib->getTrail('route', 'brands.index', front_trans('product.brand')));
        }

        $this->breadcrumbs[] = $this->formatBreadcrumb($breadcrumbLib->getTrail($type, $value, $title));

        $routes = [
            'products.index',
            'categories.slug_show',
            'categories.show',
            'brands.show',
            'brands.slug_show',
        ];

        if ($showFilter) {
            $this->showFilter = true;
        } elseif (in_array(pure_route_name(), $routes)) {
            $this->showFilter = true;
        }
    }

    /**
     * Format breadcrumb title with truncation
     *
     * @param  array  $breadcrumb
     * @return array
     */
    private function formatBreadcrumb(array $breadcrumb): array
    {
        $maxLength = 30;
        $title     = $breadcrumb['title'] ?? '';

        if (mb_strlen($title) > $maxLength) {
            $breadcrumb['display_title'] = mb_substr($title, 0, $maxLength).'...';
            $breadcrumb['full_title']    = $title;
        } else {
            $breadcrumb['display_title'] = $title;
        }

        return $breadcrumb;
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('components.breadcrumb');
    }
}
