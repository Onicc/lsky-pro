# Lsky Pro 上游 Issue 台账

审计基线 `38d52c46`。2026-11-19（洛杉矶时间）从 GitHub REST API 获取 `state=all` 的全部分页，排除 Pull Request 后共 627 条 Issue，其中开放 161 条、关闭 466 条。所有记录保留上游状态与入口。对 248 条定向归并或核对，其中开放 161 条。其余基于标题、标签与版本线索初筛。上游关闭状态本身不代表修复。 [上游 Issue](https://github.com/lsky-org/lsky-pro/issues) · [取数接口](https://api.github.com/repos/lsky-org/lsky-pro/issues?state=all&per_page=100) · [主审计报告](./2026-11-19-maintenance-audit.md)

| 当前判定 | 开放 | 关闭 |
| --- | ---: | ---: |
| 已验证 | 2 | 0 |
| 确认 | 8 | 10 |
| 条件成立 | 0 | 1 |
| 部分确认 | 27 | 28 |
| 部分修复 | 1 | 4 |
| 已修复 | 1 | 2 |
| 功能缺口 | 15 | 10 |
| 兼容待核 | 7 | 7 |
| 待核 | 38 | 1 |
| 历史关联 | 0 | 12 |
| 历史待核 | 0 | 221 |
| 行为可解释 | 2 | 6 |
| 部署与配置 | 4 | 6 |
| 部署或用法 | 14 | 23 |
| 需求或咨询 | 29 | 127 |
| 范围外 | 13 | 8 |

「已验证」含当前版本动态证据。「确认」有可定位的当前代码路径。「部分确认」「部分修复」只将证据覆盖的部分纳入修复范围。主题按标题选一个入口，跨主题问题在判定依据中注明。

## 开放 Issue

| Issue | 标题 | 主题 | 判定 | 依据或下一步 |
| --- | --- | --- | --- | --- |
| [#885](https://github.com/lsky-org/lsky-pro/issues/885) | 挂在飞牛nas Webdav，无法上传，本地电脑测试webdav正常访问，防火墙已全开 | 存储与外链 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#881](https://github.com/lsky-org/lsky-pro/issues/881) | 支持通过重定向的方式访问第三方存储 | 存储与外链 | 功能缺口 | 存储固定直链与应用输出可选。缺少带访问控制的短期链接和策略级签名重定向。 |
| [#879](https://github.com/lsky-org/lsky-pro/issues/879) | 太难安装了 | 安装与部署 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#877](https://github.com/lsky-org/lsky-pro/issues/877) | webdav挂载正常上传正常预览但是访问404 | 存储与外链 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#871](https://github.com/lsky-org/lsky-pro/issues/871) | 如何更改IP获取方式 | 其他 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#869](https://github.com/lsky-org/lsky-pro/issues/869) | 请问企业版能否通过api连接picgo方便上传图片 | API 与集成 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#867](https://github.com/lsky-org/lsky-pro/issues/867) | 123盘 | 存储与外链 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#865](https://github.com/lsky-org/lsky-pro/issues/865) | 一键备份 | 安装与部署 | 功能缺口 | 缺少成体系的数据迁移/导入导出体验。备份需要同时覆盖数据库、对象、配置和恢复演练。 |
| [#861](https://github.com/lsky-org/lsky-pro/issues/861) | 上传图片时出现“服务端异常，请稍后重试”..上传分辨率过大的图片会出现 | 上传与图片处理 | 部分确认 | 上传主请求同步解码和缩略图，存在提交后仍因处理或外部超时返回失败的窗口。原帖各自的资源阈值待验证。 |
| [#860](https://github.com/lsky-org/lsky-pro/issues/860) | 生成的图片外链接无法在其他网站引用展示 | 存储与外链 | 部署与配置 | 原帖示例为 HTTP 外链。在 HTTPS 页面优先排查混合内容，也核对资源实际访问权限与源站响应。 |
| [#858](https://github.com/lsky-org/lsky-pro/issues/858) | 获取图片希望支持每页大小 | 图库与界面 | 功能缺口 | API 图片列表固定 40。建议在保留默认值后增加有上限的 `per_page`。 |
| [#855](https://github.com/lsky-org/lsky-pro/issues/855) | 配置邮箱出现问题 | 其他 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#854](https://github.com/lsky-org/lsky-pro/issues/854) | 部分域名无法登录 | 安全与权限 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#850](https://github.com/lsky-org/lsky-pro/issues/850) | 兰空图床相册使用请教~ | 存储与外链 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#832](https://github.com/lsky-org/lsky-pro/issues/832) | 上传了，但是没有真正的上传 | 上传与图片处理 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#830](https://github.com/lsky-org/lsky-pro/issues/830) | 删除网站和数据库后，无法重新安装，提示账号重复 | 安装与部署 | 兼容待核 | 报错是重复配置键。安装会重建表，缺少原子挡板和失败恢复。原帖重复键具体因果待核。 |
| [#829](https://github.com/lsky-org/lsky-pro/issues/829) | 水印功能支持不同分类不同水印吗 | 上传与图片处理 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#828](https://github.com/lsky-org/lsky-pro/issues/828) | 可以使用英文吗 | 其他 | 功能缺口 | 存在翻译文件，但主要界面硬编码中文，缺少完整语言切换。 |
| [#826](https://github.com/lsky-org/lsky-pro/issues/826) | 图片显示异常 | 图库与界面 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#824](https://github.com/lsky-org/lsky-pro/issues/824) | 付费版本是否有docker-compose？ | 安装与部署 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#823](https://github.com/lsky-org/lsky-pro/issues/823) | minio上传异常 | 存储与外链 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#822](https://github.com/lsky-org/lsky-pro/issues/822) | 最新版的qqnt是不是失效了 | API 与集成 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#821](https://github.com/lsky-org/lsky-pro/issues/821) | 请问一下bbcode和markown的默认代码去哪里更改 | 其他 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#820](https://github.com/lsky-org/lsky-pro/issues/820) | 希望对象存储能增加cloudflare r2 | 存储与外链 | 兼容待核 | 已有 AWS S3 与 MinIO 适配。未验证 R2、MinIO 路径式寻址和相关 SDK 能力矩阵。 |
| [#819](https://github.com/lsky-org/lsky-pro/issues/819) | 上传大于100M图片显示服务端异常 | 上传与图片处理 | 部分确认 | 上传主请求同步解码和缩略图，存在提交后仍因处理或外部超时返回失败的窗口。原帖各自的资源阈值待验证。 |
| [#818](https://github.com/lsky-org/lsky-pro/issues/818) | 修改应用端口号 | 安装与部署 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#817](https://github.com/lsky-org/lsky-pro/issues/817) | Pro后台无法更新 | 安装与部署 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#809](https://github.com/lsky-org/lsky-pro/issues/809) | 上传分辨率大于4k的图片时会出现提示 服务端异常，请稍后重试 | 上传与图片处理 | 部分确认 | 上传主请求同步解码和缩略图，存在提交后仍因处理或外部超时返回失败的窗口。原帖各自的资源阈值待验证。 |
| [#807](https://github.com/lsky-org/lsky-pro/issues/807) | 如何放开画廊的访问权限？ | 安全与权限 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#806](https://github.com/lsky-org/lsky-pro/issues/806) | 是不是不支持123云盘的WebDAV | 存储与外链 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#805](https://github.com/lsky-org/lsky-pro/issues/805) | 【速度优化】如何自行更换用到的googleapis.css | API 与集成 | 确认 | 布局依赖 Google Fonts，头像依赖第三方。具体速度和可用性随访问网络变化。 |
| [#804](https://github.com/lsky-org/lsky-pro/issues/804) | 图片转换格式设置为 webp 后报错 | 上传与图片处理 | 已验证 | 转换配置与实际编码可能不一致。`.webp` 包含 PNG 字节已在当前版本的本地 GD 流程验证。历史 WebP 写入报错、PNG 质量另行判断。见主报告上传完整性。 |
| [#801](https://github.com/lsky-org/lsky-pro/issues/801) | 搭配mweb应该如何填写参数？ | 其他 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#800](https://github.com/lsky-org/lsky-pro/issues/800) | API地址404问题 | API 与集成 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#799](https://github.com/lsky-org/lsky-pro/issues/799) | docker启动时，apache报AH00558错误 | 安装与部署 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#796](https://github.com/lsky-org/lsky-pro/issues/796) | 上传失败, 图片上传失败 webdav | 存储与外链 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#795](https://github.com/lsky-org/lsky-pro/issues/795) | 用了nginx proxy manager反代后点击登陆端口没了 | 安全与权限 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#793](https://github.com/lsky-org/lsky-pro/issues/793) | 上传失败, 图片上传失败 | 上传与图片处理 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#791](https://github.com/lsky-org/lsky-pro/issues/791) | 企业版能否支持oauth登录 | 安全与权限 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#787](https://github.com/lsky-org/lsky-pro/issues/787) | 如何从一个docker容器迁移数据到另外一台服务器 | 安装与部署 | 功能缺口 | 缺少成体系的数据迁移/导入导出体验。备份需要同时覆盖数据库、对象、配置和恢复演练。 |
| [#783](https://github.com/lsky-org/lsky-pro/issues/783) | 企业版搬迁服务器后 执行php命令失败 | 安装与部署 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#781](https://github.com/lsky-org/lsky-pro/issues/781) | 开源版，图片上传后自动更名，是否可以根据用户需要选择是按照相应规则生成或者按照原文件名上传？ | 上传与图片处理 | 行为可解释 | 已有 `{filename}`。原名适合展示，持久对象键应维持唯一且独立。 |
| [#780](https://github.com/lsky-org/lsky-pro/issues/780) | minio 储存异常 | 存储与外链 | 部署与配置 | 原帖日志报告 TLS 证书与访问主机不匹配。保留 TLS 校验，核对域名模式、证书和桶路径。 |
| [#778](https://github.com/lsky-org/lsky-pro/issues/778) | 是否应使用协议相对URL指示静态资源 | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#777](https://github.com/lsky-org/lsky-pro/issues/777) | avif 图片不支持 | 上传与图片处理 | 功能缺口 | 角色组扩展名白名单与原图路由未提供 AVIF，图库须先做端到端格式兼容验证。 |
| [#776](https://github.com/lsky-org/lsky-pro/issues/776) | 阿里云鉴黄功能打开后 传图提示错误 | 安全与权限 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#773](https://github.com/lsky-org/lsky-pro/issues/773) | Lsky配置cloudreve WebDav存储策略图片显示404 页面不存在 | 存储与外链 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#772](https://github.com/lsky-org/lsky-pro/issues/772) | 关于其他登录方式 | 安全与权限 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#771](https://github.com/lsky-org/lsky-pro/issues/771) | v2.1版本直接粘贴图片出现重命名错误 | 图库与界面 | 部分确认 | 粘贴会替换原名，同一毫秒可能重复。历史报告使用默认服务器随机规则时的原因待核。 |
| [#770](https://github.com/lsky-org/lsky-pro/issues/770) | webdav挂载Alist+一刻相册做存储，上传报错 | 存储与外链 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#769](https://github.com/lsky-org/lsky-pro/issues/769) | 腾讯云安装监测未通过 chmod、chown、fileperms 函数 | 安装与部署 | 确认 | 安装页检查 exec、shell_exec、chmod、chown 等函数。需区分实际依赖、权限和可选能力。 |
| [#765](https://github.com/lsky-org/lsky-pro/issues/765) | 图床如何显示现有图片？ | 存储与外链 | 功能缺口 | 缺少成体系的数据迁移/导入导出体验。备份需要同时覆盖数据库、对象、配置和恢复演练。 |
| [#764](https://github.com/lsky-org/lsky-pro/issues/764) | 希望Pro版可以增加团队功能 | 其他 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#761](https://github.com/lsky-org/lsky-pro/issues/761) | 重启docker系统后lsky报错无法启动 | 安装与部署 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#760](https://github.com/lsky-org/lsky-pro/issues/760) | 允许在使用提交图片的API时,自定义提交的缩略图 | API 与集成 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#758](https://github.com/lsky-org/lsky-pro/issues/758) | 企业版文件命名问题，上传的图片需要自动替换文件名特殊符号 | 上传与图片处理 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#757](https://github.com/lsky-org/lsky-pro/issues/757) | 备案号显示问题，当关闭注册功能，关闭游客上传功能后不显示备案号 | 安全与权限 | 确认 | ICP备案仅在公开上传页页脚输出，登录布局无对应内容。 |
| [#756](https://github.com/lsky-org/lsky-pro/issues/756) | 安装方式不兼容docker部署 | 安装与部署 | 功能缺口 | 仓库已有 CLI 数据库初始化，但 CLI 不创建管理员，仓库没有生产容器发布和部署模板。 |
| [#752](https://github.com/lsky-org/lsky-pro/issues/752) | 上传失败, 图片记录保存失败 | 上传与图片处理 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#751](https://github.com/lsky-org/lsky-pro/issues/751) | &#91;BUG&#93; 无法添加存储类型 | 存储与外链 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#750](https://github.com/lsky-org/lsky-pro/issues/750) | 500服务器错误 | 其他 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#745](https://github.com/lsky-org/lsky-pro/issues/745) | 结合frp，如何修改login和register的链接地址 | 存储与外链 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#742](https://github.com/lsky-org/lsky-pro/issues/742) | 请教一下怎么定时自动删除游客上传的图片吗？或者批量手动删除也可以 | 上传与图片处理 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#739](https://github.com/lsky-org/lsky-pro/issues/739) | 部分页面公告 Markdown 显示问题 | 图库与界面 | 确认 | 公告共用 Markdown 标记，CSS 仅在少数页面引入。 |
| [#738](https://github.com/lsky-org/lsky-pro/issues/738) | 企业版 能否同步一下chevereto图床的api接口 | API 与集成 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#737](https://github.com/lsky-org/lsky-pro/issues/737) | first time run install was error,pls help,thanks. not found /vendor/autoload.php | 其他 | 部署与配置 | 分别缺 Composer vendor 依赖和 PDO driver。发布产物及安装预检可明确引导。 |
| [#735](https://github.com/lsky-org/lsky-pro/issues/735) | 数据库中图片和token等内容的id号是如何生成或排序的 | 安全与权限 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#734](https://github.com/lsky-org/lsky-pro/issues/734) | 请问内存占用过大怎么解决 | 其他 | 部分确认 | 上传主请求同步解码和缩略图，存在提交后仍因处理或外部超时返回失败的窗口。原帖各自的资源阈值待验证。 |
| [#733](https://github.com/lsky-org/lsky-pro/issues/733) | 图片体积过大，图片保存质量不生效，转换 webp 格式体积跟 PNG 几乎相等 | 上传与图片处理 | 已验证 | 转换配置与实际编码可能不一致。`.webp` 包含 PNG 字节已在当前版本的本地 GD 流程验证。历史 WebP 写入报错、PNG 质量另行判断。见主报告上传完整性。 |
| [#730](https://github.com/lsky-org/lsky-pro/issues/730) | 企业版需求-关于批量上传图片后产生的链接是乱序 | 存储与外链 | 部分确认 | 原帖属企业版。开源版也按响应到达追加结果，丢失输入顺序。 |
| [#729](https://github.com/lsky-org/lsky-pro/issues/729) | http接口功能 | API 与集成 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#728](https://github.com/lsky-org/lsky-pro/issues/728) | 企业版的一些功能建议及 Bug | 其他 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#726](https://github.com/lsky-org/lsky-pro/issues/726) | picgo日志显示一次性上传68张图片，但是只返回了57个链接。查看lskypro里面只有57个图片。 | API 与集成 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#725](https://github.com/lsky-org/lsky-pro/issues/725) | Web 上传文件时发生异常 | 上传与图片处理 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#724](https://github.com/lsky-org/lsky-pro/issues/724) | 手机上传图片 提示服务端异常 | 上传与图片处理 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#722](https://github.com/lsky-org/lsky-pro/issues/722) | 上传图片时创建文件夹的权限问题 | 安全与权限 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#718](https://github.com/lsky-org/lsky-pro/issues/718) | 图床部署后添加存储策略成功，但是设置中默认存储策略没有新添加的策略 | 安装与部署 | 部分确认 | 策略可用范围由组绑定决定。网页有策略保存，API 策略列表和上传默认值行为需要统一。 |
| [#716](https://github.com/lsky-org/lsky-pro/issues/716) | 覆盖原图水印BUG | 上传与图片处理 | 部分确认 | 原帖日志是水印字体读取失败。当前预检只有文件存在，未验证可读和字体有效性。#856 修改的是动态水印编码。 |
| [#715](https://github.com/lsky-org/lsky-pro/issues/715) | 企业版的一些功能建议及 Bug | 其他 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#713](https://github.com/lsky-org/lsky-pro/issues/713) | 希望能适配一下多吉云 | 其他 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#712](https://github.com/lsky-org/lsky-pro/issues/712) | php8.28安装不了报500错误 | 安装与部署 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#709](https://github.com/lsky-org/lsky-pro/issues/709) | 企业版文件命名规则使用{filename}，上传的图片需要自动替换文件名特殊符号 | 上传与图片处理 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#707](https://github.com/lsky-org/lsky-pro/issues/707) | 能不能更新一个一键下载图库所有照片的功能存本地备份用 | 安装与部署 | 功能缺口 | 缺少成体系的数据迁移/导入导出体验。备份需要同时覆盖数据库、对象、配置和恢复演练。 |
| [#706](https://github.com/lsky-org/lsky-pro/issues/706) | 提示 403 未授权的程序 怎么办 | 安全与权限 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#705](https://github.com/lsky-org/lsky-pro/issues/705) | 前端通过axios或者fetch post请求报错419 | API 与集成 | 部署与配置 | 网页上传需 CSRF。第三方客户端应走 API 并正确生成 multipart boundary，接口错误仍需改进。 |
| [#704](https://github.com/lsky-org/lsky-pro/issues/704) | 希望图片审核可以添加moderatecontent驱动(每月10000免费额度) | 安全与权限 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#703](https://github.com/lsky-org/lsky-pro/issues/703) | picgo上传失败 | API 与集成 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#698](https://github.com/lsky-org/lsky-pro/issues/698) | 2.1.0版本阿里云OSS上传失败 | 上传与图片处理 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#697](https://github.com/lsky-org/lsky-pro/issues/697) | ubuntu20.04安装问题 | 安装与部署 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#696](https://github.com/lsky-org/lsky-pro/issues/696) | curl error 6: getaddrinfo() thread failed to start | 存储与外链 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#692](https://github.com/lsky-org/lsky-pro/issues/692) | 如何批量选择图片进行操作？ | 图库与界面 | 部分确认 | 当前存在复制链接和批量选择。操作大量依赖右键、悬停和隐藏入口，移动端和键盘发现性差。 |
| [#690](https://github.com/lsky-org/lsky-pro/issues/690) | 使用了https反代但api仍是http | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#687](https://github.com/lsky-org/lsky-pro/issues/687) | 建议支持onedrive存储 | 存储与外链 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#684](https://github.com/lsky-org/lsky-pro/issues/684) | 由于数据库密码中包含有特殊字符串（！/@/#）引起链接数据库失败的一个bug | 安全与权限 | 确认 | 安装命令直接拼接 dotenv 值，特殊字符的序列化不可靠。 |
| [#681](https://github.com/lsky-org/lsky-pro/issues/681) | api怎么加自定义路径命名规则 | API 与集成 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#676](https://github.com/lsky-org/lsky-pro/issues/676) | 文档中缺少webdav和七牛云的存储配置 | 存储与外链 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#675](https://github.com/lsky-org/lsky-pro/issues/675) | &#91;bug&#93;如果订阅套餐价格设置为0 那么可以无限购买该套餐 | 其他 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#669](https://github.com/lsky-org/lsky-pro/issues/669) | 使用本地存储挂载NAS共享目录无法访问图片外链 | 存储与外链 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#668](https://github.com/lsky-org/lsky-pro/issues/668) | 请教大家，WebDAV如何设置 | 存储与外链 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#667](https://github.com/lsky-org/lsky-pro/issues/667) | 设置oss存储桶无法连接，一直不出现存储桶 | 存储与外链 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#664](https://github.com/lsky-org/lsky-pro/issues/664) | 这个只能做图床吗 能上传视频不 | 存储与外链 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#663](https://github.com/lsky-org/lsky-pro/issues/663) | 是否有Wordpress插件 | API 与集成 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#662](https://github.com/lsky-org/lsky-pro/issues/662) | webdav不能作为储存吗 | 存储与外链 | 部分确认 | 策略可用范围由组绑定决定。网页有策略保存，API 策略列表和上传默认值行为需要统一。 |
| [#661](https://github.com/lsky-org/lsky-pro/issues/661) | 利用Bit.io提供的免费PostgreSQL数据库创建失败 | 安装与部署 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#660](https://github.com/lsky-org/lsky-pro/issues/660) | 希望能支持上传到指定相册 | 上传与图片处理 | 功能缺口 | 现有上传读取账户默认相册和可见性，缺少单次显式选择和相册归属校验流程。 |
| [#655](https://github.com/lsky-org/lsky-pro/issues/655) | 关于开启水印功能后上传webp格式图片报错问题 | 上传与图片处理 | 兼容待核 | 需要用生产 Imagick 构建验证格式、动画帧、EXIF 方向和格式特定质量。历史版本结论按当前流水线复验。 |
| [#653](https://github.com/lsky-org/lsky-pro/issues/653) | 输入站点总是失败 | 其他 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#649](https://github.com/lsky-org/lsky-pro/issues/649) | 希望可以增加修改图片版本 | 图库与界面 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#647](https://github.com/lsky-org/lsky-pro/issues/647) | 考虑一下出2.1.1？ | 其他 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#644](https://github.com/lsky-org/lsky-pro/issues/644) | 对文件重命名失败，提示服务器异常 | 其他 | 部分确认 | 重命名缺少记录时，仍对空对象调用方法。原帖提供的信息不足以锁定同一根因。 |
| [#643](https://github.com/lsky-org/lsky-pro/issues/643) | 重新对接数据库----可否通过修改本地文件实现 | 安装与部署 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#642](https://github.com/lsky-org/lsky-pro/issues/642) | 自建邮件服务器无法发送验证 | 其他 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#641](https://github.com/lsky-org/lsky-pro/issues/641) | 配置缓存出错 | 其他 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#638](https://github.com/lsky-org/lsky-pro/issues/638) | 可以自定义添加网页吗？ | 其他 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#636](https://github.com/lsky-org/lsky-pro/issues/636) | 最新版本安装时报以下错误 | 安装与部署 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#635](https://github.com/lsky-org/lsky-pro/issues/635) | 提个建议 | 其他 | 需求或咨询 | 综合建议逐项评估，原帖不作为单个故障。 |
| [#632](https://github.com/lsky-org/lsky-pro/issues/632) | 图片可以正常上传和展示，但打开预览和打开图片链接返回404"error": "not_found", "reason": "Database does not exist." | 存储与外链 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#631](https://github.com/lsky-org/lsky-pro/issues/631) | 希望能有一份 chevereto 转移到 lsky 的工具~ | 其他 | 功能缺口 | 缺少成体系的数据迁移/导入导出体验。备份需要同时覆盖数据库、对象、配置和恢复演练。 |
| [#630](https://github.com/lsky-org/lsky-pro/issues/630) | 开源版V2.1 安装时 设置和获取文件、文件夹权限函数通不过 | 安全与权限 | 确认 | 安装页检查 exec、shell_exec、chmod、chown 等函数。需区分实际依赖、权限和可选能力。 |
| [#629](https://github.com/lsky-org/lsky-pro/issues/629) | 开源版V2.1,开启水印功能后,上传webp格式的代码后台报错,关闭水印功能,可上传webp格式 | 上传与图片处理 | 兼容待核 | 需要用生产 Imagick 构建验证格式、动画帧、EXIF 方向和格式特定质量。历史版本结论按当前流水线复验。 |
| [#627](https://github.com/lsky-org/lsky-pro/issues/627) | 开源版V2.1,上传webp格式的代码后台报错 | 上传与图片处理 | 兼容待核 | 需要用生产 Imagick 构建验证格式、动画帧、EXIF 方向和格式特定质量。历史版本结论按当前流水线复验。 |
| [#625](https://github.com/lsky-org/lsky-pro/issues/625) | 后期建议开发设置水印保留原图功能 | 上传与图片处理 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#622](https://github.com/lsky-org/lsky-pro/issues/622) | 有docker版吗？ | 安装与部署 | 功能缺口 | 仓库已有 CLI 数据库初始化，但 CLI 不创建管理员，仓库没有生产容器发布和部署模板。 |
| [#617](https://github.com/lsky-org/lsky-pro/issues/617) | 配置 ssl 报错问题 | 安装与部署 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#616](https://github.com/lsky-org/lsky-pro/issues/616) | 设置图片质量不生效，好像并不会改变文件大小 | 上传与图片处理 | 兼容待核 | 需要用生产 Imagick 构建验证格式、动画帧、EXIF 方向和格式特定质量。历史版本结论按当前流水线复验。 |
| [#612](https://github.com/lsky-org/lsky-pro/issues/612) | 上传图片压缩 | 上传与图片处理 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#607](https://github.com/lsky-org/lsky-pro/issues/607) | https+域名+端口的访问问题，NginxProxyManager反代 | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#604](https://github.com/lsky-org/lsky-pro/issues/604) | 希望可以支持支付宝当面付以及PAYJS（微信支付） | 其他 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#603](https://github.com/lsky-org/lsky-pro/issues/603) | 开源V2.1上传图片会出现图片重复。 | 上传与图片处理 | 部分修复 | 历史共享转换文件已由 #851 修改。当前仍有同一输入共享临时路径、不同内容共用物理对象键等独立缺陷。见主报告上传完整性。 |
| [#602](https://github.com/lsky-org/lsky-pro/issues/602) | v2.1 在 PHP 8.2.1 上无法正常运行 | 安装与部署 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#596](https://github.com/lsky-org/lsky-pro/issues/596) | 开源板2.0，接口有较大问题，只有获取toten接口能用，权限已开。 | 安全与权限 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#595](https://github.com/lsky-org/lsky-pro/issues/595) | 阿里云OSS储存策略参数设置正确但图片上传失败 | 存储与外链 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#593](https://github.com/lsky-org/lsky-pro/issues/593) | 数据库写入失败 | 安装与部署 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#588](https://github.com/lsky-org/lsky-pro/issues/588) | 上传显示：上传失败, Scan: The action not support this region. | 上传与图片处理 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#583](https://github.com/lsky-org/lsky-pro/issues/583) | 会考虑新增批量文件夹上传变成相册的模式吗？ | 上传与图片处理 | 功能缺口 | 缺少成体系的数据迁移/导入导出体验。备份需要同时覆盖数据库、对象、配置和恢复演练。 |
| [#576](https://github.com/lsky-org/lsky-pro/issues/576) | got file null when use picgo plugin langkong 1.1.3 to upload | API 与集成 | 确认 | 没有统一校验 multipart 中单个有效 `file`，缺失时进入通用错误分支。第三方客户端本身需另查。 |
| [#568](https://github.com/lsky-org/lsky-pro/issues/568) | nginx反向代理https后，有一些bug | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#563](https://github.com/lsky-org/lsky-pro/issues/563) | 希望可以有WordPress插件 | API 与集成 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#562](https://github.com/lsky-org/lsky-pro/issues/562) | 拍摄的照片上传后旋转 | 上传与图片处理 | 兼容待核 | 需要用生产 Imagick 构建验证格式、动画帧、EXIF 方向和格式特定质量。历史版本结论按当前流水线复验。 |
| [#555](https://github.com/lsky-org/lsky-pro/issues/555) | 如何设置存储策略将图片保存在cos的某个子目录下? | 存储与外链 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#554](https://github.com/lsky-org/lsky-pro/issues/554) | 请问在哪里可以设置接口的请求配额，或者如何解决短时间多次调用api会返回429状态码的问题 | API 与集成 | 行为可解释 | API 固定 60 次/分钟，另有组级上传频率。拟分开提示流量限制和组配额。 |
| [#548](https://github.com/lsky-org/lsky-pro/issues/548) | 本地存储策略，图片上传完成后无法访问 | 存储与外链 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#543](https://github.com/lsky-org/lsky-pro/issues/543) | 能通过不同角色赋予储存不同的储存容量吗？ | 存储与外链 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#538](https://github.com/lsky-org/lsky-pro/issues/538) | 请问有没有创建用户包括用户管理的接口文档的 | API 与集成 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#537](https://github.com/lsky-org/lsky-pro/issues/537) | 只能上传图片吗 | 上传与图片处理 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#531](https://github.com/lsky-org/lsky-pro/issues/531) | 希望能在上传的时候选择相册和是否公开, 而不是通过默认值 | 安全与权限 | 功能缺口 | 现有上传读取账户默认相册和可见性，缺少单次显式选择和相册归属校验流程。 |
| [#524](https://github.com/lsky-org/lsky-pro/issues/524) | 不支持winserver吗 | 其他 | 部署或用法 | 优先补安装/配置文档和诊断。缺少能归因当前代码的证据。 |
| [#522](https://github.com/lsky-org/lsky-pro/issues/522) | 可不可以推出一些编程语言sdk便于在大型系统中使用 | API 与集成 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#521](https://github.com/lsky-org/lsky-pro/issues/521) | 【功能需求】希望可以支持自动移除图片EXIF信息的功能 | 上传与图片处理 | 功能缺口 | 缺少明确且一致的元数据保留/清除策略。GPS、EXIF 方向和 ICC 应分别处理。 |
| [#509](https://github.com/lsky-org/lsky-pro/issues/509) | 后台图片搜索的问题 | 图库与界面 | 已修复 | 当前管理搜索已包含存储名、原名和别名。混合关键词与高级过滤的布尔分组是独立问题。 |
| [#505](https://github.com/lsky-org/lsky-pro/issues/505) | 无法打开安装页面 | 安装与部署 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#503](https://github.com/lsky-org/lsky-pro/issues/503) | 之后会开发按照储存策略进行剑皇的功能吗 | 存储与外链 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#501](https://github.com/lsky-org/lsky-pro/issues/501) | 无法上传，提示失败 | 上传与图片处理 | 待核 | 需要脱敏的应用/代理日志、提交号、运行时与存储配置。按对应主题归并，不计为已确认缺陷。 |
| [#499](https://github.com/lsky-org/lsky-pro/issues/499) | &#91;Bug Report&#93; "我的图片"中右上角搜索图片问题 | 图库与界面 | 确认 | 每次搜索强制发请求，无取消或响应代次保护，旧结果可与新结果混入。 |
| [#457](https://github.com/lsky-org/lsky-pro/issues/457) | nginx反代后点击注销会返回到源域名 | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#451](https://github.com/lsky-org/lsky-pro/issues/451) | 希望后台支持添加用户 | 其他 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#446](https://github.com/lsky-org/lsky-pro/issues/446) | 上传图片的建议 | 上传与图片处理 | 需求或咨询 | 综合建议逐项评估，原帖不作为单个故障。 |
| [#444](https://github.com/lsky-org/lsky-pro/issues/444) | 请问可以添加一个Oracle对象存储的策略吗 | 存储与外链 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#442](https://github.com/lsky-org/lsky-pro/issues/442) | 提供为图片打标签功能 | 图库与界面 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#440](https://github.com/lsky-org/lsky-pro/issues/440) | 写给空兰图床开发者的一些想法 | 存储与外链 | 需求或咨询 | 综合建议逐项评估，原帖不作为单个故障。 |
| [#432](https://github.com/lsky-org/lsky-pro/issues/432) | 只求一个功能。在图片列表给一个手机端访问可以 COPY URL 就好了。 | 存储与外链 | 部分确认 | 当前存在复制链接和批量选择。操作大量依赖右键、悬停和隐藏入口，移动端和键盘发现性差。 |

## 关闭 Issue

| Issue | 标题 | 主题 | 判定 | 依据或下一步 |
| --- | --- | --- | --- | --- |
| [#863](https://github.com/lsky-org/lsky-pro/issues/863) | 图片上传后返回的是ip而不是域名 | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#831](https://github.com/lsky-org/lsky-pro/issues/831) | 全局域网环境下，使用AList的webdav存储，出现404 | 存储与外链 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#814](https://github.com/lsky-org/lsky-pro/issues/814) | 图片管理添加根据上传日期筛选图片 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#810](https://github.com/lsky-org/lsky-pro/issues/810) | API调用delete 显示删除成功但是并未删除 | API 与集成 | 确认 | API 忽略删除影响行数。#810 是游客记录与用户 Token 权限不匹配，返回成功有误。物理删除、共享对象和 CDN 另有语义。 |
| [#808](https://github.com/lsky-org/lsky-pro/issues/808) | 如何将其他用户设置为管理员？ | 其他 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#802](https://github.com/lsky-org/lsky-pro/issues/802) | 图片审核功能优化 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#798](https://github.com/lsky-org/lsky-pro/issues/798) | 上传的图片小概率失败，部分缩略图与图片实际不符 | 上传与图片处理 | 部分修复 | 历史共享转换文件已由 #851 修改。当前仍有同一输入共享临时路径、不同内容共用物理对象键等独立缺陷。见主报告上传完整性。 |
| [#794](https://github.com/lsky-org/lsky-pro/issues/794) | HTTP ERROR 500 checkIp4(错误 | 安装与部署 | 兼容待核 | 原帖日志来自 Symfony/IP 处理。当前锁定较旧 HttpFoundation，具体代理输入待核。 |
| [#789](https://github.com/lsky-org/lsky-pro/issues/789) | 迁移数据库 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#768](https://github.com/lsky-org/lsky-pro/issues/768) | 反应个缩略图问题 | 上传与图片处理 | 确认 | 缩略图统一 PNG，缩放仅在宽高同时超限时执行，缩略图体积可能大于原图。 |
| [#767](https://github.com/lsky-org/lsky-pro/issues/767) | webDav挂载后不显示 | 存储与外链 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#755](https://github.com/lsky-org/lsky-pro/issues/755) | 上传文件时发生异常 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#754](https://github.com/lsky-org/lsky-pro/issues/754) | 有没有办法根据图片重建数据库 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#747](https://github.com/lsky-org/lsky-pro/issues/747) | 怎么通过Alist挂载阿里云盘的Webdav？ | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#743](https://github.com/lsky-org/lsky-pro/issues/743) | 企业版epay无法发起支付 | 其他 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#732](https://github.com/lsky-org/lsky-pro/issues/732) | 企业版 Thumbnail 图片大小问题 | 图库与界面 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#731](https://github.com/lsky-org/lsky-pro/issues/731) | 企业版 bug: 图片第一次上传，thumbnail image 无法生成，thumbnail url 为原图 url | 存储与外链 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#721](https://github.com/lsky-org/lsky-pro/issues/721) | nas使用npm反代登录地址无端口 | 安全与权限 | 部署与配置 | 反代后须核对规范 URL、外部端口和可信代理配置。 |
| [#719](https://github.com/lsky-org/lsky-pro/issues/719) | 咨询建议 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#717](https://github.com/lsky-org/lsky-pro/issues/717) | 企业版自己 build 镜像，启动后 403 | 安装与部署 | 范围外 | 从标题或标签筛查，关注开源对应能力。 |
| [#710](https://github.com/lsky-org/lsky-pro/issues/710) | 使用 MySQL 8 时安装失败 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#708](https://github.com/lsky-org/lsky-pro/issues/708) | 访问为https协议，但api访问是http协议，报错 | 安装与部署 | 部署与配置 | 原帖已自报解决。若当前仍有混合协议，应核对代理转发和策略访问 URL。 |
| [#701](https://github.com/lsky-org/lsky-pro/issues/701) | 图片超过10w张，控制台就无法使用了 | 图库与界面 | 部分确认 | 后台统计在 PHP 装载一个月的行进行聚合，可随当月数据量退化。10 万张触发 500 的阈值待压测。 |
| [#699](https://github.com/lsky-org/lsky-pro/issues/699) | 控制台菜单访问报错 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#695](https://github.com/lsky-org/lsky-pro/issues/695) | minio存储链接错误 | 存储与外链 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#689](https://github.com/lsky-org/lsky-pro/issues/689) | AMH 面板下使用Supervisor启动队列处理进程遇到的问题 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#688](https://github.com/lsky-org/lsky-pro/issues/688) | 通过API上传至非本机存储策略后，本机会存在API上传的图片 | API 与集成 | 行为可解释 | 远程策略也在本地公开目录保存缩略图。原件是否重复存储需要逐个对账。 |
| [#686](https://github.com/lsky-org/lsky-pro/issues/686) | 如何去掉路径里的端口？ | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#685](https://github.com/lsky-org/lsky-pro/issues/685) | 创建存储路径权限问题 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#683](https://github.com/lsky-org/lsky-pro/issues/683) | 对单点自建minio存储的支持问题 | 存储与外链 | 兼容待核 | 已有 AWS S3 与 MinIO 适配。未验证 R2、MinIO 路径式寻址和相关 SDK 能力矩阵。 |
| [#682](https://github.com/lsky-org/lsky-pro/issues/682) | 大佬，打扰了，安完访问报500该怎么排错 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#672](https://github.com/lsky-org/lsky-pro/issues/672) | 跨域请求问题 | API 与集成 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#671](https://github.com/lsky-org/lsky-pro/issues/671) | v2.1 使用 sqlite3.8.8+ 安装时报错 SQLSTATE&#91;HY000&#93;: General error: 1 no such function | 安装与部署 | 条件成立 | SQLite 安装会用 JSON 操作。发行版缺相应 JSON 函数时会失败，README 最低条件不完整。 |
| [#670](https://github.com/lsky-org/lsky-pro/issues/670) | 新建本地存储策略上传图片会有双份 | 存储与外链 | 待核 | 本地软链接与物理文件可能让同一文件显示两处，另有本地缩略图。检查实际对象或文件身份后再判断占用。 |
| [#666](https://github.com/lsky-org/lsky-pro/issues/666) | 想问怎么把备案号悬挂在网页 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#659](https://github.com/lsky-org/lsky-pro/issues/659) | 已经上传的图片，在相册里看不到markdown的地址 | 上传与图片处理 | 部分确认 | 当前存在复制链接和批量选择。操作大量依赖右键、悬停和隐藏入口，移动端和键盘发现性差。 |
| [#658](https://github.com/lsky-org/lsky-pro/issues/658) | 请问如何更换域名以及有什么需要注意的吗？ | 安装与部署 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#654](https://github.com/lsky-org/lsky-pro/issues/654) | 404问题 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#652](https://github.com/lsky-org/lsky-pro/issues/652) | 无法访问静态资源 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#650](https://github.com/lsky-org/lsky-pro/issues/650) | 初始化时报500错误 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#648](https://github.com/lsky-org/lsky-pro/issues/648) | redis unix模式支持? | 其他 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#646](https://github.com/lsky-org/lsky-pro/issues/646) | 在php8.2上安装后无法打开 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#645](https://github.com/lsky-org/lsky-pro/issues/645) | 邮箱验证登录后还是未激活 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#640](https://github.com/lsky-org/lsky-pro/issues/640) | 图片管理问题 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#639](https://github.com/lsky-org/lsky-pro/issues/639) | 设置中带下拉菜单的选项会遮挡侧边栏 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#634](https://github.com/lsky-org/lsky-pro/issues/634) | Gravatar镜像cravatar.cn 不稳定 | 其他 | 确认 | 布局依赖 Google Fonts，头像依赖第三方。具体速度和可用性随访问网络变化。 |
| [#628](https://github.com/lsky-org/lsky-pro/issues/628) | 无法上传图片到 AList 的 Webdav | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#626](https://github.com/lsky-org/lsky-pro/issues/626) | 如何通过接口上传到指定相册 | API 与集成 | 功能缺口 | 现有上传读取账户默认相册和可见性，缺少单次显式选择和相册归属校验流程。 |
| [#614](https://github.com/lsky-org/lsky-pro/issues/614) | 某些场景调用Api上传图片的一些问题 | API 与集成 | 部署与配置 | 网页上传需 CSRF。第三方客户端应走 API 并正确生成 multipart boundary，接口错误仍需改进。 |
| [#613](https://github.com/lsky-org/lsky-pro/issues/613) | 强烈建议可以增加github做图床存储选项吗？ | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#606](https://github.com/lsky-org/lsky-pro/issues/606) | 发现BUG，API删除图片bug | API 与集成 | 部分确认 | 当前删除状态存在假成功、缓存和对象回收问题。历史并发丢失比例及缓存原因未单独验证。 |
| [#605](https://github.com/lsky-org/lsky-pro/issues/605) | API删除图片提示成功，实际图片还是在相册显示 | API 与集成 | 确认 | API 忽略删除影响行数。#810 是游客记录与用户 Token 权限不匹配，返回成功有误。物理删除、共享对象和 CDN 另有语义。 |
| [#600](https://github.com/lsky-org/lsky-pro/issues/600) | 似乎 AWS S3 的配置项与 Cloudflare R2 不兼容 | 存储与外链 | 兼容待核 | 已有 AWS S3 与 MinIO 适配。未验证 R2、MinIO 路径式寻址和相关 SDK 能力矩阵。 |
| [#594](https://github.com/lsky-org/lsky-pro/issues/594) | 验证邮箱页面403，访问被拒绝 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#592](https://github.com/lsky-org/lsky-pro/issues/592) | 可否接入Oauth2登录 | 安全与权限 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#587](https://github.com/lsky-org/lsky-pro/issues/587) | 静态资源文件能否添加CDN地址加速？ | 安装与部署 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#584](https://github.com/lsky-org/lsky-pro/issues/584) | Step 2 &gt; 配置数据库和管理员账号出现问题 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#582](https://github.com/lsky-org/lsky-pro/issues/582) | 可否给flarum当图床使用？ | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#581](https://github.com/lsky-org/lsky-pro/issues/581) | 申请构建docker镜像 | 安装与部署 | 功能缺口 | 仓库已有 CLI 数据库初始化，但 CLI 不创建管理员，仓库没有生产容器发布和部署模板。 |
| [#575](https://github.com/lsky-org/lsky-pro/issues/575) | &#91;Security&#93; 储存桶/邮件配置密码明文保存在数据库中 | 安全与权限 | 确认 | 策略和邮件配置可包含明文密钥。需要敏感字段加密、备份保护和迁移。 |
| [#574](https://github.com/lsky-org/lsky-pro/issues/574) | 企业版支付重大bug | 其他 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#573](https://github.com/lsky-org/lsky-pro/issues/573) | 需要怎样设置才能让原来的图片地址自动跳转到带i前缀的地址 | 图库与界面 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#572](https://github.com/lsky-org/lsky-pro/issues/572) | 用户通过优惠券购买完用户组以后统计错误(或显示错误) | 图库与界面 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#571](https://github.com/lsky-org/lsky-pro/issues/571) | qq社会化绑定登录回调服务器500 | 安全与权限 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#569](https://github.com/lsky-org/lsky-pro/issues/569) | 能支持docker吗？ | 安装与部署 | 功能缺口 | 仓库已有 CLI 数据库初始化，但 CLI 不创建管理员，仓库没有生产容器发布和部署模板。 |
| [#558](https://github.com/lsky-org/lsky-pro/issues/558) | 解压后访问出现500错误 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#551](https://github.com/lsky-org/lsky-pro/issues/551) | 请求接口返回错误信息 | API 与集成 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#550](https://github.com/lsky-org/lsky-pro/issues/550) | 新上传的图片能设置其文件权限嘛 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#549](https://github.com/lsky-org/lsky-pro/issues/549) | 图床安装错误 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#547](https://github.com/lsky-org/lsky-pro/issues/547) | 安装报错 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#545](https://github.com/lsky-org/lsky-pro/issues/545) | lsky 上传图片错误，但错误日志都没有... | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#541](https://github.com/lsky-org/lsky-pro/issues/541) | 如何从数据库找回密码或更改密码 | 安全与权限 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#539](https://github.com/lsky-org/lsky-pro/issues/539) | 上传psd图片后psd网址报500错误 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#536](https://github.com/lsky-org/lsky-pro/issues/536) | 最新的v2 没法重命名了 | 其他 | 部分确认 | 重命名缺少记录时，仍对空对象调用方法。原帖提供的信息不足以锁定同一根因。 |
| [#533](https://github.com/lsky-org/lsky-pro/issues/533) | webp动图会自动转换成webp静图 | 上传与图片处理 | 兼容待核 | 需要用生产 Imagick 构建验证格式、动画帧、EXIF 方向和格式特定质量。历史版本结论按当前流水线复验。 |
| [#526](https://github.com/lsky-org/lsky-pro/issues/526) | 加了nginx，内网http链接被转为https，图片无法查看 | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#525](https://github.com/lsky-org/lsky-pro/issues/525) | 本地存储上传后缩略图生成失败 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#523](https://github.com/lsky-org/lsky-pro/issues/523) | 迁移网站后，开启原图保护，一部分图片可以访问，一部分图片404 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#520](https://github.com/lsky-org/lsky-pro/issues/520) | webdav &#124; 访问域名怎么填啊 | 安装与部署 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#516](https://github.com/lsky-org/lsky-pro/issues/516) | 请问一下Token是永久有效吗 | 安全与权限 | 部分确认 | 默认 Token 不过期。已有清空全部 Token 接口，缺少按设备管理、单条撤销和作用域。 |
| [#514](https://github.com/lsky-org/lsky-pro/issues/514) | &#91;优化&#93;更改存储逻辑以优化存储空间 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#513](https://github.com/lsky-org/lsky-pro/issues/513) | &#91;版本升级问题&#93; 2.0.4 升级到 17 号刚发布的 2.1 困难 &#124; 提供升级的服务器全国各地域名解析有些问题 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#512](https://github.com/lsky-org/lsky-pro/issues/512) | https的静态资源问题 | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#506](https://github.com/lsky-org/lsky-pro/issues/506) | 未能通过创建sqlite数据库的方式安装 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#504](https://github.com/lsky-org/lsky-pro/issues/504) | 可以在主界面（上传界面）增加一个公告的按钮吗 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#502](https://github.com/lsky-org/lsky-pro/issues/502) | 水印模式设置显示的问题 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#500](https://github.com/lsky-org/lsky-pro/issues/500) | 存储策略接口获取自定义策略失败 | API 与集成 | 部分确认 | 策略可用范围由组绑定决定。网页有策略保存，API 策略列表和上传默认值行为需要统一。 |
| [#498](https://github.com/lsky-org/lsky-pro/issues/498) | 图床突然无法登陆 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#497](https://github.com/lsky-org/lsky-pro/issues/497) | webdav存储策略问题 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#496](https://github.com/lsky-org/lsky-pro/issues/496) | &#91;Bug Report&#93;上传时生成文件名错误 | 上传与图片处理 | 部分修复 | 768ca9f2 在当前祖先。特殊字符路径、URL 编码以及同名键覆盖仍需处理。 |
| [#495](https://github.com/lsky-org/lsky-pro/issues/495) | 希望拓展api功能的建议 | API 与集成 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#494](https://github.com/lsky-org/lsky-pro/issues/494) | 跨域问题 | API 与集成 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#493](https://github.com/lsky-org/lsky-pro/issues/493) | 希望添加图片全选和批量选择 | 图库与界面 | 部分确认 | 当前存在复制链接和批量选择。操作大量依赖右键、悬停和隐藏入口，移动端和键盘发现性差。 |
| [#487](https://github.com/lsky-org/lsky-pro/issues/487) | https 静态资源问题 | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#486](https://github.com/lsky-org/lsky-pro/issues/486) | bug 提交 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#485](https://github.com/lsky-org/lsky-pro/issues/485) | 使用数据库PostgreSQL安装出错：could not find driver ，，， | 安装与部署 | 部署与配置 | 分别缺 Composer vendor 依赖和 PDO driver。发布产物及安装预检可明确引导。 |
| [#484](https://github.com/lsky-org/lsky-pro/issues/484) | 部署此项目到服务器后访问返回500 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#483](https://github.com/lsky-org/lsky-pro/issues/483) | 图片链接无效 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#477](https://github.com/lsky-org/lsky-pro/issues/477) | 博客照片无法展示 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#476](https://github.com/lsky-org/lsky-pro/issues/476) | 关于nsfw-api鉴黄设置问题 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#475](https://github.com/lsky-org/lsky-pro/issues/475) | 请问如何删除token | 安全与权限 | 部分确认 | 默认 Token 不过期。已有清空全部 Token 接口，缺少按设备管理、单条撤销和作用域。 |
| [#473](https://github.com/lsky-org/lsky-pro/issues/473) | 图片管理，点击某一张图片的用户名，显示的剩余容量是已使用的容量 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#472](https://github.com/lsky-org/lsky-pro/issues/472) | 图片重命名问题 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#471](https://github.com/lsky-org/lsky-pro/issues/471) | 文件名导致前端图片显示炸了 | 上传与图片处理 | 部分修复 | 历史特殊字符替换补丁 a4a60f13 在当前祖先。模板仍以字符串生成 DOM，需用上下文安全绑定。 |
| [#470](https://github.com/lsky-org/lsky-pro/issues/470) | &#91;人性化&#93; 改变储存策略中COS的 APPID 填写位置 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#465](https://github.com/lsky-org/lsky-pro/issues/465) | 使用minio作为存储空间，上传文件失败 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#463](https://github.com/lsky-org/lsky-pro/issues/463) | 缩略图可以存到外部去吗 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#462](https://github.com/lsky-org/lsky-pro/issues/462) | 希望可以修改系统管理员邮件地址 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#461](https://github.com/lsky-org/lsky-pro/issues/461) | 存储 策略问题 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#460](https://github.com/lsky-org/lsky-pro/issues/460) | 关于忘记密码功能：使用电子邮件地址发送重置密码邮件时文案的建议 | 安全与权限 | 确认 | 重置密码控制器对存在和不存在的邮箱返回不同反馈。统一反馈并对邮件请求限流。 |
| [#459](https://github.com/lsky-org/lsky-pro/issues/459) | 公告页面可以链接调出吗 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#458](https://github.com/lsky-org/lsky-pro/issues/458) | 【Bug】存储单元使用Minio，链接地址使用域名就会出问题 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#456](https://github.com/lsky-org/lsky-pro/issues/456) | 安装完毕后，无法访问首页 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#455](https://github.com/lsky-org/lsky-pro/issues/455) | 【BUG】升级后修改密码提示粘贴动作值选择错误 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#454](https://github.com/lsky-org/lsky-pro/issues/454) | 页面默认是ip，怎么设置成域名呢 | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#450](https://github.com/lsky-org/lsky-pro/issues/450) | 腾讯云COS 查看图片时404 | 存储与外链 | 部分确认 | 写入地址与公开读取地址独立。缩略图可成功而原图链接映射仍失败。当前缺少配置健康检查，逐帖环境根因仍需日志。 |
| [#449](https://github.com/lsky-org/lsky-pro/issues/449) | 本地存储上传图片后，直接访问url404；在管理后台访问图片管理查看刚上传的那张图片之后url又正常访问。 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#447](https://github.com/lsky-org/lsky-pro/issues/447) | 关于弹出式公告的建议 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#438](https://github.com/lsky-org/lsky-pro/issues/438) | 初始安装报错 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#436](https://github.com/lsky-org/lsky-pro/issues/436) | 使用nginx配置Content-Security-Policy策略后，无法弹出登录界面 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#435](https://github.com/lsky-org/lsky-pro/issues/435) | 请问1.x版本里的url_query在2.x该如何设置？ | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#430](https://github.com/lsky-org/lsky-pro/issues/430) | 旧版V1.6.3静态库外链cdn.jsdelivr.net被墙了，咋换成本地的css、js？谢谢 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#429](https://github.com/lsky-org/lsky-pro/issues/429) | 为什么宝塔软连接不能做为存储路径的目录 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#427](https://github.com/lsky-org/lsky-pro/issues/427) | v2.0.4 接口上传图片一直失败，返回Call to a member function getClientOriginalExtension() on null | API 与集成 | 确认 | 没有统一校验 multipart 中单个有效 `file`，缺失时进入通用错误分支。第三方客户端本身需另查。 |
| [#425](https://github.com/lsky-org/lsky-pro/issues/425) | 求助：首次安装，install界面无法正常载入 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#422](https://github.com/lsky-org/lsky-pro/issues/422) | 通过Rclone挂载B2到本地后无法正常使用生成的链接 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#421](https://github.com/lsky-org/lsky-pro/issues/421) | &#91;功能建议&#93;选择多个文件后，增加“开始上传”按钮 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#415](https://github.com/lsky-org/lsky-pro/issues/415) | 是否考虑添加上传图片自动转换格式的功能？（如自动转换WebP格式） | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#408](https://github.com/lsky-org/lsky-pro/issues/408) | 非本地ftp(127.0.0.1)无法上传 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#407](https://github.com/lsky-org/lsky-pro/issues/407) | 账号验证功能出现问题 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#406](https://github.com/lsky-org/lsky-pro/issues/406) | 在使用带端口的域名时，api模式下系统不带端口号 | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#405](https://github.com/lsky-org/lsky-pro/issues/405) | php安装出错 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#403](https://github.com/lsky-org/lsky-pro/issues/403) | python中添加header头无法上传 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#400](https://github.com/lsky-org/lsky-pro/issues/400) | 建议：整合一些三方教学中提供的必要信息 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#395](https://github.com/lsky-org/lsky-pro/issues/395) | 图片链接如何转为https | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#383](https://github.com/lsky-org/lsky-pro/issues/383) | api 接口删除无论成功与否皆返回 200 | API 与集成 | 确认 | API 忽略删除影响行数。#810 是游客记录与用户 Token 权限不匹配，返回成功有误。物理删除、共享对象和 CDN 另有语义。 |
| [#379](https://github.com/lsky-org/lsky-pro/issues/379) | 如何提交 Issue | 其他 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#378](https://github.com/lsky-org/lsky-pro/issues/378) | 功能建议：上传图片时支持指定不同的相册 | 上传与图片处理 | 功能缺口 | 现有上传读取账户默认相册和可见性，缺少单次显式选择和相册归属校验流程。 |
| [#375](https://github.com/lsky-org/lsky-pro/issues/375) | 提个建议 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#374](https://github.com/lsky-org/lsky-pro/issues/374) | 邮件验证链接返回403 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#372](https://github.com/lsky-org/lsky-pro/issues/372) | MinIO配置Endpoint似乎不支持域名https形式？ | 安装与部署 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#371](https://github.com/lsky-org/lsky-pro/issues/371) | 升级改建建议 | 安装与部署 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#368](https://github.com/lsky-org/lsky-pro/issues/368) | 宝塔安装图床，生成连接还是http | 安装与部署 | 部分确认 | 应用 URL、代理头和策略 URL 是不同来源。当前信任全部代理，安装时还会用请求主机初始化本地访问 URL。逐部署核对。 |
| [#367](https://github.com/lsky-org/lsky-pro/issues/367) | 可以支持oauth2吗？ | 安全与权限 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#366](https://github.com/lsky-org/lsky-pro/issues/366) | 通过 API 删除图片，接口返回成功，但是实际并没有被删除 | API 与集成 | 确认 | API 忽略删除影响行数。#810 是游客记录与用户 Token 权限不匹配，返回成功有误。物理删除、共享对象和 CDN 另有语义。 |
| [#365](https://github.com/lsky-org/lsky-pro/issues/365) | 使用CDN 上传图片为什么会提示服务端错误 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#363](https://github.com/lsky-org/lsky-pro/issues/363) | 2.0.2 创建相册时，使用回车进行确认会出错。 | 图库与界面 | 已修复 | aa988771 已处理创建或编辑相册的回车行为。 |
| [#362](https://github.com/lsky-org/lsky-pro/issues/362) | 关于api中token的建议 | 安全与权限 | 部分确认 | 默认 Token 不过期。已有清空全部 Token 接口，缺少按设备管理、单条撤销和作用域。 |
| [#361](https://github.com/lsky-org/lsky-pro/issues/361) | 能否添加批量生成已经上传了的照片的直链？ | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#359](https://github.com/lsky-org/lsky-pro/issues/359) | 角色组保存失败 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#358](https://github.com/lsky-org/lsky-pro/issues/358) | 手贱把数据库给删除了 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#356](https://github.com/lsky-org/lsky-pro/issues/356) | bug:网页公告留空后打开主页会弹出公告加载动画（一闪而过） | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#351](https://github.com/lsky-org/lsky-pro/issues/351) | 在宝塔安装了 Fileinfo 拓展，但安装程序显示未安装 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#349](https://github.com/lsky-org/lsky-pro/issues/349) | 希望能支持微软onedrive或sharepoint（国际版） | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#348](https://github.com/lsky-org/lsky-pro/issues/348) | 按照教程添加了腾讯COS但是不能上传图片 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#347](https://github.com/lsky-org/lsky-pro/issues/347) | ［功能建议］增设nsfwjs鉴图（RestfulAPI）接口 | API 与集成 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#346](https://github.com/lsky-org/lsky-pro/issues/346) | 请问可以对接Pic Go软件吗？ | API 与集成 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#345](https://github.com/lsky-org/lsky-pro/issues/345) | 建议将静态资源存放到&#96;/assets&#96;目录下 | 安装与部署 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#343](https://github.com/lsky-org/lsky-pro/issues/343) | 图片管理使用高级搜索，翻页会清除高级搜索的查询语句，显示全部图片的页面结果 | 图库与界面 | 已修复 | 683a0a48 为后台翻页保留搜索关键字。 |
| [#342](https://github.com/lsky-org/lsky-pro/issues/342) | 希望添加LDAP帐号支持 | 安全与权限 | 需求或咨询 | 按实际用例评审与既有能力的差异，当前证据不足以判为故障。 |
| [#337](https://github.com/lsky-org/lsky-pro/issues/337) | 希望可以自定义底部版权所有 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#336](https://github.com/lsky-org/lsky-pro/issues/336) | 请问下2.0的版本怎么生成 Token | 安全与权限 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#335](https://github.com/lsky-org/lsky-pro/issues/335) | 当数据库密码存在特殊字符时，.env写入DB_PASSWORD应加引号 | 安全与权限 | 确认 | 安装命令直接拼接 dotenv 值，特殊字符的序列化不可靠。 |
| [#333](https://github.com/lsky-org/lsky-pro/issues/333) | 创建本地存储策略，报错服务异常 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#332](https://github.com/lsky-org/lsky-pro/issues/332) | 文案错误 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#331](https://github.com/lsky-org/lsky-pro/issues/331) | 关于图片鉴定的问题 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#329](https://github.com/lsky-org/lsky-pro/issues/329) | ios post 请求upload接口出现CSRF token mismatch. | 安全与权限 | 部署与配置 | 网页上传需 CSRF。第三方客户端应走 API 并正确生成 multipart boundary，接口错误仍需改进。 |
| [#328](https://github.com/lsky-org/lsky-pro/issues/328) | 无法显示注册ip/冻结用户按钮失效 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#327](https://github.com/lsky-org/lsky-pro/issues/327) | 扩展插件需要适配 "manifest_version": 3 | API 与集成 | 历史关联 | 标题涉及旧版或旧栈，当前影响待核。 |
| [#326](https://github.com/lsky-org/lsky-pro/issues/326) | 图片水印文案颜色无法修改 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#325](https://github.com/lsky-org/lsky-pro/issues/325) | 原图保护开启后上传图片404无法打开 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#324](https://github.com/lsky-org/lsky-pro/issues/324) | V2.0版本，腾讯云存储策略上传出现错误 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#323](https://github.com/lsky-org/lsky-pro/issues/323) | 支持自适应访问协议 | 存储与外链 | 部署与配置 | 反代后须核对规范 URL、外部端口和可信代理配置。 |
| [#322](https://github.com/lsky-org/lsky-pro/issues/322) | 图片鉴黄等配置文档和操作流程 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#321](https://github.com/lsky-org/lsky-pro/issues/321) | 文档完善-2.0版本Docker部署 | 安装与部署 | 功能缺口 | 仓库已有 CLI 数据库初始化，但 CLI 不创建管理员，仓库没有生产容器发布和部署模板。 |
| [#320](https://github.com/lsky-org/lsky-pro/issues/320) | 如果图床支持onedrive存储会更好 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#319](https://github.com/lsky-org/lsky-pro/issues/319) | 【BUG】关于修改用户密码 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#318](https://github.com/lsky-org/lsky-pro/issues/318) | 浏览器支持 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#317](https://github.com/lsky-org/lsky-pro/issues/317) | 静态资源加载失败 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#316](https://github.com/lsky-org/lsky-pro/issues/316) | ICP备案号未显示 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#315](https://github.com/lsky-org/lsky-pro/issues/315) | 相册中显示图片数量不全 | 图库与界面 | 部分修复 | 当前相册有分页和加载路径，迁移后尺寸及计数另行核对。 |
| [#314](https://github.com/lsky-org/lsky-pro/issues/314) | 粘贴上传相关 | 上传与图片处理 | 部分确认 | 粘贴会替换原名，同一毫秒可能重复。历史报告使用默认服务器随机规则时的原因待核。 |
| [#313](https://github.com/lsky-org/lsky-pro/issues/313) | 希望增加自定义后缀 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#312](https://github.com/lsky-org/lsky-pro/issues/312) | 安装时http code0 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#311](https://github.com/lsky-org/lsky-pro/issues/311) | 1.6.4升级到2.0时升级程序报错 | 安装与部署 | 历史关联 | 标题涉及旧版或旧栈，当前影响待核。 |
| [#310](https://github.com/lsky-org/lsky-pro/issues/310) | 修改了上传大小限制为50M传10M的文件提示文件过大 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#309](https://github.com/lsky-org/lsky-pro/issues/309) | 修改上传大小后就会出现这个，有谁懂啥原因吗 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#308](https://github.com/lsky-org/lsky-pro/issues/308) | 开启剑皇后出现报错 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#307](https://github.com/lsky-org/lsky-pro/issues/307) | API上传疑似不支持含有中文日文这类非ASCII码的文字的文件 | API 与集成 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#306](https://github.com/lsky-org/lsky-pro/issues/306) | 上传图片后报错code32 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#303](https://github.com/lsky-org/lsky-pro/issues/303) | 希望能支持上传的图片带水印 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#302](https://github.com/lsky-org/lsky-pro/issues/302) | 不支持redis吗 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#301](https://github.com/lsky-org/lsky-pro/issues/301) | 希望添加更加多样和通用的远程存储方式 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#300](https://github.com/lsky-org/lsky-pro/issues/300) | 希望添加docker支持 | 安装与部署 | 功能缺口 | 仓库已有 CLI 数据库初始化，但 CLI 不创建管理员，仓库没有生产容器发布和部署模板。 |
| [#299](https://github.com/lsky-org/lsky-pro/issues/299) | 希望增加新的存储策略 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#298](https://github.com/lsky-org/lsky-pro/issues/298) | 开启鉴黄后出现SSL错误 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#296](https://github.com/lsky-org/lsky-pro/issues/296) | 使用oss返回的链接默认是http 应为https | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#295](https://github.com/lsky-org/lsky-pro/issues/295) | 新用户注册时，验证码图片不显示 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#294](https://github.com/lsky-org/lsky-pro/issues/294) | 图片上传的“估计”计算 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#293](https://github.com/lsky-org/lsky-pro/issues/293) | 一个图片上传的小Bug | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#292](https://github.com/lsky-org/lsky-pro/issues/292) | zip更新包，包损坏，请再重新打一下包，重新分发一下，谢谢 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#291](https://github.com/lsky-org/lsky-pro/issues/291) | oss策略下图片管理展示时能可选使用缩略图吗？ | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#290](https://github.com/lsky-org/lsky-pro/issues/290) | Upload Error：Call to undefined function imagecreatefromjpeg() | 其他 | 兼容待核 | 需要用生产 Imagick 构建验证格式、动画帧、EXIF 方向和格式特定质量。历史版本结论按当前流水线复验。 |
| [#289](https://github.com/lsky-org/lsky-pro/issues/289) | 可选关闭注册验证码 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#288](https://github.com/lsky-org/lsky-pro/issues/288) | 一点点的功能建议 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#287](https://github.com/lsky-org/lsky-pro/issues/287) | 上传文件失败，lsky-pro-1.6.4版本，1.6.0是可以正常使用的 | 上传与图片处理 | 历史关联 | 标题涉及旧版或旧栈，当前影响待核。 |
| [#285](https://github.com/lsky-org/lsky-pro/issues/285) | 图片删除了，但是还能访问 | 存储与外链 | 部分确认 | 当前删除状态存在假成功、缓存和对象回收问题。历史并发丢失比例及缓存原因未单独验证。 |
| [#284](https://github.com/lsky-org/lsky-pro/issues/284) | 问题：邮件失效 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#282](https://github.com/lsky-org/lsky-pro/issues/282) | 页面样式失效 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#281](https://github.com/lsky-org/lsky-pro/issues/281) | 2.0 版本计划 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#280](https://github.com/lsky-org/lsky-pro/issues/280) | 大佬有计划添加S3和WebDAV支持么 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#279](https://github.com/lsky-org/lsky-pro/issues/279) | 水印功能增强 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#278](https://github.com/lsky-org/lsky-pro/issues/278) | smtp问题 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#277](https://github.com/lsky-org/lsky-pro/issues/277) | 支持水印吗?看到好像说支持没看到在哪设置 | 上传与图片处理 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#276](https://github.com/lsky-org/lsky-pro/issues/276) | 使用picgo插件上传失败, | API 与集成 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#275](https://github.com/lsky-org/lsky-pro/issues/275) | GITHUB存储 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#274](https://github.com/lsky-org/lsky-pro/issues/274) | 能加github和gitee吗（github、gitee作为图床） | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#272](https://github.com/lsky-org/lsky-pro/issues/272) | 功能建议 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#271](https://github.com/lsky-org/lsky-pro/issues/271) | 页脚版权归属询问 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#270](https://github.com/lsky-org/lsky-pro/issues/270) | 数据库的建议 | 安装与部署 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#269](https://github.com/lsky-org/lsky-pro/issues/269) | 图片会自动删除吗 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#268](https://github.com/lsky-org/lsky-pro/issues/268) | 兰空图床 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#267](https://github.com/lsky-org/lsky-pro/issues/267) | 可以用阿里云oss的图片规则/保护吗，就是给网址加后缀 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#265](https://github.com/lsky-org/lsky-pro/issues/265) | 关于wordpress中无法使用External Media without Import插件导入图片到存储库 | API 与集成 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#264](https://github.com/lsky-org/lsky-pro/issues/264) | 腾讯云cos问题 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#262](https://github.com/lsky-org/lsky-pro/issues/262) | ftp问题 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#261](https://github.com/lsky-org/lsky-pro/issues/261) | Call to undefined function think\finfo_open() | 其他 | 历史关联 | 标题涉及旧版或旧栈，当前影响待核。 |
| [#260](https://github.com/lsky-org/lsky-pro/issues/260) | 如何设置访客不能上传图片 | 上传与图片处理 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#259](https://github.com/lsky-org/lsky-pro/issues/259) | 容量无法修改 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#258](https://github.com/lsky-org/lsky-pro/issues/258) | 支持onedrive嗎？ | 存储与外链 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#257](https://github.com/lsky-org/lsky-pro/issues/257) | 无法安装，提示SQLSTATE&#91;HY000&#93; &#91;2002&#93; php_network_getaddresses: getaddrinfo failed: Name or service not known | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#256](https://github.com/lsky-org/lsky-pro/issues/256) | 文档完善-使用Docker-Compose搭建-LNMP反向代理 | 安装与部署 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#255](https://github.com/lsky-org/lsky-pro/issues/255) | 希望可以增加一个修改本地储存位置的功能 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#254](https://github.com/lsky-org/lsky-pro/issues/254) | 希望支持smtp的STARTTLS加密方式 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#253](https://github.com/lsky-org/lsky-pro/issues/253) | 建议支持Minio | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#252](https://github.com/lsky-org/lsky-pro/issues/252) | 能否给又拍云上传增加路径功能 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#251](https://github.com/lsky-org/lsky-pro/issues/251) | 访问图片链接显示404 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#250](https://github.com/lsky-org/lsky-pro/issues/250) | 希望可以指定需鉴黄图片的格式 | 安全与权限 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#249](https://github.com/lsky-org/lsky-pro/issues/249) | 以中文开头的图片上传后文件名消失。 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#248](https://github.com/lsky-org/lsky-pro/issues/248) | 对于鉴黄的疑问 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#247](https://github.com/lsky-org/lsky-pro/issues/247) | smtp bug | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#246](https://github.com/lsky-org/lsky-pro/issues/246) | 能否开发PicGo平台的插件？或开发支持上传的软件？ | API 与集成 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#245](https://github.com/lsky-org/lsky-pro/issues/245) | 升级群晖到7.0后图床站无法正常显示的解决方法 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#244](https://github.com/lsky-org/lsky-pro/issues/244) | 能否支持github + jsdelivr | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#243](https://github.com/lsky-org/lsky-pro/issues/243) | 1.6.3版本在docker环境下上传图片权限问题 | 安全与权限 | 历史关联 | 标题涉及旧版或旧栈，当前影响待核。 |
| [#242](https://github.com/lsky-org/lsky-pro/issues/242) | &#91;可能是BUG&#93;“移动”图片时的文件夹显示问题 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#241](https://github.com/lsky-org/lsky-pro/issues/241) | 设置邮件出现问题 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#240](https://github.com/lsky-org/lsky-pro/issues/240) | &#91;可能是BUG&#93; undefined文件夹 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#239](https://github.com/lsky-org/lsky-pro/issues/239) | 迁移（及安装）出问题了 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#238](https://github.com/lsky-org/lsky-pro/issues/238) | 我的图片界面预览问题 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#237](https://github.com/lsky-org/lsky-pro/issues/237) | 希望能够支持svg图片预览 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#236](https://github.com/lsky-org/lsky-pro/issues/236) | 图片管理中选择某项后翻页的小BUG | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#233](https://github.com/lsky-org/lsky-pro/issues/233) | 可否加个百度鉴黄，现有的那个一直提示error。。。。 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#232](https://github.com/lsky-org/lsky-pro/issues/232) | 希望增加图片以及文件夹一键分享功能 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#231](https://github.com/lsky-org/lsky-pro/issues/231) | 希望支持多语言切换 | 其他 | 功能缺口 | 存在翻译文件，但主要界面硬编码中文，缺少完整语言切换。 |
| [#230](https://github.com/lsky-org/lsky-pro/issues/230) | 配置文件写入失败 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#229](https://github.com/lsky-org/lsky-pro/issues/229) | 鉴黄不起作用 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#228](https://github.com/lsky-org/lsky-pro/issues/228) | 希望能加上一个请求数和流量统计功能。 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#226](https://github.com/lsky-org/lsky-pro/issues/226) | 我的图片一直在加载中。。。 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#225](https://github.com/lsky-org/lsky-pro/issues/225) | PNG上传后自定义路径无效 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#224](https://github.com/lsky-org/lsky-pro/issues/224) | 您已超出本月免费请求的限制 (10000)。请访问mediumcontent.com 注册一个即用即付帐户。 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#223](https://github.com/lsky-org/lsky-pro/issues/223) | 采用Webuploader插件上传对接API出错 | API 与集成 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#222](https://github.com/lsky-org/lsky-pro/issues/222) | 图床支持Git仓库吗？ | 存储与外链 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#221](https://github.com/lsky-org/lsky-pro/issues/221) | 希望增加百度智能云的对象储存支持 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#220](https://github.com/lsky-org/lsky-pro/issues/220) | 目前七牛云配置似乎不可用 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#219](https://github.com/lsky-org/lsky-pro/issues/219) | 油猴脚本跨域登录信息获取问题 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#218](https://github.com/lsky-org/lsky-pro/issues/218) | LDAP功能 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#217](https://github.com/lsky-org/lsky-pro/issues/217) | 发现一个奇葩问题，最新1.6.3上传文件权限为600 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#215](https://github.com/lsky-org/lsky-pro/issues/215) | 鉴黄功能建议 | 安全与权限 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#214](https://github.com/lsky-org/lsky-pro/issues/214) | 图片鉴黄问题 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#213](https://github.com/lsky-org/lsky-pro/issues/213) | 安装时 访问首页--系统发生错误 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#212](https://github.com/lsky-org/lsky-pro/issues/212) | 文档完善-Docker搭建 | 安装与部署 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#211](https://github.com/lsky-org/lsky-pro/issues/211) | 迁移后出错 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#210](https://github.com/lsky-org/lsky-pro/issues/210) | 希望增加用户付费功能 | 其他 | 范围外 | 原帖属于企业版、支付、套餐或第三方扩展。开源 fork 独立评估相似功能。 |
| [#209](https://github.com/lsky-org/lsky-pro/issues/209) | d | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#207](https://github.com/lsky-org/lsky-pro/issues/207) | 远程FTP上传，无法访问图片 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#206](https://github.com/lsky-org/lsky-pro/issues/206) | 配置第二步完成后 然后呢？再次打开页面还是配置第一步的 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#205](https://github.com/lsky-org/lsky-pro/issues/205) | 不能打开 设置页面 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#204](https://github.com/lsky-org/lsky-pro/issues/204) | 更新1.6.3版本后新上传的图片无法通过URL访问 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#203](https://github.com/lsky-org/lsky-pro/issues/203) | 作者大大水印位置是否考虑可以设置成随机 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#202](https://github.com/lsky-org/lsky-pro/issues/202) | 水印设置可否由用户独立配置？ | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#200](https://github.com/lsky-org/lsky-pro/issues/200) | 希望支持游客限制 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#199](https://github.com/lsky-org/lsky-pro/issues/199) | 使用PHP8时，添加角色组导致错误 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#198](https://github.com/lsky-org/lsky-pro/issues/198) | 建议文件路径命名规则增加username | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#197](https://github.com/lsky-org/lsky-pro/issues/197) | docker支持 | 安装与部署 | 功能缺口 | 仓库已有 CLI 数据库初始化，但 CLI 不创建管理员，仓库没有生产容器发布和部署模板。 |
| [#196](https://github.com/lsky-org/lsky-pro/issues/196) | 自动添加水印？ | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#195](https://github.com/lsky-org/lsky-pro/issues/195) | 能够支持onedrive吗 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#194](https://github.com/lsky-org/lsky-pro/issues/194) | 粘贴图片上传命名问题 | 上传与图片处理 | 部分确认 | 粘贴会替换原名，同一毫秒可能重复。历史报告使用默认服务器随机规则时的原因待核。 |
| [#193](https://github.com/lsky-org/lsky-pro/issues/193) | 咨询一下部署该程序问题 | 安装与部署 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#192](https://github.com/lsky-org/lsky-pro/issues/192) | PHP8支持 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#191](https://github.com/lsky-org/lsky-pro/issues/191) | 图片鉴黄按钮问题问题 | 安全与权限 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#190](https://github.com/lsky-org/lsky-pro/issues/190) | 希望API支持批量上传 | API 与集成 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#189](https://github.com/lsky-org/lsky-pro/issues/189) | 能否对接一下sm.ms | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#188](https://github.com/lsky-org/lsky-pro/issues/188) | 登录后需要强制刷新一次才能使用 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#187](https://github.com/lsky-org/lsky-pro/issues/187) | 能否添加多个存储账户功能 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#186](https://github.com/lsky-org/lsky-pro/issues/186) | 加个水印功能把，在后台设置好水印图片。开启就自动给图片添加水印。 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#185](https://github.com/lsky-org/lsky-pro/issues/185) | 手机号注册和火狐浏览器无法点击链接反馈 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#184](https://github.com/lsky-org/lsky-pro/issues/184) | &#91;Doc&#93; Nginx 的配置方式修改 | 安装与部署 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#183](https://github.com/lsky-org/lsky-pro/issues/183) | 安装问题 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#182](https://github.com/lsky-org/lsky-pro/issues/182) | 腾讯COS存储桶位置离服务器主机地理位置较远时容易发生页面错误 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#181](https://github.com/lsky-org/lsky-pro/issues/181) | 1.6.1版本上传图片无法显示问题 | 上传与图片处理 | 历史关联 | 标题涉及旧版或旧栈，当前影响待核。 |
| [#180](https://github.com/lsky-org/lsky-pro/issues/180) | 上传后希望能返回图片的 id | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#179](https://github.com/lsky-org/lsky-pro/issues/179) | 请求修复后台IP接口失效的问题 | API 与集成 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#178](https://github.com/lsky-org/lsky-pro/issues/178) | 万人血书 增加多远程策略qwq | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#177](https://github.com/lsky-org/lsky-pro/issues/177) | 希望支持https协议 | 安装与部署 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#176](https://github.com/lsky-org/lsky-pro/issues/176) | 能不能生成一个展示图片的网址，必须输入密码才能查看 | 安全与权限 | 功能缺口 | 存储固定直链与应用输出可选。缺少带访问控制的短期链接和策略级签名重定向。 |
| [#174](https://github.com/lsky-org/lsky-pro/issues/174) | 【系统管理-图片管理】不显示上传过的图片 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#173](https://github.com/lsky-org/lsky-pro/issues/173) | 什么时候支持下web直传呢？ | 其他 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#172](https://github.com/lsky-org/lsky-pro/issues/172) | 能不能做成clouddeve那样上传视频? | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#170](https://github.com/lsky-org/lsky-pro/issues/170) | 上传优化，qq里无法上传，微信只能单个上传 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#169](https://github.com/lsky-org/lsky-pro/issues/169) | 几个新功能请求&amp;bug反馈 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#168](https://github.com/lsky-org/lsky-pro/issues/168) | 请求添加svg的上传支持 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#167](https://github.com/lsky-org/lsky-pro/issues/167) | 多图片上传，一键复制链接 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#166](https://github.com/lsky-org/lsky-pro/issues/166) | 图片管理中出现好多空白占位空图 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#165](https://github.com/lsky-org/lsky-pro/issues/165) | 你好这个可以支持华为云的对象储存吗 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#164](https://github.com/lsky-org/lsky-pro/issues/164) | 似乎上传同样的文件，返回的URL是第一次上传的，可以手动关闭这个特性吗？ | 存储与外链 | 行为可解释 | 当前同策略可共享物理字节但仍创建各自记录，需分别统计逻辑图片和物理容量。 |
| [#163](https://github.com/lsky-org/lsky-pro/issues/163) | 在我的图片栏目不能新建文件夹 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#162](https://github.com/lsky-org/lsky-pro/issues/162) | 分用户设置存储路径（云存储和本地分开设置路径） | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#161](https://github.com/lsky-org/lsky-pro/issues/161) | 希望第三方云储存能够支持OneDrive | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#160](https://github.com/lsky-org/lsky-pro/issues/160) | 两个新功能能否考虑下？ | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#159](https://github.com/lsky-org/lsky-pro/issues/159) | 当文件路径设置为空时，图片链接存在问题 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#158](https://github.com/lsky-org/lsky-pro/issues/158) | 1.6.1版本中，调整最大上传大小后，图床主页面显示会报错 | 存储与外链 | 历史关联 | 标题涉及旧版或旧栈，当前影响待核。 |
| [#157](https://github.com/lsky-org/lsky-pro/issues/157) | 请问怎么获取token | 安全与权限 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#156](https://github.com/lsky-org/lsky-pro/issues/156) | 当两张图片内容不同而MD5相同时，上传会出错，只能上传其中一张 | 上传与图片处理 | 历史关联 | 旧版出现相近症状。当前原图去重用 MD5 和 SHA-1，缩略图仅用 MD5。与当前 #798 按各路径验证。 |
| [#155](https://github.com/lsky-org/lsky-pro/issues/155) | 设置管理员账号时配置文件写入失败 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#154](https://github.com/lsky-org/lsky-pro/issues/154) | 可以支持base64格式的图片上传吗 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#153](https://github.com/lsky-org/lsky-pro/issues/153) | 图片复制链接可以支持https吗 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#152](https://github.com/lsky-org/lsky-pro/issues/152) | 大图片超时上传失败 | 上传与图片处理 | 部分确认 | 上传主请求同步解码和缩略图，存在提交后仍因处理或外部超时返回失败的窗口。原帖各自的资源阈值待验证。 |
| [#151](https://github.com/lsky-org/lsky-pro/issues/151) | 发现一个bug：同一文件夹图片超60后点击重置数据图片显示数目出错 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#150](https://github.com/lsky-org/lsky-pro/issues/150) | 希望可以添加下载功能 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#149](https://github.com/lsky-org/lsky-pro/issues/149) | 如何打赏？ | 其他 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#148](https://github.com/lsky-org/lsky-pro/issues/148) | 好像删除账号，不能删除账号下的数据（图片） | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#147](https://github.com/lsky-org/lsky-pro/issues/147) | Can the gitee and github libraries be used as storage? | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#146](https://github.com/lsky-org/lsky-pro/issues/146) | 生成的链接为http | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#145](https://github.com/lsky-org/lsky-pro/issues/145) | is it in english too? | 其他 | 功能缺口 | 存在翻译文件，但主要界面硬编码中文，缺少完整语言切换。 |
| [#144](https://github.com/lsky-org/lsky-pro/issues/144) | 希望能用上Onedrive+反向代理sharepoint域名选项 | 安装与部署 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#142](https://github.com/lsky-org/lsky-pro/issues/142) | 安装第3步创建管理员点击无反应 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#141](https://github.com/lsky-org/lsky-pro/issues/141) | 希望可以弄一个上传文件类型用户组 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#140](https://github.com/lsky-org/lsky-pro/issues/140) | 能否加个api对外放开 | API 与集成 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#139](https://github.com/lsky-org/lsky-pro/issues/139) | 作者大大，可以增加京东云对象储存吗 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#138](https://github.com/lsky-org/lsky-pro/issues/138) | 图片上传后能不能添加水印 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#137](https://github.com/lsky-org/lsky-pro/issues/137) | Google浏览器兼容问题，图片下载地址没有展开 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#135](https://github.com/lsky-org/lsky-pro/issues/135) | 图片多次上传，images表数据重复，页面展示重复 | 上传与图片处理 | 行为可解释 | 当前同策略可共享物理字节但仍创建各自记录，需分别统计逻辑图片和物理容量。 |
| [#134](https://github.com/lsky-org/lsky-pro/issues/134) | 会考虑支持视频上传管理吗 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#132](https://github.com/lsky-org/lsky-pro/issues/132) | 设置页面无法保存 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#131](https://github.com/lsky-org/lsky-pro/issues/131) | 上传错误：非法的图片文件 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#130](https://github.com/lsky-org/lsky-pro/issues/130) | 安装报错 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#129](https://github.com/lsky-org/lsky-pro/issues/129) | 开启鉴黄报错 Url not accessible or malformed image | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#128](https://github.com/lsky-org/lsky-pro/issues/128) | 可疑图片只能查看第一页 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#127](https://github.com/lsky-org/lsky-pro/issues/127) | 【系统管理-图片管理】图片预览模式提议 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#126](https://github.com/lsky-org/lsky-pro/issues/126) | &#91;Bug Report&#93;缺乏对webp格式图片的支持 | 上传与图片处理 | 兼容待核 | 需要用生产 Imagick 构建验证格式、动画帧、EXIF 方向和格式特定质量。历史版本结论按当前流水线复验。 |
| [#125](https://github.com/lsky-org/lsky-pro/issues/125) | &#91;Bug Report&#93;目前文档中缺乏对.env.example重要性的说明 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#124](https://github.com/lsky-org/lsky-pro/issues/124) | 希望添加网络相册同步或者云盘同步 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#122](https://github.com/lsky-org/lsky-pro/issues/122) | 请问底部内容是否可以修改 | 其他 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#121](https://github.com/lsky-org/lsky-pro/issues/121) | 希望大佬加一个8位随机字符串重命名 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#120](https://github.com/lsky-org/lsky-pro/issues/120) | 使用又怕云USS存储策略上传时报错 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#119](https://github.com/lsky-org/lsky-pro/issues/119) | 安装时环境提示都是绿色的，但是到第四步就500错误，帮忙看看谢谢 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#118](https://github.com/lsky-org/lsky-pro/issues/118) | 腾讯云存储设置了图片处理样式，图床环境变量那里开启样式后，得出来的图片连接会出现两次样式 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#117](https://github.com/lsky-org/lsky-pro/issues/117) | 可以增加个图片压缩的功能吗 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#116](https://github.com/lsky-org/lsky-pro/issues/116) | 您好，我遇到一个问题需要您的帮助 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#115](https://github.com/lsky-org/lsky-pro/issues/115) | 图片预览的建议 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#114](https://github.com/lsky-org/lsky-pro/issues/114) | 功能建议 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#113](https://github.com/lsky-org/lsky-pro/issues/113) | 无法删除的bug问题 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#112](https://github.com/lsky-org/lsky-pro/issues/112) | 数据库密码带有符号会导致php报500错误 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#111](https://github.com/lsky-org/lsky-pro/issues/111) | 老版本升级的时候数据库对有几个数据库结构没升级导致后台错误。 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#110](https://github.com/lsky-org/lsky-pro/issues/110) | 能否增加注册时邮箱验证的功能 | 安全与权限 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#108](https://github.com/lsky-org/lsky-pro/issues/108) | 没有注册邮箱验证的功能吗 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#107](https://github.com/lsky-org/lsky-pro/issues/107) | 建议加入一些功能 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#106](https://github.com/lsky-org/lsky-pro/issues/106) | 关于阿里云oss对接 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#105](https://github.com/lsky-org/lsky-pro/issues/105) | 升级出现安装包损坏错误 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#104](https://github.com/lsky-org/lsky-pro/issues/104) | 有关于图片管理的建议 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#103](https://github.com/lsky-org/lsky-pro/issues/103) | 1 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#102](https://github.com/lsky-org/lsky-pro/issues/102) | 上传相同图片时，虽然不会重复上传，但首页的图片数量仍然会增加。 | 上传与图片处理 | 行为可解释 | 当前同策略可共享物理字节但仍创建各自记录，需分别统计逻辑图片和物理容量。 |
| [#100](https://github.com/lsky-org/lsky-pro/issues/100) | 新功能建议(4个) | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#99](https://github.com/lsky-org/lsky-pro/issues/99) | 1.6 无法搜索文件名 | 上传与图片处理 | 历史关联 | 标题涉及旧版或旧栈，当前影响待核。 |
| [#98](https://github.com/lsky-org/lsky-pro/issues/98) | 新功能建议 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#97](https://github.com/lsky-org/lsky-pro/issues/97) | 希望能用上Onedrive | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#96](https://github.com/lsky-org/lsky-pro/issues/96) | 文件命名规则的{original}变量没有用 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#95](https://github.com/lsky-org/lsky-pro/issues/95) | 更新到1.6.0以后管理后台无法看到之前上传的图像 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#94](https://github.com/lsky-org/lsky-pro/issues/94) | 500 Internal Server Error | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#92](https://github.com/lsky-org/lsky-pro/issues/92) | 文件别名在哪设置？ | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#91](https://github.com/lsky-org/lsky-pro/issues/91) | 对于下个版本的建议 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#90](https://github.com/lsky-org/lsky-pro/issues/90) | 希望增加静态文件CDN加速的功能 | 安装与部署 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#89](https://github.com/lsky-org/lsky-pro/issues/89) | 上传大文件(&gt;50MB)报错 | 上传与图片处理 | 部分确认 | 上传主请求同步解码和缩略图，存在提交后仍因处理或外部超时返回失败的窗口。原帖各自的资源阈值待验证。 |
| [#88](https://github.com/lsky-org/lsky-pro/issues/88) | 请问一下有关于接口的相关说明吗？ | API 与集成 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#87](https://github.com/lsky-org/lsky-pro/issues/87) | 希望添加直链存储方式 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#86](https://github.com/lsky-org/lsky-pro/issues/86) | 添加两个功能的建议 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#85](https://github.com/lsky-org/lsky-pro/issues/85) | 可以增加一个个图片加水印的功能吗？ | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#84](https://github.com/lsky-org/lsky-pro/issues/84) | 阿里云oss内网访问问题 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#83](https://github.com/lsky-org/lsky-pro/issues/83) | V1.5.6版本api上传失败 | API 与集成 | 历史关联 | 标题涉及旧版或旧栈，当前影响待核。 |
| [#82](https://github.com/lsky-org/lsky-pro/issues/82) | 更新检测建议 | 安装与部署 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#81](https://github.com/lsky-org/lsky-pro/issues/81) | 上传大小问题 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#80](https://github.com/lsky-org/lsky-pro/issues/80) | 图片鉴黄的问题 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#79](https://github.com/lsky-org/lsky-pro/issues/79) | 上传竖立的照片自动变成横照 | 上传与图片处理 | 兼容待核 | 需要用生产 Imagick 构建验证格式、动画帧、EXIF 方向和格式特定质量。历史版本结论按当前流水线复验。 |
| [#78](https://github.com/lsky-org/lsky-pro/issues/78) | 是否支持非注册用户禁止上传功能 | 安全与权限 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#77](https://github.com/lsky-org/lsky-pro/issues/77) | 建议添加在上传时选择上传的文件夹，这样可以规避整理图片麻烦的问题 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#76](https://github.com/lsky-org/lsky-pro/issues/76) | 重大问题，有时候某些图片会404找不到 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#75](https://github.com/lsky-org/lsky-pro/issues/75) | 图床一般用的都是本地存储策略，支持添加图片水印就完美了。 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#74](https://github.com/lsky-org/lsky-pro/issues/74) | 能否支持删除上传的图片在第三方如七牛云同步 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#73](https://github.com/lsky-org/lsky-pro/issues/73) | 建议增加图片处理规则 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#71](https://github.com/lsky-org/lsky-pro/issues/71) | Fatal error: Call to undefined function str_rand() | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#70](https://github.com/lsky-org/lsky-pro/issues/70) | 建议添加封禁IP功能。 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#69](https://github.com/lsky-org/lsky-pro/issues/69) | 建议增加图片粘贴上传功能 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#68](https://github.com/lsky-org/lsky-pro/issues/68) | 建议添加一个站内信功能，或者公告模块，有时候有的调整没办法及时通知用户 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#67](https://github.com/lsky-org/lsky-pro/issues/67) | 建议加上第三方存储的webp功能 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#66](https://github.com/lsky-org/lsky-pro/issues/66) | 第三方储存能不能加上cloudinary | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#65](https://github.com/lsky-org/lsky-pro/issues/65) | 希望大佬新增一个返回所有照片的API接口 | API 与集成 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#64](https://github.com/lsky-org/lsky-pro/issues/64) | API上传经常出现timeout | API 与集成 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#63](https://github.com/lsky-org/lsky-pro/issues/63) | 苹果8，QQ浏览器，网页上传多张不同的图片时，上传后的多张图片均成了同一张图片 | 上传与图片处理 | 历史关联 | 旧版出现相近症状。当前原图去重用 MD5 和 SHA-1，缩略图仅用 MD5。与当前 #798 按各路径验证。 |
| [#62](https://github.com/lsky-org/lsky-pro/issues/62) | “图片管理”菜单中，“全部”选项并不能查看全部！ | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#61](https://github.com/lsky-org/lsky-pro/issues/61) | 你好，能否在子文件中增加上传按钮？这个功能比较常用。 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#60](https://github.com/lsky-org/lsky-pro/issues/60) | 第三方存储能不能加上backblaze b2 或者aws s3 之类的？ | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#59](https://github.com/lsky-org/lsky-pro/issues/59) | 图片排序问题 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#58](https://github.com/lsky-org/lsky-pro/issues/58) | 增加功能建议 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#57](https://github.com/lsky-org/lsky-pro/issues/57) | 开放注册之后，管理员在后台将用户删除之后，控制台还是显示的用户数量不会减少，并且数据库中lsky_users字段未删除 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#56](https://github.com/lsky-org/lsky-pro/issues/56) | 能不能加个相册功能 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#55](https://github.com/lsky-org/lsky-pro/issues/55) | Windows IIS环境下,安装提示 ini_set(): A session is active. You cannot change the session module's ini settings at this time | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#54](https://github.com/lsky-org/lsky-pro/issues/54) | 使用 Caddy 的话重写规则 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#53](https://github.com/lsky-org/lsky-pro/issues/53) | 查看演示页面无法打开 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#52](https://github.com/lsky-org/lsky-pro/issues/52) | 使用配置完邮箱后没有短信验证 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#51](https://github.com/lsky-org/lsky-pro/issues/51) | 怎么安装完成后样式全乱了呢，如图 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#50](https://github.com/lsky-org/lsky-pro/issues/50) | 样式有几个小问题 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#49](https://github.com/lsky-org/lsky-pro/issues/49) | 希望增加后台图片管理按照用户筛选以及批量导出图片地址 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#48](https://github.com/lsky-org/lsky-pro/issues/48) | 建议添加阿里云邮件推送 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#47](https://github.com/lsky-org/lsky-pro/issues/47) | 运行速度慢 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#46](https://github.com/lsky-org/lsky-pro/issues/46) | 能加个公告栏吗？ | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#45](https://github.com/lsky-org/lsky-pro/issues/45) | 建议添加阿里云鉴黄接口 | 安全与权限 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#44](https://github.com/lsky-org/lsky-pro/issues/44) | 图片是先上传到服务器，然后在上传到第三方云存储的吗 | 存储与外链 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#43](https://github.com/lsky-org/lsky-pro/issues/43) | 添加新功能：自定义图片名，根据图片名搜索，自定义OSS存储的“上传的目录” | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#42](https://github.com/lsky-org/lsky-pro/issues/42) | 使用宝塔面板windows版 出现无法上传图片问题 请求帮助 | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#41](https://github.com/lsky-org/lsky-pro/issues/41) | 上传500 Trying to get property of non-object | 上传与图片处理 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#40](https://github.com/lsky-org/lsky-pro/issues/40) | 能否考虑增加一个“提供能删除图片的链接”的功能呢？ | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#39](https://github.com/lsky-org/lsky-pro/issues/39) | 本站已存储图片展示单页 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#38](https://github.com/lsky-org/lsky-pro/issues/38) | 统计代码设置问题 | 图库与界面 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#37](https://github.com/lsky-org/lsky-pro/issues/37) | 不知道大佬后面会不会开发广告位、在线支付等功能 | 其他 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#36](https://github.com/lsky-org/lsky-pro/issues/36) | 请求增加防盗链功能 | 安全与权限 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#35](https://github.com/lsky-org/lsky-pro/issues/35) | 上传文件经常失败 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#34](https://github.com/lsky-org/lsky-pro/issues/34) | 希望能提供自定义水印功能、图片压缩等级 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#33](https://github.com/lsky-org/lsky-pro/issues/33) | 批量外链图片功能。 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#31](https://github.com/lsky-org/lsky-pro/issues/31) | LNMP(lnmp.org) 环境下该网页无法正常运作 HTTP ERROR 500 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#30](https://github.com/lsky-org/lsky-pro/issues/30) | 增加一个文件名按 md5 命名的。多个文件只传一次。返回同一个文件的地址。节省空间 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#29](https://github.com/lsky-org/lsky-pro/issues/29) | 您好，是否考虑增加同时上传至多个云存储的功能？ | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#27](https://github.com/lsky-org/lsky-pro/issues/27) | 使用 IPv6 地址注册时出错，存储注册 IP 的数据库字段长度不足 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#26](https://github.com/lsky-org/lsky-pro/issues/26) | api接口文档 | API 与集成 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#25](https://github.com/lsky-org/lsky-pro/issues/25) | 允许二开吗大佬 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#24](https://github.com/lsky-org/lsky-pro/issues/24) | 你好，能否添加一个上传至远程ftp服务器的功能呢 | 存储与外链 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#23](https://github.com/lsky-org/lsky-pro/issues/23) | 建议升级tp版本 | 安装与部署 | 历史关联 | 标题涉及旧版或旧栈，当前影响待核。 |
| [#22](https://github.com/lsky-org/lsky-pro/issues/22) | 请问为什么我上传以后直接打开文件是没问题的，但在图片管理里面却打不开？ | 上传与图片处理 | 部署或用法 | 按标题筛查，需结合版本和配置判定。 |
| [#21](https://github.com/lsky-org/lsky-pro/issues/21) | 后期不知道能不能添加,自定义水印,:-) | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#20](https://github.com/lsky-org/lsky-pro/issues/20) | 能不能用QQ或者微信截图之后不用保存本地，直接ctrl+v就可以上传 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#17](https://github.com/lsky-org/lsky-pro/issues/17) | 希望添加上传命名原文件名规则 | 上传与图片处理 | 行为可解释 | 已有 `{filename}`。原名适合展示，持久对象键应维持唯一且独立。 |
| [#16](https://github.com/lsky-org/lsky-pro/issues/16) | 建议添加记住登陆 | 安全与权限 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#15](https://github.com/lsky-org/lsky-pro/issues/15) | Users-&gt;default_folder 这个错误是admin账户没有创建默认目录？ | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#13](https://github.com/lsky-org/lsky-pro/issues/13) | 更新到1.4.2时，后台仍然提示有此版本更新 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#12](https://github.com/lsky-org/lsky-pro/issues/12) | 希望可以添加***已托管*** 张图片 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#11](https://github.com/lsky-org/lsky-pro/issues/11) | php7.3下程序出现错误 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#10](https://github.com/lsky-org/lsky-pro/issues/10) | 希望在后台-我的图片-增加“分类管理”功能 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#9](https://github.com/lsky-org/lsky-pro/issues/9) | 开启图片鉴黄后，无法上传图片 | 安全与权限 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#8](https://github.com/lsky-org/lsky-pro/issues/8) | 以阿里云OSS为存储后端如何使用CDN？ | 安装与部署 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#7](https://github.com/lsky-org/lsky-pro/issues/7) | 同时上传线程影响前端选择图片数量 | 上传与图片处理 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#6](https://github.com/lsky-org/lsky-pro/issues/6) | 程序严重安全缺陷反馈建议 | 安全与权限 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#5](https://github.com/lsky-org/lsky-pro/issues/5) | 后台部分项目显示乱码 | 其他 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#4](https://github.com/lsky-org/lsky-pro/issues/4) | 希望添加上传命名原文件名规则 | 上传与图片处理 | 行为可解释 | 已有 `{filename}`。原名适合展示，持久对象键应维持唯一且独立。 |
| [#3](https://github.com/lsky-org/lsky-pro/issues/3) | 安装步骤第三步500响应 | 安装与部署 | 历史待核 | 历史记录完成初筛，当前分支影响未逐项验证。 |
| [#2](https://github.com/lsky-org/lsky-pro/issues/2) | 希望可以有“新建文件夹”功能 | 图库与界面 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
| [#1](https://github.com/lsky-org/lsky-pro/issues/1) | 开放注册与关闭注册模板修正 | 安全与权限 | 需求或咨询 | 按标题或标签筛查，现有能力需核对。 |
