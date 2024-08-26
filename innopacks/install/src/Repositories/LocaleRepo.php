<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Repositories;

use Exception;
use InnoShop\Panel\Repositories\BaseRepo;

class LocaleRepo extends BaseRepo
{
    /**
     * Get panel languages by path.
     *
     * @throws Exception
     */
    public function getInstallLanguages(): array
    {
        $items = [];
        foreach (panel_lang_path_codes() as $localeCode) {
            $langFile = lang_path("/$localeCode/panel/base.php");
            if (! is_file($langFile)) {
                throw new Exception("File ($langFile) not exist!");
            }
            $baseData = require $langFile;
            $name     = $baseData['name'] ?? $localeCode;
            $items[]  = [
                'code'  => $localeCode,
                'name'  => $name,
                'image' => "images/flag/$localeCode.png",
            ];
        }

        return $items;
    }
}
