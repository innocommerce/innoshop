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

class Image extends Component
{
    public string $name;

    public string $title;

    public string $type;

    public string $value;

    public string $description;

    public function __construct(string $name, ?string $title, ?string $value, ?string $description = '', string $type = 'common')
    {
        $this->name        = $name;
        $this->title       = $title       ?? '';
        $this->value       = $value       ?? '';
        $this->description = $description ?? '';
        $this->type        = $type;
    }

    public function render()
    {
        return view('panel::components.form.image');
    }
}
