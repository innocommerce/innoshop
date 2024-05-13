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
use InnoShop\Common\Models\Country;
use InnoShop\Common\Repositories\CountryRepo;

class UpdateCountries extends Command
{
    const API_URL = 'https://api.first.org/data/v1/countries?limit=300';

    protected $signature = 'country:update';

    protected $description = 'Update countries from api.first.org';

    /**
     * Get countries from below:
     * https://api.first.org/data/v1/countries?limit=300
     * https://raw.gitcode.com/dr5hn/countries-states-cities-database/raw/master/countries.json
     *
     * @throws \Throwable
     */
    public function handle(): void
    {
        $body   = Http::get(self::API_URL)->body();
        $result = json_decode($body, true);
        if ($result['status'] != 'OK' || $result['status-code'] != 200) {
            throw new \Exception('Something wrong');
        }
        if (empty($result['data'])) {
            throw new \Exception('Empty country data');
        }

        Country::query()->truncate();
        $countries = [];
        foreach ($result['data'] as $code => $item) {
            $countries[] = [
                'code'      => $code,
                'name'      => $item['country'],
                'continent' => $item['region'],
            ];
        }
        CountryRepo::getInstance()->createMany($countries);
    }
}
