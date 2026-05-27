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
            [
                'id'     => 4,
                'slug'   => 'privacy-policy',
                'viewed' => 0,
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
                'locale'   => 'zh-cn',
                'title'    => '产品',
                'content'  => '',
                'template' => '<style>.product-card{border:1px solid #eee;border-radius:8px;padding:32px 24px;transition:box-shadow .25s,transform .25s;}.product-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08);transform:translateY(-2px);}</style>
<div class="py-4 py-md-5">
    <div class="container">
      <div class="text-center mb-4 mb-md-5">
        <h2 class="fw-bold mb-2">我们的产品</h2>
        <p class="text-secondary">全场景电商解决方案，助力您的业务增长</p>
      </div>
      <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-github" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop 社区版</h5>
            <p class="text-secondary mb-0">开源免费的电商系统，Laravel 12 框架，模块化架构，支持多语言、多货币、插件市场和主题系统。</p>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-building" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop 工厂版</h5>
            <p class="text-secondary mb-0">专为制造业打造，支持批量报价、生产订单跟踪、供应链管理和经销商分级体系。</p>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-briefcase-fill" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop 企业版</h5>
            <p class="text-secondary mb-0">面向中大型企业，高级数据分析、AI 智能推荐、API 集成、专属客户经理与优先技术支持。</p>
          </div>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-people-fill" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop 多商家</h5>
            <p class="text-secondary mb-0">多商户入驻平台，商家独立管理店铺，统一收款、自动分账、商户审核与佣金管理。</p>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-truck" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop 供应商</h5>
            <p class="text-secondary mb-0">供应商管理平台，入驻、商品托管、采购订单管理与库存同步，打通上下游供应链。</p>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-phone-fill" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop App</h5>
            <p class="text-secondary mb-0">原生 iOS/Android 应用，个性化推送、增强搜索、流畅购物体验与社交分享。</p>
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
                'locale'   => 'zh-cn',
                'title'    => '服务',
                'content'  => '',
                'template' => "<div class=\"page-service-content\">
    <div class=\"container\">
      <div class=\"row align-items-center\">
        <div class=\"col-12 col-md-5\">
          <div class=\"service-icon\"><img src=\"{{ asset('images/front/service/bg-1.svg') }}\" class=\"img-fluid\"></div>
        </div>
        <div class=\"col-12 col-md-7\">
          <div class=\"title-box\">
            <div class=\"title\">我们的服务</div>
            <div class=\"sub-title\">我们不仅提供定制化的解决方案，还以专业的技术知识、创新的思维方式和全方位的支持，确保您能够享受到卓越而高效的服务体验。</div>
          </div>
        </div>
      </div>
      <div class=\"row mt-4 mt-md-5\">
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-gear-fill\"></i></div>
            <div class=\"title\">开源系统</div>
            <div class=\"sub-title\">致力于提供高度灵活和可定制的解决方案，利用开放源代码的优势，帮助企业构建可扩展的系统。</div>
          </div>
        </div>
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-puzzle-fill\"></i></div>
            <div class=\"title\">插件市场</div>
            <div class=\"sub-title\">通过插件市场轻松扩展系统功能，丰富的插件选择满足不同业务需求，让定制化服务触手可及。</div>
          </div>
        </div>
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-code-slash\"></i></div>
            <div class=\"title\">定制开发</div>
            <div class=\"sub-title\">根据具体需求打造独一无二的软件解决方案，从概念到实现紧密合作，确保产品超出期望。</div>
          </div>
        </div>
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-wrench-adjustable-circle-fill\"></i></div>
            <div class=\"title\">安装维护</div>
            <div class=\"sub-title\">确保系统运行平稳，通过定期更新和故障排除提供无忧技术支持，让您专注核心业务。</div>
          </div>
        </div>
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-mortarboard-fill\"></i></div>
            <div class=\"title\">技术培训</div>
            <div class=\"sub-title\">帮助团队获得必要的技能和知识，培训课程旨在提升效率、促进创新，确保长期技术自给自足。</div>
          </div>
        </div>
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-headset\"></i></div>
            <div class=\"title\">售后支持</div>
            <div class=\"sub-title\">提供全天候技术支持服务，快速响应并解决您在使用过程中遇到的任何问题，保障业务持续稳定运行。</div>
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
                'locale'   => 'zh-cn',
                'title'    => '关于',
                'content'  => '',
                'template' => "<div class=\"page-about-content\">
  <div class=\"container\">
    <div class=\"row\">
      <div class=\"col-12 col-md-6\">
        <div class=\"about-img\">
          <img src=\"{{ asset('images/front/about/bg-2.svg') }}\" class=\"img-fluid\">
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
                'page_id'  => 1,
                'locale'   => 'en',
                'title'    => 'Creations',
                'content'  => '',
                'template' => '<style>.product-card{border:1px solid #eee;border-radius:8px;padding:32px 24px;transition:box-shadow .25s,transform .25s;}.product-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08);transform:translateY(-2px);}</style>
<div class="py-4 py-md-5">
    <div class="container">
      <div class="text-center mb-4 mb-md-5">
        <h2 class="fw-bold mb-2">Our Products</h2>
        <p class="text-secondary">Full-spectrum e-commerce solutions to power your business growth</p>
      </div>
      <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-github" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop Community</h5>
            <p class="text-secondary mb-0">Open-source and free, built on Laravel 12 with modular architecture. Multi-language, multi-currency, plugin marketplace and theme system.</p>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-building" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop Factory</h5>
            <p class="text-secondary mb-0">Tailored for manufacturing with bulk quoting, production order tracking, supply chain management, and dealer tier systems.</p>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-briefcase-fill" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop Enterprise</h5>
            <p class="text-secondary mb-0">Advanced analytics, AI recommendations, deep API integration, dedicated account managers, and priority support.</p>
          </div>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-people-fill" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop Multi-Vendor</h5>
            <p class="text-secondary mb-0">Multi-merchant marketplace with vendor-managed stores, unified payments, automated settlement, and commission management.</p>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-truck" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop Supplier</h5>
            <p class="text-secondary mb-0">Supplier management with vendor onboarding, product consignment, purchase orders, and inventory sync for streamlined supply chain.</p>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="product-card text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:rgba(233,30,99,.08);">
              <i class="bi bi-phone-fill" style="font-size:36px;color:#E91E63;"></i>
            </div>
            <h5 class="fw-bold mb-2">InnoShop App</h5>
            <p class="text-secondary mb-0">Native iOS and Android app with push notifications, enhanced search, smooth shopping experience, and social sharing.</p>
          </div>
        </div>
      </div>
    </div>
  </div>',
                'meta_title'       => 'Creations',
                'meta_description' => 'Creations',
                'meta_keywords'    => 'Creations',
            ],
            [
                'page_id'  => 2,
                'locale'   => 'en',
                'title'    => 'Services',
                'content'  => '',
                'template' => "<div class=\"page-service-content\">
    <div class=\"container\">
      <div class=\"row align-items-center\">
        <div class=\"col-12 col-md-5\">
          <div class=\"service-icon\"><img src=\"{{ asset('images/front/service/bg-1.svg') }}\" class=\"img-fluid\"></div>
        </div>
        <div class=\"col-12 col-md-7\">
          <div class=\"title-box\">
            <div class=\"title\">Our Services</div>
            <div class=\"sub-title\">We go beyond ready-made solutions — with deep technical expertise, innovative thinking, and comprehensive support, we ensure you receive an outstanding and efficient service experience.</div>
          </div>
        </div>
      </div>
      <div class=\"row mt-4 mt-md-5\">
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-gear-fill\"></i></div>
            <div class=\"title\">Open Source System</div>
            <div class=\"sub-title\">Committed to providing highly flexible and customizable solutions. Leveraging open source, we help businesses build scalable systems with transparency and community support.</div>
          </div>
        </div>
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-puzzle-fill\"></i></div>
            <div class=\"title\">Plugin Marketplace</div>
            <div class=\"sub-title\">Through our plugin marketplace, users can easily extend system functionality. A rich selection of plugins makes customization easily accessible.</div>
          </div>
        </div>
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-code-slash\"></i></div>
            <div class=\"title\">Custom Development</div>
            <div class=\"sub-title\">Focused on building unique software solutions tailored to your needs. From concept to delivery, we work closely with you to exceed expectations.</div>
          </div>
        </div>
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-wrench-adjustable-circle-fill\"></i></div>
            <div class=\"title\">Installation &amp; Maintenance</div>
            <div class=\"sub-title\">Our services ensure your system runs smoothly. With regular updates and troubleshooting, we provide worry-free technical support.</div>
          </div>
        </div>
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-mortarboard-fill\"></i></div>
            <div class=\"title\">Technical Training</div>
            <div class=\"sub-title\">Empower your team with essential skills and knowledge. Our courses boost efficiency, foster innovation, and ensure long-term self-sufficiency.</div>
          </div>
        </div>
        <div class=\"col-12 col-md-4\">
          <div class=\"service-item\">
            <div class=\"icon\"><i class=\"bi bi-headset\"></i></div>
            <div class=\"title\">After-Sales Support</div>
            <div class=\"sub-title\">Round-the-clock technical support with fast response times. We resolve any issues you encounter to keep your business running smoothly.</div>
          </div>
        </div>
      </div>
    </div>
  </div>",
                'meta_title'       => 'Services',
                'meta_description' => 'Services',
                'meta_keywords'    => 'Services',
            ],
            [
                'page_id'  => 3,
                'locale'   => 'en',
                'title'    => 'About',
                'content'  => '',
                'template' => "<div class=\"page-about-content\">
  <div class=\"container\">
    <div class=\"row\">
      <div class=\"col-12 col-md-6\">
        <div class=\"about-img\">
          <img src=\"{{ asset('images/front/about/bg-2.svg') }}\" class=\"img-fluid\">
        </div>
      </div>
      <div class=\"col-12 col-md-6\">
        <div class=\"about-text\">
          <div class=\"main-title\">Innovation-driven, professional team, outstanding technology, shaping the future together.</div>
          <div class=\"about-text-item\">
            <div class=\"left\"><i class=\"bi bi-check-circle\"></i></div>
            <div class=\"right\">
              <div class=\"title\">Our Team</div>
              <div class=\"sub-title\">
                Our team is made up of passionate and creative professionals from diverse backgrounds, united by a shared enthusiasm for technology and a pursuit of excellence. We encourage collaboration and open communication to spark innovative thinking and knowledge sharing.
              </div>
            </div>
          </div>
          <div class=\"about-text-item\">
            <div class=\"left\"><i class=\"bi bi-check-circle\"></i></div>
            <div class=\"right\">
              <div class=\"title\">Work Environment</div>
              <div class=\"sub-title\">
                Our modern and comfortable workspace is designed to inspire creativity and boost productivity. Open-plan areas promote communication and teamwork, while quiet zones offer a place to recharge during busy days.
              </div>
            </div>
          </div>
          <div class=\"about-text-item\">
            <div class=\"left\"><i class=\"bi bi-check-circle\"></i></div>
            <div class=\"right\">
              <div class=\"title\">Technical Expertise</div>
              <div class=\"sub-title\">
                We possess strong technical capabilities. Our team is proficient in the latest programming languages and development tools, with deep understanding and hands-on experience in AI, machine learning, and data analytics. We are committed to leveraging these technologies to create efficient, intelligent solutions for our users.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>",
                'meta_title'       => 'About Us',
                'meta_description' => 'About Us',
                'meta_keywords'    => 'About Us',
            ],
            [
                'page_id' => 4,
                'locale'  => 'zh-cn',
                'title'   => '隐私政策',
                'content' => '<p>InnoShop 非常重视用户的隐私保护。本隐私政策说明了我们如何收集、使用和保护您的个人信息。</p>

<h3>1. 信息收集</h3>
<p>我们收集的信息包括：</p>
<ul>
    <li>账号信息：邮箱、用户名等</li>
    <li>设备信息：IP地址、浏览器类型等</li>
    <li>使用数据：访问记录、操作日志等</li>
</ul>

<h3>2. 信息使用</h3>
<p>我们使用收集的信息用于：</p>
<ul>
    <li>提供和改进服务</li>
    <li>发送重要通知</li>
    <li>防止欺诈和滥用</li>
</ul>

<h3>3. 信息保护</h3>
<p>我们采取严格的安全措施保护您的信息，包括：</p>
<ul>
    <li>数据加密存储</li>
    <li>访问权限控制</li>
    <li>定期安全审计</li>
</ul>

<h3>4. 信息共享</h3>
<p>我们不会出售您的个人信息。仅在以下情况下可能共享信息：</p>
<ul>
    <li>获得您的明确同意</li>
    <li>法律要求</li>
    <li>保护我们的合法权益</li>
</ul>

<h3>5. 您的权利</h3>
<p>您有权：</p>
<ul>
    <li>访问您的个人信息</li>
    <li>更正不准确的信息</li>
    <li>要求删除您的信息</li>
    <li>限制信息处理</li>
</ul>

<h3>6. 联系我们</h3>
<p>如果您有任何关于隐私政策的疑问，请联系我们：</p>
<p>邮箱：privacy@innoshop.com</p>',
                'meta_title'       => '隐私政策 - InnoShop',
                'meta_description' => 'InnoShop 隐私政策说明',
                'meta_keywords'    => '隐私政策,数据保护,个人信息',
            ],
            [
                'page_id' => 4,
                'locale'  => 'en',
                'title'   => 'Privacy Policy',
                'content' => '<p>InnoShop takes your privacy seriously. This Privacy Policy explains how we collect, use, and protect your personal information.</p>

<h3>1. Information Collection</h3>
<p>We collect the following information:</p>
<ul>
    <li>Account information: email, username, etc.</li>
    <li>Device information: IP address, browser type, etc.</li>
    <li>Usage data: access records, operation logs, etc.</li>
</ul>

<h3>2. Information Usage</h3>
<p>We use the collected information to:</p>
<ul>
    <li>Provide and improve services</li>
    <li>Send important notifications</li>
    <li>Prevent fraud and abuse</li>
</ul>

<h3>3. Information Protection</h3>
<p>We implement strict security measures to protect your information, including:</p>
<ul>
    <li>Data encryption</li>
    <li>Access control</li>
    <li>Regular security audits</li>
</ul>

<h3>4. Information Sharing</h3>
<p>We do not sell your personal information. We may share information only in the following cases:</p>
<ul>
    <li>With your explicit consent</li>
    <li>When required by law</li>
    <li>To protect our legal rights</li>
</ul>

<h3>5. Your Rights</h3>
<p>You have the right to:</p>
<ul>
    <li>Access your personal information</li>
    <li>Correct inaccurate information</li>
    <li>Request deletion of your information</li>
    <li>Restrict information processing</li>
</ul>

<h3>6. Contact Us</h3>
<p>If you have any questions about our Privacy Policy, please contact us:</p>
<p>Email: privacy@innoshop.com</p>',
                'meta_title'       => 'Privacy Policy - InnoShop',
                'meta_description' => 'InnoShop Privacy Policy',
                'meta_keywords'    => 'Privacy Policy, Data Protection, Personal Information',
            ],
        ];
    }
}
