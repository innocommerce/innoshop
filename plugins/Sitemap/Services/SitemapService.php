<?php

namespace Plugin\Sitemap\Services;

use Illuminate\Http\Request;
use InnoShop\Common\Models\Brand;
use InnoShop\Common\Models\Catalog;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Models\Product;
use Plugin\Sitemap\Models\GoogleFeed;
use Plugin\Sitemap\Models\LSeoUrl;
use Plugin\Sitemap\Models\SitemapUrl;

class SitemapService
{
    const types = [
        LSeoUrl::type_products,
        LSeoUrl::type_categories,
        LSeoUrl::type_pages,
        LSeoUrl::type_catalogs,
        LSeoUrl::type_brands,
    ];

    const typesText = [
        LSeoUrl::type_products   => '商品详情',
        LSeoUrl::type_categories => '商品分类',
        LSeoUrl::type_pages      => '文章详情',
        LSeoUrl::type_catalogs   => '文章分类',
        LSeoUrl::type_brands     => '品牌',
        // 'other'                  => '其他',
    ];

    public function getAllSitemapData(Request $request, $pageSize = 20, $page = null)
    {

        $qType = $request->type;

        if ($qType == 'other') {

            $tmpProducts = [];

            $sitemapurls = SitemapUrl::query()->orderByDesc('id');
            $sitemapurls = $sitemapurls->where('type', $qType);
            $sitemapurls = $sitemapurls->orderByDesc('id')->paginate($pageSize, ['*'], 'page', $page);

            foreach ($sitemapurls as $sitemapurl) {
                $tmpProducts[] = [
                    'id'       => $sitemapurl->id,
                    'loc'      => $sitemapurl->type_url,
                    'lastmod'  => $sitemapurl->updated_at,
                    'priority' => $sitemapurl->priority,
                    'status'   => ''.$sitemapurl->status,
                    'name'     => $sitemapurl->name,
                    'type'     => 'other',
                    'type_id'  => 0,
                ];

            }

            return [
                'data'        => $tmpProducts,
                'sitemapurls' => $sitemapurls,
            ];
        } else {
            $editUrl = null;
            $setting = plugin_setting('site_map');
            $ext     = isset($setting['ext']) ? $setting['ext'] : 'html';
            if ($qType == LSeoUrl::type_products) {

                $editUrl = panel_route('products.edit', ['###id###']);
                //商品数据
                $objDatas = Product::query()->with('translation')->where('active', 1)->orderByDesc('id')->paginate($pageSize, ['*'], 'page', $page);
            } elseif ($qType == LSeoUrl::type_categories) {
                $editUrl = panel_route('categories.edit', ['###id###']);
                //商品分类数据
                $objDatas = Category::query()->with('translation')->where('active', 1)->orderByDesc('id')->paginate($pageSize, ['*'], 'page', $page);
            } elseif ($qType == LSeoUrl::type_pages) {
                $editUrl = panel_route('pages.edit', ['###id###']);
                //文章数据
                $objDatas = Page::query()->with('translation')->where('active', 1)->orderByDesc('id')->paginate($pageSize, ['*'], 'page', $page);
            } elseif ($qType == LSeoUrl::type_catalogs) {
                $editUrl = panel_route('catalogs.edit', ['###id###']);
                //文章分类数据
                $objDatas = Catalog::query()->with('translation')->where('active', 1)->orderByDesc('id')->paginate($pageSize, ['*'], 'page', $page);
            } elseif (empty($qType) || $qType == LSeoUrl::type_brands) {
                $editUrl = null;
                //品牌数据
                $objDatas = Brand::query()->orderByDesc('id')->where('active', 1)->paginate($pageSize);
            }
            //商品数据
            $pids = [];
            foreach ($objDatas as $objData) {
                $pids[] = $objData->id;
            }
            $sitemapurls = SitemapUrl::query()->where('type', $qType)->whereIn('id', $pids)->get();
            foreach ($sitemapurls as $sitemapurl) {
                $tmpSitemapUrls[$sitemapurl->type.'-'.$sitemapurl->type_id] = $sitemapurl;
            }
            foreach ($objDatas as $objData) {
                $status   = 1;
                $priority = 0.5;
                $id       = 0;
                if (isset($tmpSitemapUrls[$qType.'-'.$objData->id])) {
                    $sitemap  = $tmpSitemapUrls[$qType.'-'.$objData->id];
                    $status   = $sitemap->status;
                    $priority = $sitemap->priority;
                    $id       = $sitemap->id;

                }

                $leoUrls = LSeoUrl::query();
                if (! empty($qType)) {
                    $leoUrls = $leoUrls->where('type', $qType)->whereIn('id', $pids);
                }
                $leoUrls = $leoUrls->get();

                $tmpLeoUrls = [];
                if ($qType == LSeoUrl::type_products || $qType == LSeoUrl::type_categories || $qType == LSeoUrl::type_pages || $qType == LSeoUrl::type_catalogs || $qType == LSeoUrl::type_brands) {
                    $tmpLeoUrls[$qType.'-'.$objData->id] = $objData->url;
                } else {
                    foreach ($leoUrls as $leoUrl) {
                        $tmpLeoUrls[$leoUrl->type.'-'.$leoUrl->type_id] = env('APP_URL').'/en/'.$leoUrl->url_name;
                    }
                }

                $url = $loc = env('APP_URL').'/en/'.$qType.'/'.$objData->id;
                if (isset($tmpLeoUrls[$qType.'-'.$objData->id])) {
                    $loc = $tmpLeoUrls[$qType.'-'.$objData->id];
                }

                $tmpProducts[] = [
                    'id'       => $id,
                    'url'      => $url,
                    'loc'      => $loc,
                    'lastmod'  => $objData->updated_at,
                    'priority' => $priority,
                    'status'   => ''.$status,
                    'name'     => isset($objData->translation) ? (isset($objData->translation->name) ? $objData->translation->name : $objData->translation->title) : $objData->name,
                    'type'     => $qType,
                    'type_id'  => $objData->id,
                    'edit_url' => $editUrl ? str_replace('###id###', $objData->id, $editUrl) : '',
                    //'description' => $product->description,
                ];
            }

            return [
                'data'        => $tmpProducts,
                'sitemapurls' => $objDatas,
            ];

        }
    }

