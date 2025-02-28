<?php

namespace Plugin\Sitemap\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Setting;
use Plugin\Sitemap\Models\GoogleFeed;
use Plugin\Sitemap\Models\SitemapPwd;
use Plugin\Sitemap\Models\SitemapUrl;
use Plugin\Sitemap\Services\SitemapService;

class SitemapController
{
    public function index(Request $request)
    {
        $plugin = app('plugin')->getPlugin('site_map');
        $data   = [
            'name'        => 'SEO URL管理',
            'description' => $plugin->getLocaleDescription(),
        ];
        $data['types'] = SitemapService::typesText;
        $data['type']  = $request->type;

        return view('Sitemap::panel.config_form', $data);
    }

    public function googleFeeds(Request $request)
    {
        $pwd    = SitemapPwd::query()->first();
        $pwdStr = null;
        if (! $pwd) {
            $pwdStr = md5(time().rand(10000, 9999).time().rand(10000, 9999));
            SitemapPwd::query()->insert(['pwd' => $pwdStr]);
        } else {
            $pwdStr = $pwd->pwd;
        }
        $google_feed_url = front_route('google_feed.xml', [
            'pwd'  => $pwdStr,
            'page' => $request->page ? $request->page : 1,
        ]);
        $google_feeds = (new SitemapService)->getGoogleFeeds($request);

        return response()->json(['google_feeds' => $google_feeds,
            'google_feed_url'                   => $google_feed_url,
        ]);
    }

    public function siteMapData(Request $request)
    {

        $sitemap = (new SitemapService)->getAllSitemapData($request);

        $qType = $request->type;

        $pwd    = SitemapPwd::query()->first();
        $pwdStr = null;
        if (! $pwd) {
            $pwdStr = md5(time().rand(10000, 9999).time().rand(10000, 9999));
            SitemapPwd::query()->insert(['pwd' => $pwdStr]);
        } else {
            $pwdStr = $pwd->pwd;
        }

        $sitemaps_url = front_route('sitemap.xml', [
            'pwd'  => $pwdStr,
            'type' => $qType,
            'page' => $request->page ? $request->page : 1,
        ]);
        $sitemap['sitemaps_url'] = $sitemaps_url;

        return response()->json($sitemap);
    }

