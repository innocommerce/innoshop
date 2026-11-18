<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Issue #368 — HTTP level companion of PanelResourceRouteActionsTest.
 *
 * The URLs of the removed resource actions used to blow up with a 500
 * ("Method … does not exist"). They must now answer "not found" instead:
 *
 *  - 404 when the whole URI disappeared (the `edit` actions),
 *  - 405 when the URI is still served for another verb (the `show` actions —
 *    the same `{model}` uri is still used by `update` / `destroy`).
 *
 * Either way the request must never end up as a 500.
 */
class PanelRemovedResourceUrlHttpTest extends TestCase
{
    /**
     * Route name => "METHOD|uri|expected status".
     *
     * @var array<string, string>
     */
    private const REMOVED_URLS = [
        'panel.attribute_groups.edit' => 'GET|attribute_groups/1/edit|404',
        'panel.attribute_values.edit' => 'GET|attribute_values/1/edit|404',
        'panel.option_values.edit'    => 'GET|option_values/1/edit|404',
        'panel.products.show'         => 'GET|products/1|405',
        'panel.categories.show'       => 'GET|categories/1|405',
        'panel.customers.show'        => 'GET|customers/1|405',
        'panel.reviews.show'          => 'GET|reviews/1|405',
        'panel.orders.store'          => 'POST|orders|405',
        'panel.orders.update'         => 'PUT|orders/1|405',
    ];

    #[Test]
    public function removed_resource_urls_are_not_found_instead_of_broken(): void
    {
        foreach (self::REMOVED_URLS as $name => $definition) {
            [$method, $uri, $expected] = explode('|', $definition);

            $response = $this->call($method, '/'.panel_name().'/'.$uri);

            $this->assertSame((int) $expected, $response->getStatusCode(), $name.' must answer '.$expected);
        }
    }
}
