<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BaseModel extends Model
{
    /**
     * Build a tree from a flat collection.
     *
     * @return Collection
     */
    public static function tree(): Collection
    {
        $items     = static::all();
        $tree      = collect();
        $itemsById = $items->keyBy('id');

        foreach ($items as $item) {
            if (isset($itemsById[$item->parent_id])) {
                $itemsById[$item->parent_id]->children->push($item);
            } else {
                $tree->push($item);
            }
            $item->children = collect();
        }

        return $tree;
    }
}
