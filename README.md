# Lsky Pro

兰空图床是可自托管的图片上传、图库与分享服务。本仓库从 [lsky-org/lsky-pro](https://github.com/lsky-org/lsky-pro) 的开源版延续维护。保留了 `/api/v1`、PicGo 接入以及常用外链格式。

## 安装与升级

建议使用 [Docker Compose 部署与升级文档](docs/deployment.md)。应用需要持久化存储、数据库和固定 `APP_KEY`。全新安装可使用 SQLite，支持连接 MySQL 8.4 或 PostgreSQL。容器不需要特权模式。

初始化由服务器上的命令完成。环境中的 `LSKY_ADMIN_EMAIL` 与 `LSKY_ADMIN_PASSWORD` 只需提供给第一次安装。将数据库连接、`APP_URL` 和 `APP_KEY` 配置妥当后，运行安装命令。

```sh
php artisan lsky:install --no-interaction
```

接管既有数据库前，应在同一时间点备份数据库、对象和原 `APP_KEY`。在停写窗口中按部署文档执行 `lsky:install --adopt`。旧的画廊隐藏图片保留持链接访问的语义。启用真正的私有访问前，需迁移并关闭任何仍能直接读取对象的旧外部公开路径。

## 上传和访问

支持逐张管理或批量选择上传。用户可选择存储策略和相册，为每张图片选择私有、持链接可访问或公开到画廊，并在图库中生成短期分享链接。首次安装默认关闭游客上传与公开注册，管理员可在系统设置中开启。

- 本地、S3、MinIO、WebDAV、SFTP、FTP、阿里云 OSS、腾讯云 COS、七牛云和又拍云策略通过统一的图片接口访问。
- API 的 `file` 字段、v1 响应结构和 Markdown、HTML、BBCode 复制格式兼容已有客户端。新建 Token 具有有效期、设备名称和可选授权范围。
- 老库可用 `php artisan lsky:media:audit --metadata-only` 检查路径与记录，并按部署文档逐批读取校验旧对象。

## 开发

应用依赖 PHP 8.5 和 Laravel 13，资产由 Node.js 24 LTS 和 Vite 构建，Dockerfile 提供统一环境。运行验证套件。

```sh
npm ci
npm run build
composer install
vendor/bin/phpunit
```

使用 Docker 验证镜像和上传流程。

```sh
docker build --tag lsky-pro:local .
scripts/smoke-container.sh lsky-pro:local
```

[接手审计](docs/audit/2026-11-19-maintenance-audit.md)记录了上游问题、优先级和历史基线。发布版本和迁移变更见 [GitHub Releases](https://github.com/Onicc/lsky-pro/releases)。

## 许可证

GPL-3.0。版权与依赖声明见 [LICENSE](LICENSE)。感谢原作者 WispX 与 Lsky Pro 社区贡献者。
