<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Panel - Settings')]
class SettingController extends BaseController
{
    #[Endpoint('Get all settings grouped')]
    public function index(): mixed
    {
        try {
            $settings = SettingRepo::getInstance()->groupedSettings();

            return read_json_success($settings);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Update settings')]
    public function update(Request $request): mixed
    {
        try {
            $settings = $request->all();
            SettingRepo::getInstance()->updateValues($settings);

            return update_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
