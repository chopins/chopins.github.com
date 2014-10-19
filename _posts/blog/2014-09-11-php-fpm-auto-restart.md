---
layout: blog_contents
title: php-fpm 自动重启脚本
categories: blog
---

本脚本检测php-fpm进程数目，达到最大进程数目后自动重启php-fpm
脚本需要加入crontab中按计划执行，脚本执行间隔不会小于2分钟

```
#!/bin/bash

LOCK_FILE='/tmp/restart_php.lock'
MAX_PROC_NUM=500

if [ ! -e $LOCK_FILE ] ;then
touch $LOCK_FILE
fi
LOCK_TIME=`stat -c %X $LOCK_FILE`
let "LOCK_TIME=$LOCK_TIME + 120"
NOW_TIME=`date +%s`                                                                                                                    

if [ $NOW_TIME -le $LOCK_TIME ];then
    exit 0
fi

proc_num=`ps aux|grep php-fpm|grep -v grep |wc -l`

if [ $proc_num -ge $MAX_PROC_NUM ];then
    killall -s USR2 php-fpm
    touch $LOCK_FILE
fi
```