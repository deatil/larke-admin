# Host: localhost  (Version: 5.5.53)
# Date: 2020-10-23 13:18:26
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "lake_larke_admin"
#

DROP TABLE IF EXISTS `lake_larke_admin`;
CREATE TABLE `lake_larke_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `name` varchar(20) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '管理账号',
  `password` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '管理密码',
  `passport_salt` varchar(6) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '加密因子',
  `nickname` varchar(150) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '昵称',
  `email` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `avatar` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `last_active` int(10) DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '最后登录IP',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`),
  KEY `username` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员表';

#
# Data for table "lake_larke_admin"
#

INSERT INTO `lake_larke_admin` VALUES (1,'admin','deb53b67ac3e817885b7fed0b012fc96','rRDXUv','admin12','larke-admin@qq.com','3198388a6850426917a91ac0f3698131',1,1603384175,'127.0.0.1',1564667925,'2130706433');

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
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='操作日志';

#
# Data for table "lake_larke_admin_log"
#

INSERT INTO `lake_larke_admin_log` VALUES ('','1','admin','GET','http://larke.php1000.com.cn/admin-api/log/index?app_id=API2020090322513090789&limit=10&nonce_str=idEMyxTmRLjpn6i0&order=ASC&start=0&timestamp=1603429535','{\n    \"start\": \"0\",\n    \"limit\": \"10\",\n    \"order\": \"ASC\",\n    \"app_id\": \"API2020090322513090789\",\n    \"nonce_str\": \"idEMyxTmRLjpn6i0\",\n    \"timestamp\": \"1603429535\"\n}','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36','127.0.0.1',1,1603429536,'127.0.0.1'),('091b2e9c010bef78e9222b4e5dcfb957','1','admin','GET','http://larke.php1000.com.cn/admin-api/log/detail?app_id=API2020090322513090789&id=45ee3f3207de0e8f765a5d64817fdb00&nonce_str=bNvY57B8Jh7C3WqK&timestamp=1603429776','{\n    \"id\": \"45ee3f3207de0e8f765a5d64817fdb00\",\n    \"app_id\": \"API2020090322513090789\",\n    \"nonce_str\": \"bNvY57B8Jh7C3WqK\",\n    \"timestamp\": \"1603429776\"\n}','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36','127.0.0.1',1,1603429777,'127.0.0.1'),('0e2fa2ee67de2f375793deb6dbef6170','1','admin','GET','http://larke.php1000.com.cn/admin-api/log/detail?app_id=API2020090322513090789&id=45ee3f3207de0e8f765a5d64817fdb00&nonce_str=6n8Oxm2nTJ3srIH0&timestamp=1603429872','{\n    \"id\": \"45ee3f3207de0e8f765a5d64817fdb00\",\n    \"app_id\": \"API2020090322513090789\",\n    \"nonce_str\": \"6n8Oxm2nTJ3srIH0\",\n    \"timestamp\": \"1603429872\"\n}','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36','127.0.0.1',1,1603429873,'127.0.0.1'),('451dbf80403494d241a4a725d8769e72','1','admin','GET','http://larke.php1000.com.cn/admin-api/log/index?app_id=API2020090322513090789&limit=10&nonce_str=7wa5Z7E7YLx1ohdd&order=ASC&start=0&timestamp=1603429657','{\n    \"start\": \"0\",\n    \"limit\": \"10\",\n    \"order\": \"ASC\",\n    \"app_id\": \"API2020090322513090789\",\n    \"nonce_str\": \"7wa5Z7E7YLx1ohdd\",\n    \"timestamp\": \"1603429657\"\n}','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36','127.0.0.1',1,1603429658,'127.0.0.1'),('53a85384f76ec14570eb78f88cd5a272','1','admin','GET','http://larke.php1000.com.cn/admin-api/log/index?app_id=API2020090322513090789&limit=10&nonce_str=e8GSVGoy805UrG7b&order=ASC&start=0&timestamp=1603429660','{\n    \"start\": \"0\",\n    \"limit\": \"10\",\n    \"order\": \"ASC\",\n    \"app_id\": \"API2020090322513090789\",\n    \"nonce_str\": \"e8GSVGoy805UrG7b\",\n    \"timestamp\": \"1603429660\"\n}','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36','127.0.0.1',1,1603429660,'127.0.0.1'),('6fe0dadbe39818b157bdc5a803662f64','1','admin','POST','http://larke.php1000.com.cn/admin-api/log/delete','{\n    \"app_id\": \"API2020090322513090789\",\n    \"timestamp\": 1603429849,\n    \"nonce_str\": \"lBkVehPtxOCZUEXv\",\n    \"id\": \"45ee3f3207de0e8f765a5d64817fdb00\"\n}','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36','127.0.0.1',1,1603429850,'127.0.0.1'),('a20e00fdf44e59e37911bae9f941897e','1','admin','POST','http://larke.php1000.com.cn/admin-api/log/delete','{\n    \"app_id\": \"API2020090322513090789\",\n    \"timestamp\": 1603429830,\n    \"nonce_str\": \"FKtPFjuj1VQDXjQK\",\n    \"id\": \"45ee3f3207de0e8f765a5d64817fdb00\"\n}','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36','127.0.0.1',1,1603429830,'127.0.0.1'),('c18ed3458f2e0787a8b2440d0d0e7470','1','admin','GET','http://larke.php1000.com.cn/admin-api/log/index?app_id=API2020090322513090789&limit=10&nonce_str=zOs8ApY7oRZv1dhQ&order=ASC&start=0&timestamp=1603429568','{\n    \"start\": \"0\",\n    \"limit\": \"10\",\n    \"order\": \"ASC\",\n    \"app_id\": \"API2020090322513090789\",\n    \"nonce_str\": \"zOs8ApY7oRZv1dhQ\",\n    \"timestamp\": \"1603429568\"\n}','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36','127.0.0.1',1,1603429569,'127.0.0.1'),('c3108f60785363bd1fff5e5c18388e72','1','admin','POST','http://larke.php1000.com.cn/admin-api/log/delete','{\n    \"app_id\": \"API2020090322513090789\",\n    \"timestamp\": 1603429826,\n    \"nonce_str\": \"d5t6UT2DNQmsy3bn\",\n    \"id\": \"45ee3f3207de0e8f765a5d64817fdb00\"\n}','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36','127.0.0.1',1,1603429827,'127.0.0.1'),('c7044d159c3f343d5c80be1c4b40dd5d','1','admin','GET','http://larke.php1000.com.cn/admin-api/log/detail?app_id=API2020090322513090789&id=45ee3f3207de0e8f765a5d64817fdb00&nonce_str=VjJjzBn5waPnmFnm&timestamp=1603429795','{\n    \"id\": \"45ee3f3207de0e8f765a5d64817fdb00\",\n    \"app_id\": \"API2020090322513090789\",\n    \"nonce_str\": \"VjJjzBn5waPnmFnm\",\n    \"timestamp\": \"1603429795\"\n}','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36','127.0.0.1',1,1603429795,'127.0.0.1');

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
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '修改IP',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='附件表';

