<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PluginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @throws \Exception
     */
    public function toArray(Request $request): array
    {
        return [
            'code'        => $this->getCode(),
            'name'        => $this->getLocaleName(),
            'description' => $this->getLocaleDescription(),
            'path'        => $this->getPath(),
            'version'     => $this->getVersion(),
            'priority'    => $this->getPriority(),
            'dir_name'    => $this->getDirName(),
            'type'        => $this->getType(),
            'author'      => $this->getAuthor(),
            'enabled'     => $this->getEnabled(),
            'installed'   => $this->checkInstalled(),
            'edit_url'    => $this->getEditUrl(),
            'icon'        => plugin_resize($this->getCode(), $this->getIcon()),
            'type_format' => panel_trans('plugin.'.$this->getType()),
        ];
    }
}
