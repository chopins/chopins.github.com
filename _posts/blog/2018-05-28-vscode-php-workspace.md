---
layout: blog_contents
title: VS Code php 工作环境搭建
categories: blog
---

VS Code ( Visual Studio Code ) 是微软开发的一个编辑器，速度比较快，支持安装扩展。比传统IDE更加灵活，对PHP支持也较好，以下为VS Code 的 PHP开发环境搭建信息。

本内容其实就是介绍安装相关扩展和设置，以获得PHP的友好支持。

### 安装扩展

    * Code Outline ，安装执行`ext install patrys.vscode-code-outline`, 获得代码导航器支持，安装后，在左侧导航图标栏会出现`Outline`图标
    * PHP Symbols，安装执行`ext install linyang95.php-symbols` 使 Code Outline 支持PHP的导航
    * PHP Intelephense，安装执行`ext install bmewburn.vscode-intelephense-client`，获得更好的 PHP 代码提示功能。扩展需要在设置中修改PHP的配置：`php.suggest.basic = false`，本扩展比内建提示更加友好，比如能提示常量（2018-5-28日内建的不支持）等
    * phpfmt - PHP formatter，安装执行`ext install kokororin.vscode-phpfmt`,获得PHP格式化支持
    * HTML CSS Support，安装执行`ext install ecmel.vscode-html-css`,以便使PHP文件编辑内嵌HTML代码时获得提醒支持

### 其他扩展

    * Code Runner   代码运行支持
    * ESLine        javascript
    * PHP DocBlocker    代码注释
    * Smarty    smarty 模板

