<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ProductExporter\Commands;

use Illuminate\Console\Command;
use Plugin\ProductExporter\Repositories\ImportRepo;
use Rap2hpoutre\FastExcel\FastExcel;
use SebastianBergmann\Timer\Timer;
use Throwable;

class ProductImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exporter:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Product';

    /**
     * @return void
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->info('====== Start ======');
        $bench = new \Ubench;
        $bench->start();

        $timer = new Timer;
        $timer->start();

        try {
            $excelFile = plugin_path('ProductExporter/Storage/products.xlsx');
            $excelData = (new FastExcel)->importSheets($excelFile);

            ImportRepo::getInstance(true)->importSheets($excelData);
        } catch (\Exception $e) {
            dump($e->getMessage());
        }

        $duration = $timer->stop();
        $bench->end();
        $this->info('Timer: '.$duration->asString().' | '.$duration->asSeconds());
        $this->info('Bench: '.$bench->getTime(true).' | '.$bench->getMemoryUsage());
    }
}
