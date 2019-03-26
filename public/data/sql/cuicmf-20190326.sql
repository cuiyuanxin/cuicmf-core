-- MySQL dump 10.13  Distrib 5.6.41, for Linux (x86_64)
--
-- Host: localhost    Database: cuicmf
-- ------------------------------------------------------
-- Server version	5.6.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cui_addons`
--

DROP TABLE IF EXISTS `cui_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cui_addons` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '插件唯一标识',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '插件名称',
  `author` varchar(40) NOT NULL DEFAULT '' COMMENT '作者',
  `version` varchar(50) NOT NULL DEFAULT '' COMMENT '插件版本',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '插件描述',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '插件后台\n0:不存在\n1:存在',
  `is_index` tinyint(1) NOT NULL DEFAULT '0' COMMENT '插件前台\n0:不存在\n1:存在',
  `setting` mediumtext NOT NULL COMMENT '插件配置',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '插件状态\n0:系统\n1:用户',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='插件管理表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cui_addons`
--

LOCK TABLES `cui_addons` WRITE;
/*!40000 ALTER TABLE `cui_addons` DISABLE KEYS */;
INSERT INTO `cui_addons` VALUES (1,'systeminfo','系统环境信息','cuiyuanxin','0.1','用于显示一些服务器的信息',0,0,'{\"title\":\"系统信息\",\"display\":\"1\"}',1553331579,1553331579,1),(2,'demo','演示插件','byron sampson','0.1','thinkph5.1 演示插件',0,0,'{\"display\":\"1\"}',1553503839,1553503839,1);
/*!40000 ALTER TABLE `cui_addons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cui_auth_group`
--

DROP TABLE IF EXISTS `cui_auth_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cui_auth_group` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '用户组名称',
  `description` varchar(150) NOT NULL DEFAULT '' COMMENT '用户组描述',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '权限组状态\n1:开启\n0:关闭',
  `rules` char(80) NOT NULL DEFAULT '' COMMENT '用户组规则ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='用户组表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cui_auth_group`
--

LOCK TABLES `cui_auth_group` WRITE;
/*!40000 ALTER TABLE `cui_auth_group` DISABLE KEYS */;
INSERT INTO `cui_auth_group` VALUES (1,'超级管理员','拥有基本拥有系统的所有权限是除系统管理员之外拥有权限最多的用户组',1,'1,2,8,9,10,11,20,3,4,6,7,17,5,12,13,14,15,16,18,19'),(2,'管理员','拥有大部分管理权限',1,'1,2,8,9,10,11,20'),(3,'ceshi1234','ceshi',1,'1'),(4,'编辑','拥有编辑权限',1,''),(5,'编辑1','编辑',1,'1');
/*!40000 ALTER TABLE `cui_auth_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cui_auth_group_access`
--

DROP TABLE IF EXISTS `cui_auth_group_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cui_auth_group_access` (
  `uid` mediumint(8) NOT NULL COMMENT '管理员ID',
  `group_id` mediumint(8) NOT NULL COMMENT '用户组ID',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户组明细表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cui_auth_group_access`
--

LOCK TABLES `cui_auth_group_access` WRITE;
/*!40000 ALTER TABLE `cui_auth_group_access` DISABLE KEYS */;
INSERT INTO `cui_auth_group_access` VALUES (2,2),(2,3),(3,3);
/*!40000 ALTER TABLE `cui_auth_group_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cui_auth_rule`
--

DROP TABLE IF EXISTS `cui_auth_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cui_auth_rule` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `pid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `title` varchar(30) NOT NULL DEFAULT '' COMMENT '规则名称',
  `sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '规则排序',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '规则标识',
  `condition` varchar(100) NOT NULL DEFAULT '' COMMENT '规则表达式',
  `icon` varchar(45) NOT NULL DEFAULT '' COMMENT '图标代码',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '规则状态\n1:开启\n0:关闭',
  `level` tinyint(1) NOT NULL DEFAULT '1' COMMENT '等级',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COMMENT='权限规则表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cui_auth_rule`
--

LOCK TABLES `cui_auth_rule` WRITE;
/*!40000 ALTER TABLE `cui_auth_rule` DISABLE KEYS */;
INSERT INTO `cui_auth_rule` VALUES (1,0,'控制台',0,'Index/index','','fa fa-home',1,1),(2,0,'菜单管理',3,'Menu/index','','fa fa-list',1,1),(3,0,'管理员管理',2,'User/index','','fa fa-users',1,1),(4,3,'管理员列表',10,'User/index','','fa fa-circle-o',1,2),(5,3,'添加管理员',11,'User/create','','fa fa-circle-o',1,2),(6,4,'编辑管理员',100,'User/update','','fa fa-circle-o',0,3),(7,4,'删除管理员',101,'User/delete','','fa fa-circle-o',0,3),(8,2,'菜单列表',10,'Menu/index','','fa fa-circle-o',1,2),(9,8,'添加菜单',100,'Menu/create','','fa fa-circle-o',0,3),(10,8,'编辑菜单',101,'Menu/update','','fa fa-circle-o',0,3),(11,8,'删除菜单',102,'Menu/delete','','fa fa-circle-o',0,3),(12,3,'角色组列表',12,'Auth/index','','fa fa-circle-o',1,2),(13,12,'添加角色组',100,'Auth/create','','fa fa-circle-o',0,3),(14,12,'编辑角色组',101,'Auth/update','','fa fa-circle-o',0,3),(15,12,'删除角色组',102,'Auth/delete','','fa fa-circle-o',0,3),(16,12,'设置权限',103,'Auth/rules','','fa fa-circle-o',0,3),(17,4,'管理员操作',102,'User/write','','fa fa-circle-o',0,3),(18,12,'角色组操作',104,'Auth/write','','fa fa-circle-o',0,3),(19,12,'权限操作',105,'Auth/rules_write','','fa fa-circle-o',0,3),(20,8,'菜单操作',103,'Menu/write','','fa fa-circle-o',0,3),(21,0,'系统管理',1,'System/index','','fa fa-cog',1,1),(22,21,'插件管理',10,'Addons/index','','fa fa-skyatlas',1,2),(23,22,'插件列表',100,'Addons/index','','fa fa-circle-o',1,3),(24,23,'未安装插件列表',1000,'Addons/uninstalled','','fa fa-circle-o',0,4),(25,23,'创建插件',1001,'Addons/create','','fa fa-circle-o',0,4),(26,23,'预览插件',1002,'Addons/preview','','fa fa-circle-o',0,4),(27,23,'插件操作',1003,'Addons/write','','fa fa-circle-o',0,4),(28,23,'安装插件',1004,'Addons/install','','fa fa-circle-o',0,4),(29,23,'卸载插件',1005,'Addons/uninstall','','fa fa-circle-o',0,4),(30,23,'删除插件',1006,'Addons/delete','','fa fa-circle-o',0,4),(31,21,'钩子管理',11,'Hooks/index','','fa fa-anchor',1,2),(32,31,'钩子列表',1000,'Hooks/index','','fa fa-circle-o',1,4),(33,31,'添加钩子',1001,'Hooks/create','','fa fa-circle-o',0,4),(34,31,'编辑钩子',1002,'Hooks/update','','fa fa-circle-o',0,4),(35,31,'钩子操作',1003,'Hooks/write','','fa fa-circle-o',0,4),(36,31,'删除钩子',1004,'Hooks/delete','','fa fa-circle-o',0,4);
/*!40000 ALTER TABLE `cui_auth_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cui_hooks`
--

DROP TABLE IF EXISTS `cui_hooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cui_hooks` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `description` text NOT NULL COMMENT '钩子描述',
  `addons` varchar(255) NOT NULL DEFAULT '' COMMENT '插件列表',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '钩子状态\n0:禁用\n1:启用',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='钩子列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cui_hooks`
--

LOCK TABLES `cui_hooks` WRITE;
/*!40000 ALTER TABLE `cui_hooks` DISABLE KEYS */;
INSERT INTO `cui_hooks` VALUES (1,'pageHeader','页面header钩子，一般用于加载插件CSS文件和代码','demo',0,1553503839,1),(2,'pageFooter','页面footer钩子，一般加载具体业务内容','',0,1553329170,1),(3,'adminIndex','后台管理首页钩子','systeminfo',0,1553504227,1),(4,'adminLogin','后台登录钩子','',1553528667,1553528753,1),(5,'app','应用模块','',1553594491,1553594491,1);
/*!40000 ALTER TABLE `cui_hooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cui_user`
--

DROP TABLE IF EXISTS `cui_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cui_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL COMMENT '用户名',
  `password` char(32) NOT NULL COMMENT '密码',
  `nickname` varchar(30) NOT NULL DEFAULT '' COMMENT '昵称',
  `realname` varchar(30) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '用户状态\n0:禁用\n1:正常\n2:锁定',
  `reg_ip` int(10) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_ip` int(10) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_time` int(10) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `mobile` (`mobile`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='管理员账户';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cui_user`
--

LOCK TABLES `cui_user` WRITE;
/*!40000 ALTER TABLE `cui_user` DISABLE KEYS */;
INSERT INTO `cui_user` VALUES (1,'cuiyuanxin','ccba12ad2927d5860c157622754fa38d','Redcar','崔元欣','15811506097','15811506097@163.com',1,2147483647,2147483647,1553515622,1552379385,1552379387),(2,'cuiyuanxin1','9817133175eb93d9e845e6d513f4f37f','CC','CC','13037047497','745454106@qq.com',1,2147483647,2147483647,1553182679,1552636244,1552636244),(3,'cuiyuanxin2','9817133175eb93d9e845e6d513f4f37f','cuiyuanxi','cuiyuanxi','18513636097','15811506097@139.com',1,2147483647,0,0,1553518652,1553518706);
/*!40000 ALTER TABLE `cui_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-03-26 10:38:30
