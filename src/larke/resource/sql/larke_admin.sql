# Host: localhost  (Version: 5.5.53)
# Date: 2020-10-29 00:25:43
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "lake_larke_admin"
#

DROP TABLE IF EXISTS `lake_larke_admin`;
CREATE TABLE `lake_larke_admin` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'ID',
  `name` varchar(20) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '管理账号',
  `password` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '管理密码',
  `passport_salt` varchar(6) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '加密因子',
  `nickname` varchar(150) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '昵称',
  `email` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `avatar` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像',
  `is_root` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-超级管理',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `last_active` int(10) DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '最后登录IP',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`),
  KEY `username` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员表';

#
# Data for table "lake_larke_admin"
#

INSERT INTO `lake_larke_admin` VALUES ('04f65b19e5a2513fe5a89100309da9b7','admin','17f7ebcebe6ec437baf57361a723e871','NgWvGe','admin','larke-admin@qq.com','e76037551a4ea416bc729419bae69f5e',1,1,1603817609,'127.0.0.1',1564667925,'2130706433'),('b74613391d4163dd0dd91b3581fb4d8e','admin233','e472261e75b7452f19d20e6a4e98836e','l05NVz','admin2233333','larke-admin232@qq.com','f78f80bfa13629fae66716ed021b9f3b',0,1,1603467330,'127.0.0.1',1603465524,'127.0.0.1');

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
# Data for table "lake_larke_admin_log"
#


#
# Structure for table "lake_larke_attachment"
#

DROP TABLE IF EXISTS `lake_larke_attachment`;
CREATE TABLE `lake_larke_attachment` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '附件关联类型',
  `type_id` varchar(32) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '关联类型ID',
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
# Data for table "lake_larke_attachment"
#

INSERT INTO `lake_larke_attachment` VALUES ('e76037551a4ea416bc729419bae69f5e','admin','04f65b19e5a2513fe5a89100309da9b7','2.jpg','larke/e65809c9d74739103bd953ef179a58a1.jpeg','image/jpeg','jpeg',845941,'ba45c8f60456a672e003a875e469d0eb','30420d1a9afb2bcb60335812569af4435a59ce17','local',1,1603718443,'127.0.0.1',1603718430,'127.0.0.1'),('f78f80bfa13629fae66716ed021b9f3b','admin','04f65b19e5a2513fe5a89100309da9b7','1.jpg','larke/55bdfb56e89a5685e18cef03d3a47568.jpeg','image/jpeg','jpeg',780831,'2b04df3ecc1d94afddff082d139c6f15','9c3dcb1f9185a314ea25d51aed3b5881b32f420c','local',1,1603718423,'127.0.0.1',1603718423,'127.0.0.1');

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
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '修改IP',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='权限组表';

#
# Data for table "lake_larke_auth_group"
#

INSERT INTO `lake_larke_auth_group` VALUES ('19698bbf2bae9918c529a8230261dab2','9b7931a5d9e8edf555f62e346f4f68b2','编辑1','编辑的描述1',105,0,1,1603559664,'127.0.0.1',1603557994,'127.0.0.1'),('9b7931a5d9e8edf555f62e346f4f68b2','0','编辑3','编辑的描述23',103,0,1,1603558451,'127.0.0.1',1603558451,'127.0.0.1'),('a19b310629bce9a51a2d05d2cbd5940a','0','编辑2','编辑的描述2',102,0,1,1603558434,'127.0.0.1',1603558434,'127.0.0.1');

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

INSERT INTO `lake_larke_auth_group_access` VALUES ('04f65b19e5a2513fe5a89100309da9b7','9b7931a5d9e8edf555f62e346f4f68b2'),('b74613391d4163dd0dd91b3581fb4d8e','9b7931a5d9e8edf555f62e346f4f68b2');

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
# Data for table "lake_larke_auth_rule"
#

