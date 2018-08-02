# ************************************************************
# Sequel Pro SQL dump
# Version 4541
# Host: 127.0.0.1 (MySQL 5.6.38)
# Database: myframe
# ************************************************************



# Dump of table fa_action_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_action_log`;

CREATE TABLE `fa_action_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '管理员名字',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '操作页面',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '日志标题',
  `content` text NOT NULL COMMENT '内容',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `name` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='操作日志表';

# Dump of table fa_auth_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_article`;

CREATE TABLE `fa_article` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `category_id` int(11) DEFAULT NULL COMMENT '分类ID',
  `title` varchar(100) DEFAULT NULL COMMENT '文章标题',
  `author` varchar(50) DEFAULT NULL COMMENT '作者',
  `desc` varchar(500) DEFAULT NULL COMMENT '简介',
  `pic` varchar(200) DEFAULT NULL COMMENT '配图',
  `content` text COMMENT '文章内容',
  `attrdata` set('hot','index','recommend','intui') DEFAULT NULL COMMENT '属性:hot=热门,index=首页,recommend=推荐,intui=内推',
  `views` int(11) DEFAULT '0' COMMENT '浏览量',
  `publishtime` int(11) DEFAULT NULL COMMENT '发布时间',
  `createtime` int(11) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='文章表';

DROP TABLE IF EXISTS `fa_auth_group`;

# Dump of table fa_auth_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_auth_group`;

CREATE TABLE `fa_auth_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父组别',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '组名',
  `rules` text NOT NULL COMMENT '规则ID',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分组表';

LOCK TABLES `fa_auth_group` WRITE;
/*!40000 ALTER TABLE `fa_auth_group` DISABLE KEYS */;

INSERT INTO `fa_auth_group` (`id`, `pid`, `name`, `rules`, `createtime`, `updatetime`, `status`)
VALUES
  (1, 0, '超级管理员', '*', 1490883540, 1490883540, 'normal');

/*!40000 ALTER TABLE `fa_auth_group` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table fa_auth_group_access
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_auth_group_access`;

