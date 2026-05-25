# 宇宙测速 - 项目说明文档

> 作者: 抖音@NCYXF  
> 版本: 1.0.0

---

## 项目简介

「宇宙测速」是一款仿全球测速 APP 风格的网络测速 Web 应用，支持下行/上行速率测试、Ping 延迟检测，包含前台测速页面和后台管理系统。

---

## 技术架构

| 层级 | 技术 | 说明 |
|------|------|------|
| 后端 | PHP 7.4+ | 动态渲染、API 接口、Session 认证 |
| 数据库 | MySQL 5.7+ | 表前缀 `speed_`，InnoDB 引擎 |
| 前端 | 原生 JS + CSS | Canvas 仪表盘/波形图，无框架依赖 |
| 运行环境 | phpStudy (Nginx + PHP + MySQL) | 本地开发环境 |

---

## 目录结构

```
yuzhou-speed/
├── index.php                  # 主入口（PHP 动态渲染）
├── reset_password.php         # 管理员密码重置工具
│
├── app/                       # 前端资源
│   ├── css/
│   │   └── style.css          # 全局样式（6个页面 + 组件）
│   ├── js/
│   │   ├── app.js            # 主应用逻辑（页面切换、测速控制、服务器列表）
│   │   ├── gauge.js           # Canvas 对数刻度仪表盘
│   │   ├── wave.js            # Canvas 实时波形图
│   │   ├── speed-test.js      # 测速核心引擎（下载/上传/延迟测试）
│   │   └── servers.js         # 服务器数据层（读取 PHP 注入 + 硬编码回退）
│   └── img/                   # 本地图片资源
│
├── admin/                     # 后台管理
│   ├── index.php              # 管理主页（PHP Session 校验 + 数据预加载）
│   ├── login.php              # 登录页（PHP Session 认证）
│   ├── css/
│   │   └── admin.css          # 后台样式
│   └── js/
│       └── admin.js           # 后台逻辑（CRUD、设置保存、统计图表）
│
├── api/                       # 后端 API
│   ├── config.php             # 数据库配置（禁止直接访问）
│   ├── db.php                 # 数据库连接类（SpeedDB 单例）
│   ├── servers.php            # 服务器列表 API（公开，GET）
│   ├── settings.php           # 系统设置 API（公开，GET/PUT）
│   ├── speed_test.php         # 测速接口
│   ├── history.php            # 测速历史
│   ├── admin/
│   │   ├── auth.php           # 管理员认证（登录/验证，含 getallheaders 兼容）
│   │   └── servers.php        # 服务器 CRUD（需 Bearer Token 认证）
│   └── test_*.php             # 诊断脚本（排查数据库连接等）
│
├── sql/
│   └── speed_servers.sql      # 数据库建表 + 全国测速节点数据（166条）
│
└── database.sql               # 旧版数据库结构（参考）
```

---

## 前台页面说明

应用采用单页应用（SPA）架构，通过底部导航栏切换 5 个页面：

| 页面 | ID | 说明 |
|------|----|------|
| 网络测速 | `page-speed` | 首页，仪表盘 + 波形图 + 一键测速 |
| 体验测速 | `page-exp` | 2×2 网格卡片（网站/视频/文件/游戏），纯展示 |
| 自动测速 | `page-auto` | 动态圆环 + 设置列表，纯展示 |
| 网络诊断 | `page-diag` | Ping/DNS/DIG 测试面板，工具/WIFI 标签切换 |
| 设置 | `page-set` | Logo 区 + 设置项列表，纯展示 |

### 首页测速流程

1. 用户点击「开始测试」按钮
2. 进入下载测速阶段 → 仪表盘实时显示速率，波形图绘制
3. 下载完成后，若配置了 `dl_ul_interval` 则倒计时等待
4. 进入上传测速阶段
5. 测速完成 → 仪表盘归零，弹出「测速完成」Toast 提示（3秒自动消失）

### 数据流

```
index.php 查询数据库
    ↓ 注入 window.SERVER_DATA / window.DEFAULT_SERVER / window.SERVER_SETTINGS
servers.js 读取 window.SERVER_DATA（回退：硬编码5个服务器）
    ↓
app.js 使用 ServerData 初始化，渲染服务器列表（省份折叠式）
    ↓
speed-test.js 根据选中服务器配置执行测速
    ↓
gauge.js / wave.js Canvas 绘制实时数据
```

---

## 后台管理系统

### 访问方式

- 地址：`/admin/login.php`
- 默认账号：`admin` / `admin123`
- 认证方式：PHP Session + Bearer Token（API 调用）

### 功能模块

| 模块 | 功能 |
|------|------|
| 服务器管理 | 增删改查测速节点，支持城市/运营商字段自动提取 |
| 系统设置 | IP API 地址、上下行测速间隔时间等可配置项 |
| 统计面板 | 服务器数量、总测速次数等概览 |

### API 认证

- 登录获取 Token：`POST /api/admin/auth.php` → `Bearer Token`
- 管理接口请求头：`Authorization: Bearer <token>`
- `auth.php` 包含 `getallheaders()` 兼容函数（Nginx/CGI 环境下该函数不存在）
- 路由隔离：`basename($_SERVER['SCRIPT_NAME']) === 'auth.php'` 包裹路由逻辑，防止被 require 时 405 报错

---

## 数据库

### 连接信息

| 配置项 | 值 |
|--------|-----|
| 主机 | localhost |
| 端口 | 3306 |
| 数据库名 | yuzhou_speed |
| 用户名 | root |
| 密码 | heicat |
| 表前缀 | speed_ |

