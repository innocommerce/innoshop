# InnoShop 发版流程

本文档面向 InnoShop 核心维护者，描述从代码冻结到对外发布完整流程。
当前发版由 GitHub Actions 自动化（`.github/workflows/release.yml`），但版本号一致性与发版前检查仍需人工保证。

---

## TL;DR

```bash
# 1. 改版本号
$EDITOR innopacks/common/config/innoshop.php   # 改 'version' 和 'build'

# 2. commit
git add innopacks/common/config/innoshop.php
git commit -m "release vX.Y.Z"
git push origin main

# 3. 打 tag
git tag -a vX.Y.Z -m "vX.Y.Z"
git push origin vX.Y.Z

# 4. 等 ~2 分钟，workflow 自动构建并发布
#    https://github.com/innocommerce/innoshop/actions
```

---

## 发版前 Checklist

| 项 | 检查内容 |
|---|---|
| ✅ 版本号字段 | `innopacks/common/config/innoshop.php` 里的 `'version'` 与即将打的 tag 一致 |
| ✅ Build 戳 | 同文件的 `'build'` 改成发版日（YYYYMMDD） |
| ✅ 主分支绿色 | main 上最新 CI 跑过（`Laravel` workflow） |
| ✅ CHANGELOG | `BuildPack/Logs/CE/` 里加上对应版本的中英文 changelog |
| ✅ Packagist 同步 | webhook 正常工作，之前版本已被 Packagist 收录 |

---

## 自动化流程

`push tag v*` 触发 `release.yml`，按顺序执行：

1. **Checkout** 源码
2. **Setup PHP 8.3 + Node 18** + 必要扩展
3. **Verify code version matches tag** — 读 `innoshop.php` 的 version 字段，与 tag 不一致直接 fail
4. **Prepare .env** — cp `.env.example`，强制 `APP_DEBUG=false`、`APP_URL=http://localhost:8000`
5. **Drop scribe configs** — 删除 `innopacks/restapi/config/scribe_{front,panel}.php`，避免 `composer install --no-dev` 时 `package:discover` 因找不到 `knuckles/scribe` 报错
6. **composer install --no-dev** — 装生产依赖
7. **npm install + npm run build** — 编译前端资源到 `public/build/`
8. **Generate APP_KEY** — 让用户解压即用，安装向导可改
9. **Clear optimization caches** — 防止配置/路由/视图缓存被冻结到 zip 里
10. **Clean runtime artifacts** — 见下方「清理范围」
11. **Strip dev-only files** — 删 `node_modules`、`tests`、`.git`、`.github`、`.idea`、`phpunit.xml` 等
12. **Build distribution zip** — 打包为 `innoshop-vX.Y.Z.zip`（顶层目录同名）
13. **Attach to GitHub Release** — 自动创建/更新 Release，挂 zip，生成默认 notes

### 清理范围（参考 `BuildPack/BuildCE/build_ce.sh` 历史经验）

| 范围 | 处理 |
|---|---|
| `storage/app/*`、`storage/debugbar/*`、`storage/framework/{cache,sessions,testing,views}/*`、`storage/logs/*`、`storage/upload/*` | 全清 |
| `public/cache`、`public/upload` | 全清 |
| `plugins/*/Storage/` 下的 `.mmdb`、`.db`、`.sqlite`、超过 1MB 的非模板文件 | 删除（保留 `.xlsx`、`.csv`、`template*`、`demo.*`、`.json`） |
| 嵌套 `.git` 目录 | 递归删除（防御 vendored 插件 / 子模块残留） |
| `.DS_Store` | 递归删除（防御 macOS 开发环境污染） |

---

## 发版后验证

| 步骤 | 命令 / 链接 |
|---|---|
| 看 workflow 是否成功 | https://github.com/innocommerce/innoshop/actions |
| 看 Release 是否完整 | https://github.com/innocommerce/innoshop/releases/tag/vX.Y.Z |
| 检查 zip 体积 | `gh release view vX.Y.Z --json assets -q '.assets[].size'`（预期 30-40 MB） |
| 验证 zip 内 version | 下载 zip → 解压 → `grep 'version' innoshop/common/config/innoshop.php` |
| 验证 composer 渠道 | `composer create-project innoshop/innoshop test-install`（Packagist 拉取需 1-2 分钟） |
| 更新 Release notes | `gh release edit vX.Y.Z --notes-file ./release-notes.md`（可选，对自动生成的补充） |

---

## 故障排查

### CI 在「Verify code version matches tag」步骤失败

```
❌ Version mismatch — code says 0.8.6 but tag is 0.8.7
```

原因：发版前忘了改 `innoshop.php` 的 version 字段。
解决：在 main 上改 version → commit → push → 删除未对齐的 tag → 重打。

```bash
git tag -d vX.Y.Z
git push origin :refs/tags/vX.Y.Z
# 改 version，commit，push
git tag -a vX.Y.Z -m "vX.Y.Z"
git push origin vX.Y.Z
```

### CI 在 `composer install --no-dev` 卡住

通常是 scribe 配置未清理或某扩展缺失。检查：
- `innopacks/restapi/config/scribe_{front,panel}.php` 是否被删除
- `setup-php` 的 `extensions:` 是否齐全（`bcmath, curl, dom, fileinfo, libxml, openssl, pdo, simplexml`）

### Release notes 被覆盖

`softprops/action-gh-release@v2` 默认**保留**已存在的 body，不会覆盖你手动写的 changelog。如果不慎被覆盖，从 `BuildPack/Logs/CE/` 或本地备份恢复：

```bash
gh release edit vX.Y.Z --notes-file ./release-notes.md
```

### Packagist 没拉到新版本

- 检查 GitHub → Settings → Webhooks，Packagist webhook 应是绿色 ✓
- 手动触发：https://packagist.org/packages/innoshop/innoshop → Update button
- 1-2 分钟后 `composer create-project` 才能拉到新版本

---

## 双轨分发说明

| 渠道 | 受众 | 入口 |
|---|---|---|
| **Composer** | 开发者 | `composer create-project innoshop/innoshop` |
| **Release ZIP** | 小白客户 / 销售 | GitHub Releases 下载 `innoshop-vX.Y.Z.zip` |

Composer 渠道由 Packagist 提供，**首次提交需在 Packagist 注册**：
- https://packagist.org/packages/submit
- 仓库 URL：`https://github.com/innocommerce/innoshop`
- 设置 GitHub webhook 自动同步

ZIP 渠道由 `release.yml` 全自动，每次 push tag 自动更新。

---

## 历史经验

- **v0.8.6 重打事件**：首次发版时 `innoshop.php` 的 version 字段未同步到 v0.8.6，导致客户安装 v0.8.6 后后台显示 v0.8.5。修复后加入 `Verify code version matches tag` CI 步骤防止再次发生。
- **scribe 配置坑**：`composer install --no-dev` 会移除 `knuckles/scribe`，但 scribe 配置文件存在会让 `package:discover` 报错。参考 `build_ce.sh` 的预清理做法。
- **build_ce.sh 是本地打包脚本（macOS）**，与 GitHub Actions workflow 是两条独立链路，逻辑应保持同步。位置：`BuildPack/BuildCE/build_ce.sh`。
