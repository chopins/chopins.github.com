---
layout: blog_contents
title: MacBook Air 上安装Fedora 21问题解决
categories: blog
---

解决办法见下面两个页面中介绍的解决方法：  
[http://mattoncloud.org/2014/02/05/fedora-20-on-a-macbook-air/](http://mattoncloud.org/2014/02/05/fedora-20-on-a-macbook-air/)      
[https://wiki.archlinux.org/index.php/MacBook#MacBook_Air](https://wiki.archlinux.org/index.php/MacBook#MacBook_Air)   

主要解决了显示器在挂起唤醒后无法调节亮度，以及键盘映射错误的问题。  
解决的主要步骤是先安装[https://github.com/patjak/mba6x_bl](https://github.com/patjak/mba6x_bl)提供的驱动，
然后下载[https://github.com/matthicksj/mba-fixes/tree/master/mba-fixes](https://github.com/matthicksj/mba-fixes/tree/master/mba-fixes)
页面中的systemd serivce配置文件与rules文件  
将serivce文件其复制到`/usr/lib/systemd/system`目录下，将rules文件复制到/usr/lib/udev/rules.d目录下，
然后启用`mapping_fix.service`与`wakeup_fix.service`服务，重启系统即可。

需要注意的是mba6x_bl是内核模块驱动，所以内核更新后需要再次重新编译。
