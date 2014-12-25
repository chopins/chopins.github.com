---
layout: blog_contents
title: 网络掩码转换为CIDR算法
categories: blog
---

CIDR是指无类别域间路由，就是指当使用类似`192.168.0.1/24`中的24这个值，目前流行使用这个值来代替掩码，他们之间的转换算法如下:   

(2<sup>32<sup> - 1) - ( 2<sup>32-CIDR<sup> - 1)

php计算如下:

```
(pow(2,32)-1) ^ (pow(2,(32-$CIDR)-1);
echo long2ip((pow(2,32)-1) - (pow(2,(32-24)-1)); //255.255.255.0
echo long2ip((pow(2,32)-1) - (pow(2,(32-19)-1)); //255.255.224.0
```