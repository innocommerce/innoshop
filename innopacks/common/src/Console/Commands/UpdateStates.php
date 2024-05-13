<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use InnoShop\Common\Models\State;
use InnoShop\Common\Repositories\StateRepo;

class UpdateStates extends Command
{
    const API_URL = 'https://raw.gitcode.com/dr5hn/countries-states-cities-database/raw/master/states.json';

    protected $signature = 'state:update';

    protected $description = 'Update countries from https://gitcode.com/dr5hn/countries-states-cities-database';

    /**
     * https://api.first.org/data/v1/countries?limit=300
     *
     * @throws \Throwable
     */
    public function handle(): void
    {
        $body   = Http::get(self::API_URL)->body();
        $result = json_decode($body, true);
        if (empty($result)) {
            return;
        }

        State::query()->truncate();
        $states = [];
        foreach ($result as $item) {
            $states[] = [
                'name'         => $item['name'],
                'country_code' => $item['country_code'],
                'code'         => $item['state_code'],
            ];
        }
        StateRepo::getInstance()->createMany($states);
    }
}
