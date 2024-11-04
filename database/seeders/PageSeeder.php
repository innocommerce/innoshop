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
use InnoShop\Common\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getPages();
        if ($items) {
            Page::query()->truncate();
            foreach ($items as $item) {
                Page::query()->create($item);
            }
        }

        $items = $this->getPageTranslations();
        if ($items) {
            Page\Translation::query()->truncate();
            foreach ($items as $item) {
                Page\Translation::query()->create($item);
            }
        }
    }

    /**
     * @return array[]
     */
    private function getPages(): array
    {
        return [
            [
                'id'     => 1,
                'slug'   => 'creations',
                'viewed' => 666,
                'active' => 1,
            ],
            [
                'id'     => 2,
                'slug'   => 'services',
                'viewed' => 888,
                'active' => 1,
            ],
            [
                'id'     => 3,
                'slug'   => 'about',
                'viewed' => 999,
                'active' => 1,
            ],
        ];
    }

    /**
     * @return array[]
     */
    private function getPageTranslations(): array
    {
        return [
            [
                'page_id'  => 1,
                'locale'   => 'zh_cn',
                'title'    => '产品',
                'content'  => '',
                'template' => '<div class="page-product-content">
    <div class="container">
      <div class="title-box">
        <div class="title">我们的产品</div>
        <div class="sub-title">Our Creations</div>
      </div>
      <div class="row">
        <div class="col-12 col-md-6">
          <div class="product-item">
            <div class="top">
              <div class="left"><i class="bi bi-box-seam-fill"></i></div>
              <div class="name">InnoShop</div>
            </div>
            <div class="content">
              InnoShop是一款面向中小企业的电子商务平台，提供一站式在线商店解决方案。它以用户友好的界面和强大的后台管理功能著称，帮助商家轻松管理商品、订单和客户关系。InnoShop支持多种支付方式，并集成了社交媒体营销工具，助力商家扩大市场影响力。
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="product-item">
            <div class="top">
              <div class="left"><i class="bi bi-box-seam-fill"></i></div>
              <div class="name">InnoShop Pro</div>
            </div>
            <div class="content">
              InnoShop Pro是InnoShop的高级版本，专为需要更高级功能和定制服务的企业设计。除了基础版所有功能外，Pro版本提供高级数据分析、个性化推荐引擎和API集成，以满足更复杂的业务需求。它还包含专业的客户支持和优先更新服务，确保商家能够充分利用平台潜力。
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="product-item">
            <div class="top">
              <div class="left"><i class="bi bi-wechat"></i></div>
              <div class="name">小程序</div>
            </div>
            <div class="content">
              我们的小程序为移动用户提供了便捷的购物体验。它轻量级、易于访问，特别适合快速浏览和购买。小程序与主流社交媒体和通讯工具无缝集成，支持一键分享和邀请朋友，通过社交网络快速传播，增加用户粘性和品牌曝光度。
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="product-item">
            <div class="top">
              <div class="left"><i class="bi bi-phone-fill"></i></div>
              <div class="name">APP</div>
            </div>
            <div class="content">
              我们的App是一款为移动设备优化的应用程序，提供更加丰富和个性化的用户体验。它不仅包含了小程序的所有功能，还增加了个性化推送、增强的搜索功能和更高级的用户互动元素。App的设计注重流畅性和互动性，确保用户在移动设备上也能享受到优质的购物和服务体验。
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>',
                'meta_title'       => '产品',
                'meta_description' => '产品',
                'meta_keywords'    => '产品',
            ],
            [
                'page_id'  => 2,
                'locale'   => 'zh_cn',
                'title'    => '服务',
                'content'  => '',
                'template' => "<div class=\"page-service-content\">
    <div class=\"container\">
      <div class=\"row\">
        <div class=\"col-12 col-md-5\">
          <div class=\"service-icon\"><img src=\"{{ asset('images/front/service/bg-1.png') }}\" class=\"img-fluid\"></div>
        </div>
        <div class=\"col-12 col-md-7\">
          <div class=\"row\">
            <div class=\"col-12\">
              <div class=\"title-box\">
                <div class=\"title\">我们的服务</div>
                <div class=\"sub-title\">我们不仅提供定制化的解决方案，还以专业的技术知识、创新的思维方式和全方位的支持，确保您能够享受到卓越而高效的服务体验。我们承诺，无论您的需求如何变化，我们都能为您提供最匹配的专业服务。</div>
              </div>
            </div>
            <div class=\"col-12 col-md-6\">
              <div class=\"service-item\">
                <div class=\"icon\"><i class=\"bi bi-house-door-fill\"></i></div>
                <div class=\"title\">开源系统</div>
                <div class=\"sub-title\">致力于提供高度灵活和可定制的解决方案。利用开放源代码的优势，我们帮助企业构建可扩展的系统，同时确保透明度和社区支持。</div>
              </div>
            </div>
            <div class=\"col-12 col-md-6\">
              <div class=\"service-item\">
                <div class=\"icon\"><i class=\"bi bi-house-door-fill\"></i></div>
                <div class=\"title\">插件市场</div>
                <div class=\"sub-title\">通过我们的插件市场，用户可以轻松扩展其系统功能。我们提供丰富的插件选择，以满足不同的业务需求，让定制化服务触手可及</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class=\"row\">
        <div class=\"col-12 col-md-1\"></div>
        <div class=\"col-12 col-md-11 service-row-2\">
          <div class=\"row\">
            <div class=\"col-12 col-md-4\">
              <div class=\"service-item\">
                <div class=\"icon\"><i class=\"bi bi-house-door-fill\"></i></div>
                <div class=\"title\">定制开发</div>
                <div class=\"sub-title\">专注于根据您的具体需求，打造独一无二的软件解决方案。从概念到实现，我们与您紧密合作，确保最终产品超出您的期望。</div>
              </div>
            </div>
            <div class=\"col-12 col-md-4\">
              <div class=\"service-item\">
                <div class=\"icon\"><i class=\"bi bi-house-door-fill\"></i></div>
                <div class=\"title\">安装维护</div>
                <div class=\"sub-title\">我们的安装维护服务确保您的系统运行平稳，通过定期更新和故障排除，我们提供无忧的技术支持，让您专注于核心业务。</div>
              </div>
            </div>
            <div class=\"col-12 col-md-4\">
              <div class=\"service-item\">
                <div class=\"icon\"><i class=\"bi bi-house-door-fill\"></i></div>
                <div class=\"title\">技术培训</div>
                <div class=\"sub-title\">通过我们的技术培训服务，您的团队将获得必要的技能和知识。我们的培训课程旨在提升效率，促进创新，并确保长期的技术自给自足。</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>",
                'meta_title'       => '服务',
                'meta_description' => '服务',
                'meta_keywords'    => '服务',
            ],
            [
                'page_id'  => 3,
                'locale'   => 'zh_cn',
                'title'    => '关于',
                'content'  => '',
                'template' => "<div class=\"page-about-content\">
  <div class=\"container\">
    <div class=\"row\">
      <div class=\"col-12 col-md-6\">
        <div class=\"about-img\">
          <img src=\"{{ asset('images/front/about/bg-2.png') }}\" class=\"img-fluid\">
        </div>
      </div>
      <div class=\"col-12 col-md-6\">
        <div class=\"about-text\">
          <div class=\"main-title\">创新驱动，专业团队，卓越技术，共创未来。</div>
          <div class=\"about-text-item\">
            <div class=\"left\"><i class=\"bi bi-check-circle\"></i></div>
            <div class=\"right\">
              <div class=\"title\">我们的团队</div>
              <div class=\"sub-title\">
                我们的团队由一群充满激情和创造力的专业人士组成，他们来自不同的背景，但共同拥有对技术的热情和对卓越的追求。我们鼓励团队成员之间的协作与交流，以促进创新思维的碰撞和知识的共享。
              </div>
            </div>
          </div>
          <div class=\"about-text-item\">
            <div class=\"left\"><i class=\"bi bi-check-circle\"></i></div>
            <div class=\"right\">
              <div class=\"title\">办公环境</div>
              <div class=\"sub-title\">
                我们的办公空间设计现代而舒适，旨在激发员工的创造力和提高工作效率。开放式的工作区域促进了团队成员之间的沟通与合作，同时，我们也提供了安静的休息区，供员工在紧张的工作之余放松身心。
              </div>
            </div>
          </div>
          <div class=\"about-text-item\">
            <div class=\"left\"><i class=\"bi bi-check-circle\"></i></div>
            <div class=\"right\">
              <div class=\"title\">技术能力</div>
              <div class=\"sub-title\">
                我们拥有强大的技术实力，团队成员不仅精通最新的编程语言和开发工具，还对人工智能、机器学习、数据分析等前沿技术有着深入的理解和实践经验。我们致力于利用这些技术为用户创造高效、智能的解决方案。
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>",
                'meta_title'       => '关于',
                'meta_description' => '关于',
                'meta_keywords'    => '关于',
            ],

            [
                'page_id'          => 1,
                'locale'           => 'en',
                'title'            => 'Creations',
                'content'          => 'This is Creations page for English',
                'meta_title'       => 'Creations',
                'meta_description' => 'Creations',
                'meta_keywords'    => 'Creations',
            ],
            [
                'page_id'          => 2,
                'locale'           => 'en',
                'title'            => 'Services',
                'content'          => 'This is Services page for English',
                'meta_title'       => 'Services',
                'meta_description' => 'Services',
                'meta_keywords'    => 'Services',
            ],
            [
                'page_id'          => 3,
                'locale'           => 'en',
                'title'            => 'About',
                'content'          => 'This is About page for English',
                'meta_title'       => 'About Us',
                'meta_description' => 'About Us',
                'meta_keywords'    => 'About Us',
            ],
        ];
    }
}
