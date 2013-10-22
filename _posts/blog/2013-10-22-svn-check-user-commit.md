---
layout: blog_contents
title: svn查看指定用户更新记录 
categories: blog
---

命令行执行以下命令,__需要安装有`sed`__： 

+ 查询指定日期到当前日内，指定用户的更新记录  
  `svn log -rhead:{2013-05-20} -v|sed -n '/username/,/-----$/ p'`  
  __2013-05-20__ 为开始日期，__username__ 为指定用户

+ 查询指定用户在某一个版本号以后的更新记录  
  `svn log -rhead:12345 -v|sed -n '/username/,/-----$/ p'`  
  __12345__ 为开始版本号，__username__ 为指定用户

我写了一个脚本来完成这两个功能 [下载](http://toknot.com/download/svn-ext)
