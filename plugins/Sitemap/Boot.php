<?php

namespace Plugin\Sitemap;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Plugin\Sitemap\Models\LSeoUrl;

class Boot
{
    public function init(): void
    {

        //加入后台管理菜单
        listen_hook_filter('panel.component.sidebar.product.routes', function ($data) {
            $data[] = [
                'route' => 'seo_index',
                'title' => 'SEO 优化',
            ];

            return $data;
        });

        /**
        listen_hook_action('panel.product.store.after', function ($data) {
            $product      = $data['product'];
            $request_data = $data['request_data'];
            $this->updateSeoUrlName($product->id, $request_data['translation']['en']['name'], LSeoUrl::type_products, $request_data);
            return $data;
        }, 2000093);

        listen_hook_action('panel.product.update.after', function (Request $request) {
            $obj = $request->session()->get('product');
            $this->updateSeoUrlName($obj->id, $obj->translations->keyBy('locale')['en']->name, LSeoUrl::type_products, $obj->slug);
        }, 2000093);


        listen_hook_action('panel.product.destroy.after', function ($product) {
            $this->deleteSeoUrlName($product->id, LSeoUrl::type_products);
        }, 2000093);



        listen_hook_action('panel.category.store.after', function ($data) {
            $category     = $data['category'];
            $request_data = $data['request_data'];
            $this->updateSeoUrlName($category->id, $request_data['translation']['en']['name'], LSeoUrl::type_categories, $request_data);
            return $data;
        }, 2000093);

        listen_hook_action('panel.category.update.after', function (Request $request) {
            $obj = $request->category;
            $this->updateSeoUrlName($obj->id, $obj->translations->keyBy('locale')['en']->name, LSeoUrl::type_categories, $obj->slug);
        }, 2000093);

        listen_hook_action('panel.category.destroy.after', function ($category) {
            $this->deleteSeoUrlName($category->id, LSeoUrl::type_categories);
        }, 2000093);




        listen_hook_action('panel.page.store.after', function (Request $request) {
            //$page = $request->page;
            $this->updateSeoUrlName($page->id, $page->translations->keyBy('locale')['en']->title, LSeoUrl::type_pages, $page->slug);
        }, 2000093);

        listen_hook_action('panel.page.update.after', function (Request $request) {
            $obj = $request->page;
            $this->updateSeoUrlName($obj->id, $obj->translations->keyBy('locale')['en']->title, LSeoUrl::type_pages, $obj->slug);

        }, 2000093);


        listen_hook_action('panel.page.destroy.after', function ($page_id) {
            $this->deleteSeoUrlName($page_id, LSeoUrl::type_pages);
        }, 2000093);




        listen_hook_action('panel.article.store.after', function ($data) {
            $page         = $data['page'];
            $request_data = $data['request_data'];
            $this->updateSeoUrlName($page->id, $request_data['translation']['en']['title'], LSeoUrl::type_articles, $request_data);
            return $data;
        }, 2000093);

        listen_hook_action('panel.article.update.after', function (Request $request) {
            $obj = $request->article;
            $this->updateSeoUrlName($obj->id, $obj->translations->keyBy('locale')['en']->title, LSeoUrl::type_articles, $obj->slug);
        }, 2000093);


        listen_hook_action('panel.article.destroy.after', function ($page_id) {
            $this->deleteSeoUrlName($page_id, LSeoUrl::type_pages);
        }, 2000093);



        listen_hook_action('panel.catalog.store.after', function ($data) {
            $product      = $data['page_category'];
            $request_data = $data['request_data'];
            $this->updateSeoUrlName($product->id, $request_data['translation']['en']['title'], LSeoUrl::type_catalogs, $request_data);
            return $data;
        }, 2000093);

        listen_hook_action('panel.catalog.update.after', function (Request $request) {
            $obj = $request->catalog;
            $this->updateSeoUrlName($obj->id, $obj->translations->keyBy('locale')['en']->title, LSeoUrl::type_catalogs, $obj->slug);
        }, 2000093);

        listen_hook_action('panel.catalog.destroy.after', function ($page_category_id) {
            $this->deleteSeoUrlName($page_category_id, LSeoUrl::type_page_categories);
        }, 2000093);




        listen_hook_action('panel.brand.store.after', function ($data) {
            $product              = $data['brand'];
            $request_data         = $data['request_data'];
            $data['seo_url_name'] = $this->updateSeoUrlName($product->id, $request_data['name'], LSeoUrl::type_brands, $request_data);
            return $data;
        }, 2000093);

        listen_hook_action('panel.brand.update.after', function (Request $request) {
            $obj = $request->brand;
            $this->updateSeoUrlName($obj->id, $obj->translations->keyBy('locale')['en']->name, LSeoUrl::type_brands, $obj->slug);
        }, 2000093);

        listen_hook_action('admin.brand.destroy.after', function ($brand) {
            $this->deleteSeoUrlName($brand->id, LSeoUrl::type_brands);
        }, 2000093);
         * **/
    }

    private function updateSeoUrlName($id, $enName, $type, $seo_url_name)
    {
        if (empty($seo_url_name)) {
            $seo_url_name = $enName;
        }
        $seo_url_name = str_replace(['/'], '-', $seo_url_name);
        $pattern      = '/[^a-zA-Z0-9\'-_]/'; // 正则表达式，匹配非英文字母、数字、指定符号
        $seo_url_name = preg_replace($pattern, '-', $seo_url_name); // 将不符合的字符和符号替换成横杠

        $oldLseoUrl = LSeoUrl::query()->where('url_name', $seo_url_name)->first();
        if ($oldLseoUrl && ($oldLseoUrl->type != $type || $oldLseoUrl->type_id != $id)) {
            throw new Exception('seo url 别名有重复，请换一个');
        }
        $lseoUrl = LSeoUrl::query()->where('type', $type)->where('type_id', $id)->first();
        if ($lseoUrl) {
            $lseoUrl->url_name   = $seo_url_name;
            $lseoUrl->updated_at = Carbon::now();
            $lseoUrl->update();
        } else {
            LSeoUrl::query()->insert([
                'type'       => $type,
                'type_id'    => $id,
                'url_name'   => $seo_url_name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        return $seo_url_name;
    }

    private function deleteSeoUrlName($id, $type)
    {
        LSeoUrl::query()->where('type', $type)->where('type_id', $id)->delete();
    }
}