#
# Data for table "lake_larke_attachment"
#

INSERT INTO `lake_larke_attachment` VALUES ('3198388a6850426917a91ac0f3698131','admin',1,'1.jpg','larke/81850c282c8b083a99c07590bfb5c644.jpeg','image/jpeg','jpeg',780831,'2b04df3ecc1d94afddff082d139c6f15','9c3dcb1f9185a314ea25d51aed3b5881b32f420c','local',1,1603427640,'127.0.0.1',1603427477,'127.0.0.1');

#
# Structure for table "lake_larke_auth_group"
#

DROP TABLE IF EXISTS `lake_larke_auth_group`;
CREATE TABLE `lake_larke_auth_group` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户组id',
  `parentid` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '父组别',
  `type` tinyint(4) NOT NULL COMMENT '组类型',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述信息',
  `listorder` smallint(5) NOT NULL DEFAULT '100' COMMENT '排序ID',
  `is_system` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '1-系统默认角色',
  `is_root` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '1-超级管理组',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='权限组表';

#
# Data for table "lake_larke_auth_group"
#


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
# Data for table "lake_larke_auth_group_access"
#


#
# Structure for table "lake_larke_auth_rule"
#

DROP TABLE IF EXISTS `lake_larke_auth_rule`;
CREATE TABLE `lake_larke_auth_rule` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则id',
  `parentid` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '上级分类ID',
  `title` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '名称',
  `url` varchar(250) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '权限链接',
  `slug` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '地址标识',
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求类型',
  `description` varchar(255) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '描述',
  `type` tinyint(2) DEFAULT '1' COMMENT '1-url;2-主菜单',
  `listorder` smallint(5) DEFAULT '100' COMMENT '排序ID',
  `is_need_auth` tinyint(1) DEFAULT '1' COMMENT '是否验证权限',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '1-系统权限',
  `edit_time` int(10) DEFAULT '0' COMMENT '编辑时间',
  `edit_ip` varchar(25) CHARACTER SET utf8mb4 DEFAULT '',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='规则表';

#
# Data for table "lake_larke_auth_rule"
#


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
# Data for table "lake_larke_auth_rule_access"
#

