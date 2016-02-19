---
layout: blog_contents
title: 解决Linux Flash Player 磁盘IO过高问题 
categories: blog
---

解决linux flash player io 问题  

根据情况找到以下目录：  

  * `~/.macromedia` 目录,此为官方版本时
  * `~/.config/freshwrapper-data/` 目录，此为使用freshplayerplugin时
  * `~/.config/google-chrome/Default/Pepper Data/Shockwave Flash/`目录，此为google chrome
  
将这些目录软链到/dev/shm下

另外对于Firefox同样可以通过修改缓存到内存实现加速，方法是将在`about:config`中添加或修改`browser.cache.disk.parent_directory`项目，将其值设置成`/dev/shm/firefox-cache`即可。此方法并影响Firefox的历史记录，崩溃恢复等功能
