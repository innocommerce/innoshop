<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Feature;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Issue #368 — the panel route file registered plain `Route::resource()` calls
 * while a number of panel controllers do not implement every standard action
 * (ProductController has no `show()`, OrderController has no `store()`, …).
 * Those URLs used to blow up with a 500 ("Method … does not exist") instead of
 * a plain 404.
 *
 * The resource routes are now trimmed to the actions the controller really
 * implements. This test pins that contract down:
 *  - every registered panel route must point at a method that exists,
 *  - the removed actions must not be registered any more,
 *  - the URLs of the removed actions must never resolve to a missing method,
 *  - the actions the controllers do implement must stay registered.
 */
class PanelResourceRouteActionsTest extends BaseTestCase
{
    /**
     * The removed actions: route name => "METHOD|uri".
     *
     * @var array<string, string>
     */
    private const REMOVED_ROUTES = [
        'panel.orders.create'           => 'GET|orders/create',
        'panel.orders.store'            => 'POST|orders',
        'panel.orders.update'           => 'PUT|orders/1',
        'panel.order_returns.show'      => 'GET|order_returns/1',
        'panel.products.show'           => 'GET|products/1',
        'panel.categories.show'         => 'GET|categories/1',
        'panel.attribute_groups.create' => 'GET|attribute_groups/create',
        'panel.attribute_groups.edit'   => 'GET|attribute_groups/1/edit',
        'panel.attributes.show'         => 'GET|attributes/1',
        'panel.attribute_values.index'  => 'GET|attribute_values',
        'panel.attribute_values.create' => 'GET|attribute_values/create',
        'panel.attribute_values.show'   => 'GET|attribute_values/1',
        'panel.attribute_values.edit'   => 'GET|attribute_values/1/edit',
        'panel.option_values.create'    => 'GET|option_values/create',
        'panel.option_values.edit'      => 'GET|option_values/1/edit',
        'panel.reviews.show'            => 'GET|reviews/1',
        'panel.articles.show'           => 'GET|articles/1',
        'panel.catalogs.show'           => 'GET|catalogs/1',
        'panel.tags.show'               => 'GET|tags/1',
        'panel.announcements.show'      => 'GET|announcements/1',
        'panel.pages.show'              => 'GET|pages/1',
        'panel.customers.show'          => 'GET|customers/1',
        'panel.customer_groups.show'    => 'GET|customer_groups/1',
        'panel.weight_classes.show'     => 'GET|weight_classes/1',
    ];

