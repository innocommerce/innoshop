<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Components\Forms;

use Illuminate\View\Component;

class LocaleModal extends Component
{
    public string $modalId;

    public string $inputPrefix;

    public string $title;

    public function __construct(
        string $modalId = 'localeModal',
        string $inputPrefix = 'locale-input',
        string $title = '',
    ) {
        $this->modalId     = $modalId;
        $this->inputPrefix = $inputPrefix;
        $this->title       = $title;
    }

    public function render(): mixed
    {
        return view('common::components.form.locale-modal');
    }
}
