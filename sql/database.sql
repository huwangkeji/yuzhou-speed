-- ============================================================
-- 宇宙测速 数据库安装文件
-- 版本: 1.0.0
-- 作者: 抖音@NCYXF
-- 
-- 使用说明:
--   1. 先创建数据库: CREATE DATABASE yuzhou_speed DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   2. 选择数据库: USE yuzhou_speed;
--   3. 导入本文件: SOURCE database.sql;  或在 phpMyAdmin / Navicat 中导入
--   4. 修改 api/config.php 中的数据库连接信息
--   5. 默认管理员账号: admin / admin123 (请登录后立即修改密码)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 表结构: 测速服务器
-- ----------------------------
DROP TABLE IF EXISTS `speed_servers`;
CREATE TABLE `speed_servers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '服务器名称',
  `region` varchar(50) NOT NULL DEFAULT '' COMMENT '所属地区',
  `download_speed` float NOT NULL DEFAULT '100' COMMENT '下行速度(Mbps)',
  `upload_speed` float NOT NULL DEFAULT '50' COMMENT '上行速度(Mbps)',
  `speed_fluctuation` float NOT NULL DEFAULT '10' COMMENT '速度波动范围(%)',
  `ping_value` int(11) NOT NULL DEFAULT '15' COMMENT 'Ping值(ms)',
  `jitter_value` int(11) NOT NULL DEFAULT '5' COMMENT '抖动值(ms)',
  `packet_loss` float NOT NULL DEFAULT '0' COMMENT '丢包率(%)',
  `test_duration` int(11) NOT NULL DEFAULT '8' COMMENT '测速时长(秒)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:0禁用1启用',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_region` (`region`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='测速服务器配置表';

-- ----------------------------
-- 默认服务器数据
-- ----------------------------
INSERT INTO `speed_servers` (`name`, `region`, `download_speed`, `upload_speed`, `speed_fluctuation`, `ping_value`, `jitter_value`, `packet_loss`, `test_duration`, `sort_order`) VALUES
('北京电信', '北京', 500, 200, 15, 12, 3, 0, 8, 1),
('北京联通', '北京', 450, 180, 12, 15, 4, 0, 8, 2),
('北京移动', '北京', 400, 150, 18, 18, 5, 0.5, 8, 3),
('上海电信', '上海', 550, 220, 10, 10, 2, 0, 8, 4),
('上海联通', '上海', 500, 200, 12, 12, 3, 0, 8, 5),
('上海移动', '上海', 480, 190, 15, 14, 4, 0, 8, 6),
('广州电信', '广东', 480, 200, 15, 14, 4, 0, 8, 7),
('广州联通', '广东', 450, 180, 12, 16, 5, 0, 8, 8),
('深圳移动', '广东', 420, 160, 18, 18, 6, 0.5, 8, 9),
('成都电信', '四川', 400, 160, 15, 18, 5, 0, 8, 10),
('成都联通', '四川', 380, 150, 12, 20, 6, 0, 8, 11),
('杭州电信', '浙江', 500, 200, 10, 12, 3, 0, 8, 12),
('杭州移动', '浙江', 460, 180, 15, 14, 4, 0, 8, 13),
('武汉电信', '湖北', 420, 170, 15, 16, 5, 0, 8, 14),
('西安电信', '陕西', 400, 160, 18, 18, 5, 0.5, 8, 15),
('石家庄移动', '河北', 350, 140, 20, 22, 7, 0.5, 8, 16);

-- ----------------------------
-- 表结构: 系统设置
-- ----------------------------
DROP TABLE IF EXISTS `speed_settings`;
CREATE TABLE `speed_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key_name` varchar(50) NOT NULL DEFAULT '',
  `key_value` text,
  `description` varchar(255) NOT NULL DEFAULT '',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统设置表';

-- ----------------------------
-- 默认设置数据
-- ----------------------------
INSERT INTO `speed_settings` (`key_name`, `key_value`, `description`) VALUES
('site_name', '宇宙测速', '网站名称'),
('app_version', '1.0.0', '应用版本'),
('default_server_id', '1', '默认服务器ID'),
('download_test_duration', '5', '下载测试时长(秒)'),
('upload_test_duration', '5', '上传测试时长(秒)'),
('ping_test_count', '4', 'Ping测试次数'),
('max_history_records', '300', '最大历史记录数'),
('wave_animation_enabled', '1', '波形动画开关'),
('gauge_animation_speed', 'normal', '仪表盘动画速度'),
('theme_color', '#00d4ff', '主题色');

-- ----------------------------
-- 表结构: 管理员
-- ----------------------------
DROP TABLE IF EXISTS `speed_admin`;
CREATE TABLE `speed_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `login_token` varchar(255) NOT NULL DEFAULT '',
  `last_login_ip` varchar(50) NOT NULL DEFAULT '',
  `last_login_time` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- ----------------------------
-- 默认管理员 (账号: admin  密码: admin123)
-- 密码使用 password_hash('admin123', PASSWORD_DEFAULT) 生成
-- 请登录后立即修改密码！
-- ----------------------------
INSERT INTO `speed_admin` (`username`, `password`) VALUES
('admin', '$2y$10$QuBfHccdMlmVTqiFN.BfjOdJasg3qhctbI1A2KtX5AHJR06B6A7Ca');

-- ----------------------------
-- 表结构: 测速历史记录
-- ----------------------------
DROP TABLE IF EXISTS `speed_history`;
CREATE TABLE `speed_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL DEFAULT '0',
  `server_name` varchar(100) NOT NULL DEFAULT '',
  `ping` int(11) NOT NULL DEFAULT '0',
  `jitter` int(11) NOT NULL DEFAULT '0',
  `packet_loss` float NOT NULL DEFAULT '0',
  `download_speed` float NOT NULL DEFAULT '0',
  `upload_speed` float NOT NULL DEFAULT '0',
  `test_time` int(11) NOT NULL DEFAULT '0',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `ip_address` varchar(50) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='测速历史记录表';

SET FOREIGN_KEY_CHECKS = 1;
