---
layout: blog_contents
title: 网络代理相关PHP脚本
categories: blog
---

Google相关IP获取脚本，命令行执行本脚本，会获取google相关IP并生成BIND服务zone配置文件    
[http://toknot.com/download/getGoogleIp.php](http://toknot.com/download/getGoogleIp.php)  

服务器端代理PHP脚本，放在国外服务器上    
[http://toknot.com/download/down.php](http://toknot.com/download/down.php)  

本地端代理PHP脚本，放在本地   
[http://toknot.com/download/google.php](http://toknot.com/download/google.php)  

以上脚本需要安装的PHP支持openssl，fopen 需要支持http协议

本地代理可以使用nginx + php-fpm 模式来搭建。另外需要搭建一个本地DNS服务,解析需要代理的域名。   
google play 市场下载的域名是`*.c.android.clients.google.com`。