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

class Images extends Component
{
    public string $name;

    public string $title;

    public string $type;

    public array $values;

    public int $max;

    public function __construct(string $name, ?string $title, int $max = 0, string $type = 'common', $values = [])
    {
        $this->name   = $name;
        $this->values = $values;
        $this->max    = $max;
        $this->type   = $type;
        $this->title  = $title ?? '';
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('panel::components.form.images');
    }
}
