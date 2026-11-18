# 插件测试标准

适用对象:所有 `plugins/*` 下的 InnoShop 插件。
参考实现:`plugins/ProductExporter/Tests/`、`plugins/EasyQuote/Tests/`。

## 目录结构

```
plugins/{PluginName}/
└── Tests/
    ├── TestCase.php           # 插件级基类,继承项目 Tests\TestCase
    ├── Unit/                  # 纯逻辑测试(不触 DB / 框架服务)
    │   └── *Test.php
    └── Feature/               # 集成测试(Repository / Controller / 数据流)
        └── *Test.php
```

**必备文件**:`TestCase.php`、`Unit/`、`Feature/`。三者缺一即视为测试不达标。

## 命名规范

| 维度 | 规范 |
| --- | --- |
| 命名空间 | `Plugin\{PluginName}\Tests` / `...\Unit` / `...\Feature` |
| 类名 | `XxxTest`(PascalCase,Test 后缀) |
| 方法名 | `test_<lowercase_with_underscores>(): void` |
| 基类 | `Plugin\{PluginName}\Tests\TestCase`(不要直接继承 `Tests\TestCase`) |

❌ 错误:`public function it_works()` 或 `public function TestXxx()`
✅ 正确:`public function test_works_correctly(): void`

## TestCase 模板

每个插件必须有自己的 `TestCase.php`,职责:

1. **加载插件 migrations**(如果插件有自己的数据库表,见 EasyQuote 范例)
2. **注册 i18n 命名空间**(`$this->app['translator']->addNamespace(...)`)
3. **固定 locale**(`app()->setLocale('en')` + `session(['locale' => 'en'])`)

```php
<?php
namespace Plugin\{PluginName}\Tests;

use Tests\TestCase as RootTestCase;

abstract class TestCase extends RootTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 加载插件自己的 migrations(如果有)
        // $create = require plugin_path('{PluginName}/Database/Migrations/xxx.php');
        // $create->up();

        // 注册翻译命名空间
        $this->app['translator']->addNamespace(
            '{PluginName}', plugin_path('{PluginName}/Lang')
        );

        // 固定 locale 为英文,避免不同 locale 下断言失败
        app()->setLocale('en');
        session(['locale' => 'en']);
    }
}
```

## Unit vs Feature 划分

| 类别 | 触发场景 | 示例 |
| --- | --- | --- |
| **Unit** | 纯函数 / 工具类 / 解析器 | `ImageResolverTest`、`BotDetectorTest`、`IpTest` |
| **Feature** | Repository / Controller / 多组件协作 / 数据库读写 | `ExportRepoTest`、`ImportPreviewTest`、`ForceCreateImportTest` |

**判定准则**:测试是否需要 `RefreshDatabase`、HTTP 请求、或多个 Model 协作。
- 不需要 → Unit
- 需要 → Feature

## 复用项目级 Trait

`tests/Traits/CreatesProduct.php` 提供的工厂方法(插件测试可直接 use):

```php
use Tests\Traits\CreatesProduct;

class XxxTest extends TestCase
{
    use CreatesProduct;

    public function test_xxx(): void
    {
        $product = $this->createProduct(['price' => 99.9]);
        $sku = $this->createProductSku(['product_id' => $product->id]);

        $this->assertSame(99.9, (float) $product->fresh()->price);
    }
}
```

可用方法:`createProduct` / `createProductWithSku` / `createProductSku` / `createCategory` / `createBrand` / `createInactiveProduct` / `createVirtualProduct` / `createBundleProduct`。

## 运行方式

```bash
# 跑某个插件的全部测试
php artisan test plugins/{PluginName}/Tests

# 跑某个文件
php artisan test plugins/{PluginName}/Tests/Feature/ImportPreviewTest.php

# 跑某个方法
php artisan test plugins/{PluginName}/Tests --filter=test_xxx_yyy
```

## 闸门规则

> **没有测试的插件视为未通过 CI,在被禁用 / 隐藏前不允许合入主仓。**

具体执行:

1. 仓库合并前必须 `php artisan test plugins/{PluginName}/Tests` 全绿
2. 失败的测试 = 测试代码本身陈旧,需修复后重跑
3. 全新插件必须随代码提交 `TestCase.php` + 至少一个 `Feature/` 覆盖主流程

## 常见陷阱

- **`products.variables` 等已废弃字段**:InnoShop 主项目已移除旧字段(如 `variables` → `product_skus.variants`)。测试工厂 (`database/factories/ProductFactory.php`) 需同步清理,否则 SQLite `:memory:` 报 "no column named variables"。
- **插件 migrations 不自动跑**:`RefreshDatabase` 只跑主项目 migrations,插件自己的表需在 `TestCase::setUp()` 里手动 `require + ->up()`。
- **PluginServiceProvider 不注册 translations**:`installed()` / `plugins-table` 在测试 SQLite 环境下都不成立,必须手动 `addNamespace`。
- **跨 locale 断言**:插件里有 "按当前语言读字段" 的逻辑(如 `nameForLocale()`),测试要把 `app()->setLocale` 和 `session(['locale' => ...])` 都钉住。