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
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Services\GeoLite2Service;
use InnoShop\Common\Services\VisitEnrichService;

class BackfillVisitGeo extends Command
{
    protected $signature = 'visits:backfill-geo
                            {--limit=100000 : 处理记录上限}
                            {--delay=0 : 每批次之间暂停的毫秒数}
                            {--dry-run : 只统计,不实际写入}
                            {--skip-prune : 不删除 127.0.0.1/::1/空 IP 的本地记录}
                            {--skip-reset : 不重置空字符串字段为 NULL}';

    protected $description = '为 visits 表批量补全 country/browser/os (先删本地 IP,再重置空字符串,最后补全; city 字段不处理)';

    public function handle(): int
    {
        $geo = new GeoLite2Service;
        if (! $geo->isAvailable()) {
            $this->error('GeoLite2 mmdb 文件未安装,请先在 Panel → 系统设置 → 下载 GeoLite2 数据库');

            return self::FAILURE;
        }

        $info = $geo->getDatabaseInfo();
        $this->info('GeoLite2 database: '.$info['path']);
        $this->info('  size: '.$info['size_formatted'].'  modified: '.$info['modified_formatted']);
        $this->newLine();

        $limit     = (int) $this->option('limit');
        $delayMs   = (int) $this->option('delay');
        $dryRun    = (bool) $this->option('dry-run');
        $skipPrune = (bool) $this->option('skip-prune');
        $skipReset = (bool) $this->option('skip-reset');

        $totalBefore = Visit::count();
        $this->info("数据库总记录: {$totalBefore}");

        if (! $skipPrune) {
            $localQuery = function () {
                return Visit::where(function ($q) {
                    $q->whereNull('ip_address')
                        ->orWhere('ip_address', '')
                        ->orWhere('ip_address', '127.0.0.1')
                        ->orWhere('ip_address', '::1')
                        ->orWhere('ip_address', 'like', '::ffff:127.%')
                        ->orWhere('ip_address', 'like', '127.%');
                });
            };
            $localCount = $localQuery()->count();
            $this->info("本地/无效 IP 记录 (127.x/::1/空): {$localCount}");
            if ($dryRun) {
                $this->warn('  DRY RUN: 不删除');
            } elseif ($localCount > 0) {
                $localQuery()->delete();
                $this->info("  已删除: {$localCount} 条");
            }
        }

        if (! $skipReset) {
            $emptyCounts = [
                'country_code' => Visit::where('country_code', '')->count(),
                'country_name' => Visit::where('country_name', '')->count(),
                'browser'      => Visit::where('browser', '')->count(),
                'os'           => Visit::where('os', '')->count(),
            ];
            $emptyTotal = array_sum($emptyCounts);
            $this->info("空字符串字段总计: {$emptyTotal} 个 ".json_encode($emptyCounts, JSON_UNESCAPED_UNICODE));
            if ($dryRun) {
                $this->warn('  DRY RUN: 不重置');
            } elseif ($emptyTotal > 0) {
                foreach (array_keys($emptyCounts) as $field) {
                    Visit::where($field, '')->update([$field => null]);
                }
                $this->info("  已重置: {$emptyTotal} 个字段 → NULL");
            }
        }
        $this->newLine();

        $pendingQuery = function () {
            return Visit::where(function ($q) {
                $q->whereNull('country_name')
                    ->orWhereNull('browser')
                    ->orWhereNull('os');
            });
        };

        $pending = $pendingQuery()->count();
        if ($pending === 0) {
            $this->info('没有需要补全的记录。');

            return self::SUCCESS;
        }

        $this->info("待补全记录: {$pending}");
        $this->info('处理上限: '.min($pending, $limit));
        if ($dryRun) {
            $this->warn('DRY RUN: 不会写入数据库');
            $this->info('预计轮数: '.ceil(min($pending, $limit) / 500).' (每轮 500 条)');

            return self::SUCCESS;
        }
        $this->newLine();

        $maxRounds    = (int) ceil($limit / 500) + 1;
        $totalUpdated = 0;
        $rounds       = 0;
        $bar          = $this->output->createProgressBar(min($pending, $limit));
        $bar->start();

        $enrich = new VisitEnrichService;

        while ($totalUpdated < $limit && $rounds < $maxRounds) {
            $result  = $enrich->batchLocate();
            $updated = (int) ($result['updated'] ?? 0);
            $totalUpdated += $updated;
            $rounds++;

            $bar->advance($updated);

            if ($updated === 0) {
                break;
            }

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        $bar->finish();
        $this->newLine();
        $this->newLine();

        $stillRemaining = $pendingQuery()->count();

        $this->info("处理轮数: {$rounds}");
        $this->info("本次更新: {$totalUpdated} 条");

        if ($stillRemaining > 0) {
            $this->warn("仍为空: {$stillRemaining} 条 (mmdb 没有这些 IP 的地理数据,例如未收录的公网 IP)");
        } else {
            $this->info('所有记录地理信息已补全。');
        }

        $totalAfter = Visit::count();
        $this->info("最终数据库记录: {$totalAfter} (".($totalBefore - $totalAfter).' 条已删除)');

        return self::SUCCESS;
    }
}
