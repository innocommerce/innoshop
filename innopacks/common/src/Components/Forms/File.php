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

class File extends Component
{
    public string $name;

    public string $title;

    public string $type;

    public string $fileType;

    public string $value;

    public string $description;

    public function __construct(string $name, ?string $title, ?string $value, ?string $description = '', string $type = 'common', string $fileType = 'zip')
    {
        $this->name        = $name;
        $this->title       = $title       ?? '';
        $this->value       = $value       ?? '';
        $this->description = $description ?? '';
        $this->type        = $type;
        $this->fileType    = $fileType;
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {

        $data['accept'] = match ($this->fileType) {
            'zip'   => '.zip',
            'pdf'   => '.pdf',
            'doc'   => '.doc, .docx',
            'xls'   => '.xls, .xlsx',
            'ppt'   => '.ppt, .pptx',
            'img'   => '.jpg, .jpeg, .png, .gif',
            default => '.zip',
        };

        return view('panel::components.form.file', $data);
    }
}
