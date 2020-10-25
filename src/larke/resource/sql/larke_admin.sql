# Host: localhost  (Version: 5.5.53)
# Date: 2020-10-25 14:05:00
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "lake_larke_admin"
#

DROP TABLE IF EXISTS `lake_larke_admin`;
CREATE TABLE `lake_larke_admin` (
  `id` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT 'ID',
  `name` varchar(20) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '管理账号',
  `password` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '管理密码',
  `passport_salt` varchar(6) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '加密因子',
  `nickname` varchar(150) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '昵称',
  `email` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `avatar` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `last_active` int(10) DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '最后登录IP',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`),
  KEY `username` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员表';

#
# Structure for table "lake_larke_admin_log"
#

DROP TABLE IF EXISTS `lake_larke_admin_log`;
CREATE TABLE `lake_larke_admin_log` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '日志ID',
  `admin_id` varchar(32) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '管理账号ID',
  `admin_name` varchar(250) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '管理账号',
  `method` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求类型',
  `url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `info` text COLLATE utf8mb4_unicode_ci COMMENT '内容信息',
  `useragent` text COLLATE utf8mb4_unicode_ci COMMENT 'User-Agent',
  `ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '0',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='操作日志';

#
# Structure for table "lake_larke_attachment"
#

DROP TABLE IF EXISTS `lake_larke_attachment`;
CREATE TABLE `lake_larke_attachment` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '附件关联类型',
  `type_id` bigint(20) DEFAULT '0' COMMENT '关联类型ID',
  `name` char(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件名',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件mime类型',
  `extension` char(10) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '文件类型',
  `size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `md5` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'sha1 散列值',
  `driver` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '上传驱动',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '修改IP',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='附件表';

#
# Structure for table "lake_larke_auth_group"
#

DROP TABLE IF EXISTS `lake_larke_auth_group`;
CREATE TABLE `lake_larke_auth_group` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户组id',
  `parentid` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '父组别',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '描述信息',
  `listorder` smallint(5) DEFAULT '100' COMMENT '排序ID',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-系统默认角色',
  `is_root` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-超级管理组',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '修改IP',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='权限组表';

#
# Structure for table "lake_larke_auth_group_access"
#

DROP TABLE IF EXISTS `lake_larke_auth_group_access`;
CREATE TABLE `lake_larke_auth_group_access` (
  `admin_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `group_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  UNIQUE KEY `admin_id` (`admin_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员与用户组关联表';

#
# Structure for table "lake_larke_auth_rule"
#

DROP TABLE IF EXISTS `lake_larke_auth_rule`;
CREATE TABLE `lake_larke_auth_rule` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则id',
  `parentid` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `title` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '名称',
  `url` varchar(250) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '权限链接',
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求类型',
  `slug` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '地址标识',
  `description` varchar(255) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '描述',
  `listorder` smallint(5) DEFAULT '100' COMMENT '排序ID',
  `is_need_auth` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-验证权限',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-系统权限',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '修改IP',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='规则表';

#
# Structure for table "lake_larke_auth_rule_access"
#

DROP TABLE IF EXISTS `lake_larke_auth_rule_access`;
CREATE TABLE `lake_larke_auth_rule_access` (
  `group_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `rule_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  UNIQUE KEY `rule_id` (`rule_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户组与权限关联表';

#
# Structure for table "lake_larke_config"
#

DROP TABLE IF EXISTS `lake_larke_config`;
CREATE TABLE `lake_larke_config` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置ID',
  `group` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '配置分组',
  `type` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '配置类型',
  `title` varchar(80) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '配置标题',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置名称',
  `options` text CHARACTER SET utf8mb4 COMMENT '配置项',
  `value` text COLLATE utf8mb4_unicode_ci COMMENT '配置值',
  `description` varchar(250) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '配置描述',
  `listorder` smallint(5) DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) DEFAULT '1' COMMENT '1-显示',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '1-系统默认角色',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，1-启用',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '修改IP',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `group` (`group`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='网站配置';
