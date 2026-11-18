<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Services\GeoLite2Service;

class UpdateGeoLite2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geolite2:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download/update the GeoLite2 mmdb database used for visit geo lookup';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $result = app(GeoLite2Service::class)->downloadDatabase();
        } catch (Exception $e) {
            Log::error('GeoLite2 download failed: '.$e->getMessage());
            $this->error('GeoLite2 download failed: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($result['success']) {
            $this->info('GeoLite2 database updated: '.$result['message']);

            return self::SUCCESS;
        }

        // Cron output goes nowhere, so every failure must also land in the log
        // to keep weekly refresh failures observable.
        Log::error('GeoLite2 download failed: '.$result['message']);
        $this->error('GeoLite2 download failed: '.$result['message']);

        return self::FAILURE;
    }
}
