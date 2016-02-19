---
layout: blog_contents
title: 解决Linux Flash Player 磁盘IO过高问题 
categories: blog
---

解决linux flash player io 问题  

根据情况找到以下目录：  

  * `~/.macromedia` 目录,此为官方版本时
  * `~/.config/freshwrapper-data/` 目录，此为使用freshplayerplugin时
  * `~/.config/google-chrome/Default/Pepper Data/Shockwave Flash/目录，此为google chrome
  
这这些目录软链到/dev/shm下
