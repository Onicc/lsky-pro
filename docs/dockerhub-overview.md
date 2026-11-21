# Lsky Pro

兰空图床是面向自托管场景的图片上传、图库、外链和分享服务。

## 当前版本

- 镜像：`onicc/lsky-pro:3.2.1`
- 稳定别名：`onicc/lsky-pro:latest`
- 平台：Linux amd64
- 许可证：GPL-3.0-only
- 当前摘要：`sha256:4c37b90e8ecd17810d468f005f7150baa699ddb19d8f18fb6379151286ef6f70`

`3.2.1` 和 `latest` 指向同一个正式镜像。生产环境建议固定版本标签或完整摘要。

## 快速开始

```bash
mkdir lsky-pro && cd lsky-pro
curl -fsSLO https://raw.githubusercontent.com/Onicc/lsky-pro/v3.2.1/compose.yaml
curl -fsSLO https://raw.githubusercontent.com/Onicc/lsky-pro/v3.2.1/docker/compose.env.example
mv compose.env.example .env
```

编辑 `.env`，至少设置 `LSKY_IMAGE`、`APP_URL` 和随机生成的 `APP_KEY`。

```bash
printf 'base64:%s\\n' "$(openssl rand -base64 32)"
docker compose pull
read -rp '管理员邮箱 ' LSKY_ADMIN_EMAIL
read -rsp '管理员密码 ' LSKY_ADMIN_PASSWORD
printf '\\n'
export LSKY_ADMIN_EMAIL LSKY_ADMIN_PASSWORD
docker compose run --rm -e LSKY_ADMIN_EMAIL -e LSKY_ADMIN_PASSWORD app php artisan lsky:install --no-interaction
unset LSKY_ADMIN_EMAIL LSKY_ADMIN_PASSWORD
docker compose up -d --wait
```

就绪检查如下。

```bash
curl -fsS https://your-domain.example/up
```

## 能力

- 批量上传、幂等重试和逐张链接映射
- 私有、持链接可访问和公开三种访问权限
- 短期签名分享、私有缩略图和后台缩略图调度
- 本地、S3、MinIO、WebDAV、SFTP、FTP、OSS、COS、七牛和又拍云策略
- SQLite、MySQL 8.4 和 PostgreSQL 16
- PicGo 接入、Token 权限范围和 Markdown、HTML、BBCode 导出
- 不可变媒体键、并发写入保护和失败对象回收
- PHP 8.5、Laravel 13、Intervention Image 4、Node.js 24 和 Vite

## 安全基线

生产镜像以非 root 用户运行，应用代码只读，容器移除全部 Linux capabilities，启用 `no-new-privileges`。图片通过应用路由交付，统一执行权限检查和 `nosniff` 响应策略。上传和图像处理受文件大小、像素数、动画帧数、临时空间和 ImageMagick policy 限制。

## 文档和项目链接

- [项目仓库](https://github.com/Onicc/lsky-pro)
- [部署文档](https://github.com/Onicc/lsky-pro/blob/v3.2.1/docs/deployment.md)
- [媒体运维](https://github.com/Onicc/lsky-pro/blob/v3.2.1/docs/media-operations.md)
- [审计报告](https://github.com/Onicc/lsky-pro/blob/v3.2.1/docs/audit/2026-11-19-maintenance-audit.md)
- [发布记录](https://github.com/Onicc/lsky-pro/releases)

本镜像按全新安装基线发布。旧物理路径直链已移除。正式部署前请完成反向代理、TLS、主机补丁、数据库备份、对象存储备份和恢复演练。
