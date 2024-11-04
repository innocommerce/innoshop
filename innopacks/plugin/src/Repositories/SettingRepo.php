<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Repositories;

use InnoShop\Common\Repositories\SettingRepo as CommonSettingRepo;
use InnoShop\Plugin\Models\Setting;

class SettingRepo extends CommonSettingRepo
{
    /**
     * Get plugin active field.
     *
     * @return array
     */
    public function getPluginActiveField(): array
    {
        return [
            'name'     => 'active',
            'label'    => panel_trans('common.status'),
            'type'     => 'bool',
            'required' => true,
        ];
    }

    /**
     * Get billing plugin available field.
     *
     * @return array
     */
    public function getPluginAvailableField(): array
    {
        return [
            'name'    => 'available',
            'label'   => panel_trans('common.available'),
            'type'    => 'checkbox',
            'options' => [
                ['label' => 'PC WEB', 'value' => 'pc_web'],
                ['label' => 'Mobile Web', 'value' => 'mobile_web'],
                ['label' => 'WeChat Mini', 'value' => 'wechat_mini'],
                ['label' => 'WeChat Official', 'value' => 'wechat_official'],
                ['label' => 'APP', 'value' => 'app'],
            ],
            'required' => true,
            'rules'    => 'required',
        ];
    }

    /**
     * Get all fields by plugin code.
     *
     * @param  $pluginCode
     * @return mixed
     */
    public function getPluginFields($pluginCode): mixed
    {
        return Setting::query()
            ->where('space', $pluginCode)
            ->get()
            ->keyBy('name');
    }
}
