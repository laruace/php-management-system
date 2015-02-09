-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- 主机: localhost
-- 生成日期: 2014 年 06 月 04 日 17:45
-- 服务器版本: 5.0.45
-- PHP 版本: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- 数据库: `test`
-- 

-- --------------------------------------------------------

-- 
-- 表的结构 `test_admin`
-- 

CREATE TABLE `test_admin` (
  `admin_id` int(10) NOT NULL auto_increment COMMENT '管理员ID',
  `username` varchar(30) NOT NULL COMMENT '管理员用户名',
  `admin_name` varchar(30) NOT NULL COMMENT '名字',
  `department_id` int(10) NOT NULL COMMENT '部门ＩＤ',
  `password` char(32) NOT NULL COMMENT '密码',
  `last_login` int(11) NOT NULL COMMENT '最后登录时间',
  `error_times` smallint(6) NOT NULL COMMENT '密码错误次数',
  `error_date` int(11) NOT NULL COMMENT '密码错误时间',
  `add_time` int(10) NOT NULL COMMENT '添加时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY  (`admin_id`),
  KEY `username` (`username`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='管理员' AUTO_INCREMENT=6 ;

-- 
-- 导出表中的数据 `test_admin`
-- 

INSERT INTO `test_admin` VALUES (4, 'admin', 'admin', 1, '3ee24b9464e705587440670e79e61c18', 1401875012, 7, 20140604, 1400467588, 1401864220);

-- --------------------------------------------------------

-- 
-- 表的结构 `test_admin_department`
-- 

CREATE TABLE `test_admin_department` (
  `department_id` int(10) NOT NULL auto_increment COMMENT '部门ID',
  `department_name` varchar(30) NOT NULL COMMENT '部门名称',
  `sort` smallint(5) NOT NULL COMMENT '排序',
  `is_show` tinyint(1) NOT NULL COMMENT '是否显示',
  `add_time` int(10) NOT NULL COMMENT '添加时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY  (`department_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='部门' AUTO_INCREMENT=2 ;

-- 
-- 导出表中的数据 `test_admin_department`
-- 


INSERT INTO `test_admin_department` VALUES (1, '超级管理员', 255, 0, 1399975325, 1399975325);

-- --------------------------------------------------------

-- 
-- 表的结构 `test_admin_login`
-- 

CREATE TABLE `test_admin_login` (
  `login_id` int(11) NOT NULL auto_increment COMMENT '自增ＩＤ',
  `admin_id` int(11) NOT NULL COMMENT '管理员ＩＤ',
  `login_ip` bigint(20)	NOT NULL COMMENT '登录ID',
  `add_time` int(11) NOT NULL COMMENT '登录时间',
  PRIMARY KEY  (`login_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='管理员登录日志' AUTO_INCREMENT=28 ;

-- 
-- 导出表中的数据 `test_admin_login`
-- 

-- --------------------------------------------------------

-- 
-- 表的结构 `test_admin_menu`
-- 

CREATE TABLE `test_admin_menu` (
  `menu_id` int(10) NOT NULL auto_increment COMMENT '菜单ID',
  `menu_name` varchar(30) NOT NULL COMMENT '菜单名称',
  `controller` varchar(30) NOT NULL COMMENT 'controller',
  `action` varchar(30) NOT NULL COMMENT 'action',
  `parent_id` int(10) NOT NULL COMMENT '父级ＩＤ',
  `menu_level` tinyint(3) NOT NULL COMMENT '菜单层级',
  `top_id` int(10) NOT NULL COMMENT '顶级父类ID',
  `is_show` tinyint(1) NOT NULL COMMENT '是否显示',
  `sort` smallint(6) NOT NULL COMMENT '排序',
  `add_time` int(10) NOT NULL COMMENT '添加时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY  (`menu_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='管理菜单' AUTO_INCREMENT=36 ;

-- 
-- 导出表中的数据 `test_admin_menu`
-- 

INSERT INTO `test_admin_menu` VALUES (1, '系统管理', '', '', 0, 1, 0, 1, 256, 1400066015, 1400150002);
INSERT INTO `test_admin_menu` VALUES (5, '管理员管理', '', '0', 1, 2, 1, 1, 255, 1400121289, 1400466874);
INSERT INTO `test_admin_menu` VALUES (6, '菜单管理', '', '0', 1, 2, 1, 1, 255, 1400121357, 1400121357);
INSERT INTO `test_admin_menu` VALUES (7, '用户管理', 'system', 'adminmanage', 5, 3, 1, 1, 255, 1400121595, 1400137205);
INSERT INTO `test_admin_menu` VALUES (10, '菜单管理', 'system', 'menumanage', 6, 3, 1, 1, 255, 1400149854, 1400149854);
INSERT INTO `test_admin_menu` VALUES (11, '部门管理', 'system', 'departmentmanage', 5, 3, 1, 1, 255, 1400149927, 1400149965);
INSERT INTO `test_admin_menu` VALUES (12, '添加菜单', 'system', 'addmenu', 6, 3, 1, 0, 255, 1400232319, 1400232319);
INSERT INTO `test_admin_menu` VALUES (13, '编辑菜单', 'system', 'editmenu', 6, 3, 1, 0, 255, 1400232345, 1400232345);
INSERT INTO `test_admin_menu` VALUES (14, '编辑管理员', 'system', 'editadmin', 5, 3, 1, 0, 255, 1400232406, 1400232406);
INSERT INTO `test_admin_menu` VALUES (15, '添加管理员', 'system', 'addadmin', 5, 3, 1, 0, 255, 1400232431, 1400232431);
INSERT INTO `test_admin_menu` VALUES (16, '部门编辑', 'system', 'editdepartment', 5, 3, 1, 0, 255, 1400467718, 1400467718);
INSERT INTO `test_admin_menu` VALUES (17, '删除管理员', 'system', 'deleteadmin', 5, 3, 1, 0, 255, 1400471099, 1400471099);
INSERT INTO `test_admin_menu` VALUES (18, '添加部门', 'system', 'adddepartment', 5, 3, 1, 0, 255, 1400471163, 1400471163);
INSERT INTO `test_admin_menu` VALUES (19, '编辑部门', 'system', 'editdepartment', 5, 3, 1, 0, 255, 1400471180, 1400471180);
INSERT INTO `test_admin_menu` VALUES (20, '删除部门', 'system', 'deletedepartment', 5, 3, 1, 0, 255, 1400471196, 1400471196);
INSERT INTO `test_admin_menu` VALUES (21, '删除菜单', 'system', 'deletemenu', 6, 3, 1, 0, 255, 1400471763, 1400471763);
INSERT INTO `test_admin_menu` VALUES (30, '子菜单', 'system', 'submenu', 6, 3, 1, 0, 255, 1400486774, 1400486774);
INSERT INTO `test_admin_menu` VALUES (35, '登录日志', 'system', 'loginlog', 5, 3, 1, 1, 255, 1400825010, 1400830876);


-- --------------------------------------------------------

-- 
-- 表的结构 `test_auth`
-- 

CREATE TABLE `test_auth` (
  `auth_id` int(10) NOT NULL auto_increment COMMENT '权限ID',
  `department_id` int(10) NOT NULL COMMENT '部门ID',
  `admin_id` int(10) NOT NULL COMMENT '管理员ID',
  `menu_id` int(10) NOT NULL COMMENT '菜单ID',
  `add_time` int(10) NOT NULL COMMENT '添加时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY  (`auth_id`),
  KEY `department_id` (`department_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='权限' AUTO_INCREMENT=136 ;

-- 
-- 导出表中的数据 `test_auth`
-- 


INSERT INTO `test_auth` VALUES (135, 1, 0, 7, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (134, 1, 0, 11, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (133, 1, 0, 14, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (132, 1, 0, 15, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (131, 1, 0, 16, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (130, 1, 0, 17, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (129, 1, 0, 20, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (128, 1, 0, 35, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (127, 1, 0, 19, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (126, 1, 0, 18, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (125, 1, 0, 12, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (124, 1, 0, 10, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (123, 1, 0, 13, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (122, 1, 0, 21, 1401864187, 1401864187);
INSERT INTO `test_auth` VALUES (121, 1, 0, 30, 1401864187, 1401864187);

