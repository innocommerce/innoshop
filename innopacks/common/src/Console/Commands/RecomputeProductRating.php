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
use Illuminate\Support\Facades\DB;

class RecomputeProductRating extends Command
{
    protected $signature = 'products:recompute-rating
                            {--product_id= : Recompute only one product}';

    protected $description = 'Recompute products.rating and products.reviews_count from reviews table (active only)';

    public function handle(): int
    {
        $productId = $this->option('product_id');

        $query = DB::table('products')->when($productId, function ($q) use ($productId) {
            return $q->where('id', $productId);
        });

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('No products to process.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->orderBy('id')->chunk(500, function ($products) use ($bar) {
            foreach ($products as $product) {
                $stats = DB::table('reviews')
                    ->where('product_id', $product->id)
                    ->where('active', 1)
                    ->selectRaw('COALESCE(AVG(rating), 0) AS avg_rating, COUNT(*) AS cnt')
                    ->first();

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'rating'        => round((float) $stats->avg_rating, 2),
                        'reviews_count' => (int) $stats->cnt,
                    ]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Done. Recomputed {$total} product(s).");

        return self::SUCCESS;
    }
}