    /**
     * Route names of the actions the controllers do implement — they must all
     * stay registered so that the panel keeps working.
     *
     * @var array<int, string>
     */
    private const RETAINED_ROUTES = [
        'panel.orders.index',
        'panel.orders.show',
        'panel.orders.edit',
        'panel.orders.destroy',
        'panel.order_returns.index',
        'panel.order_returns.create',
        'panel.order_returns.store',
        'panel.order_returns.edit',
        'panel.order_returns.update',
        'panel.order_returns.destroy',
        'panel.products.index',
        'panel.products.create',
        'panel.products.store',
        'panel.products.edit',
        'panel.products.update',
        'panel.products.destroy',
        'panel.categories.index',
        'panel.categories.create',
        'panel.categories.store',
        'panel.categories.edit',
        'panel.categories.update',
        'panel.categories.destroy',
        'panel.attribute_groups.index',
        'panel.attribute_groups.show',
        'panel.attribute_groups.store',
        'panel.attribute_groups.update',
        'panel.attribute_groups.destroy',
        'panel.attributes.index',
        'panel.attributes.create',
        'panel.attributes.store',
        'panel.attributes.edit',
        'panel.attributes.update',
        'panel.attributes.destroy',
        'panel.attribute_values.store',
        'panel.attribute_values.update',
        'panel.attribute_values.destroy',
        'panel.options.index',
        'panel.options.create',
        'panel.options.store',
        'panel.options.show',
        'panel.options.edit',
        'panel.options.update',
        'panel.options.destroy',
        'panel.option_values.index',
        'panel.option_values.store',
        'panel.option_values.show',
        'panel.option_values.update',
        'panel.option_values.destroy',
        'panel.brands.index',
        'panel.brands.create',
        'panel.brands.store',
        'panel.brands.show',
        'panel.brands.edit',
        'panel.brands.update',
        'panel.brands.destroy',
        'panel.reviews.index',
        'panel.reviews.create',
        'panel.reviews.store',
        'panel.reviews.edit',
        'panel.reviews.update',
        'panel.reviews.destroy',
        'panel.articles.index',
        'panel.articles.create',
        'panel.articles.store',
        'panel.articles.edit',
        'panel.articles.update',
        'panel.articles.destroy',
        'panel.catalogs.index',
        'panel.catalogs.create',
        'panel.catalogs.store',
        'panel.catalogs.edit',
        'panel.catalogs.update',
        'panel.catalogs.destroy',
        'panel.tags.index',
        'panel.tags.create',
        'panel.tags.store',
        'panel.tags.edit',
        'panel.tags.update',
        'panel.tags.destroy',
        'panel.announcements.index',
        'panel.announcements.create',
        'panel.announcements.store',
        'panel.announcements.edit',
        'panel.announcements.update',
        'panel.announcements.destroy',
        'panel.pages.index',
        'panel.pages.create',
        'panel.pages.store',
        'panel.pages.edit',
        'panel.pages.update',
        'panel.pages.destroy',
        'panel.customers.index',
        'panel.customers.create',
        'panel.customers.store',
        'panel.customers.edit',
        'panel.customers.update',
        'panel.customers.destroy',
        'panel.customer_groups.index',
        'panel.customer_groups.create',
        'panel.customer_groups.store',
        'panel.customer_groups.edit',
        'panel.customer_groups.update',
        'panel.customer_groups.destroy',
        'panel.admins.index',
        'panel.admins.create',
        'panel.admins.store',
        'panel.admins.show',
        'panel.admins.edit',
        'panel.admins.update',
        'panel.admins.destroy',
        'panel.roles.index',
        'panel.roles.create',
        'panel.roles.store',
        'panel.roles.show',
        'panel.roles.edit',
        'panel.roles.update',
        'panel.roles.destroy',
        'panel.currencies.index',
        'panel.currencies.store',
        'panel.currencies.show',
        'panel.currencies.update',
        'panel.currencies.destroy',
        'panel.countries.index',
        'panel.countries.create',
        'panel.countries.store',
        'panel.countries.show',
        'panel.countries.edit',
        'panel.countries.update',
        'panel.countries.destroy',
        'panel.states.index',
        'panel.states.create',
        'panel.states.store',
        'panel.states.show',
        'panel.states.edit',
        'panel.states.update',
        'panel.states.destroy',
        'panel.regions.index',
        'panel.regions.create',
        'panel.regions.store',
        'panel.regions.show',
        'panel.regions.edit',
        'panel.regions.update',
        'panel.regions.destroy',
        'panel.tax_classes.index',
        'panel.tax_classes.create',
        'panel.tax_classes.store',
        'panel.tax_classes.show',
        'panel.tax_classes.edit',
        'panel.tax_classes.update',
        'panel.tax_classes.destroy',
        'panel.tax_rates.index',
        'panel.tax_rates.create',
        'panel.tax_rates.store',
        'panel.tax_rates.show',
        'panel.tax_rates.edit',
        'panel.tax_rates.update',
        'panel.tax_rates.destroy',
        'panel.weight_classes.index',
        'panel.weight_classes.create',
        'panel.weight_classes.store',
        'panel.weight_classes.edit',
        'panel.weight_classes.update',
        'panel.weight_classes.destroy',
        'panel.visits.index',
        'panel.visits.show',
        'panel.variant_templates.index',
        'panel.variant_templates.store',
        'panel.variant_templates.show',
        'panel.variant_templates.destroy',
    ];

    /**
     * Every panel route must point at a controller method that really exists,
     * otherwise the URL ends up in a 500 instead of a 404.
     */
    #[Test]
    public function every_panel_route_targets_an_existing_controller_method(): void
    {
        $missing = [];

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();

            if (! str_contains($action, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $action);

            if (! str_starts_with($class, 'InnoShop\\Panel\\Controllers\\')) {
                continue;
            }

            if (! class_exists($class) || ! method_exists($class, $method)) {
                $missing[] = $action;
            }
        }

        $this->assertSame([], $missing, 'panel routes must not target missing controller methods');
    }

    #[Test]
    public function unimplemented_resource_actions_are_not_registered(): void
    {
        foreach (array_keys(self::REMOVED_ROUTES) as $name) {
            $this->assertFalse(Route::has($name), $name.' must not be registered');
        }
    }

    #[Test]
    public function implemented_resource_actions_are_still_registered(): void
    {
        foreach (self::RETAINED_ROUTES as $name) {
            $this->assertTrue(Route::has($name), $name.' must still be registered');
        }
    }

    /**
     * The URL of a removed action must never be routed to a controller method
     * that does not exist — that is exactly what turned the URL into a 500.
     * It either matches no route at all (404) or falls back to a route that
     * does exist.
     */
    #[Test]
    public function removed_resource_urls_never_reach_a_missing_controller_method(): void
    {
        foreach (self::REMOVED_ROUTES as $name => $definition) {
            [$method, $uri] = explode('|', $definition);

            try {
                $route = Route::getRoutes()->match(Request::create('/'.panel_name().'/'.$uri, $method));
            } catch (NotFoundHttpException|MethodNotAllowedHttpException) {
                // No route (or no route for that verb) any more — the URL 404s.
                continue;
            }

            [$class, $action] = explode('@', $route->getActionName());

            $this->assertTrue(
                class_exists($class) && method_exists($class, $action),
                $name.' must not be routed to a missing controller method'
            );
        }
    }
}
