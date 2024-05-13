<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Models\ArticleTag;
use InnoShop\Common\Models\ArticleTranslation;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getArticles();
        if ($items) {
            Article::query()->truncate();
            foreach ($items as $item) {
                Article::query()->create($item);
            }
        }

        $items = $this->getArticleTranslations();
        if ($items) {
            ArticleTranslation::query()->truncate();
            foreach ($items as $item) {
                ArticleTranslation::query()->create($item);
            }
        }

        $items = $this->getArticleTags();
        if ($items) {
            ArticleTag::query()->truncate();
            foreach ($items as $item) {
                ArticleTag::query()->create($item);
            }
        }
    }

    /**
     * @return array[]
     */
    private function getArticles(): array
    {
        return [
            [
                'id'         => 1,
                'catalog_id' => 1,
                'slug'       => 'innoshop-innovative-open-source-ecommerce',
                'position'   => 0,
                'viewed'     => 16,
                'author'     => null,
                'active'     => 1,
            ],
        ];
    }

    /**
     * @return array[]
     */
    private function getArticleTranslations(): array
    {
        return [
            [
                'id'         => 1,
                'article_id' => 1,
                'locale'     => 'zh_cn',
                'title'      => 'InnoShop - 创新电商，智选未来',
                'summary'    => 'Innoshop，一个以创新为核心的开源电商平台，致力于提供灵活、可扩展的电商解决方案。我们的产品管理功能全面，包括产品分类、添加、库存及价格管理等，旨在帮助商家轻松管理商品，提升运营效率。',
                'image'      => '/storage/common/zvvVpS8ZSW0xxYk676kwOGH26OgTI8gYa6xnGXqy.png',
                'content'    => '<p><span style="box-sizing: inherit; font-weight: bold;">[创新驱动，技术领先]</span></p>
<p>Innoshop，一个以创新为核心的开源电商平台，致力于提供灵活、可扩展的电商解决方案。我们的产品管理功能全面，包括产品分类、添加、库存及价格管理等，旨在帮助商家轻松管理商品，提升运营效率。</p>
<p>&nbsp;</p>
<p><span style="box-sizing: inherit; font-weight: bold;">[订单与用户管理，一站式服务]</span></p>
<p>我们的订单管理系统覆盖订单查询、管理、状态跟踪及退款处理等环节，确保交易流程的顺畅与透明。同时，用户管理系统简化了用户注册、登录及个人信息管理，优化了购物体验。</p>
<p>&nbsp;</p>
<p><span style="box-sizing: inherit; font-weight: bold;">[支付与物流，全球化布局]</span></p>
<p>Innoshop支持多种支付方式，包括PayPal、Stripe、Alipay、WeChat Pay以及银行转账和加密货币支付，满足全球用户的支付需求。物流管理功能则涵盖了配送、物流跟踪和订单交付等，确保商品能够安全、高效地送达消费者手中。</p>
<p>&nbsp;</p>
<p><span style="box-sizing: inherit; font-weight: bold;">[营销与SEO，助力品牌成长]</span></p>
<p>通过集成的促销和营销工具，Innoshop帮助商家开展折扣、优惠券等促销活动，同时结合电子邮件营销、短信营销等多渠道推广方式，扩大品牌影响力。SEO优化功能则助力商家提高网站可见度，吸引更多潜在客户。</p>
<p>&nbsp;</p>
<p><span style="box-sizing: inherit; font-weight: bold;">[多语言多货币，服务全球用户]</span></p>
<p>Innoshop支持多语言和多货币系统，为全球用户提供无障碍的购物体验，帮助商家拓展国际市场。</p>
<p>&nbsp;</p>
<p><span style="box-sizing: inherit; font-weight: bold;">[数据分析，洞察市场脉搏]</span></p>
<p>通过数据分析和报告功能，Innoshop帮助商家深入理解用户行为和销售趋势，从而优化运营策略，提升销售业绩。</p>
<p>&nbsp;</p>
<p><span style="box-sizing: inherit; font-weight: bold;">[开放源码，高度可定制]</span></p>
<p>作为开源电商平台，Innoshop具有高度的可扩展性和定制性，商家可以根据业务需求进行个性化开发和定制。</p>
<p>&nbsp;</p>
<p><span style="box-sizing: inherit; font-weight: bold;">[关于Innoshop]</span></p>
<p>Innoshop是您创新电商路上的可靠伙伴，我们相信通过不断的技术创新和优化服务，能够助力每一位商家实现商业成功。加入Innoshop，开启您的电商新篇章。</p>
<p>&nbsp;</p>
<p><span style="box-sizing: inherit; font-weight: bold;">欢迎访问 <a style="box-sizing: inherit;" href="https://www.innoshop.cn/" target="_blank" rel="noopener">Innoshop 中文官方网站</a> 了解更多信息。</span></p>',
                'meta_title'       => 'Innoshop - 创新电商，智选未来',
                'meta_description' => 'Innoshop - 创新电商，智选未来',
                'meta_keywords'    => 'Innoshop - 创新电商，智选未来',
            ],
        ];

    }

    /**
     * @return \int[][]
     */
    private function getArticleTags(): array
    {
        return [
            [
                'id'         => 1,
                'article_id' => 1,
                'tag_id'     => 1,
            ],
            [
                'id'         => 2,
                'article_id' => 1,
                'tag_id'     => 2,
            ],
        ];
    }
}
