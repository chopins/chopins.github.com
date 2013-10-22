---
layout: blog_contents
title: 给下载的多页PHP HTML文档增加搜索功能
---

php 搜索程序[下载](http://toknot.com/php-document-search.php)
本脚本支持函数名，类名及方法名搜索

+ 首先配置服务器重写，nginx 类似这样 `rewrite ^(.+)/?$ /index.php;` 其中`index.php`为本程序文件名
+ 搜索类型按先后顺序为下面所列，都不区分大小写，URL类似这样 `http://localhost/serach_kery`, `search_key`为搜索关键字
    1. http://localhost/_SERVER 将访问 reserved.variables.server.html 页，就是 `$_SERVER` 说明页
    2. 函数名（完全匹配函数名），直接进入该页
    3. 实际文件名（完全匹配文件名，不包括扩展），直接进入该页
    4. 类名（完全匹配类名），直接进入该页
    5. PHP扩展名（完全匹配扩展名），直接进入该页
    6. 扩展类型名（完全匹配扩展类型名），直接进入该页
    7. 搜匹配函数名与类名，得到搜索列表

下载页面样式文件(http://static.php.net/www.php.net/styles/site.css)
批量给下载的多页HMTL文档增加样式命令为
`sed -i "6i<link href=\"site.css\" type=\"text/css\" rel=\"stylesheet\">" *.html`，
其中的数字 __6__ 为插入行数