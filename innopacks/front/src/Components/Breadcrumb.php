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

    /**
     * @param  $type
     * @param  $value
     * @param  string  $title
     * @throws Exception
     */
    public function __construct($type, $value, string $title = '')
    {
        $this->breadcrumbs[] = [
            'title' => trans('front::common.home'),
            'url'   => front_route('home.index'),
        ];

        $breadcrumbLib = BreadcrumbLib::getInstance();

        $accountRoutes = [
            'account.orders.index',
            'account.favorites.index',
            'account.addresses.index',
            'account.edit.index',
            'account.password.index',
        ];

        if ($type == 'order' || in_array($value, $accountRoutes)) {
            $this->breadcrumbs[] = $breadcrumbLib->getTrail('route', 'account.index', trans('front::account.account'));
        }

        if ($type == 'order') {
            $this->breadcrumbs[] = $breadcrumbLib->getTrail('route', 'account.orders.index', trans('front::account.orders'));
        }

        if ($type == 'brand') {
            $this->breadcrumbs[] = $breadcrumbLib->getTrail('route', 'brands.index', trans('front::product.brand'));
        }

        $this->breadcrumbs[] = $breadcrumbLib->getTrail($type, $value, $title);
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('components.breadcrumb');
    }
}
