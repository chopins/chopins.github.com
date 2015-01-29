---
layout: blog_contents
title: 网络掩码转换为CIDR算法
categories: blog
---

CIDR是指无类别域间路由，就是指当使用类似`192.168.0.1/24`中的24这个值，这个值的定义了本网络的IP地址前缀的位数，对于`192.168.0.1/24`这个网络，他定义的是本网络内IP的前24位与`192.168.0.1`的前24位相同，对于`216.239.32.0/19`这个网络，标识了本网络前19位与`216.239.32.0`的前19位相同。这里的位数是指二进制位数。  
目前流行使用这个值来代替掩码，以避免子网码导致的问题，他们之间的简单转换算法如下:   

(2<sup>CIDR</sup> - 1) << (32-CIDR)

php计算如下:

```
(pow(2,$CIDR)-1) << (32 - $CIDR) ;
echo long2ip((pow(2,24)-1) << (32-24)); //255.255.255.0
echo long2ip((pow(2,19)-1) << (32-19)); //255.255.224.0
```