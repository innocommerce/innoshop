<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Commands;

use Illuminate\Console\Command;
use InnoShop\Front\Services\LlmsService;
use InnoShop\Front\Services\RobotsService;

class GenerateSiteTxtCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:generate-txt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate robots.txt and llms.txt with the current environment URLs';

    /**
     * robots.txt and llms.txt embed absolute URLs of the current environment,
     * so they must be regenerated per environment instead of being committed.
     */
    public function handle(): int
    {
        RobotsService::getInstance()->writeFile();
        $this->info('robots.txt generated.');

        LlmsService::getInstance()->writeFile();
        $this->info('llms.txt generated.');

        return self::SUCCESS;
    }
}
