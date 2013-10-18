---
layout: blog_contents
title: Firefox 鼠标相关功能设置
---

firefox nightly 版本在浏览器选项中去掉了 __禁用或替换上下文菜单__ 修改功能，
只能在 `about:config` 中修改 `dom.event.contextmenu.enabled` 项来实现

firefox 禁止页面 __屏蔽鼠标选择与鼠标导航__ 脚本

    // ==UserScript==
    // @name        PageProhibitSelect
    // @namespace   disable.page.prohibit.user.select
    // @include     http://*/*
    // @version     1
    // @grant       none
    // ==/UserScript==

    document.body.onselectstart = true;
    document.body.style = '-moz-user-select: text !important';
