---
layout: blog_contents
title: Linux NetBeans IDE 8.2 字体显示问题和界面中文方块问题解决
categories: blog
---

__字体锯齿问题解决方法__  

打开配置文件`YOUR_INSTALL_PATH/netbeans-8.2/etc/netbeans.conf`，找到`netbeans_default_option=`配置项，在其末尾添加`-J-Dsun.java2d.noddraw=true -J-Dawt.useSystemAAFontSettings=on`

__界面部分中文出现方块问题解决方法__

安装`wqy-zenhei-fonts`(文泉译正黑字体)字体即可解决。该字体为`ttc`格式。

