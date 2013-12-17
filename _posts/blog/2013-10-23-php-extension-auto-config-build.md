---
layout: blog_contents
title: PHP 扩展自动配置与编译安装脚本 
categories: blog
---

##PHP extension automatic configure and build, install script

##特性
+ 自动搜索`phpize`位置，搜索路径依次为:
    1. /opt/php/bin/phpize
    2. /usr/local/php/bin/phpize
    3. /usr/bin/phpize
+ 自动执行`phpize`
+ 自动执行`configure`
+ 自动执行`make && make install`
+ 自动添加到`php.ini`文件
+ 自动清理编译目录
+ 自动重启`php-fpm`
+ 不支持依赖检查

[下载地址](http://toknot.com/download/phpicm)

使用时切换到root用户，进入PHP扩展源码目录，然后执行

Features:
+ Auto search `phpize` path, search path order by: 
    1. /opt/php/bin/phpize
    2. /usr/local/php/bin/phpize
    3. /usr/bin/phpize 
+ Auto exec `phpize`
+ Auto exec `configure`
+ Auto exec `make && make install`
+ Auto add extension config to `php.ini`
+ Auto clean bulid dir
+ Auto restart `php-fpm`
+ Can not check depend

[Now Download](http://toknot.com/download/phpicm)

The script need run on __root__ user, and exec in extension directory
	
