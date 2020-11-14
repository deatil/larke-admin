DROP TABLE IF EXISTS `pre__larke_admin`;
CREATE TABLE `pre__larke_admin` (
  `id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'ID',
  `name` varchar(30) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '管理账号',
  `password` char(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '管理密码',
  `password_salt` char(6) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '加密因子',
  `nickname` varchar(150) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '昵称',
  `email` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `avatar` char(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像',
  `introduce` mediumtext CHARACTER SET utf8mb4 COMMENT '简介',
  `is_root` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-超级管理',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `last_active` int(10) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员表';

DROP TABLE IF EXISTS `pre__larke_admin_log`;
CREATE TABLE `pre__larke_admin_log` (
  `id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '日志ID',
  `admin_id` char(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '管理账号ID',
  `admin_name` varchar(20) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '管理账号',
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求类型',
  `url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `info` text CHARACTER SET utf8mb4 NOT NULL COMMENT '内容信息',
  `useragent` text CHARACTER SET utf8mb4 NOT NULL COMMENT 'User-Agent',
  `ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='操作日志';

DROP TABLE IF EXISTS `pre__larke_attachment`;
CREATE TABLE `pre__larke_attachment` (
  `id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `belong_type` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '附件属于',
  `belong_id` varchar(32) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '附件属于ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件名',
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
  `id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户组id',
  `parentid` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '父组别',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '描述信息',
  `listorder` smallint(5) DEFAULT '100' COMMENT '排序ID',
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
  `id` char(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `admin_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `group_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_id` (`admin_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员与用户组关联表';

DROP TABLE IF EXISTS `pre__larke_auth_rule`;
CREATE TABLE `pre__larke_auth_rule` (
  `id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则id',
  `parentid` char(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `title` varchar(150) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '名称',
  `url` varchar(250) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '权限链接',
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求类型',
  `slug` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '地址标识',
  `description` varchar(255) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '描述',
  `listorder` smallint(5) DEFAULT '100' COMMENT '排序ID',
  `is_need_auth` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-验证权限',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-系统权限',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '修改IP',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='规则表';

DROP TABLE IF EXISTS `pre__larke_auth_rule_access`;
CREATE TABLE `pre__larke_auth_rule_access` (
  `id` char(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `group_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `rule_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_id` (`rule_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户组与权限关联表';

DROP TABLE IF EXISTS `pre__larke_config`;
CREATE TABLE `pre__larke_config` (
  `id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置ID',
  `group` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '配置分组',
  `type` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '配置类型',
  `title` varchar(80) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '配置标题',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置名称',
  `options` text CHARACTER SET utf8mb4 COMMENT '配置项',
  `value` text COLLATE utf8mb4_unicode_ci COMMENT '配置值',
  `description` varchar(250) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '配置描述',
  `listorder` smallint(5) DEFAULT '100' COMMENT '排序',
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
  `id` char(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `name` varchar(160) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '扩展id',
  `title` varchar(250) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '名称',
  `introduce` mediumtext CHARACTER SET utf8mb4 NOT NULL COMMENT '简介',
  `author` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '作者',
  `authorsite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '作者地址',
  `authoremail` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '作者邮箱',
  `version` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '版本',
  `adaptation` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '适配最低版本',
  `require_extension` text CHARACTER SET utf8mb4 COMMENT '依赖扩展',
  `config` mediumtext CHARACTER SET utf8mb4 COMMENT '配置设置信息',
  `config_data` text CHARACTER SET utf8mb4 COMMENT '配置结果信息',
  `class_name` text CHARACTER SET utf8mb4 COMMENT '扩展绑定类',
  `installtime` int(10) NOT NULL DEFAULT '0' COMMENT '安装时间',
  `upgradetime` int(10) DEFAULT '0' COMMENT '更新时间',
  `listorder` smallint(5) DEFAULT '100' COMMENT '排序ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '更新IP',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='已安装模块列表';

DROP TABLE IF EXISTS `pre__larke_rules`;
CREATE TABLE `pre__larke_rules` (
  `id` char(32) NOT NULL DEFAULT '',
  `ptype` varchar(255) DEFAULT NULL,
  `v0` varchar(255) DEFAULT NULL,
  `v1` varchar(255) DEFAULT NULL,
  `v2` varchar(255) DEFAULT NULL,
  `v3` varchar(255) DEFAULT NULL,
  `v4` varchar(255) DEFAULT NULL,
  `v5` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `pre__larke_admin` (`id`,`name`,`password`,`password_salt`,`nickname`,`email`,`avatar`,`introduce`,`is_root`,`status`,`last_active`,`last_ip`,`create_time`,`create_ip`) VALUES ('04f65b19e5a2513fe5a89100309da9b7','admin','7105beba161478cc991acd223d226a1a','y5kB4X','管理员','larke-admin@larke-admin.com','3166c0bdb87f6865dce2aba75de987cd','admin的简介',1,1,1605191369,'127.0.0.1',1564667925,'127.0.0.1');
