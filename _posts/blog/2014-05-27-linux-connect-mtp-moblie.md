---
layout: blog_contents
title: Linux 连接 MTP 手机
categories: blog
---

比较好的解决方案是安装 `gvfs-mtp`
如果在文件管理器没有自动加载连接的 MTP 设备，可以使用 `lsusb` 查看 USB 设备，然后得到类似如下信息:

`Bus 001 Device 007: ID 1bcf:0007 Google Inc`

然后在文件管理器的地址栏输入 `mtp://[usb:001,007]/`,  第一个数字是Bus 号,第二个数字是设备号.注意设备名称可能不一样

