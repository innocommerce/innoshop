<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FrequentQuestion;

use Exception;
use InnoShop\Plugin\Core\BaseBoot;
use Plugin\FrequentQuestion\Repositories\FaqCategoryRepo;
use Plugin\FrequentQuestion\Repositories\FaqRepo;

class Boot extends BaseBoot
{
    /**
     * @return void
     * @throws Exception
     */
    public function init(): void
    {
        $this->addImageBotMenu();
        $this->addProductFaqsByCartHook();
        $this->addProductFaqsByTabHook();
        $this->addArticleFaqsByHook();
    }

    /**
     * @return void
     * @throws Exception
     */
    private function addImageBotMenu(): void
    {
        listen_hook_filter('panel.component.sidebar.content.routes', function ($data) {
            $data[] = [
                'route' => 'faqs.index',
                'title' => __('FrequentQuestion::common.faq'),
            ];

            // $data[] = [
            //     'route' => 'faq_categories.index',
            //     'title' => __('FrequentQuestion::common.faq_category'),
            // ];

            return $data;
        });
    }

    /**
     * @return void
     * @throws Exception
     */
    private function addProductFaqsByCartHook(): void
    {
        if (! plugin_setting('FrequentQuestion', 'product_detail_cart_after')) {
            return;
        }

        listen_blade_insert('product.detail.after', function ($data) {
            $product     = $data['product'];
            $faqCategory = FaqCategoryRepo::getInstance()->withActive()->builder(['product_id' => $product->id])->first();
            if (empty($faqCategory)) {
                return '';
            }

            $data['faqs'] = FaqRepo::getInstance()->withActive()->builder(['faq_category_id' => $faqCategory->id])->get();

            return view('FrequentQuestion::front.faq_after_detail', $data)->render();
        });
    }

    /**
     * @return void
     * @throws Exception
     */
    private function addProductFaqsByTabHook(): void
    {
        if (! plugin_setting('FrequentQuestion', 'product_detail_tab_after')) {
            return;
        }

        listen_blade_insert('product.detail.tab.link.after', function ($data) {
            return view('FrequentQuestion::front.tab_link', $data)->render();
        });

        listen_blade_insert('product.detail.tab.pane.after', function ($data) {
            $product     = $data['product'];
            $faqCategory = FaqCategoryRepo::getInstance()->withActive()->builder(['product_id' => $product->id])->first();
            if (empty($faqCategory)) {
                return '';
            }

            $data['faqs'] = FaqRepo::getInstance()->withActive()->builder(['faq_category_id' => $faqCategory->id])->get();

            return view('FrequentQuestion::front.tab_faq', $data)->render();
        });
    }

    /**
     * @return void
     */
    private function addArticleFaqsByHook(): void
    {
        if (! plugin_setting('FrequentQuestion', 'article_detail_tab_after')) {
            return;
        }

        listen_blade_insert('article.show.content.after', function ($data) {
            $article     = $data['article'];
            $faqCategory = FaqCategoryRepo::getInstance()->withActive()->builder(['article_id' => $article->id])->first();
            if (empty($faqCategory)) {
                return '';
            }

            $data['faqs'] = FaqRepo::getInstance()->withActive()->builder(['faq_category_id' => $faqCategory->id])->get();

            return view('FrequentQuestion::front.faq_after_detail', $data)->render();
        });
    }
}
