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
        $this->addProductFaqHooks();
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

            return $data;
        });
    }

    /**
     * @return void
     * @throws Exception
     */
    private function addProductFaqHooks(): void
    {
        listen_blade_insert('product.detail.tab.link.after', function ($data) {
            return view('FrequentQuestion::front.tab_link', $data)->render();
        });

        listen_blade_insert('product.detail.tab.pane.after', function ($data) {
            $data['faqs'] = FaqRepo::getInstance()->withActive()->builder()->get();

            return view('FrequentQuestion::front.faq', $data)->render();
        });
    }
}
