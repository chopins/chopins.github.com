---
layout: blog_contents
title: Macbook Air 安装 Fedora 20 问题处理
categories: blog
---

我的安装步骤是先在Macbook Air上安装rEFIt,然后通过Fedora的Live USB来安装Fedora。安装过程与普通安装Fedora一样

这里主要记录遇到的两个问题的处理方法：  

1. 无线网卡无法使用的问题： 解决办法是通过rpmfusion源安装broadcom-wl包及相关依赖包，主要包括两个kmod-wl包，可以先使用USB转接网卡进行在线安装，离线的需要把这三个包下载后在进行安装

2. 键盘问题，～建输入不正确，以及功能键无法使用，详细解决办法见[https://wiki.archlinux.org/index.php/Apple_Keyboard](https://wiki.archlinux.org/index.php/Apple_Keyboard),主要是执行下面的设置：  

```
# echo 2 > /sys/module/hid_apple/parameters/fnmode
# echo 0 > /sys/module/hid_apple/parameters/iso_layout
```