    public function getSitemap2XmlData(Request $request)
    {

        $sitemaps    = $this->getAllSitemapData($request, 500, $request->page);
        $tmpSitemaps = [];
        foreach ($sitemaps['data'] as $sitemap) {
            if ($sitemap['status'] == '1') {
                $tmpSitemaps[] = $sitemap;
            }
        }

        return $tmpSitemaps;
    }

    public function getGoogleFeeds(Request $request)
    {
        $googleFeeds        = GoogleFeed::query()->get();
        $googleFeedProducts = [];
        foreach ($googleFeeds as $googleFeed) {
            $googleFeedProducts[$googleFeed->product_id] = $googleFeed;
        }

        $products = Product::query()->with('description')->where('active', 1)->paginate(perPage());

        foreach ($products as $product) {
            if (isset($googleFeedProducts[$product->id])) {
                $product->google_feed_gtin      = $googleFeedProducts[$product->id]->gtin;
                $product->google_feed_status    = ''.$googleFeedProducts[$product->id]->status;
                $product->google_feed_condition = $googleFeedProducts[$product->id]->condition;
                $product->google_feed_statusStr = $googleFeedProducts[$product->id]->status == 1 ? '生成中' : '未生成';
            } else {
                $product->google_feed_gtin      = '';
                $product->google_feed_status    = '1';
                $product->google_feed_condition = 'new';
                $product->google_feed_statusStr = '未生成(点击保存可生成)';
            }
            $product->name = $product->description->name;
            $product->url  = $product->url;
        }

        return $products;

    }
}
