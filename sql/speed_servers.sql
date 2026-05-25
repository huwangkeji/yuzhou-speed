-- ============================================================
-- 宇宙测速 - 测速服务器节点数据
-- 作者: 抖音@NCYXF
-- 格式参考: 全球测速APP服务器列表
-- ============================================================

-- 先删除旧表(如果存在)，确保结构正确重建
DROP TABLE IF EXISTS `speed_servers`;

-- 创建测速服务器表 (与现有API兼容)
CREATE TABLE `speed_servers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '主键ID',
  `name` VARCHAR(64) NOT NULL COMMENT '服务器名称(如:北京电信)',
  `region` VARCHAR(32) NOT NULL COMMENT '所属省份/直辖市/自治区',
  `city` VARCHAR(32) NOT NULL COMMENT '城市名称',
  `operator` VARCHAR(16) NOT NULL COMMENT '运营商(电信/联通/移动/教育网/广电)',
  `host` VARCHAR(128) DEFAULT NULL COMMENT '测速服务器主机地址',
  `port` INT UNSIGNED DEFAULT 80 COMMENT '测速端口',
  `download_speed` INT UNSIGNED DEFAULT 1000 COMMENT '下行带宽能力(Mbps)',
  `upload_speed` INT UNSIGNED DEFAULT 500 COMMENT '上行带宽能力(Mbps)',
  `speed_fluctuation` INT UNSIGNED DEFAULT 10 COMMENT '速度波动范围(%)',
  `ping_value` DECIMAL(6,2) UNSIGNED DEFAULT 20.00 COMMENT '基准延迟(ms)',
  `jitter_value` DECIMAL(6,2) UNSIGNED DEFAULT 5.00 COMMENT '基准抖动(ms)',
  `packet_loss` DECIMAL(5,2) UNSIGNED DEFAULT 0.00 COMMENT '基准丢包率(%)',
  `test_duration` INT UNSIGNED DEFAULT 10 COMMENT '测速时长(秒)',
  `status` TINYINT UNSIGNED DEFAULT 1 COMMENT '状态:0禁用 1启用 2维护中',
  `sort_order` INT UNSIGNED DEFAULT 0 COMMENT '排序权重',
  `is_recommended` TINYINT UNSIGNED DEFAULT 0 COMMENT '是否推荐:0否 1是',
  `latitude` DECIMAL(10,6) DEFAULT NULL COMMENT '纬度',
  `longitude` DECIMAL(10,6) DEFAULT NULL COMMENT '经度',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  INDEX `idx_region` (`region`),
  INDEX `idx_city` (`city`),
  INDEX `idx_operator` (`operator`),
  INDEX `idx_status` (`status`),
  INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='测速服务器节点表';


-- ============================================================
-- 数据插入: 全国测速服务器节点
-- 格式: 省份分组 -> 城市+运营商节点
-- 字段顺序: name,region,city,operator,host,port,download_speed,upload_speed,speed_fluctuation,ping_value,jitter_value,packet_loss,test_duration,status,sort_order,is_recommended
-- ============================================================

-- -------------------- 安徽 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('合肥电信','安徽','合肥','电信','speed.ah.ct.com',80,1000,500,10,15.00,3.00,0.00,10,1,1,0),
('合肥联通','安徽','合肥','联通','speed.ah.cu.com',80,1000,500,10,15.00,3.00,0.00,10,1,2,0),
('合肥移动','安徽','合肥','移动','speed.ah.cm.com',80,1000,500,10,18.00,4.00,0.00,10,1,3,0),
('芜湖电信','安徽','芜湖','电信','speed.wh.ah.ct.com',80,800,400,12,18.00,4.00,0.00,10,1,4,0),
('芜湖联通','安徽','芜湖','联通','speed.wh.ah.cu.com',80,800,400,12,18.00,4.00,0.00,10,1,5,0),
('芜湖移动','安徽','芜湖','移动','speed.wh.ah.cm.com',80,800,400,12,20.00,5.00,0.00,10,1,6,0),
('蚌埠电信','安徽','蚌埠','电信','speed.bb.ah.ct.com',80,600,300,15,20.00,5.00,0.00,10,1,7,0),
('蚌埠联通','安徽','蚌埠','联通','speed.bb.ah.cu.com',80,600,300,15,20.00,5.00,0.00,10,1,8,0);

-- -------------------- 北京 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('北京电信','北京','北京','电信','speed.bj.ct.com',80,2000,1000,8,8.00,2.00,0.00,10,1,1,1),
('北京联通','北京','北京','联通','speed.bj.cu.com',80,2000,1000,8,8.00,2.00,0.00,10,1,2,1),
('北京移动','北京','北京','移动','speed.bj.cm.com',80,2000,1000,8,10.00,3.00,0.00,10,1,3,1),
('北京教育网','北京','北京','教育网','speed.bj.edu.cn',80,1000,500,12,12.00,3.00,0.00,10,1,4,0);

-- -------------------- 重庆 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('重庆电信','重庆','重庆','电信','speed.cq.ct.com',80,1000,500,10,12.00,3.00,0.00,10,1,1,0),
('重庆联通','重庆','重庆','联通','speed.cq.cu.com',80,1000,500,10,12.00,3.00,0.00,10,1,2,0),
('重庆移动','重庆','重庆','移动','speed.cq.cm.com',80,1000,500,10,14.00,4.00,0.00,10,1,3,0),
('重庆教育网','重庆','重庆','教育网','speed.cq.edu.cn',80,800,400,12,15.00,4.00,0.00,10,1,4,0);

-- -------------------- 福建 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('福州电信','福建','福州','电信','speed.fj.ct.com',80,1000,500,10,14.00,3.00,0.00,10,1,1,0),
('福州联通','福建','福州','联通','speed.fj.cu.com',80,1000,500,10,14.00,3.00,0.00,10,1,2,0),
('福州移动','福建','福州','移动','speed.fj.cm.com',80,1000,500,10,16.00,4.00,0.00,10,1,3,0),
('厦门电信','福建','厦门','电信','speed.xm.fj.ct.com',80,1000,500,10,15.00,3.00,0.00,10,1,4,0),
('厦门联通','福建','厦门','联通','speed.xm.fj.cu.com',80,1000,500,10,15.00,3.00,0.00,10,1,5,0),
('厦门移动','福建','厦门','移动','speed.xm.fj.cm.com',80,1000,500,10,17.00,4.00,0.00,10,1,6,0);

-- -------------------- 甘肃 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('兰州电信','甘肃','兰州','电信','speed.gs.ct.com',80,800,400,12,22.00,5.00,0.00,10,1,1,0),
('兰州联通','甘肃','兰州','联通','speed.gs.cu.com',80,800,400,12,22.00,5.00,0.00,10,1,2,0),
('兰州移动','甘肃','兰州','移动','speed.gs.cm.com',80,800,400,12,24.00,6.00,0.00,10,1,3,0),
('临夏电信','甘肃','临夏','电信','speed.lx.gs.ct.com',80,500,250,15,28.00,7.00,0.00,10,1,4,0),
('临夏联通','甘肃','临夏','联通','speed.lx.gs.cu.com',80,500,250,15,28.00,7.00,0.00,10,1,5,0),
('临夏移动','甘肃','临夏','移动','speed.lx.gs.cm.com',80,500,250,15,30.00,8.00,0.00,10,1,6,0),
('天水电信','甘肃','天水','电信','speed.ts.gs.ct.com',80,500,250,15,26.00,6.00,0.00,10,1,7,0);

-- -------------------- 广东 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('广州电信','广东','广州','电信','speed.gd.ct.com',80,2000,1000,8,10.00,2.00,0.00,10,1,1,1),
('广州联通','广东','广州','联通','speed.gd.cu.com',80,2000,1000,8,10.00,2.00,0.00,10,1,2,1),
('广州移动','广东','广州','移动','speed.gd.cm.com',80,2000,1000,8,12.00,3.00,0.00,10,1,3,1),
('深圳电信','广东','深圳','电信','speed.sz.gd.ct.com',80,2000,1000,8,10.00,2.00,0.00,10,1,4,1),
('深圳联通','广东','深圳','联通','speed.sz.gd.cu.com',80,2000,1000,8,10.00,2.00,0.00,10,1,5,1),
('深圳移动','广东','深圳','移动','speed.sz.gd.cm.com',80,2000,1000,8,12.00,3.00,0.00,10,1,6,1),
('东莞电信','广东','东莞','电信','speed.dg.gd.ct.com',80,1000,500,10,14.00,3.00,0.00,10,1,7,0),
('东莞联通','广东','东莞','联通','speed.dg.gd.cu.com',80,1000,500,10,14.00,3.00,0.00,10,1,8,0);

-- -------------------- 广西 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('南宁电信','广西','南宁','电信','speed.gx.ct.com',80,800,400,12,18.00,4.00,0.00,10,1,1,0),
('南宁联通','广西','南宁','联通','speed.gx.cu.com',80,800,400,12,18.00,4.00,0.00,10,1,2,0),
('南宁移动','广西','南宁','移动','speed.gx.cm.com',80,800,400,12,20.00,5.00,0.00,10,1,3,0),
('柳州电信','广西','柳州','电信','speed.lz.gx.ct.com',80,600,300,15,20.00,5.00,0.00,10,1,4,0),
('柳州联通','广西','柳州','联通','speed.lz.gx.cu.com',80,600,300,15,20.00,5.00,0.00,10,1,5,0);

-- -------------------- 贵州 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('贵阳电信','贵州','贵阳','电信','speed.gz.ct.com',80,800,400,12,18.00,4.00,0.00,10,1,1,0),
('贵阳联通','贵州','贵阳','联通','speed.gz.cu.com',80,800,400,12,18.00,4.00,0.00,10,1,2,0),
('贵阳移动','贵州','贵阳','移动','speed.gz.cm.com',80,800,400,12,20.00,5.00,0.00,10,1,3,0),
('遵义电信','贵州','遵义','电信','speed.zy.gz.ct.com',80,600,300,15,22.00,5.00,0.00,10,1,4,0),
('遵义联通','贵州','遵义','联通','speed.zy.gz.cu.com',80,600,300,15,22.00,5.00,0.00,10,1,5,0);

-- -------------------- 海南 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('海口电信','海南','海口','电信','speed.hi.ct.com',80,800,400,12,22.00,5.00,0.00,10,1,1,0),
('海口联通','海南','海口','联通','speed.hi.cu.com',80,800,400,12,22.00,5.00,0.00,10,1,2,0),
('海口移动','海南','海口','移动','speed.hi.cm.com',80,800,400,12,24.00,6.00,0.00,10,1,3,0),
('三亚电信','海南','三亚','电信','speed.sy.hi.ct.com',80,600,300,15,25.00,6.00,0.00,10,1,4,0),
('三亚联通','海南','三亚','联通','speed.sy.hi.cu.com',80,600,300,15,25.00,6.00,0.00,10,1,5,0);

-- -------------------- 河北 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('石家庄电信','河北','石家庄','电信','speed.he.ct.com',80,1000,500,10,15.00,3.00,0.00,10,1,1,0),
('石家庄联通','河北','石家庄','联通','speed.he.cu.com',80,1000,500,10,15.00,3.00,0.00,10,1,2,0),
('石家庄移动','河北','石家庄','移动','speed.he.cm.com',80,1000,500,10,18.00,4.00,0.00,10,1,3,0),
('保定电信','河北','保定','电信','speed.bd.he.ct.com',80,800,400,12,18.00,4.00,0.00,10,1,4,0),
('保定联通','河北','保定','联通','speed.bd.he.cu.com',80,800,400,12,18.00,4.00,0.00,10,1,5,0),
('唐山电信','河北','唐山','电信','speed.ts.he.ct.com',80,800,400,12,18.00,4.00,0.00,10,1,6,0),
('唐山联通','河北','唐山','联通','speed.ts.he.cu.com',80,800,400,12,18.00,4.00,0.00,10,1,7,0);

-- -------------------- 河南 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('郑州电信','河南','郑州','电信','speed.ha.ct.com',80,1000,500,10,14.00,3.00,0.00,10,1,1,0),
('郑州联通','河南','郑州','联通','speed.ha.cu.com',80,1000,500,10,14.00,3.00,0.00,10,1,2,0),
('郑州移动','河南','郑州','移动','speed.ha.cm.com',80,1000,500,10,16.00,4.00,0.00,10,1,3,0),
('洛阳电信','河南','洛阳','电信','speed.ly.ha.ct.com',80,800,400,12,16.00,4.00,0.00,10,1,4,0),
('洛阳联通','河南','洛阳','联通','speed.ly.ha.cu.com',80,800,400,12,16.00,4.00,0.00,10,1,5,0);

-- -------------------- 黑龙江 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('哈尔滨电信','黑龙江','哈尔滨','电信','speed.hl.ct.com',80,800,400,12,20.00,5.00,0.00,10,1,1,0),
('哈尔滨联通','黑龙江','哈尔滨','联通','speed.hl.cu.com',80,800,400,12,20.00,5.00,0.00,10,1,2,0),
('哈尔滨移动','黑龙江','哈尔滨','移动','speed.hl.cm.com',80,800,400,12,22.00,5.00,0.00,10,1,3,0),
('大庆电信','黑龙江','大庆','电信','speed.dq.hl.ct.com',80,600,300,15,24.00,6.00,0.00,10,1,4,0),
('大庆联通','黑龙江','大庆','联通','speed.dq.hl.cu.com',80,600,300,15,24.00,6.00,0.00,10,1,5,0);

-- -------------------- 湖北 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('武汉电信','湖北','武汉','电信','speed.hb.ct.com',80,1000,500,10,12.00,3.00,0.00,10,1,1,0),
('武汉联通','湖北','武汉','联通','speed.hb.cu.com',80,1000,500,10,12.00,3.00,0.00,10,1,2,0),
('武汉移动','湖北','武汉','移动','speed.hb.cm.com',80,1000,500,10,14.00,3.00,0.00,10,1,3,0),
('宜昌电信','湖北','宜昌','电信','speed.yc.hb.ct.com',80,800,400,12,16.00,4.00,0.00,10,1,4,0),
('宜昌联通','湖北','宜昌','联通','speed.yc.hb.cu.com',80,800,400,12,16.00,4.00,0.00,10,1,5,0);

-- -------------------- 湖南 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('长沙电信','湖南','长沙','电信','speed.hn.ct.com',80,1000,500,10,14.00,3.00,0.00,10,1,1,0),
('长沙联通','湖南','长沙','联通','speed.hn.cu.com',80,1000,500,10,14.00,3.00,0.00,10,1,2,0),
('长沙移动','湖南','长沙','移动','speed.hn.cm.com',80,1000,500,10,16.00,4.00,0.00,10,1,3,0),
('株洲电信','湖南','株洲','电信','speed.zz.hn.ct.com',80,800,400,12,16.00,4.00,0.00,10,1,4,0),
('株洲联通','湖南','株洲','联通','speed.zz.hn.cu.com',80,800,400,12,16.00,4.00,0.00,10,1,5,0);

-- -------------------- 吉林 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('长春电信','吉林','长春','电信','speed.jl.ct.com',80,800,400,12,18.00,4.00,0.00,10,1,1,0),
('长春联通','吉林','长春','联通','speed.jl.cu.com',80,800,400,12,18.00,4.00,0.00,10,1,2,0),
('长春移动','吉林','长春','移动','speed.jl.cm.com',80,800,400,12,20.00,5.00,0.00,10,1,3,0),
('吉林电信','吉林','吉林','电信','speed.jl.jl.ct.com',80,600,300,15,22.00,5.00,0.00,10,1,4,0),
('吉林联通','吉林','吉林','联通','speed.jl.jl.cu.com',80,600,300,15,22.00,5.00,0.00,10,1,5,0);

-- -------------------- 江苏 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('南京电信','江苏','南京','电信','speed.js.ct.com',80,2000,1000,8,10.00,2.00,0.00,10,1,1,1),
('南京联通','江苏','南京','联通','speed.js.cu.com',80,2000,1000,8,10.00,2.00,0.00,10,1,2,1),
('南京移动','江苏','南京','移动','speed.js.cm.com',80,2000,1000,8,12.00,3.00,0.00,10,1,3,1),
('苏州电信','江苏','苏州','电信','speed.sz.js.ct.com',80,2000,1000,8,10.00,2.00,0.00,10,1,4,1),
('苏州联通','江苏','苏州','联通','speed.sz.js.cu.com',80,2000,1000,8,10.00,2.00,0.00,10,1,5,1),
('苏州移动','江苏','苏州','移动','speed.sz.js.cm.com',80,2000,1000,8,12.00,3.00,0.00,10,1,6,1),
('无锡电信','江苏','无锡','电信','speed.wx.js.ct.com',80,1000,500,10,12.00,3.00,0.00,10,1,7,0),
('无锡联通','江苏','无锡','联通','speed.wx.js.cu.com',80,1000,500,10,12.00,3.00,0.00,10,1,8,0);

-- -------------------- 江西 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('南昌电信','江西','南昌','电信','speed.jx.ct.com',80,800,400,12,16.00,4.00,0.00,10,1,1,0),
('南昌联通','江西','南昌','联通','speed.jx.cu.com',80,800,400,12,16.00,4.00,0.00,10,1,2,0),
('南昌移动','江西','南昌','移动','speed.jx.cm.com',80,800,400,12,18.00,4.00,0.00,10,1,3,0),
('赣州电信','江西','赣州','电信','speed.gz.jx.ct.com',80,600,300,15,20.00,5.00,0.00,10,1,4,0),
('赣州联通','江西','赣州','联通','speed.gz.jx.cu.com',80,600,300,15,20.00,5.00,0.00,10,1,5,0);

-- -------------------- 辽宁 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('沈阳电信','辽宁','沈阳','电信','speed.ln.ct.com',80,1000,500,10,16.00,4.00,0.00,10,1,1,0),
('沈阳联通','辽宁','沈阳','联通','speed.ln.cu.com',80,1000,500,10,16.00,4.00,0.00,10,1,2,0),
('沈阳移动','辽宁','沈阳','移动','speed.ln.cm.com',80,1000,500,10,18.00,4.00,0.00,10,1,3,0),
('大连电信','辽宁','大连','电信','speed.dl.ln.ct.com',80,1000,500,10,16.00,4.00,0.00,10,1,4,0),
('大连联通','辽宁','大连','联通','speed.dl.ln.cu.com',80,1000,500,10,16.00,4.00,0.00,10,1,5,0),
('大连移动','辽宁','大连','移动','speed.dl.ln.cm.com',80,1000,500,10,18.00,4.00,0.00,10,1,6,0);

-- -------------------- 内蒙古 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('呼和浩特电信','内蒙古','呼和浩特','电信','speed.nm.ct.com',80,800,400,12,20.00,5.00,0.00,10,1,1,0),
('呼和浩特联通','内蒙古','呼和浩特','联通','speed.nm.cu.com',80,800,400,12,20.00,5.00,0.00,10,1,2,0),
('呼和浩特移动','内蒙古','呼和浩特','移动','speed.nm.cm.com',80,800,400,12,22.00,5.00,0.00,10,1,3,0),
('包头电信','内蒙古','包头','电信','speed.bt.nm.ct.com',80,600,300,15,22.00,5.00,0.00,10,1,4,0),
('包头联通','内蒙古','包头','联通','speed.bt.nm.cu.com',80,600,300,15,22.00,5.00,0.00,10,1,5,0);

-- -------------------- 宁夏 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('银川电信','宁夏','银川','电信','speed.nx.ct.com',80,600,300,15,22.00,5.00,0.00,10,1,1,0),
('银川联通','宁夏','银川','联通','speed.nx.cu.com',80,600,300,15,22.00,5.00,0.00,10,1,2,0),
('银川移动','宁夏','银川','移动','speed.nx.cm.com',80,600,300,15,24.00,6.00,0.00,10,1,3,0),
('石嘴山电信','宁夏','石嘴山','电信','speed.szs.nx.ct.com',80,500,250,18,26.00,6.00,0.00,10,1,4,0),
('石嘴山联通','宁夏','石嘴山','联通','speed.szs.nx.cu.com',80,500,250,18,26.00,6.00,0.00,10,1,5,0);

-- -------------------- 青海 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('西宁电信','青海','西宁','电信','speed.qh.ct.com',80,600,300,15,25.00,6.00,0.00,10,1,1,0),
('西宁联通','青海','西宁','联通','speed.qh.cu.com',80,600,300,15,25.00,6.00,0.00,10,1,2,0),
('西宁移动','青海','西宁','移动','speed.qh.cm.com',80,600,300,15,27.00,7.00,0.00,10,1,3,0);

-- -------------------- 山东 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('济南电信','山东','济南','电信','speed.sd.ct.com',80,1000,500,10,12.00,3.00,0.00,10,1,1,0),
('济南联通','山东','济南','联通','speed.sd.cu.com',80,1000,500,10,12.00,3.00,0.00,10,1,2,0),
('济南移动','山东','济南','移动','speed.sd.cm.com',80,1000,500,10,14.00,3.00,0.00,10,1,3,0),
('青岛电信','山东','青岛','电信','speed.qd.sd.ct.com',80,1000,500,10,12.00,3.00,0.00,10,1,4,0),
('青岛联通','山东','青岛','联通','speed.qd.sd.cu.com',80,1000,500,10,12.00,3.00,0.00,10,1,5,0),
('青岛移动','山东','青岛','移动','speed.qd.sd.cm.com',80,1000,500,10,14.00,3.00,0.00,10,1,6,0),
('烟台电信','山东','烟台','电信','speed.yt.sd.ct.com',80,800,400,12,14.00,3.00,0.00,10,1,7,0),
('烟台联通','山东','烟台','联通','speed.yt.sd.cu.com',80,800,400,12,14.00,3.00,0.00,10,1,8,0);

-- -------------------- 山西 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('太原电信','山西','太原','电信','speed.sx.ct.com',80,800,400,12,16.00,4.00,0.00,10,1,1,0),
('太原联通','山西','太原','联通','speed.sx.cu.com',80,800,400,12,16.00,4.00,0.00,10,1,2,0),
('太原移动','山西','太原','移动','speed.sx.cm.com',80,800,400,12,18.00,4.00,0.00,10,1,3,0),
('大同电信','山西','大同','电信','speed.dt.sx.ct.com',80,600,300,15,20.00,5.00,0.00,10,1,4,0),
('大同联通','山西','大同','联通','speed.dt.sx.cu.com',80,600,300,15,20.00,5.00,0.00,10,1,5,0);

-- -------------------- 陕西 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('西安电信','陕西','西安','电信','speed.sn.ct.com',80,1000,500,10,14.00,3.00,0.00,10,1,1,0),
('西安联通','陕西','西安','联通','speed.sn.cu.com',80,1000,500,10,14.00,3.00,0.00,10,1,2,0),
('西安移动','陕西','西安','移动','speed.sn.cm.com',80,1000,500,10,16.00,4.00,0.00,10,1,3,0),
('宝鸡电信','陕西','宝鸡','电信','speed.bj.sn.ct.com',80,600,300,15,18.00,4.00,0.00,10,1,4,0),
('宝鸡联通','陕西','宝鸡','联通','speed.bj.sn.cu.com',80,600,300,15,18.00,4.00,0.00,10,1,5,0);

-- -------------------- 上海 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('上海电信','上海','上海','电信','speed.sh.ct.com',80,2000,1000,8,8.00,2.00,0.00,10,1,1,1),
('上海联通','上海','上海','联通','speed.sh.cu.com',80,2000,1000,8,8.00,2.00,0.00,10,1,2,1),
('上海移动','上海','上海','移动','speed.sh.cm.com',80,2000,1000,8,10.00,2.00,0.00,10,1,3,1),
('上海教育网','上海','上海','教育网','speed.sh.edu.cn',80,1000,500,12,12.00,3.00,0.00,10,1,4,0);

-- -------------------- 四川 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('成都电信','四川','成都','电信','speed.sc.ct.com',80,1000,500,10,14.00,3.00,0.00,10,1,1,0),
('成都联通','四川','成都','联通','speed.sc.cu.com',80,1000,500,10,14.00,3.00,0.00,10,1,2,0),
('成都移动','四川','成都','移动','speed.sc.cm.com',80,1000,500,10,16.00,4.00,0.00,10,1,3,0),
('绵阳电信','四川','绵阳','电信','speed.my.sc.ct.com',80,800,400,12,16.00,4.00,0.00,10,1,4,0),
('绵阳联通','四川','绵阳','联通','speed.my.sc.cu.com',80,800,400,12,16.00,4.00,0.00,10,1,5,0);

-- -------------------- 天津 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('天津电信','天津','天津','电信','speed.tj.ct.com',80,1000,500,10,12.00,3.00,0.00,10,1,1,0),
('天津联通','天津','天津','联通','speed.tj.cu.com',80,1000,500,10,12.00,3.00,0.00,10,1,2,0),
('天津移动','天津','天津','移动','speed.tj.cm.com',80,1000,500,10,14.00,3.00,0.00,10,1,3,0);

-- -------------------- 西藏 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('拉萨电信','西藏','拉萨','电信','speed.xz.ct.com',80,500,250,18,30.00,8.00,0.00,10,1,1,0),
('拉萨联通','西藏','拉萨','联通','speed.xz.cu.com',80,500,250,18,30.00,8.00,0.00,10,1,2,0),
('拉萨移动','西藏','拉萨','移动','speed.xz.cm.com',80,500,250,18,32.00,9.00,0.00,10,1,3,0);

-- -------------------- 新疆 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('乌鲁木齐电信','新疆','乌鲁木齐','电信','speed.xj.ct.com',80,800,400,12,28.00,7.00,0.00,10,1,1,0),
('乌鲁木齐联通','新疆','乌鲁木齐','联通','speed.xj.cu.com',80,800,400,12,28.00,7.00,0.00,10,1,2,0),
('乌鲁木齐移动','新疆','乌鲁木齐','移动','speed.xj.cm.com',80,800,400,12,30.00,8.00,0.00,10,1,3,0),
('克拉玛依电信','新疆','克拉玛依','电信','speed.klmy.xj.ct.com',80,600,300,15,32.00,8.00,0.00,10,1,4,0),
('克拉玛依联通','新疆','克拉玛依','联通','speed.klmy.xj.cu.com',80,600,300,15,32.00,8.00,0.00,10,1,5,0);

-- -------------------- 云南 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('昆明电信','云南','昆明','电信','speed.yn.ct.com',80,800,400,12,20.00,5.00,0.00,10,1,1,0),
('昆明联通','云南','昆明','联通','speed.yn.cu.com',80,800,400,12,20.00,5.00,0.00,10,1,2,0),
('昆明移动','云南','昆明','移动','speed.yn.cm.com',80,800,400,12,22.00,5.00,0.00,10,1,3,0),
('大理电信','云南','大理','电信','speed.dl.yn.ct.com',80,600,300,15,24.00,6.00,0.00,10,1,4,0),
('大理联通','云南','大理','联通','speed.dl.yn.cu.com',80,600,300,15,24.00,6.00,0.00,10,1,5,0);

-- -------------------- 浙江 --------------------
INSERT INTO `speed_servers` (`name`,`region`,`city`,`operator`,`host`,`port`,`download_speed`,`upload_speed`,`speed_fluctuation`,`ping_value`,`jitter_value`,`packet_loss`,`test_duration`,`status`,`sort_order`,`is_recommended`) VALUES
('杭州电信','浙江','杭州','电信','speed.zj.ct.com',80,2000,1000,8,10.00,2.00,0.00,10,1,1,1),
('杭州联通','浙江','杭州','联通','speed.zj.cu.com',80,2000,1000,8,10.00,2.00,0.00,10,1,2,1),
('杭州移动','浙江','杭州','移动','speed.zj.cm.com',80,2000,1000,8,12.00,3.00,0.00,10,1,3,1),
('宁波电信','浙江','宁波','电信','speed.nb.zj.ct.com',80,1000,500,10,12.00,3.00,0.00,10,1,4,0),
('宁波联通','浙江','宁波','联通','speed.nb.zj.cu.com',80,1000,500,10,12.00,3.00,0.00,10,1,5,0),
('温州电信','浙江','温州','电信','speed.wz.zj.ct.com',80,1000,500,10,14.00,3.00,0.00,10,1,6,0),
('温州联通','浙江','温州','联通','speed.wz.zj.cu.com',80,1000,500,10,14.00,3.00,0.00,10,1,7,0);


-- ============================================================
-- 数据统计
-- ============================================================
-- SELECT region, COUNT(*) as node_count FROM speed_servers GROUP BY region ORDER BY node_count DESC;
-- SELECT operator, COUNT(*) as node_count FROM speed_servers GROUP BY operator ORDER BY node_count DESC;
