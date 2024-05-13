<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Components\Forms;

use Illuminate\View\Component;

class LangTab extends Component
{
    // public string $name;

    // public function __construct(string $name, ?string $title, ?string $value, ?string $description = '')
    public function __construct()
    {
        // $this->name        = $name;
    }

    public function render()
    {
        $data['locales'] = [
            'zh' => '中文',
            'en' => '英文',
        ];

        $data['id'] = substr(md5(time()), 0, 6);

        return view('panel::components.form.lang-tab', $data);
    }
}
