Toknot.com网站页面

AOL reader 字体及收卷脚本
[aol-reader-font-color.js](https://github.com/chopins/chopins.github.com/blob/master/aol-reader-font-color.js)

PHP 扩展自动编译脚本
[phpcim](https://github.com/chopins/chopins.github.com/blob/master/phpicm)

PHP Shell 脚本
[phpsh](https://github.com/chopins/chopins.github.com/blob/master/phpsh)

SVN 查询脚本, 根据用户名查询指定日期或版本号以后该用户更新日志
[svn-ext.sh](https://github.com/chopins/chopins.github.com/blob/master/svn-ext.sh)

firefox nightly 版本在浏览器选项中去掉了“禁用或替换上下文菜单”修改功能，
只能在about:config中修改dom.event.contextmenu.enabled项来实现

终端提示字符：
```bash
PS1="\`if [ \$? == 0 ];then echo \#;else echo \e[41m \# \e[0m;fi\`\e[44m\h \e[0m\e[45m \A \e[m\e[42m \u \e[0m\e[46m \w \e[0m \$ "
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
document.body.style = '-moz-user-select: text !important';
```
