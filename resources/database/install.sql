DROP TABLE IF EXISTS `pre__larke_admin`;
CREATE TABLE `pre__larke_admin` (
  `id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT 'ID',
  `name` varchar(30) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '管理账号',
  `password` char(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '管理密码',
  `password_salt` char(6) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '加密因子',
  `nickname` varchar(150) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '昵称',
  `email` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `avatar` char(36) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '头像',
  `introduce` mediumtext CHARACTER SET utf8mb4 COMMENT '简介',
  `is_root` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-超级管理',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `refresh_time` int(10) NOT NULL DEFAULT '0' COMMENT '刷新时间',
  `refresh_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '刷新IP',
  `last_active` int(10) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员表';

DROP TABLE IF EXISTS `pre__larke_attachment`;
CREATE TABLE `pre__larke_attachment` (
  `id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `belong_type` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '附件属于',
  `belong_id` varchar(36) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '附件属于ID',
  `name` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件名',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件mime类型',
  `extension` varchar(10) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '文件类型',
  `size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `md5` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'sha1 散列值',
  `driver` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '上传驱动',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '修改IP',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='附件表';

DROP TABLE IF EXISTS `pre__larke_auth_group`;
CREATE TABLE `pre__larke_auth_group` (
  `id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '用户组id',
  `parentid` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0' COMMENT '父组别',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '描述信息',
  `listorder` int(10) DEFAULT '100' COMMENT '排序ID',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-系统默认角色',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '修改IP',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='权限组表';

DROP TABLE IF EXISTS `pre__larke_auth_group_access`;
CREATE TABLE `pre__larke_auth_group_access` (
  `id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `admin_id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `group_id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_id` (`admin_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员与用户组关联表';

DROP TABLE IF EXISTS `pre__larke_auth_rule`;
CREATE TABLE `pre__larke_auth_rule` (
  `id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '规则id',
  `parentid` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `title` varchar(150) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '名称',
  `url` varchar(250) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '权限链接',
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求类型',
  `slug` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '地址标识',
  `description` varchar(255) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '描述',
  `listorder` int(10) DEFAULT '100' COMMENT '排序ID',
  `is_need_auth` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-验证权限',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-系统权限',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '修改IP',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='规则表';

DROP TABLE IF EXISTS `pre__larke_auth_rule_access`;
CREATE TABLE `pre__larke_auth_rule_access` (
  `id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `group_id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `rule_id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_id` (`rule_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户组与权限关联表';

DROP TABLE IF EXISTS `pre__larke_config`;
CREATE TABLE `pre__larke_config` (
  `id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '配置ID',
  `group` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '配置分组',
  `type` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '配置类型',
  `title` varchar(80) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '配置标题',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置名称',
  `options` text CHARACTER SET utf8mb4 COMMENT '配置项',
  `value` text COLLATE utf8mb4_unicode_ci COMMENT '配置值',
  `description` varchar(250) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '配置描述',
  `listorder` int(10) DEFAULT '100' COMMENT '排序',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-显示',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-系统默认角色',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，1-启用',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '修改IP',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `group` (`group`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='网站配置';

DROP TABLE IF EXISTS `pre__larke_extension`;
CREATE TABLE `pre__larke_extension` (
  `id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `name` varchar(160) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '扩展包名',
  `title` varchar(250) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '名称',
  `description` mediumtext CHARACTER SET utf8mb4 NOT NULL COMMENT '描述',
  `keywords` varchar(200) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '关键字',
  `homepage` varchar(200) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '扩展地址',
  `authors` text CHARACTER SET utf8mb4 NOT NULL COMMENT '作者',
  `version` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '版本',
  `adaptation` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '适配最低版本',
  `require` text CHARACTER SET utf8mb4 COMMENT '依赖扩展',
  `config` mediumtext CHARACTER SET utf8mb4 COMMENT '配置设置信息',
  `config_data` text CHARACTER SET utf8mb4 COMMENT '配置结果信息',
  `class_name` text CHARACTER SET utf8mb4 COMMENT '扩展绑定类',
  `installtime` int(10) NOT NULL DEFAULT '0' COMMENT '安装时间',
  `upgradetime` int(10) DEFAULT '0' COMMENT '更新时间',
  `listorder` int(10) DEFAULT '100' COMMENT '排序ID',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '更新IP',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='已安装模块列表';

DROP TABLE IF EXISTS `pre__larke_rules`;
CREATE TABLE `pre__larke_rules` (
  `id` char(36) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `ptype` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v0` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v5` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `pre__larke_admin` VALUES ('6e5b96c8-d1f3-4d23-a016-38f994b6f9bc','larke','ec47d3facd39e00af6083e785c0cc8f8','HjESXC','larke','larke-admin@qq.com','85fb1314-1297-4382-82f9-82ee9f47c000','larke测试管理员',0,1,1652454692,'127.0.0.1',1652454692,'127.0.0.1',1605712035,'127.0.0.1'),('9b09f6b6-5808-4234-a7ff-0316838ed467','admin','7e80400632f60ff86ce0f4fd729b49b4','1aEAlZ','管理员','larke-admin@larke-admin.com','85fb1314-1297-4382-82f9-82ee9f47c000','larke-admin 是基于 laravel8 版本的后台快速开发框架，完全api接口化，适用于前后端分离的项目',1,1,1670942707,'127.0.0.1',1670942707,'127.0.0.1',1605712032,'127.0.0.1');
INSERT INTO `pre__larke_config` VALUES ('04232277-0396-45c1-9d98-4debb393779c','text','select','下拉','select','1:正确\n2:错误\n3:其他','2','下拉',108,1,0,1,1607660366,'127.0.0.1',1607530469,'127.0.0.1'),('17a7e07a-7dd4-4c43-abaa-feff290c93e2','text','textarea','文本框','textarea','','textarea12','文本框',103,1,0,1,1607660366,'127.0.0.1',1606147534,'127.0.0.1'),('20751644-684b-4d02-bcdf-cee92ad6dd8f','text','text','文本','text','','text12','文本',102,1,0,1,1607660352,'127.0.0.1',1606148681,'127.0.0.1'),('29809046-1f15-48ff-af3a-847c61acc860','setting','image','单图','image','','0e3248cc5401076224c2bdea55fc6ad5','单图',100,1,0,1,1645541805,'127.0.0.1',1607699712,'127.0.0.1'),('489d3037-82f0-49b5-8a5d-9c71d7011a53','web','time','时间','time','','11:56:05',NULL,100,1,0,1,1607710687,'127.0.0.1',1607662560,'127.0.0.1'),('5a5d8597-3371-4ebe-957f-e06abc6656f7','text','radio','单选','radio','1:正确\n2:错误','2',NULL,106,1,0,1,1607660366,'127.0.0.1',1607530258,'127.0.0.1'),('629e6f02-6e34-4908-86a3-6a220cfc2810','web','range-date','日期范围','range-date','','2020-12-02 00:00:00,2021-01-19 00:00:00',NULL,100,1,0,1,1607710650,'127.0.0.1',1607663696,'127.0.0.1'),('6d0474af-69d4-4ca8-96ba-32322b79e77a','setting','slider','滑块','slider','','63',NULL,100,1,0,1,1670470242,'127.0.0.1',1607662462,'127.0.0.1'),('a156d7c5-059d-41a9-a5b0-c5043f215e82','setting','rate','评分','rate','','5',NULL,100,1,0,1,1620484181,'127.0.0.1',1607661997,'127.0.0.1'),('a902f810-e52f-4bd7-afac-ede7269da50c','setting','color','颜色','color','','#0465B4',NULL,100,1,0,1,1670470224,'127.0.0.1',1607662418,'127.0.0.1'),('ae804ff4-2836-4bcd-ba08-ac4a2f05fcae','web','range-time','时间范围','range-time','','08:01:00,08:21:02',NULL,100,1,0,1,1607710672,'127.0.0.1',1607663675,'127.0.0.1'),('b22a0ea5-2d3c-4609-8017-894d54bc577d','text','checkbox','复选','checkbox','1:正确\n2:错误','1,2','复选',107,1,0,1,1607660366,'127.0.0.1',1607530365,'127.0.0.1'),('b7d5719f-bb6a-46a1-af65-f8de02c53120','web','date','日期','date','','2020-12-26 00:02:00',NULL,100,1,0,1,1607710699,'127.0.0.1',1607662597,'127.0.0.1'),('d36480c9-d32c-4701-b175-016276225cfe','text','array','分组','group','','web:网站\nsetting:设置\ntext:文档','设置数组',100,1,0,1,1607919232,'127.0.0.1',1607665273,'127.0.0.1'),('e3f8a15d-e1fe-49d1-8a80-a97f8d4e7de7','text','number','数字','number','','6','数字',105,1,0,1,1607660366,'127.0.0.1',1606884298,'127.0.0.1'),('e48a327a-f6b3-485f-9774-4e596b602bc5','text','switch','滑块','switch','','1','滑块',109,1,0,1,1607660319,'127.0.0.1',1607530498,'127.0.0.1');
