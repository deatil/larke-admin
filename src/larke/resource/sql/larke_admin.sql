# Host: localhost  (Version: 5.5.53)
# Date: 2020-10-30 13:27:00
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

INSERT INTO `lake_larke_admin` VALUES ('04f65b19e5a2513fe5a89100309da9b7','admin','17f7ebcebe6ec437baf57361a723e871','NgWvGe','admin','larke-admin@qq.com','e76037551a4ea416bc729419bae69f5e',1,1,1603945227,'127.0.0.1',1564667925,'2130706433'),('b74613391d4163dd0dd91b3581fb4d8e','admin233','e655b9dd6a4fd02789f80771bbf6988a','I5jHRh','admin2233333','larke-admin232@qq.com','f78f80bfa13629fae66716ed021b9f3b',0,1,1604032301,'127.0.0.1',1603465524,'127.0.0.1');

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

INSERT INTO `lake_larke_attachment` VALUES ('b1396f72875f7f22b499a059671c3e31','admin','04f65b19e5a2513fe5a89100309da9b7','2.zip','files/6c7213ae825cf405dd0282c880a70366.zip','application/zip','zip',842883,'ee637621eb920f3cfeec7a50c83bdf55','76f385daafe63643fade5a66a6c0f2db23a11bba','local',1,1603948279,'127.0.0.1',1603948279,'127.0.0.1'),('e76037551a4ea416bc729419bae69f5e','admin','04f65b19e5a2513fe5a89100309da9b7','2.jpg','larke/e65809c9d74739103bd953ef179a58a1.jpeg','image/jpeg','jpeg',845941,'ba45c8f60456a672e003a875e469d0eb','30420d1a9afb2bcb60335812569af4435a59ce17','local',1,1603718443,'127.0.0.1',1603718430,'127.0.0.1'),('f78f80bfa13629fae66716ed021b9f3b','admin','04f65b19e5a2513fe5a89100309da9b7','1.jpg','larke/55bdfb56e89a5685e18cef03d3a47568.jpeg','image/jpeg','jpeg',780831,'2b04df3ecc1d94afddff082d139c6f15','9c3dcb1f9185a314ea25d51aed3b5881b32f420c','local',1,1603718423,'127.0.0.1',1603718423,'127.0.0.1');

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
  `id` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `admin_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `group_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_id` (`admin_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员与用户组关联表';

#
# Data for table "lake_larke_auth_group_access"
#

INSERT INTO `lake_larke_auth_group_access` VALUES ('38eddce57cc6df32f2b7f9944554afb6','b74613391d4163dd0dd91b3581fb4d8e','9b7931a5d9e8edf555f62e346f4f68b2');

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

