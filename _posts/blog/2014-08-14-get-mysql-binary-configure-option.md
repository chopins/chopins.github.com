---
layout: blog_contents
title: 查看当前运行 MySQL 编译参数
categories: blog
---

查看 MySQL 当前运行的 MySQL 服务器的编译配置参数:

`cat /usr/local/mysql/bin/mysqlbug | grep CONFIGURE_LINE`

上面的是 MySQL 安装在 `/usr/local/mysql` 目录下，根据实际情况修改目录