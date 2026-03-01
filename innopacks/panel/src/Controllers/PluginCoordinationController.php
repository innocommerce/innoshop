<?php

/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\Request;
use InnoShop\Common\Services\PluginCoordinationService;

class PluginCoordinationController extends BaseController
{
    /**
     * Display plugin coordination settings
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $coordinationService = app(PluginCoordinationService::class);
        $pluginManager       = app('plugin');

        $types   = ['price', 'orderfee'];
        $configs = [];

        foreach ($types as $type) {
            $config  = $coordinationService->getConfig($type);
            $plugins = $pluginManager->getPlugins()->filter(function ($plugin) use ($type) {
                return $plugin->getType() === $type;
            })->map(function ($plugin) {
                return (object) [
                    'code' => $plugin->getCode(),
                    'name' => $plugin->getLocaleName(),
                ];
            })->values();

            $configs[$type] = [
                'config'          => $config,
                'plugins'         => $plugins,
                'sort_order'      => $config ? $config->getSortOrder() : [],
                'exclusive_mode'  => $config ? $config->getExclusiveMode() : 'all_stack',
                'exclusive_pairs' => $config ? $config->getExclusivePairs() : [],
            ];
        }

        $data = [
            'configs' => $configs,
            'types'   => [
                'price'    => trans('panel/plugin_coordination.price'),
                'orderfee' => trans('panel/plugin_coordination.orderfee'),
            ],
            'exclusive_modes' => [
                'first_only' => trans('panel/plugin_coordination.exclusive_mode_first_only'),
                'all_stack'  => trans('panel/plugin_coordination.exclusive_mode_all_stack'),
                'custom'     => trans('panel/plugin_coordination.exclusive_mode_custom'),
            ],
        ];

        return view('panel::plugin_coordination.index', $data);
    }

    /**
     * Update plugin coordination settings
     *
     * @param  Request  $request
     * @return mixed
     */
    public function update(Request $request): mixed
    {
        $request->validate([
            'type'              => 'required|in:price,orderfee',
            'sort_order'        => 'array',
            'sort_order.*'      => 'string',
            'exclusive_mode'    => 'required|in:first_only,all_stack,custom',
            'exclusive_pairs'   => 'array',
            'exclusive_pairs.*' => 'array',
        ]);

        try {
            $coordinationService = app(PluginCoordinationService::class);

            $coordinationService->updateConfig($request->input('type'), [
                'sort_order'      => $request->input('sort_order', []),
                'exclusive_mode'  => $request->input('exclusive_mode'),
                'exclusive_pairs' => $request->input('exclusive_pairs', []),
            ]);

            $coordinationService->clearCache($request->input('type'));

            return redirect()->back()
                ->with('success', trans('common/base.updated_success'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }
}
