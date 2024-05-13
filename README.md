# InnoShop
-- 轻量级企业官网CMS

打造企业官网，从未如此简单！我们的轻量级CMS，专为快速开发和上线设计，让您的官网从构想到现实，仅需几步。

易用性与高效性并存，让您的团队轻松上手，快速掌握。

现在就加入我们，体验前所未有的开发速度和便捷性，让您的企业在互联网上大放异彩！

## Demo 演示站
- 前台: https://demo.innoshop.com/
- 后台: https://demo.innoshop.com/panel
- 账号: admin@innoshop.com
- 密码: 123456

### Demo 前台截图
<p align="center">
    <a href="https://www.innoshop.com" target="_blank">
        <img src="https://www.innoshop.cn/images/readme/front.jpg?" alt="Front">
    </a>
</p>

### Demo 后台截图
<p align="center">
    <a href="https://www.innoshop.com" target="_blank">
        <img src="https://www.innoshop.cn/images/readme/panel.jpg?" alt="Panel">
    </a>
</p>

## 安装说明
### 1. 选择PHP环境：
您可以选择小皮、宝塔、XAMPP等集成环境，这些都很便捷。如果您喜欢自定义，自己编译安装PHP也是极好的。

### 2. 设置网站根目录：
接下来，让我们把您的网站根目录指向当前项目的 `public` 文件夹并做好伪静态配置，这样访问网站时就能直接看到我们的内容了。

### 3. 安装第三方依赖：
打开命令行工具，定位到您当前的项目目录，然后输入 `composer install`，这会帮我们安装所需的第三方包。

### 4. 创建配置文件
复制一份配置文件作为起点，运行以下命令： `cp .env.example .env` 这样您就有了一个初始的配置文件。

### 5. 配置数据库
打开 `.env` 文件，找到以 `DB_` 开头的行，填写您的数据库配置信息。

### 6. 生成系统密钥
运行以下命令为系统生成一个安全密钥： `php artisan key:generate`

### 7. 数据库迁移与数据填充
执行以下命令创建数据库结构并导入基础数据： `php artisan migrate && php artisan db:seed`

### 8. 上传资源目录设置
运行以下命令来创建上传资源目录： `php artisan storage:link`

### 9. 前端资源编译
最后，运行以下命令来编译前端的CSS和JavaScript文件： `npm install && npm run prod`

### 10. 登录网站后台进行配置
要访问您的网站后台，请使用您网站的特定后台地址。

例如，如果您的网站地址是 `example.com`，则后台访问地址是 `example.com/panel`。

登录时，请使用您的管理员账号和密码。通常，初始登录凭证是：
- 邮箱: admin@innoshop.com
- 密码: 123456

输入您的登录信息后，即可进入后台进行配置。

祝您的安装过程顺利无阻。如果在安装或使用过程中遇到任何疑问或困难，欢迎加入我们的QQ交流群，群号为：960062283

我们的团队和热心用户将为您提供实时帮助和支持。


## 开发说明
### 1. 独立发布提醒：
请注意，`/innopacks` 目录下的三个文件夹将在 v1.0 版本发布后作为独立的 Composer 包进行发布。

因此，我们建议您避免直接编辑该目录下的文件。

### 2. 前台开发指南：
如果您正在进行前台的二次开发，可以通过执行以下命令来发布所需的视图文件：
```
php artisan vendor:publish --provider="InnoShop\Front\FrontServiceProvider" --tag=views
```
这样操作后，系统会自动在 `/resources/views/vendor` 目录下为您生成相应的模板文件。

### 3. 后台开发指南：
类似地，后台的二次开发也可以通过以下命令来获取模板文件：
```
php artisan vendor:publish --provider="InnoShop\Panel\PanelServiceProvider" --tag=views
```
执行命令后，您将在相同的目录下找到后台所需的模板文件。

### 4. 自由发挥邀请：
现在，您可以在这个安全的环境中大胆地进行开发和自定义，无需担心破坏原始代码。

### 5. 错误修正提示：
如果您不小心做了错误的修改，不用担心！只需删除那些被修改的文件，系统就会自动恢复到最初的状态。

### 6. 后端开发：
即将推出基于 “钩子(`hook`)” 的插件开发机制，让您在无需触碰系统核心代码的前提下，享受到自由灵活的定制化开发体验。

### 敬请期待我们带来的创新与便利！

- 如果您发现 `InnoShop` 对您有所帮助，请不吝赐给我们一个星星(star)。
- 您的每一次点赞都是我们不断进步的动力。