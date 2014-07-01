---
layout: blog_contents
title: MySQL innodb 简单备份脚本
categories: blog
---

脚本下载 [http://toknot.com/download/mysqlbackup]

脚本功能：
分每日备份，每周备份，每月备份，所以备份默认只保留7份，多余的会删除  
脚本支持多数据库分别备份

用法：
配置好以下变量：

    1. DBLIST 数据库列表
    2. USER   数据库用户名
    3. PASS   数据库用户密码
    4. BACKUP_DIR  备份文件存放文件夹
    5. MAXFILENUM  最大保留文件数

然后将本脚本添加到每日备份计划任务即可
