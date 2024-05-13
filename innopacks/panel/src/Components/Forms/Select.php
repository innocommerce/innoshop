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

class Select extends Component
{
    public string $name;

    public bool $required;

    public string $value;

    public string $title;

    public array $options;

    public string $key;

    public string $label;

    public function __construct(string $name, string $value, string $title, array $options, ?string $key = 'value', ?string $label = 'label', ?bool $required = false)
    {
        $this->name     = $name;
        $this->title    = $title;
        $this->value    = $value;
        $this->options  = $options;
        $this->key      = $key;
        $this->label    = $label;
        $this->required = $required;
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('panel::components.form.select');
    }
}