INSERT INTO `lake_larke_auth_rule` VALUES ('010bc1be6fc393e2c55a2a0b10a8986d','0','','admin-api/attachment/download','HEAD','larke-admin-attachment-download','larke-admin-attachment-download',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('02e8b83915dfacb3b2f2de96531acb7e','0','','admin-api/auth/rule/delete','DELETE','larke-admin-auth-rule-delete','larke-admin-auth-rule-delete',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('04beff822107ec17a34ff8d644974677','0','','admin-api/passport/refresh-token','PUT','larke-admin-passport-refresh-token','larke-admin-passport-refresh-token',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('04f42de416880fe8b27c5ae3647dd9a8','0','','admin-api/auth/rule/update','PUT','larke-admin-auth-rule-update','larke-admin-auth-rule-update',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('0517c42f2e5be507df21c61e9aacf003','0','','admin-api/attachment/detail','HEAD','larke-admin-attachment-detail','larke-admin-attachment-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('090a32d690cfb142b8bfdaa5fb5557ab','0','','admin-api/auth/group/index','GET','larke-admin-auth-group-index','larke-admin-auth-group-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('0b8bba9f00856a3fb794374de0c75517','0','','admin-api/profile/password','PUT','larke-admin-profile-password','larke-admin-profile-password',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('0d0ab05613609f077674cc59c5223497','0','','admin-api/log/detail','HEAD','larke-admin-log-detail','larke-admin-log-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('11b555af262695a7cc12d3ca533633ff','0','','admin-api/admin/create','POST','larke-admin-admin-create','larke-admin-admin-create',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('137f7536bb697aeafef227644dc43258','0','','admin-api/auth/group/create','POST','larke-admin-auth-group-create','larke-admin-auth-group-create',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('1669f8ce85756f34256fdec7f3ea18b8','0','','admin-api/auth/group/update','PUT','larke-admin-auth-group-update','larke-admin-auth-group-update',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('18f87ce8a60adabfbf9b09852be7f83d','0','','admin-api/admin/index','HEAD','larke-admin-admin-index','larke-admin-admin-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('1bc8d2d935540e88933725097bb5d08c','0','','admin-api/log/detail','GET','larke-admin-log-detail','larke-admin-log-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('1d41ff6f38f9226b6334c0c577086fb1','0','','admin-api/auth/rule/detail','GET','larke-admin-auth-rule-detail','larke-admin-auth-rule-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('21426e07522862b4fd0b4eae68dbdf9b','0','','admin-api/sys/cache','POST','larke-admin-sys-cache','larke-admin-sys-cache',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('2dba35b73c5bef87be84c58b26c6b97e','0','','admin-api/auth/rule/create','POST','larke-admin-auth-rule-create','larke-admin-auth-rule-create',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('2ecfd3b0f1804adf36dd9a3c3b248446','0','','admin-api/profile/menus','GET','larke-admin-profile-menus','larke-admin-profile-menus',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('36b3919ce1e3d54f953a1741f95a7bfc','0','','admin-api/config/detail','GET','larke-admin-config-detail','larke-admin-config-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('3c9e5a1e74b8c5a79b77fc34775bc6f0','0','','admin-api/attachment/index','GET','larke-admin-attachment-index','larke-admin-attachment-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('3ed633006e1569d4fac51b2e26aaf3a3','0','','admin-api/auth/group/index-children','GET','larke-admin-auth-group-index-children','larke-admin-auth-group-index-children',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('3faeefe24bd8330560c8e7174c8c5a81','0','','admin-api/config/create','POST','larke-admin-config-create','larke-admin-config-create',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('45a6a1092bfcea3c659c38d99da8efa9','0','','admin-api/passport/logout','POST','larke-admin-passport-logout','larke-admin-passport-logout',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('49fc2faf456e0bdbff66fab6e3f84132','0','','admin-api/auth/group/index-children','HEAD','larke-admin-auth-group-index-children','larke-admin-auth-group-index-children',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('4ae74acded8d07964dd328d452c8b69c','0','','admin-api/config/delete','DELETE','larke-admin-config-delete','larke-admin-config-delete',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('4d93c21241f8cc18f2f3fdd9935661c7','0','','admin-api/profile','GET','larke-admin-profile','larke-admin-profile',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('4dfd953d574418a71ac506ef8dcdf6e3','0','','admin-api/auth/group/index','HEAD','larke-admin-auth-group-index','larke-admin-auth-group-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('4f3b1bd3840ca715907bfbf027aa894d','0','','admin-api/auth/group/detail','GET','larke-admin-auth-group-detail','larke-admin-auth-group-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('51a8c0157c1acbef39fbb1e00b536ee1','0','','admin-api/auth/rule/index','HEAD','larke-admin-auth-rule-index','larke-admin-auth-rule-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('51e783a2f05e80eae3a2a2898d3d776b','0','','admin-api/auth/rule/index-tree','GET','larke-admin-auth-rule-index-tree','larke-admin-auth-rule-index-tree',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('54885e03c7709a8655d715ed328c2141','0','','admin-api/log/index','HEAD','larke-admin-log-index','larke-admin-log-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('650ef45b76e005e59fad2a03fced0264','0','','admin-api/auth/rule/detail','HEAD','larke-admin-auth-rule-detail','larke-admin-auth-rule-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('70bbe1a901e9c18ebe5dc5db44b4b3c1','0','','admin-api/attachment/index','HEAD','larke-admin-attachment-index','larke-admin-attachment-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('7298f00d4722b631a370c9d0d3e2863a','0','','admin-api/sys/clear-cache','POST','larke-admin-sys-clear-cache','larke-admin-sys-clear-cache',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('789443c24916af2dc2093ae63d82f1bd','0','','admin-api/config/update','PUT','larke-admin-config-update','larke-admin-config-update',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('7a446b2ea7fdfc5f83e365bdb377be60','0','','admin-api/attachment/download','GET','larke-admin-attachment-download','larke-admin-attachment-download',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('806a60e980e25c1024c8011bbee5659c','0','','admin-api/log/delete','DELETE','larke-admin-log-delete','larke-admin-log-delete',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('860b7c79c133e6256fa515772d01b267','0','','admin-api/config/setting','PUT','larke-admin-config-setting','larke-admin-config-setting',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('86d243d1d7c9b12b46b2875bd39ccca1','0','','admin-api/auth/rule/index-children','GET','larke-admin-auth-rule-index-children','larke-admin-auth-rule-index-children',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('87f495cc2c1111515eaa4ac7bcee3710','0','','admin-api/auth/group/index-tree','HEAD','larke-admin-auth-group-index-tree','larke-admin-auth-group-index-tree',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('892b4cc91fa843f7db5b3e4d5c89eb75','0','','admin-api/config/index','HEAD','larke-admin-config-index','larke-admin-config-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('954386606cc35ee7fbe2e69de7472034','0','','admin-api/admin/detail','GET','larke-admin-admin-detail','larke-admin-admin-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('9662a9b6e64b28341e78b36b488cf096','0','','admin-api/profile/menus','HEAD','larke-admin-profile-menus','larke-admin-profile-menus',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('97edbd74a0f4be5d4efc856ea1fd368f','0','','admin-api/auth/rule/index-tree','HEAD','larke-admin-auth-rule-index-tree','larke-admin-auth-rule-index-tree',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('9a4392071b75cbea298ec85d2e9b4e55','0','','admin-api/auth/group/index-tree','GET','larke-admin-auth-group-index-tree','larke-admin-auth-group-index-tree',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('a1186416108b8d3a2a943c2736a2a3d1','0','','admin-api/attachment/upload','POST','larke-admin-attachment-upload','larke-admin-attachment-upload',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('a5a9a09053e3e79363b63461722ed5a8','0','','admin-api/profile/update','PUT','larke-admin-profile-update','larke-admin-profile-update',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('a63e931ba3931870c15e5fc2a674c3e3','0','','admin-api/auth/rule/index-children','HEAD','larke-admin-auth-rule-index-children','larke-admin-auth-rule-index-children',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('aa15da414ee596fd9ac7e1c0a56dafb4','0','','admin-api/admin/update','PUT','larke-admin-admin-update','larke-admin-admin-update',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('b7bad50612a688ec68c940beba4068f4','0','','admin-api/config/detail','HEAD','larke-admin-config-detail','larke-admin-config-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('bb50c10136b7f1dcd20b71b4c30b2e41','0','','admin-api/attachment/delete','DELETE','larke-admin-attachment-delete','larke-admin-attachment-delete',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('c4185d7c6694aee82f4b60d44da01956','0','','admin-api/attachment/detail','GET','larke-admin-attachment-detail','larke-admin-attachment-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('c66362b32d3a8fc60b0443ef7a00c186','0','','admin-api/passport/captcha','GET','larke-admin-passport-captcha','larke-admin-passport-captcha',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('c697670f125ee3fed22fa21be06c5925','0','','admin-api/auth/group/detail','HEAD','larke-admin-auth-group-detail','larke-admin-auth-group-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('c7af08d084751ed7464458ef415a23a6','0','','admin-api/passport/login','POST','larke-admin-passport-login','larke-admin-passport-login',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('cf028ed310e72bd311aea935c19366b8','0','','admin-api/admin/detail','HEAD','larke-admin-admin-detail','larke-admin-admin-detail',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('cf551afd17ca10664c9d213fdf183771','0','','admin-api/auth/group/delete','DELETE','larke-admin-auth-group-delete','larke-admin-auth-group-delete',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('e08d626acd58fac44a4b31f99966faf9','0','','admin-api/config/index','GET','larke-admin-config-index','larke-admin-config-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('e372043c8471293425062cec2803ade6','0','','admin-api/admin/index','GET','larke-admin-admin-index','larke-admin-admin-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('e625c75f79a71658031f3171ae709a71','0','','admin-api/auth/rule/index','GET','larke-admin-auth-rule-index','larke-admin-auth-rule-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('e7fd54d7a0a1bcb196838012dfe8e527','0','','admin-api/passport/captcha','HEAD','larke-admin-passport-captcha','larke-admin-passport-captcha',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('ec52036d224fdfec67608ec58f2578e8','0','','admin-api/admin/delete','DELETE','larke-admin-admin-delete','larke-admin-admin-delete',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('ecf721fac7782a09b07517b4cd55dbd5','0','','admin-api/log/index','GET','larke-admin-log-index','larke-admin-log-index',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('f0bb49195c123fd9c0d6db23b658f467','0','','admin-api/profile','HEAD','larke-admin-profile','larke-admin-profile',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('f79e338bce922076b70b10a9475cece8','0','','admin-api/admin/logout','POST','larke-admin-admin-logout','larke-admin-admin-logout',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1'),('fcb7912f188d8e4952579460cbdfac26','0','','admin-api/admin/password','PUT','larke-admin-admin-password','larke-admin-admin-password',100,1,0,1,1603900483,'127.0.0.1',1603900483,'127.0.0.1');

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

INSERT INTO `lake_larke_auth_rule_access` VALUES ('9b7931a5d9e8edf555f62e346f4f68b2','0517c42f2e5be507df21c61e9aacf003'),('9b7931a5d9e8edf555f62e346f4f68b2','137f7536bb697aeafef227644dc43258'),('9b7931a5d9e8edf555f62e346f4f68b2','21426e07522862b4fd0b4eae68dbdf9b');

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

#
# Data for table "lake_larke_config"
#

/*!40000 ALTER TABLE `lake_larke_config` DISABLE KEYS */;
INSERT INTO `lake_larke_config` VALUES ('83ae3ba6a20dcb8089d19f4b91f2cee1','others','text','首页2','index','222','index23332','index2 description',105,0,1,0,1603605157,'127.0.0.1',1603605063,'127.0.0.1');
/*!40000 ALTER TABLE `lake_larke_config` ENABLE KEYS */;

#
# Structure for table "lake_larke_rules"
#

DROP TABLE IF EXISTS `lake_larke_rules`;
CREATE TABLE `lake_larke_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "lake_larke_rules"
#

/*!40000 ALTER TABLE `lake_larke_rules` DISABLE KEYS */;
INSERT INTO `lake_larke_rules` VALUES (1,'p','eve','articles','read',NULL,NULL,NULL,'2020-10-28 16:04:44','2020-10-28 16:04:44'),(2,'p','writer','articles','edit',NULL,NULL,NULL,'2020-10-28 16:05:10','2020-10-28 16:05:10');
/*!40000 ALTER TABLE `lake_larke_rules` ENABLE KEYS */;
