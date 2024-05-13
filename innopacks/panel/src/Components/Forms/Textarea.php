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

class Textarea extends Component
{
    public string $name;

    public string $title;

    public string $value;

    public bool $required;

    public function __construct(string $name, string $title, ?string $value, bool $required = false)
    {
        $this->name     = $name;
        $this->title    = $title;
        $this->value    = html_entity_decode($value, ENT_QUOTES);
        $this->required = $required;
    }

    public function render()
    {
        return view('panel::components.form.textarea');
    }
}
