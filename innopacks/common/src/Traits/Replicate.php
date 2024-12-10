<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Traits;

trait Replicate
{
    public function deepReplicate(?array $except = null)
    {
        $copy = parent::replicate($except);
        $copy->push();

        foreach ($this->getRelations() as $relation => $entries) {
            foreach ($entries as $entry) {
                $newEntry = $entry->replicate();
                if ($newEntry->push()) {
                    $copy->{$relation}()->save($copy);
                }
            }
        }

        return $copy;
    }
}
