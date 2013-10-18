---
layout: blog_contents
title: Firefox 鼠标相关功能设置
---
firefox nightly 版本在浏览器选项中去掉了“禁用或替换上下文菜单”修改功能，
只能年在about:config中修改dom.event.contextmenu.enabled项来实现

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