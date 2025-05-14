# InnoShop 安装程序测试

本目录包含 InnoShop 安装程序的测试用例。

## 目录结构

```
tests/
├── Database/          # 数据库相关测试
├── Environment/       # 环境检查相关测试
├── InstallerTest.php  # 安装程序主要测试
└── TestCase.php       # 测试基类
```

## 测试内容

### 1. 安装程序测试 (InstallerTest.php)

- MySQL 安装测试
  - 数据库连接测试
  - 配置生成测试
  - 环境检查测试

- SQLite 安装测试
  - 数据库连接测试
  - 配置生成测试
  - 环境检查测试

- 无效数据测试
  - 无效数据库配置测试
  - 无效管理员信息测试

- 安装状态检查
  - 安装状态检测
  - 文件系统操作测试

- 路由测试
  - 安装页面路由测试
  - 首页路由测试

### 2. 数据库测试 (Database/)

- MySQL 数据库测试
- SQLite 数据库测试

### 3. 环境检查测试 (Environment/)

- PHP 版本检查
- 必要扩展检查
- 目录权限检查

## 运行测试

### 运行所有测试

```bash
php artisan test innopacks/install/tests
```

### 运行特定测试文件

```bash
php artisan test innopacks/install/tests/InstallerTest.php
```

### 运行特定测试方法

```bash
php artisan test --filter test_can_install_with_my_sql innopacks/install/tests/InstallerTest.php
```

## 测试环境要求

1. PHP 版本要求
   - PHP >= 8.0

2. 必要的 PHP 扩展
   - PDO
   - PDO_MySQL
   - PDO_SQLite
   - OpenSSL
   - Mbstring
   - Tokenizer
   - XML
   - Ctype
   - JSON

3. 目录权限
   - storage/ 目录可写
   - bootstrap/cache/ 目录可写
   - .env 文件可写

## 注意事项

1. 测试使用模拟（Mock）对象，不会实际修改数据库
2. 测试过程中会创建临时文件和目录
3. 测试完成后会自动清理临时文件
4. 确保测试环境与生产环境隔离

## 贡献指南

1. 编写新测试时，请继承 `TestCase` 类
2. 测试方法名应该清晰表达测试目的
3. 每个测试方法应该只测试一个功能点
4. 使用 `@dataProvider` 进行数据驱动测试
5. 保持测试代码的可读性和可维护性 