INSERT INTO `lake_larke_auth_rule` VALUES ('00a10d8dbf8ca44dedbce42fcc4fdbfb','0','larke-admin-auth-group-detail','admin-api/auth/group/detail','GET','larke-admin-auth-group-detail','larke-admin-auth-group-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('06f2c68da76848d47d9820dbeb712b2a','0','larke-admin-auth-group-index','admin-api/auth/group/index','HEAD','larke-admin-auth-group-index','larke-admin-auth-group-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('0737d4c58741479279fb5735c3bc21ba','0','larke-admin-auth-group-index-tree','admin-api/auth/group/index-tree','GET','larke-admin-auth-group-index-tree','larke-admin-auth-group-index-tree',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('0a94fc7a22d53472a02c10a36352af3f','0','larke-admin-auth-group-index-children','admin-api/auth/group/index-children','GET','larke-admin-auth-group-index-children','larke-admin-auth-group-index-children',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('0b730e2b7f5d0d26f28ed2deb7c461dc','0','larke-admin-passport-logout','admin-api/passport/logout','POST','larke-admin-passport-logout','larke-admin-passport-logout',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('0bb0c9d6d45014a96e4f4820019a6c45','0','larke-admin-auth-rule-index-tree','admin-api/auth/rule/index-tree','HEAD','larke-admin-auth-rule-index-tree','larke-admin-auth-rule-index-tree',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('1346c7c9c2d198ee1744947dcbc77fca','0','larke-admin-profile','admin-api/profile','GET','larke-admin-profile','larke-admin-profile',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('1de854092664349857cb491763076e7d','0','larke-admin-attachment-download','admin-api/attachment/download','GET','larke-admin-attachment-download','larke-admin-attachment-download',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('29a29ce516b4e67e487a1a21bd784f30','0','larke-admin-auth-rule-index-children','admin-api/auth/rule/index-children','HEAD','larke-admin-auth-rule-index-children','larke-admin-auth-rule-index-children',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('2b05ec131274c504cc01eaca171eddb5','0','larke-admin-attachment-detail','admin-api/attachment/detail','HEAD','larke-admin-attachment-detail','larke-admin-attachment-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('340c0fab7a5ef92fe1d07e57faea5f9a','0','larke-admin-auth-rule-index-children','admin-api/auth/rule/index-children','GET','larke-admin-auth-rule-index-children','larke-admin-auth-rule-index-children',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('374e06bcb37793429d13dff97eb7abc7','0','larke-admin-passport-login','admin-api/passport/login','POST','larke-admin-passport-login','larke-admin-passport-login',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('39a0dd1fcc85c198bde05def28c67584','0','larke-admin-auth-group-update','admin-api/auth/group/update','PUT','larke-admin-auth-group-update','larke-admin-auth-group-update',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('3f14dee4fbffbecddd896b9a5231c730','0','larke-admin-config-setting','admin-api/config/setting','PUT','larke-admin-config-setting','larke-admin-config-setting',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('40afc3c56ce0cdb86b649558e3cae91d','0','larke-admin-profile-password','admin-api/profile/password','PUT','larke-admin-profile-password','larke-admin-profile-password',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('490abb825c8bd9bbaf406b8ca48e848f','0','larke-admin-config-update','admin-api/config/update','PUT','larke-admin-config-update','larke-admin-config-update',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('4c0b88dbe5e57fa5440a41bec0c15dc0','0','larke-admin-auth-group-create','admin-api/auth/group/create','POST','larke-admin-auth-group-create','larke-admin-auth-group-create',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('4cc97f87c24df2d0c996e3eb2debc703','0','larke-admin-passport-captcha','admin-api/passport/captcha','GET','larke-admin-passport-captcha','larke-admin-passport-captcha',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('4d7fc7ecc2806b6822975c69a045a39e','0','larke-admin-profile','admin-api/profile','HEAD','larke-admin-profile','larke-admin-profile',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('507ee31ed9295d8b1fb4c560f35cc2c6','0','larke-admin-attachment-detail','admin-api/attachment/detail','GET','larke-admin-attachment-detail','larke-admin-attachment-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('537af42673e7de69308a771bd0c7973f','0','larke-admin-auth-rule-detail','admin-api/auth/rule/detail','GET','larke-admin-auth-rule-detail','larke-admin-auth-rule-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('5f0ea0bdd330176833b7a42890f389e1','0','larke-admin-attachment-download','admin-api/attachment/download','HEAD','larke-admin-attachment-download','larke-admin-attachment-download',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('6336a74b13af4ef09715b1b7068fbb96','0','larke-admin-auth-rule-index-tree','admin-api/auth/rule/index-tree','GET','larke-admin-auth-rule-index-tree','larke-admin-auth-rule-index-tree',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('6a8851d51738e3e42a89eecfdc6da2a1','0','larke-admin-config-create','admin-api/config/create','POST','larke-admin-config-create','larke-admin-config-create',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('70979380eea3dff9e67c01b62c157eec','0','larke-admin-admin-delete','admin-api/admin/delete','DELETE','larke-admin-admin-delete','larke-admin-admin-delete',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('738846fd044e29a5554293665b5dde3b','0','larke-admin-log-detail','admin-api/log/detail','HEAD','larke-admin-log-detail','larke-admin-log-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('73d93b1ed6cc126114416369c33a26d6','0','larke-admin-auth-group-delete','admin-api/auth/group/delete','DELETE','larke-admin-auth-group-delete','larke-admin-auth-group-delete',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('7c82cb24468aa5c7cafa4f8611c15d3f','0','larke-admin-sys-clear-cache','admin-api/sys/clear-cache','POST','larke-admin-sys-clear-cache','larke-admin-sys-clear-cache',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('88a18e13b64465fc658767a30107bcdd','0','larke-admin-auth-group-index','admin-api/auth/group/index','GET','larke-admin-auth-group-index','larke-admin-auth-group-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('897c7963cd51d9d16977ac54f282b4ee','0','larke-admin-admin-detail','admin-api/admin/detail','GET','larke-admin-admin-detail','larke-admin-admin-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('8a23da40230045dc6cda1568394a82e8','0','larke-admin-config-index','admin-api/config/index','GET','larke-admin-config-index','larke-admin-config-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('8e8cd8996561169affba983bb8d75837','0','larke-admin-log-index','admin-api/log/index','GET','larke-admin-log-index','larke-admin-log-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('9dda55fa5e0e549c6d7bb0dabd0c1962','0','larke-admin-profile-update','admin-api/profile/update','PUT','larke-admin-profile-update','larke-admin-profile-update',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('a52afe9fd37fe217d8fb7599d9f14281','0','larke-admin-auth-group-index-tree','admin-api/auth/group/index-tree','HEAD','larke-admin-auth-group-index-tree','larke-admin-auth-group-index-tree',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('a60e52a59ac1bcf5a8909a54ab4eb43e','0','larke-admin-log-detail','admin-api/log/detail','GET','larke-admin-log-detail','larke-admin-log-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('a89d2518f6d69dbd4c74da8913e9e6e5','0','larke-admin-auth-rule-delete','admin-api/auth/rule/delete','DELETE','larke-admin-auth-rule-delete','larke-admin-auth-rule-delete',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('a8e6319bf47cfac6105ed2f346205519','0','larke-admin-passport-refresh-token','admin-api/passport/refresh-token','PUT','larke-admin-passport-refresh-token','larke-admin-passport-refresh-token',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('aa0749359697333477b56ba648ecec1b','0','larke-admin-admin-index','admin-api/admin/index','HEAD','larke-admin-admin-index','larke-admin-admin-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('ac3e9446a400612c3462bdae16e718c5','0','larke-admin-auth-rule-detail','admin-api/auth/rule/detail','HEAD','larke-admin-auth-rule-detail','larke-admin-auth-rule-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('adc7feeafb001196d219e7882c7ce4ba','0','larke-admin-config-delete','admin-api/config/delete','DELETE','larke-admin-config-delete','larke-admin-config-delete',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('aee24d16be1c16a59064cada7132afc4','0','larke-admin-admin-access','admin-api/admin/access','PUT','larke-admin-admin-access','larke-admin-admin-access',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('b1f34e04e3b4b07454eed2b41ff76c80','0','larke-admin-admin-create','admin-api/admin/create','POST','larke-admin-admin-create','larke-admin-admin-create',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('b835eb46dae5350d13873dd54eb83b7b','0','larke-admin-attachment-index','admin-api/attachment/index','HEAD','larke-admin-attachment-index','larke-admin-attachment-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('b9a3b12a95dc8f1aeba328d875fdc1ff','0','larke-admin-auth-rule-create','admin-api/auth/rule/create','POST','larke-admin-auth-rule-create','larke-admin-auth-rule-create',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('bb8187b31cac40378966268873b2c259','0','larke-admin-log-delete','admin-api/log/delete','DELETE','larke-admin-log-delete','larke-admin-log-delete',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('bdc21f9d00d6c0b2c07168e9fc4461a5','0','larke-admin-auth-group-access','admin-api/auth/group/access','PUT','larke-admin-auth-group-access','larke-admin-auth-group-access',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('c28b0b82eb16e4bd1f577dae4d0adbe0','0','larke-admin-sys-cache','admin-api/sys/cache','POST','larke-admin-sys-cache','larke-admin-sys-cache',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('c36f4709c9d359c6b7c53fd06b71cf8d','0','larke-admin-config-detail','admin-api/config/detail','HEAD','larke-admin-config-detail','larke-admin-config-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('c7b3d386431be6b2ef0c3cb52814e06d','0','larke-admin-admin-index','admin-api/admin/index','GET','larke-admin-admin-index','larke-admin-admin-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('c7f5385e1744f4bc878c64dc1e7d6eea','0','larke-admin-admin-logout','admin-api/admin/logout','POST','larke-admin-admin-logout','larke-admin-admin-logout',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('c82f4a5d3849f4dad7d5ba2b3f557406','0','larke-admin-attachment-delete','admin-api/attachment/delete','DELETE','larke-admin-attachment-delete','larke-admin-attachment-delete',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('c9f8e21f92c5b1fac050158966dbc8d0','0','larke-admin-log-index','admin-api/log/index','HEAD','larke-admin-log-index','larke-admin-log-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('ca915ba6b3c4ee7845d8ca1e2e61c524','0','larke-admin-auth-group-detail','admin-api/auth/group/detail','HEAD','larke-admin-auth-group-detail','larke-admin-auth-group-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('cae9f0a274918426c8c7a86a908c0942','0','larke-admin-auth-rule-update','admin-api/auth/rule/update','PUT','larke-admin-auth-rule-update','larke-admin-auth-rule-update',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('da8849cd2d9f8d5e3366bab97df87501','0','larke-admin-admin-update','admin-api/admin/update','PUT','larke-admin-admin-update','larke-admin-admin-update',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('dd3949929ff92571dabb1e5fea3bb674','0','larke-admin-profile-menus','admin-api/profile/menus','GET','larke-admin-profile-menus','larke-admin-profile-menus',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('e0616a67900e317a795d487d6d7f4504','0','larke-admin-profile-menus','admin-api/profile/menus','HEAD','larke-admin-profile-menus','larke-admin-profile-menus',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('e17ebc399a45badc7af599f33eba2953','0','larke-admin-admin-detail','admin-api/admin/detail','HEAD','larke-admin-admin-detail','larke-admin-admin-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('e866ff4f89ed9580c7112b60fc3b50dc','0','larke-admin-attachment-index','admin-api/attachment/index','GET','larke-admin-attachment-index','larke-admin-attachment-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('eadcbfb6e2cefd3f582eb3c31dfae79f','0','larke-admin-attachment-upload','admin-api/attachment/upload','POST','larke-admin-attachment-upload','larke-admin-attachment-upload',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('ec6fb79cc72ed50d554dc7016e13326d','0','larke-admin-passport-captcha','admin-api/passport/captcha','HEAD','larke-admin-passport-captcha','larke-admin-passport-captcha',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('f0649f67c5dc5103117535cb230678f9','0','larke-admin-auth-group-index-children','admin-api/auth/group/index-children','HEAD','larke-admin-auth-group-index-children','larke-admin-auth-group-index-children',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('f08e32b88be1cd19e700e431ec891851','0','larke-admin-auth-rule-index','admin-api/auth/rule/index','GET','larke-admin-auth-rule-index','larke-admin-auth-rule-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('f4a023f17c11aaf118040c059ba32479','0','larke-admin-config-detail','admin-api/config/detail','GET','larke-admin-config-detail','larke-admin-config-detail',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('f87a4c561b7da4d31a25482469423641','0','larke-admin-admin-password','admin-api/admin/password','PUT','larke-admin-admin-password','larke-admin-admin-password',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('fe0aab56dc708bc71e0c05ed3168729b','0','larke-admin-auth-rule-index','admin-api/auth/rule/index','HEAD','larke-admin-auth-rule-index','larke-admin-auth-rule-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1'),('feede8ca7366a70242dd9c363c01669a','0','larke-admin-config-index','admin-api/config/index','HEAD','larke-admin-config-index','larke-admin-config-index',100,1,0,1,1603980048,'127.0.0.1',1603980048,'127.0.0.1');

#
# Structure for table "lake_larke_auth_rule_access"
#

DROP TABLE IF EXISTS `lake_larke_auth_rule_access`;
CREATE TABLE `lake_larke_auth_rule_access` (
  `id` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '0',
  `group_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `rule_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_id` (`rule_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户组与权限关联表';

#
# Data for table "lake_larke_auth_rule_access"
#

INSERT INTO `lake_larke_auth_rule_access` VALUES ('29c987956898bf3236eff03e67323420','9b7931a5d9e8edf555f62e346f4f68b2','39a0dd1fcc85c198bde05def28c67584');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='网站配置';

#
# Data for table "lake_larke_config"
#

INSERT INTO `lake_larke_config` VALUES ('83ae3ba6a20dcb8089d19f4b91f2cee1','others','text','首页2','index','222','index23332','index2 description',105,0,1,0,1603605157,'127.0.0.1',1603605063,'127.0.0.1');

#
# Structure for table "lake_larke_extension"
#

DROP TABLE IF EXISTS `lake_larke_extension`;
CREATE TABLE `lake_larke_extension` (
  `id` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `name` varchar(160) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '扩展id',
  `title` varchar(250) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '名称',
  `introduce` mediumtext CHARACTER SET utf8mb4 NOT NULL COMMENT '简介',
  `author` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '作者',
  `authorsite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '作者地址',
  `authoremail` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '作者邮箱',
  `version` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '版本',
  `adaptation` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '适配最低版本',
  `need_module` text CHARACTER SET utf8mb4 COMMENT '依赖扩展',
  `setting` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '设置信息',
  `setting_data` text CHARACTER SET utf8mb4 COMMENT '设置存储信息',
  `class` text CHARACTER SET utf8mb4 COMMENT '扩展绑定类',
  `installtime` int(10) DEFAULT '0' COMMENT '安装时间',
  `updatetime` int(10) DEFAULT '0' COMMENT '更新时间',
  `listorder` smallint(5) DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '更新IP',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `create_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`),
  KEY `Extension` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='已安装模块列表';

#
# Data for table "lake_larke_extension"
#


#
# Structure for table "lake_larke_rules"
#

DROP TABLE IF EXISTS `lake_larke_rules`;
CREATE TABLE `lake_larke_rules` (
  `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "lake_larke_rules"
#

INSERT INTO `lake_larke_rules` VALUES ('548cf0f7e52c758f0203abb7cd7cfa4b','p','9b7931a5d9e8edf555f62e346f4f68b2','larke-admin-auth-group-update','PUT',NULL,NULL,NULL,'2020-10-29 15:43:33','2020-10-29 15:43:33'),('9ebc7e60a1a592746296347652869b26','g','b74613391d4163dd0dd91b3581fb4d8e','9b7931a5d9e8edf555f62e346f4f68b2',NULL,NULL,NULL,NULL,'2020-10-29 15:53:04','2020-10-29 15:53:04');
