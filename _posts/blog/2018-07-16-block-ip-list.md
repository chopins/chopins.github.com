---
layout: blog_contents
title: 封IP列表
categories: blog
---

如果需要屏蔽的IP比较多，需要使用`ipset`来配合`iptable`进行。

iptable 屏蔽一个IP方法如下：
```
iptables -I INPUT -s 192.168.1.100 -j DROP
```

配合`ipset`来封大量IP方法如下:
1、创建黑名单列表：
```
ipset create blacklist hash:ip hashsize 4096
```
2、在`iptable`中使用黑名单列表
```
iptables -I INPUT -m set --match-set blacklist src -j DROP
```
3、向黑名单列表中添加IP
```
ipset add blacklist 192.168.1.100
```
4、查看黑白名单列表
```
ipset list blacklist
```
5、以上命令中出现的`blacklist`为黑名单的名字
6、`hashsize`为尺寸，默认是1024能存储128个IP，当满后会自动扩展到当前两倍的尺寸
7、封IP段
```
ipset add blacklist 192.168.1.0/24
```
