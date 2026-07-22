<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Agents\ContentAgent;
use InnoShop\Common\Models\Product;
use Throwable;

class GenerateProductContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Auto-fillable AI columns per translation row.
     * Keys are translation column names; values are ContentAgent column identifiers.
     */
    private const FIELDS = [
        'summary'          => 'product_summary',
        'selling_point'    => 'product_selling_point',
        'meta_title'       => 'product_title',
        'meta_description' => 'product_description',
        'meta_keywords'    => 'product_keywords',
        'content'          => 'product_content',
    ];

    public function __construct(
        public int $productId,
        public array $onlyFields = [],
    ) {}

    /**
     * Number of seconds the job can run before timing out.
     */
    public $timeout = 300;

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping("product-ai-{$this->productId}"))->releaseAfter(60)];
    }

    public function handle(): void
    {
        try {
            $product = Product::with(['translations', 'brand', 'categories'])->find($this->productId);
            if (! $product) {
                Log::info("GenerateProductContentJob: product {$this->productId} not found");

                return;
            }

            $context = array_filter([
                'title'    => trim((string) ($product->title ?? '')),
                'brand'    => trim((string) ($product->brand?->name ?? '')),
                'category' => $product->categories->map(fn ($c) => trim((string) ($c->name ?? '')))->filter()->implode(', '),
                'model'    => trim((string) ($product->model ?? '')),
            ]);

            $fields = $this->onlyFields ? array_intersect_key(self::FIELDS, array_flip($this->onlyFields)) : self::FIELDS;

            foreach ($product->translations as $translation) {
                $locale = $translation->locale ?? '';
                if (! $locale) {
                    continue;
                }

                foreach ($fields as $column => $agentColumn) {
                    $current = trim((string) ($translation->{$column} ?? ''));
                    if ($current !== '') {
                        continue;
                    }

                    try {
                        $agent    = new ContentAgent($agentColumn, $locale, $context);
                        $response = $agent->prompt($context['title'] ? "Generate {$agentColumn} for: {$context['title']}" : "Generate {$agentColumn}");
                        $text     = trim((string) ($response->text ?? ''));
                        if ($text !== '') {
                            $translation->{$column} = $text;
                        }
                    } catch (Throwable $e) {
                        Log::warning("GenerateProductContentJob product={$this->productId} locale={$locale} column={$column} failed: ".$e->getMessage());
                    }
                }

                $translation->save();
            }
        } catch (Throwable $e) {
            Log::error("GenerateProductContentJob failed for product {$this->productId}: ".$e->getMessage());
        }
    }
}