CREATE TABLE `fa_auth_group_access` (
  `uid` int(10) unsigned NOT NULL COMMENT '会员ID',
  `group_id` int(10) unsigned NOT NULL COMMENT '级别ID',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限分组表';

LOCK TABLES `fa_auth_group_access` WRITE;
/*!40000 ALTER TABLE `fa_auth_group_access` DISABLE KEYS */;

INSERT INTO `fa_auth_group_access` (`uid`, `group_id`)
VALUES
  (1,1);

/*!40000 ALTER TABLE `fa_auth_group_access` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table fa_auth_rule
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_auth_rule`;

CREATE TABLE `fa_auth_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('menu','file') NOT NULL DEFAULT 'file' COMMENT 'menu为菜单,file为权限节点',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则名称',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `condition` varchar(255) NOT NULL DEFAULT '' COMMENT '条件',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态 normal正常 hidden隐藏',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `pid` (`pid`),
  KEY `weigh` (`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='节点表';

LOCK TABLES `fa_auth_rule` WRITE;
/*!40000 ALTER TABLE `fa_auth_rule` DISABLE KEYS */;

INSERT INTO `fa_auth_rule` (`id`, `type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
VALUES
  (1,'file',0,'dashboard/index','控制台','fa fa-dashboard\r','','用于展示当前系统中的统计数据、统计报表及重要实时数据',1,0,0,10,'normal'),
  (2,'file',0,'general','常规管理','fa fa-cogs','','',1,0,1519615381,20,'normal'),
  (3,'file',2,'general/profile','个人配置','fa fa-user','','',1,0,1516691235,0,'normal'),
  (4,'file',3,'general/profile/index','查看个人配置','fa fa-circle-o','','',0,0,1519371352,0,'hidden'),
  (5,'file',3,'general/profile/update','更新个人信息','fa fa-circle-o','','',0,0,1519371359,0,'hidden'),
  (6,'file',0,'auth','权限管理','fa fa-group','','',1,0,0,30,'normal'),
  (7,'file',6,'auth/rule','规则管理','fa fa-bars','','规则通常对应一个控制器的方法,同时左侧的菜单栏数据也从规则中体现,通常建议通过命令行进行生成规则节点',1,0,0,0,'normal'),
  (10,'file',6,'auth/group','角色管理','fa fa-group','','',1,1516700152,1519372829,0,'normal'),
  (11,'file',10,'auth/group/index','查看','fa fa-dot','','',0,1516700766,1519372713,0,'hidden'),
  (12,'file',10,'auth/group/add','添加','','','',0,1516700808,1516700808,0,'hidden'),
  (13,'file',10,'auth/group/edit','编辑','','','',0,1516700826,1516700826,0,'hidden'),
  (14,'file',10,'auth/group/del','删除','','','',0,1516700844,1516700844,0,'hidden'),
  (15,'file',6,'auth/user','用户管理','fa fa-user','','',1,1516846652,1516846671,0,'normal'),
  (16,'file',15,'auth/user/index','查看','','','',0,1516846707,1516846707,0,'hidden'),
  (17,'file',15,'auth/user/add','添加','','','',0,1516846730,1516846730,0,'hidden'),
  (18,'file',15,'auth/user/edit','编辑','','','',0,1516846749,1516846749,0,'hidden'),
  (19,'file',15,'auth/user/del','删除','','','',0,1516846766,1516846766,0,'hidden'),
  (20,'file',7,'auth/rule/index','查看','fa fa-dot','','',0,1517808819,1517808837,0,'hidden'),
  (21,'file',7,'auth/rule/add','添加','fa fa-dot','','',0,1517808891,1517808891,0,'hidden'),
  (22,'file',7,'auth/rule/edit','编辑','fa fa-dot','','',0,1517808904,1517808904,0,'hidden'),
  (23,'file',7,'auth/rule/del','删除','fa fa-dot','','',0,1517808914,1517808914,0,'hidden'),
  (24,'file',6,'auth/actionlog/index','用户日志','fa fa-list-alt','','',1,1517809226,1517809226,0,'normal'),
  (25,'file',2,'general/system','系统配置','fa fa-cog','','',1,1517812914,1517812914,0,'normal'),
  (26,'file',25,'general/system/add','添加','fa fa-dot','','',0,1517820715,1517820715,0,'hidden'),
  (27,'file',25,'general/system/index','查看','fa fa-dot','','',0,1517820723,1517820723,0,'hidden'),
  (28,'file',25,'general/system/edit','编辑','fa fa-dot','','',0,1517820727,1517820751,0,'hidden'),
  (29,'file',25,'general/system/del','删除','fa fa-dot','','',0,1517820733,1517820733,0,'hidden'),
  (30,'file',25,'general/system/emailtest','邮件测试','fa fa-dot','','',0,1517901725,1517901725,0,'hidden'),
  (31,'file',10,'auth/group/roletree','读取角色权限树','fa fa-dot','','',0,1517985436,1517985436,0,'hidden'),
  (32,'file',0,'ajax','公共方法','fa fa-dot','','',0,1519371028,1519371170,0,'normal'),
  (33,'file',32,'ajax/upload','上传接口','fa fa-dot','','',0,1519371734,1519371734,0,'hidden'),
  (34,'file',32,'ajax/wipecache','清除缓存','fa fa-dot','','',0,1519371775,1519371783,0,'hidden'),
  (35, 'file', 0, 'article', '内容管理', 'fa fa-file-text', '', '', 1, 1531211955, 1531212082, 0, 'normal'),
  (36, 'file', 35, 'article/index', '内容管理', 'fa fa-file-text-o', '', '', 1, 1531212001, 1531212094, 0, 'normal'),
  (37, 'file', 35, 'category/index', '栏目管理', 'fa fa-list-ol', '', '', 1, 1531212043, 1531212043, 0, 'normal'),
  (38, 'file', 36, 'article/add', '添加', 'fa fa-dot', '', '', 0, 1531361021, 1531361021, 0, 'hidden'),
  (39, 'file', 36, 'article/edit', '编辑', 'fa fa-dot', '', '', 0, 1531361040, 1531361040, 0, 'hidden'),
  (40, 'file', 36, 'article/del', '删除', 'fa fa-dot', '', '', 0, 1531361067, 1531361067, 0, 'hidden'),
  (41, 'file', 37, 'category/add', '添加', 'fa fa-dot', '', '', 0, 1531361091, 1531361091, 0, 'hidden'),
  (42, 'file', 37, 'category/edit', '编辑', 'fa fa-dot', '', '', 0, 1531361105, 1531361105, 0, 'hidden'),
  (43, 'file', 37, 'category/del', '删除', 'fa fa-dot', '', '', 0, 1531361164, 1531361164, 0, 'hidden');


/*!40000 ALTER TABLE `fa_auth_rule` ENABLE KEYS */;
UNLOCK TABLES;

# Dump of table fa_category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_category`;

CREATE TABLE `fa_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0' COMMENT '父级ID',
  `name` varchar(200) DEFAULT NULL COMMENT '分类名称',
  `ord` int(11) DEFAULT '0' COMMENT '排序',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='分类表';

# Dump of table fa_config
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_config`;

CREATE TABLE `fa_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) NOT NULL DEFAULT '' COMMENT '分组',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) NOT NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `value` text NOT NULL COMMENT '变量值',
  `content` text NOT NULL COMMENT '变量字典数据',
  `rule` varchar(100) NOT NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展属性',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统配置';

LOCK TABLES `fa_config` WRITE;
/*!40000 ALTER TABLE `fa_config` DISABLE KEYS */;

INSERT INTO `fa_config` (`id`, `name`, `group`, `title`, `tip`, `type`, `value`, `content`, `rule`, `extend`)
VALUES
  (1,'name','basic','站点名称','请填写站点名称','string','后台管理系统','','required',''),
  (2,'beian','basic','备案号','豫ICP备12345678号-8','string','','','',''),
  (3,'cdnurl','basic','CDN地址','如果静态资源使用第三方云储存请配置该值','string','http://myframe','','',''),
  (4,'version','basic','版本号','如果静态资源有变动请重新配置该值','string','1.0.1','','required',''),
  (5,'timezone','basic','时区','','string','Asia/Shanghai','','required',''),
  (6,'forbiddenip','basic','禁止IP','一行一条记录','text','','','',''),
  (8,'fixedpage','basic','后台固定页','请尽量输入左侧菜单栏存在的链接','string','dashboard','','required',''),
  (9,'categorytype','dictionary','分类类型','','array','{\"Default\":\"默认\",\"Page\":\"单页\",\"Article\":\"文章\"}','','',''),
  (10,'configgroup','dictionary','配置分组','','array','{\"basic\":\"基础配置\",\"email\":\"邮箱配置\",\"dictionary\":\"字典配置\"}','','',''),
  (11,'mail_type','email','邮件发送方式','选择邮件发送方式','select','1','[\"Please select\",\"SMTP\",\"Mail\"]','',''),
  (12,'mail_smtp_host','email','SMTP服务器','错误的配置发送邮件会导致服务器超时','string','smtp.qq.com','','',''),
  (13,'mail_smtp_port','email','SMTP端口','(不加密默认25,SSL默认465,TLS默认587)','string','465','','',''),
  (14,'mail_smtp_user','email','SMTP用户名','（填写完整用户名）','string','10000','','',''),
  (15,'mail_smtp_pass','email','SMTP密码','（填写您的密码）','string','password','','',''),
  (16,'mail_verify_type','email','SMTP验证方式','（SMTP验证方式[推荐SSL]）','select','2','[\"None\",\"TLS\",\"SSL\"]','',''),
  (17,'mail_from','email','发件人邮箱','','string','10000@qq.com','','',''),
  (18,'week','dictionary','星期','','array','{\"1\":\"星期一\",\"2\":\"星期二\",\"3\":\"星期三\"}','','',''),
  (19,'sexdata','dictionary','性别','','array','{\"male\":\"男\",\"female\":\"女\"}','','',''),
  (20,'attrdata','dictionary','属性','','array','{\"hot\":\"热门\",\"index\":\"首页\",\"recommend\":\"推荐\",\"intui\":\"内推\"}','','','');

/*!40000 ALTER TABLE `fa_config` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table fa_test
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_test`;

CREATE TABLE `fa_test` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(100) DEFAULT NULL COMMENT '文章标题',
  `author` varchar(50) DEFAULT NULL COMMENT '作者',
  `desc` varchar(500) DEFAULT NULL COMMENT '简介',
  `pic` varchar(200) DEFAULT NULL COMMENT '配图',
  `content` text COMMENT '文章内容',
  `week` enum('1','2','3') DEFAULT NULL COMMENT '星期:1=星期一,2=星期二,3=星期三',
  `sexdata` enum('male','female') DEFAULT 'male' COMMENT '性别:male=男,female=女',
  `attrdata` set('hot','index','recommend','intui') DEFAULT NULL COMMENT '属性:hot=热门,index=首页,recommend=推荐,intui=内推',
  `publishtime` int(11) DEFAULT NULL COMMENT '发布时间',
  `createtime` int(11) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='测试文章表';

LOCK TABLES `fa_test` WRITE;
/*!40000 ALTER TABLE `fa_test` DISABLE KEYS */;

INSERT INTO `fa_test` (`id`, `title`, `author`, `desc`, `pic`, `content`, `week`, `sexdata`, `attrdata`, `publishtime`, `createtime`, `updatetime`)
VALUES
  (1,'121','321','321','','<h1>3213213</h1>','2','female','hot,index',1527738950,1527738960,1527747944);

/*!40000 ALTER TABLE `fa_test` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table fa_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_user`;

CREATE TABLE `fa_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` date NOT NULL COMMENT '生日',
  `score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `prevtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次登录时间',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `loginip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `joinip` varchar(50) NOT NULL DEFAULT '' COMMENT '加入IP',
  `jointime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入时间',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `token` varchar(50) NOT NULL DEFAULT '' COMMENT 'Token',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户表';

LOCK TABLES `fa_user` WRITE;
/*!40000 ALTER TABLE `fa_user` DISABLE KEYS */;

INSERT INTO `fa_user` (`id`, `username`, `nickname`, `password`, `salt`, `email`, `mobile`, `avatar`, `level`, `gender`, `birthday`, `score`, `prevtime`, `loginfailure`, `logintime`, `loginip`, `joinip`, `jointime`, `createtime`, `updatetime`, `token`, `status`)
VALUES
  (1,'admin','超级管理员','c13f62012fd6a8fdf06b3452a94430e5','rpR6Bv','admin@163.com','13888888888','/uploads/20180320/c7e5915c0771500291271eaeb3e41274.png',0,0,'2017-04-15',0,1491822015,0,1527833979,'127.0.0.1','127.0.0.1',1491461418,0,1527833979,'af833f3f-d8d3-4c14-8af7-45a69c1f9bc5','normal');

/*!40000 ALTER TABLE `fa_user` ENABLE KEYS */;
UNLOCK TABLES;
