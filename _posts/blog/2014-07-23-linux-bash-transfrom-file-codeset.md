---
layout: blog_contents
title: 文件编码转换脚本
categories: blog
---

[脚本下载地址](http://toknot.com/download/transformfile)

用法:
transformfile -t 需要转换为的编码  -f 文件编码  filename
文件名支持 * 匹配， 增加 -r 参数可以递归转换子目录中的文件
例如下面的从 GBK 转换为 utf-8 :
`transformfile -t utf-8 -f gbk *.c`  转换当前目录下以`.c` 为后缀  
 
`transformfile -t utf-8 -f gbk *.java -r` 转换当前目录及子目录下以`.c` 为后缀  

`transformfile -t utf-8 -f gbk test.sh` 转换当前目录下的`test.sh`文件  
