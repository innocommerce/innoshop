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

class LocaleInput extends Component
{
    public string $name;

    public string $type;

    public array $translations;

    public bool $required;

    public string $placeholder;

    public string $label;

    public int $rows;

    public string $nameFormat;

    public string $description;

    public bool $generate;

    public string $column;

    public bool $translate;

    public function __construct(
        string $name = '',
        string $type = 'input',
        array $translations = [],
        bool $required = false,
        string $placeholder = '',
        string $label = '',
        int $rows = 4,
        string $nameFormat = 'translations',
        string $description = '',
        bool $generate = false,
        string $column = '',
        bool $translate = true,
    ) {
        $this->name         = $name;
        $this->type         = $type;
        $this->translations = $translations;
        $this->required     = $required;
        $this->placeholder  = $placeholder;
        $this->label        = $label;
        $this->rows         = $rows;
        $this->nameFormat   = $nameFormat;
        $this->description  = $description;
        $this->generate     = $generate;
        $this->column       = $column;
        $this->translate    = $translate && has_translator();
    }

    public function render(): mixed
    {
        return view('common::components.form.locale-input');
    }
}
