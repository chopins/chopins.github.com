---
layout: blog_contents
title: 获取一个已经运行程序的运行时间
categories: blog
---

获取当前已经运行的程序的运行时间命令如下:

`ps -eo pid,lstart,etime|grep 16705`

最后的数字是进程ID，结果类似:

`16705 Tue Aug 26 12:05:22 2014    02:51:33`

分别表示的是:进程ID，开始时间，已经运行的时间