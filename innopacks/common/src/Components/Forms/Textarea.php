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

class Textarea extends Component
{
    public string $name;

    public string $title;

    public bool $required;

    public mixed $value;

    public string $description;

    public bool $multiple;

    public string $column;

    public bool $generate;

    public bool $translate;

    public string $localeCode;

    public function __construct(string $name, string $title, mixed $value = null, string $description = '', bool $required = false,
        bool $multiple = false, string $column = '', bool $generate = false, bool $translate = false, string $localeCode = '')
    {
        if (! $multiple) {
            $value = html_entity_decode($value, ENT_QUOTES);
        }

        $this->name        = $name;
        $this->title       = $title;
        $this->value       = $value;
        $this->description = $description;
        $this->required    = $required;
        $this->multiple    = $multiple;
        $this->column      = $column;
        $this->generate    = $generate;
        $this->translate   = $translate && has_translator();
        $this->localeCode  = $localeCode;
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('common::components.form.textarea');
    }
}
