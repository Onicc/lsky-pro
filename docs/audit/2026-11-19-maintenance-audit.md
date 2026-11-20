# Lsky Pro 接手审计与重构路线

审计基线为 `Onicc/lsky-pro` 的 `master@38d52c46`，日期为 2026-11-19（洛杉矶时间）。上游已停止开源版功能与缺陷维护，当前 fork 含上游后续合并的依赖更新和若干修复。本轮交付接手评估、修复设计和验收条件。 [上游仓库](https://github.com/lsky-org/lsky-pro) · [上游 Issue 台账](./2026-11-19-upstream-issues.md)

**发布前优先处理三件事。** 确保上传写入不可覆盖的对象键，隔离公开上传内容并落实私有权限，在 fork 发布链中替换旧的在线自更新。随后升级运行时和框架、把图片处理移到独立 worker，按上传和图库两个用户旅程逐步替换前端。

## 1. 上游问题归并

快照含 **627** 条 Issue，其中 **161** 条开放、**466** 条关闭，已排除 PR。248 条按问题正文、评论、现行源码、适配能力或历史提交做了定向核对或归并，覆盖全部开放条目。其余按标题、标签和版本线索初筛。开放区中，2 条关联到实测问题、8 条有明确源码依据、27 条部分确认；这些是 **Issue 条数**，同一缺陷常对应多个帖子。证据不足的原帖保留待核。

| 主题 | 对当前 fork 的判定 | 代表 Issue |
| --- | --- | --- |
| 上传成功却串图 | 历史共享临时文件缺陷已有补丁，当前仍有独立的对象覆盖风险。同名、同秒命名和重试需要分别评估。 | [#798](https://github.com/lsky-org/lsky-pro/issues/798) · [#603](https://github.com/lsky-org/lsky-pro/issues/603) · [修复 #851](https://github.com/lsky-org/lsky-pro/pull/851) · [旧版 #63](https://github.com/lsky-org/lsky-pro/issues/63) |
| 格式、缩略图和大图 | 实测配置转 WebP 时得到 PNG 字节。超大图可能触发处理资源或请求超时，单帖阈值待生产拓扑验证。缩略图 PNG 输出和缩放条件可见于代码。 | [#804](https://github.com/lsky-org/lsky-pro/issues/804) · [#733](https://github.com/lsky-org/lsky-pro/issues/733) · [#861](https://github.com/lsky-org/lsky-pro/issues/861) · [#809](https://github.com/lsky-org/lsky-pro/issues/809) · [#768](https://github.com/lsky-org/lsky-pro/issues/768) |
| 删除假成功 | 删除 API 返回成功时可能没有删除任何记录。#810 的评论明确是管理员 Token 删除游客图片，当前 API 只删 Token 所属用户的记录。共享对象及 CDN 的删除另有语义。 | [#810](https://github.com/lsky-org/lsky-pro/issues/810) · [#383](https://github.com/lsky-org/lsky-pro/issues/383) · [#606](https://github.com/lsky-org/lsky-pro/issues/606) |
| WebDAV、SFTP、MinIO 外链 | 写入与公共读取各自配置。#877 已确认写入成功，根域名和前缀的组合缺少读取映射。#780 日志指向 TLS 主机名不匹配。#885 缺服务器侧证据。 | [#877](https://github.com/lsky-org/lsky-pro/issues/877) · [#832](https://github.com/lsky-org/lsky-pro/issues/832) · [#823](https://github.com/lsky-org/lsky-pro/issues/823) · [#780](https://github.com/lsky-org/lsky-pro/issues/780) · [#885](https://github.com/lsky-org/lsky-pro/issues/885) |
| 安装与配置 | dotenv 序列化、安装预检条件和部署可诊断性确有缺陷。SQLite 是否支持所需 JSON 函数须在 PHP 链接的 SQLite 上检查。Docker 安装诉求已有部分 CLI 能力。 | [#684](https://github.com/lsky-org/lsky-pro/issues/684) · [#671](https://github.com/lsky-org/lsky-pro/issues/671) · [#756](https://github.com/lsky-org/lsky-pro/issues/756) · [#879](https://github.com/lsky-org/lsky-pro/issues/879) |
| 权限、凭证、账户 | 存储和邮件配置可明文保存在 DB。密码重置响应区分邮箱是否存在。API 分页、指定相册、备份导出和签名重定向是独立需求。 | [#575](https://github.com/lsky-org/lsky-pro/issues/575) · [#460](https://github.com/lsky-org/lsky-pro/issues/460) · [#858](https://github.com/lsky-org/lsky-pro/issues/858) · [#660](https://github.com/lsky-org/lsky-pro/issues/660) · [#865](https://github.com/lsky-org/lsky-pro/issues/865) · [#881](https://github.com/lsky-org/lsky-pro/issues/881) |
| 图库与可用性 | 搜索竞态、公告样式、备案入口和复制入口有当前依据。#730 虽为企业版反馈，开源版也按响应完成顺序追加裸链接。 | [#499](https://github.com/lsky-org/lsky-pro/issues/499) · [#739](https://github.com/lsky-org/lsky-pro/issues/739) · [#757](https://github.com/lsky-org/lsky-pro/issues/757) · [#432](https://github.com/lsky-org/lsky-pro/issues/432) · [#730](https://github.com/lsky-org/lsky-pro/issues/730) |

## 2. 上传完整性与隐藏缺陷

### 串图有三种可区分的表现

| 优先级及机制 | 当前证据 | 修复与验收 |
| --- | --- | --- |
| **P0　不同内容写入同一物理键** | `store()` 按策略与 MD5/SHA-1 找同内容。新内容直接 `writeStream`，数据库唯一约束只覆盖公开 `key`，即使两条记录的公开 key 不同，也能指向同一物理路径。可配置的 `{filename}`、`{timestamp}` 等规则会产生同一策略内的重名，后写对象可取代先写对象。删除按内容哈希判定共享，可能删除仍被其他记录指向的重名路径。 [上传逻辑](../../app/Services/ImageService.php#L182) · [命名规则](../../app/Services/ImageService.php#L634) · [删除钩子](../../app/Models/Image.php#L103) · [表结构](../../database/migrations/2021_12_11_191158_create_images_table.php#L21) | 新写入使用服务端生成的不可变对象键，原文件名保留用于展示，日期等规则可保留为目录。按真实存储命名空间和规范化对象键建立唯一保留记录，适配器支持时加条件创建。同一底层根目录/桶前缀由多个策略共享时同样须保证唯一。验收对每条成功记录验证外链、数据库、对象及缩略图确实来自同一输入。 [S3 条件写入](https://docs.aws.amazon.com/AmazonS3/latest/userguide/conditional-writes.html) |
| **P0　转换阶段仍共享临时文件** | 当前已有 #851，旧版不同图片使用相同转换文件的路径已改为输入内容哈希。相同内容并发、不同转换规则或提前清理时，仍共享同一路径。水印覆盖还会写回处理路径，异常分支缺统一清理。 [转换代码](../../app/Services/ImageService.php#L154) · [清理](../../app/Services/ImageService.php#L268) · [补丁](https://github.com/lsky-org/lsky-pro/commit/bdd440d38f817be164502bb92a257f5cb8438bfb) | 每次处理创建独占临时目录，使用明确编码器，流句柄和临时文件都在 `finally` 释放。缩略图先写临时文件再原子发布，每种规格使用独立版本键。测试相同输入的并发处理、不同规则和单个请求被取消时的完整性。 |
| **P1　前端队列与链接顺序不一致** | 预览回调完成后才入队，上传回调完成后将链接追加到脱离文件行的列表。浏览器记录中移动端预览的两张图顺序与选择顺序不同。双击、清空和重试时还缺稳定的进行中状态。 [队列](../../resources/views/components/upload.blade.php#L171) · [结果处理](../../resources/views/components/upload.blade.php#L233) · [清空](../../resources/views/components/upload.blade.php#L315) | 文件选择即分配客户端 ID 与序号。行绑定自己的 `image_id`、缩略图和链接，复制导出按选择顺序。清除运行任务前取消，重试复用幂等键，只允许失败/取消项重新提交。用故意错开的普通响应时延验证列表和字节对应。 |

遇到存量错图，先做只读对账。按实际存储命名空间和对象键找多记录共享路径，逐批检查路径是否存在、对象字节指纹是否与记录一致、历史版本是否可用，再核对 CDN 边缘、源站和缩略图。记录上现已不一致的哈希无法凭空还原字节。可用来源是对象版本、快照、备份或用户原件。修复历史数据时先发布对照报告和旧链接映射，回源验证后再按键清理缓存。

### 其他数据与功能问题

| 优先级 | 当前证据 | 修复与验收 |
| --- | --- | --- |
| P1　格式与元数据错配 | `save()` 的路径没有扩展名，也没传格式参数。Intervention v2 会回退到源 MIME，代码却用配置更新输出后缀，重新包装上传文件时也会改变记录中的原文件名。以普通 PNG 上传实测，返回 `status=true` 与 `.webp` 路径，下载 HTTP 200，实际字节和返回 MIME 均是 PNG。 [转换代码](../../app/Services/ImageService.php#L154) | 显式编码并以最终字节识别格式、MIME 和尺寸，独立保留原始上传文件名。保存转换设置前验证驱动支持目标格式。覆盖 PNG/JPEG/WebP、透明度、动画、方向、ICC/GPS、动态和覆盖水印。PNG 压缩、JPEG/WebP 视觉质量分别定义。 |
| P1　图片已保存，客户端收到失败 | 对象写、图片记录、计数、审核、缩略图分别执行。DB 异常只覆盖了 `save()` 返回 false 的补偿分支，计数失败和审核异常也可发生在数据持久化之后。尺寸校验在初次解码之后，缩略图仅在宽高同时超限时缩放，并在请求内尝试将内存上限改为 512 MB。 [上传与提交](../../app/Services/ImageService.php#L204) · [缩略图](../../app/Services/ImageService.php#L549) | 上传完成后持久返回上传 ID。图片记录、配额和 outbox 同一短事务，后续审核和衍生图异步。资源门限提前，长边缩放，失败可重试，孤儿由定期对账回收。测试存储中断、事务失败、worker 重启及索引一致性。 |
| P1　删除的数据库与对象状态可能分叉 | 控制器丢弃 `deleteImages()` 数量。模型在数据库删除事务中调用外部存储并吞异常，回滚不能恢复对象。两个并发删除可分别看到另一个引用，留下孤儿。切换组或删除旧策略也可能造成旧图地址/关联失效。 [API 删除](../../app/Http/Controllers/Api/V1/ImageController.php#L76) · [删除事务](../../app/Services/UserService.php#L22) · [策略删除](../../app/Http/Controllers/Admin/StrategyController.php#L64) | 用外键加对象引用约束，事务内写 tombstone 和回收事件。worker 确认零引用后延迟、幂等回收。区分记录已移除、对象待清理、缓存待刷新。迁移策略时先预检影响、复制校验与切流，再释放源对象。 |
| P1　频控、配额与策略默认值 | 当前上传先 `sum(size)`，再独立写入，多个成功请求可同时越过配额，且比较使用原始大小，记录使用处理后大小。按数据库行统计多时间窗口，缺原子性。网页保存默认策略，API 无指定时取组内第一项，策略接口通常走游客组。 [容量检查](../../app/Services/ImageService.php#L114) · [频控](../../app/Services/ImageService.php#L368) · [策略列表](../../app/Http/Controllers/Api/V1/StrategyController.php#L13) · [用户默认策略](../../app/Http/Controllers/User/UserController.php#L54) | 明确配额用实际持久字节并可对账。DB 原子预留及结算，Redis 或边缘限流与应用配额各司其职。显式策略优先，之后查用户有效默认，再走明确排序的组选项。API 使用一致的认证与策略解析。 |
| P1　审核失败与跳过被视为合规 | 审核晚于写入，部分大小、格式会直接跳过。NSFW HTTP 响应未先验证成功和结构，缺失分类字段时默认零分。图库过滤不健康记录，图片直链的读取策略却独立。 [扫描前的持久化](../../app/Services/ImageService.php#L236) · [审核驱动](../../app/Services/ImageService.php#L407) | 定义待审核、通过、拒绝、错误和明确豁免。开启审核则先隔离后发布，超出供应商能力给明确状态并支持重试。预检供应商区域、凭证、大小限制与错误映射。 |
| P1　请求异常和假成功 | 上传缺少有效单文件校验，缺文件会落入通用异常。重命名找不到记录时对空对象调用 `only()`，移动图片时旧相册消失可访问空对象，调用者还提供旧相册 ID。 [上传入口](../../app/Services/ImageService.php#L77) · [图库操作](../../app/Http/Controllers/User/ImageController.php#L62) · [结果协议](../../app/Http/Result.php#L9) | Web/API 共用上传请求校验，区分缺文件、格式、配额、413、429、存储失败。移动从实际记录推导受影响相册并事务更新。旧 v1 保留结构和约定，新增稳定错误码和可观测请求 ID，调整 HTTP 状态码前验证 PicGo 等客户端。 |
| P2　搜索、筛选与重命名 | 图库的原名/别名 `OR` 没单独分组，会干扰相册和权限筛选。后台高级搜索与普通关键词也缺括号，`ip:` 过滤查 `ip`，表列为 `uploaded_ip`。重复搜索可发多个请求并混入过时数据。 [图库筛选](../../app/Models/Image.php#L127) · [后台筛选](../../app/Http/Controllers/Admin/ImageController.php#L23) · [搜索请求](../../resources/js/app.js#L114) | 同组字段 `OR` 用闭包分组，组间按定义 `AND`。改正确字段、稳定分页排序，搜索防抖并取消或忽略旧请求。测试普通文字与相册、可见性、多个限定词组合。 |

## 3. 应用安全和 Linux 部署

### 源码发现

| 优先级 | 风险与证据 | 处置 |
| --- | --- | --- |
| **P0　用户内容从应用源公开服务** | SVG 允许上传并跳过转换和扫描，缩略图直接复制到公开目录。`public/i` 也映射上传对象。文件接受条件主要是客户端文件名后缀，没有统一内容类型/解码验证。同源直接打开 SVG 有浏览器执行主动内容的风险。图库、上传结果和后台仍有用户文本拼 HTML 的 DOM 注入风险，允许访客上传使后台文件名展示尤其值得修复。 [格式默认值](../../config/convention.php#L104) · [SVG 输出](../../app/Services/ImageService.php#L549) · [公开路径](../../config/filesystems.php#L53) · [图库模板](../../resources/views/user/images.blade.php#L268) · [后台模板](../../resources/views/admin/image/index.blade.php#L357) | 先关闭公开 SVG 和游客入口作为遏制选项，梳理存量 SVG 派生物和应用源路径。上传私有落盘并校验内容、容器/尺寸和允许的编码。将用户内容移至无应用 Cookie 的独立源，禁脚本，固定 MIME 和 `nosniff`。需要 SVG 预览时采用隔离光栅化或审核过的净化。前端用文本节点和属性安全绑定，公告 Markdown 禁原生 HTML，CSP 先报告后执行。 [OWASP 文件上传](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html) |
| **P0　私有权限与缓存不匹配** | `permission` 决定是否进入画廊，直链和公开缩略图没有对应访问控制。原图保护路由凭 key 输出，使用约一个月的 public 缓存，输出前未检查所有者、分享许可或审核状态。普通直链仍可能指向公开后端。 [画廊](../../app/Http/Controllers/Common/GalleryController.php#L14) · [URL/缩略图](../../app/Models/Image.php#L172) · [输出](../../app/Http/Controllers/Controller.php#L144) · [缓存路由](../../routes/image.php#L8) | 定义 `私有 / 持链接可访问 / 公开` 三种可见性。私有读都经过服务端 ACL，原件和所有派生物同一策略，短期签名分享带范围和撤销机制。私有响应使用 `private, no-store`，公开不可变对象可长期 CDN 缓存。迁移前沟通旧链接行为及失效范围。 |
| **P0　安装和自更新可写敏感状态** | 未安装时 Web 会接收安装请求、运行 `migrate:fresh`，并用锁文件判断状态，缺少原子安装事务。升级从 `api.lsky.pro` 取清单和 MD5、原地复制或删除应用文件，缺少可信签名、原子切换和可靠回滚。 [安装入口](../../app/Http/Controllers/Controller.php#L35) · [安装命令](../../app/Console/Commands/Install.php#L50) · [自更新](../../app/Services/UpgradeService.php#L19) | 现有部署限制安装入口和旧的 Web 自更新。用只初始化空目标的 CLI 部署方式及一次性引导凭证创建管理员，区分准备、初始化、完成。由 fork CI 产出不可变、版本化、可核验签名的制品，部署与运行身份分离，演练迁移失败和回退。dotenv 使用正确序列化或直接注入环境，不覆盖用户已有 `.env` 与 `APP_KEY`。 |
| P1　信任边界过宽 | `TrustProxies` 为 `*`，`TrustHosts` 未启用，信任转发主机。应用 IP 用于限流，安装会把请求主机保存为策略访问 URL。方案依赖是否能直达应用、前置代理有无覆盖转发头。 [代理配置](../../app/Http/Middleware/TrustProxies.php#L15) · [HTTP 栈](../../app/Http/Kernel.php#L16) · [安装保存域名](../../app/Http/Controllers/Controller.php#L112) | 设置实际入口 CIDR/代理地址、规范 HTTPS `APP_URL` 和允许的主机。边缘覆盖客户端转发头，PHP 上游仅接受来自入口的流量。访问 URL 通过部署配置或校验过的显式地址配置。 |
| P1　账号和 API Token 生命周期 | Token 签发只校验邮箱、密码，未校验封禁与验证状态，默认永久有效。相关状态检查分散在登录和上传流程，密码重置换 remember token，但这一路径未撤销 API Token。重置请求还区分邮箱是否存在。 [Token 签发](../../app/Http/Controllers/Api/V1/TokenController.php#L15) · [Sanctum 设置](../../config/sanctum.php#L33) · [重置申请](../../app/Http/Controllers/Auth/PasswordResetLinkController.php#L29) · [重置密码](../../app/Http/Controllers/Auth/NewPasswordController.php#L45) | 所有受保护请求统一校验账号状态，Token 增加作用域、有效期、最后使用及设备级撤销，封禁或安全性重置撤销既有访问。重置请求统一反馈与限流。为旧 PicGo Token 提供期限明确的迁移窗口。 |
| P1　密钥、出站和日志 | 策略以普通 JSON collection 存储，邮件配置以普通值存储。管理员可配置存储和审核目的地，实际出站由请求触发。默认 SFTP 连接未提供主机密钥指纹。记录和部分接口可能直接传递驱动错误。 [策略密钥](../../app/Models/Strategy.php#L41) · [配置读取](../../app/Utils.php#L49) · [连接](../../app/Services/ImageService.php#L277) · [日志](../../app/Utils.php#L14) | 敏感字段保存为加密值或 Secret 引用，编辑只显示掩码，旧值按批迁移并制定轮换。配置目的地按策略授权，限制重定向和网络出口，NAS 私网允许经过明确授权并隔离敏感网段。SFTP 校验主机密钥，TLS 校验始终开启。客户端仅见分阶段错误码和请求 ID，日志脱敏。 |

### 2026 年通告与部署适用性

锁文件扫描采用 `composer audit --locked`、`composer audit --locked --no-dev` 和 `npm audit --package-lock-only`。Composer 全量返回 **67 条公告记录，涉及 19 个包**。去掉开发包后是 **59 条，涉及 16 个包**，其中 2 条标为 critical。另有 `fruitcake/laravel-cors` 已弃用。公告可能重复引用同一问题。npm 锁文件审计报告 **110 项受影响包记录**（38 critical、34 high、31 moderate、7 low，含间接传播）。审计结果只描述命中版本与依赖关系。实际页面加载的 `public/js/app.js` 内含 Axios **0.25.0**，锁文件是 **1.8.2**，部署资产必须独立盘点。`update@0.7.4` 的间接依赖链命中大量告警，仓库内未找到对应的实际调用入口，移除前用一次干净构建验证。 [Composer 锁](../../composer.lock) · [npm 锁](../../package-lock.json) · [页面加载路径](../../resources/views/layouts/app.blade.php#L59) · [已提交浏览器资产](../../public/js/app.js)

| 层次 | 应用到本工程的结论 |
| --- | --- |
| PHP Web SAPI | PHP 官方确认 **CVE-2026-109618 / 109619** 涉及 PHP-FPM 等 Web 形态，10 月修复基线含 8.5.12、8.4.27。11 月已有 8.5.13、8.4.28 安全版。清点实际 FPM、容器镜像与发行版安全回移，更新到受支持分支的厂商最新补丁并滚动重启。 [PHP 通告](https://www.php.net/archive/2026.php) · [PHP 变更日志](https://www.php.net/ChangeLog-8.php) |
| Laravel 与 Symfony | `laravel/framework 9.52.17`、`symfony/http-foundation 6.0.20` 已命中相关公告。Laravel 默认邮箱校验的 **CVE-2026-48019** 与注册、密码重置、邮件发送路径相关，具体暴露取决于入口和邮件配置。11 月的 Unicode 邮箱校验 DoS 另与 `intl` 有关，有对应补丁。Symfony `PATH_INFO` 和生成 URL 公告提示需同时校验反向代理与应用授权。 [Laravel 邮箱通告](https://github.com/laravel/framework/security/advisories/GHSA-5vg9-5847-vvmq) · [11 月 Laravel 通告](https://github.com/laravel/framework/security/advisories/GHSA-cvfq-f547-rv2g) · [Symfony PATH_INFO](https://symfony.com/blog/cve-2025-64500-incorrect-parsing-of-path-info-can-lead-to-limited-authorization-bypass) · [Symfony URL 通告](https://github.com/symfony/symfony/security/advisories/GHSA-5mx8-mwrp-5693) |
| 严重依赖公告的可达性 | **CVE-2026-114509** 涉及 Laravel 内置 TokenGuard，当前仓库入口使用 session 或 Sanctum。**CVE-2026-54133** 涉及 JMESPath 编译模式及可控表达式，当前业务未找到直接调用。两者均应升级锁定组件并核对部署覆盖的运行时配置。SFTP 部署也应优先更新 phpseclib。 [TokenGuard 公告](https://github.com/laravel/framework/security/advisories/GHSA-f9x3-7vq9-cph6) · [JMESPath 公告](https://github.com/jmespath/jmespath.php/security/advisories/GHSA-pcw8-m77r-2528) · [phpseclib 公告](https://github.com/phpseclib/phpseclib/security/advisories) |
| Linux 内核和容器 | **Copy Fail CVE-2026-31431**、**Dirty Frag CVE-2026-43284/43500** 属 Linux 本地提权风险，容器宿主也要按发行版内核公告核对。清点宿主、群晖/飞牛 NAS、CI runner 和节点的运行中内核、Docker/containerd/runc，应用补丁并验证已引导到修复版。runc **CVE-2026-119101** 另有不可信镜像和 checkpoint/restore 前提。仓库没有生产内核、镜像或宿主信息，现场暴露需由部署清单确认。 [Ubuntu Copy Fail](https://ubuntu.com/blog/copy-fail-vulnerability-fixes-available) · [Ubuntu Dirty Frag](https://ubuntu.com/blog/dirty-frag-linux-vulnerability-fixes-available) · [Docker 减缓公告](https://www.docker.com/blog/mitigating-cve-2026-31431-copy-fail-in-docker-engine/) · [runc 安全公告](https://github.com/opencontainers/runc/security) |
| 图片解码与服务权限 | 上传 worker 使用受支持的 ImageMagick、Imagick 扩展和编解码库。以非 root 用户运行，应用代码只读，单独写入私有临时目录和对象，设置 CPU、内存、磁盘、时限、像素和动画帧数，关闭非必要 coder/delegate/出站网络。部署去掉特权模式、Docker socket 和不必要宿主挂载，保留 seccomp 与 AppArmor/SELinux。 [ImageMagick 安全策略](https://imagemagick.org/security-policy/) |

## 4. 技术升级与迁移方案

目标栈采用 **PHP 8.5 的受支持补丁版、Laravel 13.x 最新补丁、Node.js 24 LTS、Vite**。Laravel 9 常规安全支持已于 2024-02-06 结束，Laravel 13 的计划安全支持到 2028-03-17。PHP 8.5 计划安全支持到 2029-12-31。Laravel 13 官方矩阵此时列 PHP 8.3–8.5，PHP 8.6 可在适配器和依赖通过后进入试验矩阵。Laravel 新版本至少包含 13.43.0 的邮箱校验修复，构建时以最新安全公告和锁文件为准。 [Laravel 支持周期](https://laravel.com/docs/13.x/releases) · [Laravel 13 升级指南](https://laravel.com/docs/13.x/upgrade) · [PHP 支持周期](https://www.php.net/supported-versions.php) · [Node 发布计划](https://nodejs.org/en/about/previous-releases) · [Vite 文档](https://vite.dev/guide/)

| 改造域 | 实施路径与兼容条件 |
| --- | --- |
| 框架和依赖 | 用短期迁移分支逐段处理 Laravel 9→10→11→12→13 的 breaking changes，发布主线直接落受支持版本。在变更时核对 PHP 兼容、Session、Sanctum、存储 SDK、Mailer、配置缓存和中间件。迁移 CORS 至框架内置方案，清查 Octane/Debugbar/Tinker 等部署用途。现有 `array_is_list()` 也已使部分逻辑实际依赖 PHP 8.1+，同步更新安装预检与发布矩阵。 [配置合并](../../app/Utils.php#L182) · [依赖表](../../composer.json) |
| 图片流水线 | 拆分输入校验、对象写入、元数据和发布状态、衍生图、水印、审核、对象回收。建立 `images` 逻辑记录、不可变对象记录、可重试任务三层边界。Intervention v2 已停止维护，优先验证 v4 的 GD/Imagick 驱动与可选 libvips。对格式、色彩和体积用固定素材对照，水印字体先验证可读和类型。 [Intervention v2](https://image.intervention.io/v2/getting-started/upgrade) · [Intervention v4](https://image.intervention.io/v4) |
| 存储和直链契约 | Flysystem 继续承担 I/O，业务层另定义写入、读取、签名、复制、删除与公网 URL 能力。策略分别验证写入凭证、桶/根目录/驱动前缀、对象读取、外链可达、TLS、S3 路径式/虚拟主机模式和签名。路径各段单独规范化和 URL 编码，旧 `pathname`、`key`、`links` 由兼容适配层服务。先打通本地与 S3，其次 MinIO、WebDAV、SFTP，再跑 OSS、COS、七牛、又拍、FTP 等契约测试。 |
| 数据库与队列 | 先以 MySQL 8.4 LTS、受支持 PostgreSQL 版本和具备 JSON 能力的 SQLite 建立矩阵。历史 MySQL 5.7 实例按官方支持路径升级并演练恢复，SQL Server 继续列为待验证兼容项。按访问计划增加 `user_id/album_id/created_at/id`、游客 IP/时间和画廊可见性索引。后台仪表盘将 30 天逐行装载改为 SQL 日期聚合或增量聚合。大库做批处理、查询计划和容量压测，图片列表引入稳定游标分页。队列先用 DB 或 Redis，独立水平扩容 worker。 [后台统计](../../app/Http/Controllers/Admin/ConsoleController.php#L20) · [MySQL 升级路径](https://dev.mysql.com/doc/refman/8.4/en/upgrade-paths.html) · [PostgreSQL 支持策略](https://www.postgresql.org/support/versioning/) |
| 构建与发布 | Blade 保持稳定入口，把上传、图库等交互抽为可测试模块，并迁移至 Vite。按页面加载图表和图库依赖，逐步替换 jQuery 插件，取消手工提交构建产物，所有制品使用内容 hash。由干净锁文件构建，生成 SBOM，验证发布 JS/图像库与扫描结果一致。CI 监听实际默认分支及 PR，升级动作、固定可信版本，去掉递归 777。CI 应涵盖全应用测试、浏览器流程、兼容矩阵与依赖扫描。 [现有 CI](../../.github/workflows/main.yml) · [现有 Mix 构建](../../webpack.mix.js) |
| 运维和备份 | 提供版本化 OCI 镜像、Compose/传统 PHP-FPM 部署预检、独立迁移命令、版本/队列/对象可用性健康检查。备份包含 DB、对象、配置及密钥恢复流程，定期在隔离环境试恢复。以请求 ID 关联 PC/移动端、反代、PHP 和各存储驱动，记录成功率、错误分类、处理延迟、对象 404 和孤儿数量，避免日志携带凭证和长期外链。 |

### 实施批次和发布门槛

1. **首批遏制与校验。** 盘点现有部署的公开入口、Bucket/媒体域、PHP 与 Linux 补丁、备份可恢复性。限制旧安装/自更新和主动内容。以临时文件隔离、对象键唯一、正确格式和返回值、代理信任为优先小改动。通过正常批量、跨策略、重试和接近配额时的校验，包含源站和 CDN 的真实读回。
2. **运行时与数据平滑迁移。** 先加新列和对象表，按策略和主键分批回填，检查引用、字节、哈希与不一致候选。对每个旧链接保存归属与迁移决定。灰度期间让旧读路径读取兼容映射，切换前演练 DB 回退与对象保全。发布 Laravel 13 和新构建链时保存可回滚制品及迁移检查点。
3. **处理链与管理体验。** 引入队列、审核状态、配额结算和对象回收后，再扩展签名分享、可配置分页和存储诊断。以浏览器和 API 覆盖单张、50 张批量、不同大小、同名文件、取消、超时、对象暂不可读、并发删除。性能目标先用 10 万和 100 万条数据建立基线，再确定 p95 上限；容量测试使用与生产相同的引擎和资源配置。
4. **兼容发布。** PicGo、既有 `/api/v1`、Markdown/HTML/BBCode 导出和可公开的历史链接均须有发布前用例。需要收紧历史链接权限时，显式列明影响及恢复路径。验证队列、备份恢复、部署回退和各策略的连接、写、读、删后再放量。

## 5. 2026 UI/UX 改进

在本地浏览了游客上传与完成态、登录、登录后上传、我的图片、画廊和后台存储策略。覆盖桌面 1440×900 和触摸模拟 390×844，移动端另看了有图图库、仪表盘、后台系统设置和安装预检，并运行了 axe 的 WCAG A/AA 相关规则。两个尺寸都走通普通双文件上传，移动端结果区的“复制全部”在默认状态不可见。两套布局都限制页面缩放，axe 还报告了部分文本对比度、无可访问名称的图标操作和无替代文字的预览。真实设备、读屏、Safari、慢网和大图库仍需验收。 [上传界面](../../resources/views/components/upload.blade.php#L1) · [公共布局](../../resources/views/layouts/guest.blade.php#L5) · [登录布局](../../resources/views/layouts/app.blade.php#L5) · [WCAG 2.2](https://www.w3.org/TR/WCAG22/)

| 场景 | 当前摩擦 | 优先设计 |
| --- | --- | --- |
| 上传 | 上传图标兼作选择和提交，队列和链接分离。移动端多个格式标签藏在横向滚动区，复制依赖悬停。先看到大面积空拖拽区，文件动作与完成结果离得远。 | 保留拖拽、粘贴，增加常显“选择图片”和“开始上传”按钮。选完后缩短拖拽区，顶部显示本批次目的地、相册和可见性。每行显示缩略图、原名、阶段、失败原因、重试和复制。移动端结果以格式选择器和常显“复制已完成链接”为主。 |
| 我的图片与相册 | 「我也是有底线的~」既会出现于全空库，也可能出现在列表末尾。右键、悬停和拖选能做事，触摸和键盘入口却弱。 | 分开首次空、无匹配、加载失败与列表末尾。提供可见搜索清除、筛选摘要、网格/列表切换、显式选择与单项菜单。批量栏固定显示选中数量与移动、复制、删除，支持键盘多选和清除。删除确认交代当前引用与公开链接影响。 |
| 画廊与分享 | 「私有」「原图保护」「可复制链接」语义重叠，用户难判断谁能访问。第三方头像或字体不可达时会损害体验。 | 统一三态权限文案与图标，分享前显示访问范围、过期与是否允许原图，明确访客可见性。提供首字母头像失败回退，自托管字体或采用系统字体栈，补齐语言切换和中英文标签一致性。 |
| 存储策略与安装 | 连接地址和访问地址难区分，策略列表只呈现名称、存储量等静态数据。移动端安装预检在滚动区下方仅显示「检测未通过」，阻断项可能在首屏外。 | 设计连接预检，分别展示「凭证」「写入及读回」「公开地址」「TLS」状态，显示示例外链、最近一次检查和请求 ID。安装向导区分环境、DB、管理员与部署完成。预检将阻断项置顶并直达详情，移动端保留关键字段和可折叠高级选项。已配置密钥以掩码展示。 |
| 基础交互 | 灰色低对比、小图标和 CSS 隐藏按钮影响发现，模态缺少完整焦点管理，成功提示易消失。 | 按 WCAG 2.2 AA 验收，恢复缩放，按钮有名称和焦点样式，正文对比至少 4.5:1，小控件按 Target Size 24 CSS px 及其例外核对，移动主要按钮优先 44 px。弹窗标题、焦点进入/返回、Esc 和进度播报都需测试。减少动效偏好下保留状态反馈，使用文本和图标共同表达结果。 |

第一版设计交付聚焦于上传器、图库工具栏和存储预检。视觉沿用当前暗色顶栏、浅色工作区的基本结构，统一间距、对比、按钮和状态组件。可用性以「手机上传并复制正确 Markdown」「键盘完成选择、移动、退出预览」「管理员区分写入成功与外链失败」三个任务验收。前端性能先记录真实基线，再以 LCP、INP、CLS 和大图库滚动体验作为门槛。 [Core Web Vitals](https://web.dev/articles/vitals)

## 验证环境和范围

- 使用 `git archive` 隔离副本，PHP 8.2.32 CLI、SQLite、Composer 2.11.2。原配置要求 Imagick，本地验证副本切到 GD。现有 PHPUnit 套件 **19 项测试、33 项断言通过**，现有套件主要覆盖认证和工具方法。
- 在独立副本用 Node.js 24.22.3 从锁文件安装并构建，`npm ci --ignore-scripts` 和 `npm run production` 均通过。重建包包含 Axios 1.8.2，已提交产物为 0.25.0，Browserslist 的数据有过期提示。
- 用普通合成 PNG 验证了浏览器双文件上传、转换输出错误和页面体验。未测量生产超时阈值、历史错图规模或远程存储兼容性。PHP-FPM、生产 Linux 内核、反向代理拓扑、CDN、真实流量、对象备份和生产 Imagick 编译能力均需按部署核对。
- 此次交付为审计方案和 Issue 台账。报告中的建议优先级、技术目标与兼容矩阵是接手实施的建议值。
