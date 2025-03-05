<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReviewImporter;

class Boot
{
    /**
     * @return void
     */
    public function init(): void
    {
        listen_blade_insert('panel.reviews.list.buttons', function ($data) {
            return view('ReviewImporter::import_button');
        });
    }
}
