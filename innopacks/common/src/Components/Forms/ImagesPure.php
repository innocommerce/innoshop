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

class ImagesPure extends Component
{
    public string $name;

    public string $title;

    public string $type;

    public array $values;

    public string $description;

    public int $max;

    public function __construct(string $name, ?string $title, ?array $values = [], ?string $description = '', string $type = 'common', int $max = 0)
    {
        $this->name        = $name;
        $this->title       = $title       ?? '';
        $this->values      = $values      ?? [];
        $this->description = $description ?? '';
        $this->type        = $type;
        $this->max         = $max;
    }

    public function render()
    {
        return view('panel::components.form.imagesp');
    }
}
