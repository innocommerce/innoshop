<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SubNews\PanelControllers;

use InnoShop\Panel\Controllers\BaseController;
use Plugin\SubNews\Models\SubMail;

class SubMailController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $data = [
            'mails' => SubMail::query()->orderByDesc('id')->paginate(),
        ];

        return view('SubNews::panel.sub_mails_index', $data);
    }
}
