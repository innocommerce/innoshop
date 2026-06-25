<p align="center">
    <a href="https://www.innoshop.com"><img src="https://www.innoshop.com/images/logo.png" alt="Total Downloads"></a>
</p>

---

<p align="center">
    <a href="https://www.innoshop.com"><img src="https://img.shields.io/badge/License-OSL%203.0-green.svg" alt="Total Downloads"></a>
    <a href="https://www.php.net"><img src="https://img.shields.io/badge/Language-PHP%208.2-blue.svg" alt="Total Downloads"></a>
    <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12-orange" alt="Total Downloads"></a>
</p>


<p align="center">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/sa.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/de.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/us.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/es.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/fr.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/id.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/it.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/jp.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/kh.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/kr.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/my.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/nl.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/pt.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/br.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/ru.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/th.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/tr.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/vn.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/cn.svg">
    <img class="flag-img" width="32px" height="24px" src="https://flagicons.lipis.dev/flags/4x3/hk.svg">
</p>


# InnoShop
InnoShop - 创新开源电商系统

Innoshop 是一个基于 Laravel 12 的开源电子商务系统，支持多语言、多货币，并集成了 OpenAI。它还具有插件机制和主题模板开发功能，以增强用户体验和系统的可扩展性。

## 快速开始

### 环境要求
- PHP >= 8.3，需安装扩展：bcmath、cURL、dom、fileinfo、libxml、OpenSSL、PDO、simplexml
- Composer 2.x
- MySQL 5.7+ / 8.0+（本地试用也可使用 SQLite）

### 方式一：Composer 安装（推荐）

```bash
composer create-project innoshop/innoshop
cd innoshop
php artisan serve
```

浏览器打开 http://localhost:8000 ，根据安装向导完成数据库配置和管理员账号创建即可。

### 方式二：下载 ZIP 安装包

下载最新版安装包，解压到 Web 根目录：

- 官方网站：<https://www.innoshop.cn>
- GitHub Releases：<https://github.com/innocommerce/innoshop/releases>

然后浏览器访问站点 URL，按安装向导完成安装。

### 方式三：Git Clone（适合贡献者）

```bash
git clone https://github.com/innocommerce/innoshop.git
cd innoshop
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

## 介绍
- 面向全球的开源电商系统, 15年行业持续深耕集大成者。
- 用户友好、界面直观、快速上手、响应式设计。
- 基于最新技术, 深度集成 AI, 支持多语言和多货币等特性。
- 高内聚、低耦合的模块化设计, 简单方便快速开发插件。

## 开发文档
- http://docs.innoshop.cn/zh
- http://front-api.innoshop.cn
- http://panel-api.innoshop.cn

## Demo 演示站
- 前台: https://demo.innoshop.cn/
- 后台: https://demo.innoshop.cn/panel
- 账号: admin@innoshop.com
- 密码: 123456

### Demo 前台截图
<p align="center">
    <a href="https://www.innoshop.cn" target="_blank">
        <img src="https://www.innoshop.cn/images/readme/front.jpg?v" alt="Front">
    </a>
</p>

### Demo 后台截图
<p align="center">
    <a href="https://www.innoshop.com" target="_blank">
        <img src="https://www.innoshop.cn/images/readme/panel.jpg?v" alt="Panel">
    </a>
</p>

- 如果您发现 `InnoShop` 对您有所帮助，请不吝赐给我们一个星星(star)。
- 您的每一次点赞都是我们不断进步的动力。

## 贡献者

感谢各位开发者的支持与贡献! [Contributors](https://github.com/innocommerce/innoshop/graphs/contributors)

<a href="https://github.com/yushine"><img class="avatar-img" width="32px" height="32px" src="https://github.com/yushine.png"/></a>
<a href="https://github.com/liuweixxx"><img class="avatar-img" width="32px" height="32px" src="https://github.com/liuweixxx.png"/></a>
<a href="https://github.com/qxsclass"><img class="avatar-img" width="32px" height="32px" src="https://github.com/qxsclass.png"/></a>
<a href="https://github.com/NeftaliYagua"><img class="avatar-img" width="32px" height="32px" src="https://github.com/NeftaliYagua.png"/></a>
<a href="https://github.com/lunan689"><img class="avatar-img" width="32px" height="32px" src="https://github.com/lunan689.png"/></a>
<a href="https://github.com/LOLU66"><img class="avatar-img" width="32px" height="32px" src="https://github.com/LOLU66.png"/></a>

