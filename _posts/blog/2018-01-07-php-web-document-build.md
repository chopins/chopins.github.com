---
layout: blog_contents
title: PHP 文档本地 Web 页面构建脚本
categories: blog
---

PHP 文档 WEB 版本全自动创建脚本
[下载地址](http://toknot.com/download/phpdoc)

* 用法

    phpdoc co [路径]  创建文档文件到该路径
    phpdoc up [路径]  更新指定路径的文档
    phpdoc [路径]     更新指定路径的文档

* 特性
    
    默认会创建英文语言版本和中文语言版本，修改`LAN`变量后可替换中文语言版本为其他语言版本
    支持搜索功能
    目前没有notes
    页面体验与`https://php.net`体验一样

* 必须条件
    
    需要安装 rsync,svn,php,phd,sed,wget
    phd 安装见[https://wiki.php.net/doc/phd/install](https://wiki.php.net/doc/phd/install)
    phd 安装时需要将可选包 PhD_PHP 安装上
    php 需要安装 php-xml 扩展，以支持 dom, xml 操作

* PhD 使用问题解决

    `Fatal error: Allowed memory size of -XXX- in -XXXX-/pear/phpdotnet/phd/Package/PHP/Web.php 210` 错误请将该行
    
    ```php 
    $ids[] = array($index["sdesc"], $index["filename"], $index["element"]);
    ```
    替换成

    ```
    $theid = array($index["sdesc"], $index["filename"], $index["element"]);
    array_push($ids, $theid);
    ```

    可以减少该错误出现的次数

    最新版本的文档文件使用 PhD 时存在一些 Notice 信息，可以忽略，不影响使用
