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

class PriceInput extends Component
{
    public string $name;

    public string $title;

    public mixed $value;

    public bool $required;

    public string $placeholder;

    public string $addon;

    public string $error;

    public function __construct(
        string $name,
        string $title,
        $value = null,
        bool $required = false,
        string $placeholder = '',
        string $addon = '',
        string $error = ''
    ) {
        $this->name        = $name;
        $this->title       = $title;
        $this->value       = html_entity_decode((string) $value, ENT_QUOTES);
        $this->required    = $required;
        $this->placeholder = $placeholder;
        $this->addon       = $addon;
        $this->error       = $error;
    }

    public function render(): mixed
    {
        return view('panel::components.form.price-input');
    }
}
