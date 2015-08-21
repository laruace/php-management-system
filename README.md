# php-management-system
基于star Framework , bootstrap 的后台管理系统， 包含菜单管理、用户和用户组管理、权限管理

#DEMO: http://demo.xiayoo.cn
注意：菜单和权限编辑，需要重新登录才有效果
```
超级管理员  
用户名： admin 密码: 123456
普通管理员
用户名：  demo  密码： 123456
```
#环境要求
1.php5以上版本
2.安装mysqli模块

#文档及配置文件
1.后台管理数据库表结构
后台SQL路径：Document/DataBase/OnGoing/admin.sql
默认表前缀test_，可以根据项目修改表前缀统一替换，导入数据库
表结构文档地址：Document/DataBase/Main/manage数据库表结构.docx


2.修改配置文件
配置文件路径：manage.sample.com/application/configs/application.ini


#数据库相关配置
```
	;数据库适配器,目前只支持Mysqli和pdo_mysql
	resources.db.adapter = "Mysqli"
	;数据库表明前缀
	resources.db.prefix = test_
	;以下为数据库配置
	resources.db.params.host = 127.0.0.1
	resources.db.params.dbname = test
	resources.db.params.username = root
	resources.db.params.password = "123456"
	;是否多从库
	resources.db.multi_slave_db = true
	resources.db.slave_db.0.adapter = "Mysqli"
	resources.db.slave_db.0.params.host = 127.0.0.1
	resources.db.slave_db.0.params.dbname = test
	resources.db.slave_db.0.params.username = root
	resources.db.slave_db.0.params.password = "123456"
```

vhost指定后台目录:  manage.sample.com/public目录下

后台登录地址: http://127.0.0.1
初始化管理员账号： admin 管理员密码 123456
