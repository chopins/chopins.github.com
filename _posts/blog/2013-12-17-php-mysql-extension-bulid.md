---
layout: blog_contents
title: PHP编译完成后添加 MySQL相关扩展
categories: blog
---

本文是以 PHP 5.5.6 为基础介绍 MySQL 相关扩展在 PHP 编译完成后添加过程. 
MySQL 扩展主要包括 mysql， mysqli， mysqlnd， pdo 4个单独扩展，文件随 PHP 一起发布，因此在 PHP 的 ext 目录下面.

以下是编译过程： 
 
1. 先添加 mysqlnd 扩展，否则无法编译其他扩展
2. 将 mysqlnd/config9.m4 复制为 mysqlnd/config.m4 
3. 编译 mysqlnd 的时候如果你编译的 PHP 没有明确disable-openssl, 那么本扩展必须要安装有OpenSSL,
   如果需要禁止，将 mysqlnd/config.m4 文件中的（大约36行） 
    
   `if test "$PHP_OPENSSL" != "no" || test "$PHP_OPENSSL_DIR" != "no"; then`

   修改为：
   
   `if test "$PHP_OPENSSL" != "yes" || test "$PHP_OPENSSL_DIR" != "yes"; then`

4. 执行 configure 
5. 将 mysqlnd/mysqlnd\_portability.h 中（大约40行）

    `#  include <ext/mysqlnd/php_mysqlnd_config.h>`

   修改为： 
    `#  include "config.h"`

6. 开始编译，并加入 PHP 配置文件中
7. 现在可以顺利编译 mysqli 与 mysql 扩展了
8. 编译mysql扩展时出现`configure: error: Cannot find libmysqlclient under /usr.`错误时，需要指定`--with-libdir`选项，使用如下方法

    `./configure --with-php-config=/opt/php/bin/php-config --with-libdir=lib64 --with-mysql=/usr/lib64/mysql`
    以上是64位系统指定的，需要根据实际情况修改
9. 然后编译 pdo 扩展
10. 上一步完成后即可以编译 pdo_mysql

目前 php 的 mysql 相关扩展已经编译完成
为了简化扩展编译，可以下载 [PHP扩展自动编译脚本](http://toknot.com/blog/php-extension-auto-config-build/)