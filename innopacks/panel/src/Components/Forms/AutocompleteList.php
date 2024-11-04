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

class AutocompleteList extends Component
{
    public string $name;

    public string $title;

    public array $value;

    public string $api;

    public bool $required;

    public string $placeholder;

    /**
     * Create a new component instance.
     */
    public function __construct(string $name, array $value, string $api, string $placeholder = '请搜索', bool $required = false, string $title = '搜索结果')
    {
        $this->name        = $name;
        $this->value       = $value;
        $this->title       = $title;
        $this->api         = $api;
        $this->placeholder = $placeholder;
        $this->required    = $required;
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        $data['id'] = str_replace(['[', ']'], '', $this->name);

        return view('panel::components.form.autocomplete-list', $data);
    }
}
