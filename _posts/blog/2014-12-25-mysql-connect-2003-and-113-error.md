---
layout: blog_contents
title: MySQL连接出现的2003(HY000) Ip(113) 错误解决
categories: blog
---

当数据库服务器业务较多导致连接带宽过大时，会导致MySQL连接失败，出现下面的错误:  

```
ERROR 2003 (HY000): Can't connect to MySQL server on '192.168.0.2' (113)
```
此错误会不定时出现.通过`perror`命令查询113错误代码得到下面信息:  

```
OS error code 113:  No route to host
```
该信息时提示没有路由，通常由于防火墙屏蔽了来路IP才会出现这个错误.但是目前我的情况并不是完全无法访问，而是不定时出现。排查系统 messages 日志发现了下面的信息:  

```
vnstatd[4249]: Traffic rate for "eth1" higher than set maximum 100 Mbit (32->440, r660 t4), syncing.
```
信息意思是流量增加高于设置的100Mbit.`vnstatd`是一个流量监控工具,要解决这个信息问题，需要修改该工具的相关配置，配置文件通常位于`/etc/vnstat.conf`。与这个信息相关的设置项是`MaxBandwidth`项，该项设置的值是控制最大带宽的，如果大于这个值，连接将被拒绝。  
于是将默认的100改成1000，修改后重启`vnstatd`,或者重启防火墙让这个配置生效。  
然后再测试，问题得到解决。