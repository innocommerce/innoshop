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

class RichText extends Component
{
    public ?string $name;

    public ?string $title;

    public mixed $value;

    public bool $required;

    public bool $multiple;

    public bool $generate;

    public bool $translate;

    public ?string $elID;

    public function __construct(string $name, string $title = '', bool $required = false, $value = null, bool $multiple = false, string $elID = '', $generate = false, bool $translate = false)
    {
        if (! $multiple) {
            $value = html_entity_decode($value, ENT_QUOTES);
        }

        $this->name      = $name;
        $this->title     = $title;
        $this->value     = $value;
        $this->required  = $required;
        $this->multiple  = $multiple;
        $this->elID      = $elID;
        $this->generate  = $generate;
        $this->translate = $translate && has_translator();
    }

    public function render()
    {
        return view('common::components.form.rich-text');
    }
}
