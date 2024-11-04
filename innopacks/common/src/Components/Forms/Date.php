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

class Date extends Component
{
    public string $name;

    public string $title;

    public string $value;

    public string $error;

    public string $placeholder;

    public string $description;

    public string $type;

    public bool $required;

    public bool $disabled;

    public function __construct(string $name, string $title, ?string $value, bool $required = false, string $error = '', string $type = 'date', string $placeholder = '', string $description = '', bool $disabled = false)
    {
        $this->name        = $name;
        $this->title       = $title;
        $this->value       = html_entity_decode($value, ENT_QUOTES);
        $this->error       = $error;
        $this->placeholder = $placeholder;
        $this->type        = $type;
        $this->required    = $required;
        $this->description = $description;
        $this->disabled    = $disabled;
    }

    public function render()
    {
        return view('panel::components.form.date');
    }
}
