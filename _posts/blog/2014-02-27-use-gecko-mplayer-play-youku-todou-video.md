---
layout: blog_contents
title: 在 Firefox 中使用 gecko-mplayer 插件播放 youku,tudou,sohu等站的视频
categories: blog
---

由于 Flash 容易崩溃，并且使 CPU 使用率居高不下，所以写了一个脚本，替换视频网站原有的 Flash 视频播放器
本脚本需要按以下步骤来安装：

1. 安装有 gecko-mplayer 插件
2. 安装 Greasemonkey 扩展
3. 下载并安装脚本，[脚本下载地址](http://toknot.com/download/Flash2Mplayer.js)
4. 刷新页面即可

脚本特性及注意：

1. 由于 gecko-mplayer 只有 Linux 版本，所以本脚本默认状态只能运行在 Linux 下，只有修改后能支持其他系统
2. 脚本默认播放视频的超清资源
3. 如果浏览器原生支持播放当前视频/音频资源，会使用 HTML5 的 video/audio 标签播放视频/音频，如果不支持会使用 gecko-mplayer 播放
4. 脚本目前支持 youku, tudou, sohu, douban.fm
5. 由于替换了播放器，所以视频网站的广告也会被同时去掉
6. gecko-mplayer 需要的缓存设置通过 gnome-mplayer 来设置。
7. gecok-mplayer 默认缓存比例是 20%， 所以需要设置缓存为 1024KB 才能实现即时播放，注意不能少于 1024KB，否则这个值会被忽略

最近写了一改使用 flowplayer 替换youku视频播发器的脚本
[下载地址](http://toknot.com/download/MyFlashPlayer.js)
这个脚本是使用了第三方flash播发器来替换youku原有的视频播放器，主要作用其实就是去广告
