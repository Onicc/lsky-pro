# Lsky Pro 容器部署

## 发布镜像

GitHub `vMAJOR.MINOR.PATCH` 标签触发 Docker Hub 发布流程。CI 通过后，流程构建并验证同一个生产镜像，再推送到 `DOCKERHUB_USERNAME/lsky-pro` 的版本标签和 `latest`。发布任务摘要记录镜像摘要。部署时应将 `LSKY_IMAGE` 固定到版本或摘要，首版支持 Linux amd64。 [发布流程](../.github/workflows/release.yml)

## 首次安装

使用 Docker Engine 和 Compose。在同一目录放置 `compose.yaml` 和由 `docker/compose.env.example` 创建的 `.env`。将 `.env` 设为仅维护者可读，填入 `LSKY_IMAGE`、站点的 HTTPS `APP_URL` 和新实例独有的 `APP_KEY`。可以用以下命令生成应用密钥。

```sh
printf 'base64:%s\n' "$(openssl rand -base64 32)"
```

标准部署在本机回环地址监听 `8080`，由站点的反向代理终止 TLS 并转发 HTTP。让代理重写转发头并传递规范主机和协议。配置 `TRUSTED_PROXIES` 为实际代理地址或明确的 CIDR，`TRUSTED_HOSTS` 为站点允许使用的主机名。连接代理的接口应限制访问来源。纯本地 HTTP 开发时将 `APP_URL` 改为 `http://127.0.0.1:8080` 并设置 `SESSION_SECURE_COOKIE=false`。

SQLite 默认保存至命名卷的 `storage/app/lsky.sqlite`。选择 MySQL 或 PostgreSQL 时，先创建空库和专用账号，填写 `DB_CONNECTION`、`DB_HOST`、`DB_PORT`、`DB_DATABASE`、`DB_USERNAME`、`DB_PASSWORD`。根据发行版支持窗口选择数据库补丁版本，并限制数据库的网络访问。

通过一次性环境变量提供管理员邮箱和 12–72 字节的密码，完成初始化后从工作终端取消定义。以下为 Bash 命令，密码输入不会回显。

```bash
docker compose pull
read -rp '管理员邮箱 ' LSKY_ADMIN_EMAIL
read -rsp '管理员密码 ' LSKY_ADMIN_PASSWORD
printf '\n'
export LSKY_ADMIN_EMAIL LSKY_ADMIN_PASSWORD
docker compose run --rm -e LSKY_ADMIN_EMAIL -e LSKY_ADMIN_PASSWORD app \
    php artisan lsky:install --no-interaction
unset LSKY_ADMIN_EMAIL LSKY_ADMIN_PASSWORD
docker compose up -d --wait
```

容器 `GET /up` 在数据库和安装状态就绪时返回 `200`。Compose 启动独立的调度容器并共享应用的储存卷，定时重试未完成的对象清理。登录后检查储存策略与上传。默认关闭游客上传及公共注册，按使用场景从管理后台开启。生产镜像设应用代码只读，持久数据写入 `storage` 卷，`/tmp` 与 `bootstrap/cache` 用临时挂载。图片上传同时受前置代理、PHP 和应用组配置限制。

## 迁移和升级

1. 保存旧安装的数据库、对象和 `APP_KEY`，记录储存策略的根目录、访问域名以及自定义前缀。在隔离环境先验证恢复，保留原来的应用镜像或代码包。SQLite 复制文件前停止写入，或使用数据库自身的一致备份机制。外部对象存储的备份或版本控制需要随数据库一同核对。
2. 将数据库、对象和水印素材放入新实例的对应位置，确认容器 UID/GID `33` 对其持有必要的访问权限。传统本地储存默认根目录为 `/var/www/html/storage/app/uploads`，新增媒体默认位于同一卷的 `storage/app/media`。以前使用自定义根目录的实例要将数据挂载到对应容器路径，并核对策略配置。保留原 `APP_KEY` 与公开链接域名。
3. 将部署配置的 `LSKY_IMAGE` 指向新镜像，执行 `docker compose pull`。停旧实例写入后运行 `docker compose run --rm app php artisan lsky:install --adopt --no-interaction`，确认提示完成，再运行 `docker compose up -d --wait`。
4. 核对站点健康状态、管理员登录、现有和新建的外链、图片可见性及现有客户端上传。对于独立的公开对象桶，访问和缓存策略还需在该存储服务或 CDN 设置。如果回滚的版本与新数据库结构不兼容，应在恢复配套的数据和对象快照后切回原镜像。

## 开发机与测试机

在本机保留仓库，通过 `ssh docker` 进入开发机使用项目专用目录。可以将已提交的源码用 `git archive` 同步至开发机的新目录。`Dockerfile` 的 `development` 目标包含 Composer 和 PHP 测试依赖，默认目标构建生产镜像。分别运行 PHP 测试和临时 Compose 冒烟测试。

```sh
docker build --target development -t lsky-pro:dev .
docker run --rm \
  -e APP_ENV=testing \
  -e APP_KEY="base64:$(openssl rand -base64 32)" \
  -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: \
  lsky-pro:dev php vendor/bin/phpunit
docker build -t lsky-pro:local .
scripts/smoke-container.sh lsky-pro:local
```

冒烟测试会创建临时 Compose 项目和测试数据，验证安装前后的健康检查、静态资产、Token API、原名命名规则下的同名图片读回、计划任务注册和重启持久化。附加 `mysql` 或 `pgsql` 参数可验证对应外部数据库，默认使用 SQLite。命令结束时清理自己的项目与测试卷。

## 浏览器回归

`npm run test:browser` 在已安装的测试实例上验证桌面和触摸视口的登录、两张不同图片的字节与链接映射、Markdown 格式和图库键盘操作。准备一个可以上传的专用测试账户，把 Chrome 的远程调试端点仅监听在测试机的回环地址。执行命令时通过环境变量提供 `LSKY_BROWSER_BASE_URL`、`LSKY_BROWSER_CDP_ENDPOINT`、`LSKY_BROWSER_EMAIL` 和 `LSKY_BROWSER_PASSWORD`。浏览器测试会留下少量测试图片，应使用可丢弃的数据卷。
