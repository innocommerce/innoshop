<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Libraries;

class Breadcrumb
{
    public static function getInstance(): Breadcrumb
    {
        return new self;
    }

    /**
     * @param  $type
     * @param  $object
     * @param  string  $title
     * @return array
     */
    public function getTrail($type, $object, string $title = ''): array
    {
        if (in_array($type, ['category', 'product', 'catalog', 'article', 'page', 'tag'])) {
            return [
                'title' => $object->translation->name ?: $object->translation->title,
                'url'   => $object->url,
            ];
        } elseif ($type == 'brand') {
            return [
                'title' => $object->name,
                'url'   => $object->url,
            ];
        } elseif ($type == 'order') {
            return [
                'title' => $object->number,
                'url'   => account_route('orders.show', ['order' => $object->id]),
            ];
        } elseif ($type == 'route') {
            if (empty($title)) {
                $title = front_trans('common.'.str_replace('.', '_', $object));
            }

            return [
                'title' => $title,
                'url'   => front_route($object),
            ];
        } elseif ($type == 'static') {
            return [
                'title' => $title,
                'url'   => $object,
            ];
        }

        return [];
    }
}
