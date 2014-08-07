---
layout: blog_contents
title: Linux 下一个IO随机测试脚本
categories: blog
---

本脚本会在当期用户的`/home/'whoami'`目录,`/var/tmp`目录，以及`/tmp`目录创建一些随机名字的文件，并用随机字符串进行写入

每个文件在大于`2024000`字节后会被读出，然后被删除，并且会并发地进行新的写入操作

脚本能够处理 Ctl+C 的 INT 与 kill PID 的 TERM 信号，收到这两种信号，脚本会清理创建的临时读写文件

脚本的日志文件位于`/home/'whoami'/iotest.log`

脚本需要系统的 `coreutils` 版本大于 `7.5`

本脚本在连续运行2天后会自动停止。

下载地址[http://toknot.com/download/iocheck](http://toknot.com/download/iocheck)