    public function updateUrl(Request $request)
    {
        $data     = $request->all();
        $saveData = [
            'type'       => $data['type'],
            'type_id'    => $data['type_id'],
            'type_url'   => $data['loc'],
            'priority'   => $data['priority'],
            'status'     => $data['status'],
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
        if ($data['type'] == 'other') {
            if (empty($data['name']) || empty($data['loc'])) {
                return json_fail('名称和网址不能为空');
            }
            $saveData['name'] = $data['name'];
        }
        if ($data['id'] == 0) {
            $saveData['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            SitemapUrl::query()->insert($saveData);
        } else {
            SitemapUrl::query()->where('id', $data['id'])->update($saveData);
        }

        return json_success('保存成功');
    }

    public function deleteUrl(Request $request)
    {
        $id         = $request->id;
        $sitemapUrl = SitemapUrl::query()->where('id', $id)->where('type', 'other')->first();
        if ($sitemapUrl) {
            $sitemapUrl->delete();
        }

        return json_success('删除成功');
    }

    public function sitemap_xml(Request $request)
    {
        $pwd = SitemapPwd::query()->first();
        if (! $pwd || $pwd->pwd != $request->pwd) {
            echo '非法请求';
            exit;
        }
        $doc               = new \DOMDocument('1.0', 'utf-8');
        $doc->formatOutput = true;

        $urlset = $doc->createElement('urlset');
        $type   = $request->urlset_type ? $request->urlset_type : 'google';
        if ($type == 'google') {
            $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        } elseif ($request->urlset_attr_key) {
            $urlset->setAttribute($request->urlset_attr_key, urldecode($request->urlset_attr_val));
        }
        $urlset = $doc->appendChild($urlset);

        $sitemaps = (new SitemapService)->getSitemap2XmlData($request);
        foreach ($sitemaps as $sitemap) {
            $url            = $doc->createElement('url');
            $loc            = $doc->createElement('loc');
            $loc->nodeValue = $sitemap['loc'];
            $url->appendChild($loc);

            if (! empty($sitemap['lastmod'])) {
                $lastmod            = $doc->createElement('lastmod');
                $lastmod->nodeValue = date('Y-m-d', strtotime($sitemap['lastmod']));
                $url->appendChild($lastmod);
            }

            $priority            = $doc->createElement('priority');
            $priority->nodeValue = $sitemap['priority'];
            $url->appendChild($priority);
            $urlset->appendChild($url);
        }
        header('Content-Type:text/xml');
        echo $doc->saveXML();
        exit;
    }

    public function updateGoogleFeed(Request $request)
    {
        $data     = $request->all();
        $saveData = [
            'product_id' => $data['id'],
            'gtin'       => $data['google_feed_gtin'],
            'status'     => $data['google_feed_status'],
            'condition'  => $data['google_feed_condition'],
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $googleFeed = GoogleFeed::query()->where('product_id', $data['id'])->first();

        if ($googleFeed) {
            GoogleFeed::query()->where('id', $googleFeed->id)->update($saveData);
        } else {
            $saveData['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            GoogleFeed::query()->insert($saveData);
        }

        return json_success('保存成功');
    }

    public function google_feed_xml(Request $request)
    {
        $pwd = SitemapPwd::query()->first();
        if (! $pwd || $pwd->pwd != $request->pwd) {
            echo '非法请求';
            exit;
        }
        $doc               = new \DOMDocument('1.0', 'utf-8');
        $doc->formatOutput = true;

        $rss = $doc->createElement('rss');
        $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $rss->setAttribute('version', '2.0');
        $rss     = $doc->appendChild($rss);
        $channel = $doc->createElement('channel');

        $setting = Setting::query()->where('type', 'system')->where('space', 'base')->whereIn('name', [
            'meta_title',
            'meta_description',
        ])->get();
        $tmpSetting = [];
        foreach ($setting as $se) {
            $tmpSetting[$se->name] = $se->value;
        }

        $page        = $request->page;
        $googleFeeds = GoogleFeed::query()->where('status', 1)->paginate(500, ['*'], 'page', $page);
        if ($googleFeeds->count() == 0) {
            exit('暂时还没有设置好生成google feed的商品');
        }
        $googleFeedProducts = [];
        foreach ($googleFeeds as $googleFeed) {
            $googleFeedProducts[$googleFeed->product_id] = $googleFeed;
        }

        $title            = $doc->createElement('title');
        $title->nodeValue = $tmpSetting['meta_title'];
        $channel->appendChild($title);

        $link            = $doc->createElement('link');
        $link->nodeValue = env('APP_URL');
        $channel->appendChild($link);

        $description            = $doc->createElement('description');
        $description->nodeValue = $tmpSetting['meta_description'];
        $channel->appendChild($description);

        $products = Product::query()->where('active', 1)->whereIn('id', array_keys($googleFeedProducts))->get();

        foreach ($products as $product) {
            $tmpLocaleData = $product->descriptions->keyBy('locale');
            if (isset($tmpLocaleData['en'])) {
                $locale = 'en';
            } else {
                $locale = locale();
            }
            $localeData = $tmpLocaleData[$locale];
            if (! isset($localeData['name'])) {
                continue;
            }
            // print_r(json_encode($localeData));exit;

            $item = $doc->createElement('item');

            $g_id            = $doc->createElement('g:id');
            $g_id->nodeValue = $product->id;
            $item->appendChild($g_id);

            $g_title            = $doc->createElement('g:title');
            $g_title->nodeValue = htmlspecialchars($localeData['name']);
            $item->appendChild($g_title);

            $g_description            = $doc->createElement('g:description');
            $g_description->nodeValue = htmlspecialchars($localeData['meta_description']);
            $item->appendChild($g_description);

            $g_link            = $doc->createElement('g:link');
            $g_link->nodeValue = $product->url;
            $item->appendChild($g_link);

            $g_image_link            = $doc->createElement('g:image_link');
            $g_image_link->nodeValue = empty($product->image) ? asset('') : asset($product->image);
            $item->appendChild($g_image_link);

            $googleFeed             = $googleFeedProducts[$product->id];
            $g_condition            = $doc->createElement('g:condition');
            $g_condition->nodeValue = $googleFeed->condition;
            $item->appendChild($g_condition);

            $g_availability = $doc->createElement('g:availability');
            if ($product->masterSku->quantity > 0) {
                $g_availability->nodeValue = 'in_stock';
            } else {
                $g_availability->nodeValue = 'out_of_stock';
            }
            $item->appendChild($g_availability);

            $g_price            = $doc->createElement('g:price');
            $g_price->nodeValue = $this->format_money($product->masterSku->price).' '.system_setting('base.currency');
            $item->appendChild($g_price);

            if (! empty($product->brand)) {
                $g_brand            = $doc->createElement('g:brand');
                $g_brand->nodeValue = empty($product->brand) ? '' : $product->brand->name;
                $item->appendChild($g_brand);
            }

            $g_mpn            = $doc->createElement('g:mpn');
            $g_mpn->nodeValue = $product->masterSku->model;
            $item->appendChild($g_mpn);

            if (! empty($googleFeed->gtin)) {
                $g_gtin            = $doc->createElement('g:gtin');
                $g_gtin->nodeValue = $googleFeed->gtin;
                $item->appendChild($g_gtin);
            }

            $channel->appendChild($item);
        }
        $rss->appendChild($channel);
        header('Content-Type:text/xml');
        echo $doc->saveXML();
        exit;
    }

    private function format_money($amount)
    {
        return number_format((float) $amount, 2, '.', ',');
    }
}
