Toknot.com网站页面

systemd 修改默认启动级别  
 * 修改为3运行级别  
  `ln -sf /lib/systemd/system/multi-user.target /etc/systemd/system/default.target` 
 * 修改为5运行级别  
  `ln -sf /lib/systemd/system/graphical.target /etc/systemd/system/default.target`

Linux 先切换用户时，需要执行`xhost + ` 命令来允许切换后的用户使用当前用的显示服务（Xorg服务或Xwayland服务），可解决 `can't open display` 错误 

Google Translate Greasemonkey Script for Firefox (API is https://translate.google.cn) 划词翻译脚本 
[Greasemonkey Script](https://toknot.com/download/google_translate_for_firefox.js)

AOL reader 字体及收卷脚本
[aol-reader-font-color.js](https://github.com/chopins/chopins.github.com/blob/master/aol-reader-font-color.js)

PHP 扩展自动编译脚本
[phpcim](https://github.com/chopins/chopins.github.com/blob/master/phpicm)

PHP Shell 脚本
[phpsh](https://github.com/chopins/chopins.github.com/blob/master/phpsh)

SVN 查询脚本, 根据用户名查询指定日期或版本号以后该用户更新日志
[svn-ext.sh](https://github.com/chopins/chopins.github.com/blob/master/svn-ext.sh)

PHP HTML简单爬虫
[php-fetchContent.php](http://toknot.com/download/fetchContent.php)

firefox nightly 版本在浏览器选项中去掉了“禁用或替换上下文菜单”修改功能，
只能在about:config中修改dom.event.contextmenu.enabled项来实现

PHP 中文匹配正则,UTF-8编码 `/[\x{4e00}-\x{9fa5}]+/u`

终端提示字符：
```bash
PS1=" \$? \[\e[44m\] \h \[\e[0m\e[45m\] \A \[\e[m\e[42m\] \u \[\e[0m\e[46m\] \w \[\e[0m\] \$ "
```

根据文件innode删除文件:
```bash
ls -il  #查看目录下文件的innode号
find ./ -inum 1234 -delete  #删除innode号为1234的文件
```


firefox 禁止页面 “屏蔽鼠标选择与鼠标导航” 脚本
```javascript
// ==UserScript==
// @name        PageProhibitSelect
// @namespace   disable.page.prohibit.user.select
// @include     http://*/*
// @version     1
// @grant       none
// ==/UserScript==

document.body.onselectstart = true;
var st = document.createElement('style');
st.type = 'text/css';
st.innerHTML = " * {-moz-user-select: text !important;}";
document.getElementsByTagName('HEAD').item(0).appendChild(st);
```