### 核心表

**speed_servers** — 测速服务器节点

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 主键自增 |
| name | VARCHAR(64) | 服务器名称（如：北京电信） |
| region | VARCHAR(32) | 省份/直辖市/自治区 |
| city | VARCHAR(32) | 城市名称 |
| operator | VARCHAR(16) | 运营商（电信/联通/移动/教育网/广电） |
| host | VARCHAR(128) | 测速主机地址 |
| port | INT UNSIGNED | 端口（默认 80） |
| download_speed | INT UNSIGNED | 下行带宽能力（Mbps） |
| upload_speed | INT UNSIGNED | 上行带宽能力（Mbps） |
| speed_fluctuation | INT UNSIGNED | 速度波动范围（%） |
| ping_value | DECIMAL(6,2) | 基准延迟（ms） |
| jitter_value | DECIMAL(6,2) | 基准抖动（ms） |
| packet_loss | DECIMAL(5,2) | 基准丢包率（%） |
| test_duration | INT UNSIGNED | 测速时长（秒，默认 10） |
| status | TINYINT UNSIGNED | 0=禁用 1=启用 2=维护中 |
| sort_order | INT UNSIGNED | 排序权重 |
| is_recommended | TINYINT UNSIGNED | 0=否 1=是 |
| latitude / longitude | DECIMAL(10,6) | 经纬度（预留） |
| created_at / updated_at | TIMESTAMP | 创建/更新时间 |

**speed_settings** — 系统配置

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT UNSIGNED | 主键自增 |
| key_name | VARCHAR(64) | 配置键名 |
| key_value | TEXT | 配置值 |
| updated_at | TIMESTAMP | 更新时间 |

### 预置配置项

| key_name | 说明 | 示例值 |
|----------|------|--------|
| ip_api_url | IP 查询 API 地址，支持 `{ip}` 占位符 | `https://xxapi.cn/ip/{ip}` |
| dl_ul_interval | 下行结束到上行开始的间隔（秒） | `3` |

---

## 部署指南

### 环境要求

- PHP 7.4+（需启用 curl、pdo_mysql 扩展）
- MySQL 5.7+ / 8.0
- Nginx 或 Apache

### 安装步骤

1. **部署文件**  
   将 `yuzhou-speed/` 目录放到 Web 服务器根目录下

2. **创建数据库**  
   ```sql
   CREATE DATABASE yuzhou_speed DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **导入数据**  
   导入 `sql/speed_servers.sql`（建表 + 166条服务器数据）

4. **修改数据库配置**  
   编辑 `api/config.php`，修改数据库连接信息：
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'yuzhou_speed');
   define('DB_USER', 'root');
   define('DB_PASS', '你的密码');
   define('DB_PREFIX', 'speed_');
   ```

5. **配置 Nginx**（参考）
   ```nginx
   server {
       listen 80;
       server_name your-domain.com;
       root /path/to/yuzhou-speed;
       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass 127.0.0.1:9000;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

6. **访问应用**  
   - 前台：`http://your-domain.com/index.php`
   - 后台：`http://your-domain.com/admin/login.php`

7. **（可选）配置 IP 查询 API**  
   登录后台 → 系统设置 → 填写 IP API 地址（如 `https://xxapi.cn/ip/{ip}`），用于前台显示用户运营商信息

### phpStudy 本地环境

若使用 phpStudy 作为本地开发环境：
- 项目路径：`D:\wangzhan\127.0.0.1\yuzhou-speed\`
- 访问地址：`http://127.0.0.1/yuzhou-speed/index.php`
- PHP 版本：7.4.3 NTS
- MySQL 版本：5.7.26

---

## UI 设计规范

| 属性 | 值 |
|------|-----|
| 主背景色 | `#141824` |
| 卡片/区块色 | `#1a1f2e` |
| 主强调色 | `#4A9DFF`（蓝色） |
| 警告色 | `#FF9F43`（橙色） |
| 主文字色 | `#fff` / `#ccd6f6` |
| 辅助文字色 | `#8a92a8` / `#6a7490` |
| 按钮渐变 | `linear-gradient(180deg, #4A9DFF, #2B7AE8)` |
| 圆角 | 主区块 0（直角），按钮 4px，特殊卡片 12px |
| 仪表盘 | 对数刻度，Canvas 绘制，数值+单位 18px 同字号 |
| 底部导航 | 64px 高，固定底部，含 `env(safe-area-inset-bottom)` 适配 |

---

## 已知问题与注意事项

1. **`100dvh` 兼容性**：`height: 100dvh` 需要 Chrome 108+ / Safari 15.4+，旧浏览器回退到 `100vh`
2. **getallheaders() 缺失**：Nginx/CGI 环境下该函数不存在，`auth.php` 已内置兼容函数
3. **IP API 超时**：前台 IP 查询设 3 秒超时，失败回退到简易号段推断
4. **测速引擎**：当前为模拟测速（随机生成速率数据），实际测速需对接真实测速服务器
5. **纯展示页面**：体验测速、自动测速、网络诊断、设置页面均为展示效果，无实际功能逻辑

---

## 更新日志

### v1.0.0 (2026-05-25)

- 完成前台 5 页面开发（网络测速/体验测速/自动测速/网络诊断/设置）
- 完成后台管理系统（服务器 CRUD + 系统设置）
- PHP 动态渲染 + 数据预加载
- Canvas 仪表盘 + 实时波形图
- 省份折叠式服务器列表
- 手机端自适应布局（100dvh + safe-area 适配）
- IP 运营商查询 API 可配置化
- 上下行测速间隔可配置
