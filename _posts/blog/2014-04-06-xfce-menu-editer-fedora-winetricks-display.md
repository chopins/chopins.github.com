---
layout: blog_contents
title: Xfce应用程序菜单编辑工具及fedora 20 winetricks 显示问题解决
categories: blog
---

1. Xfce应用程序菜单编辑工具 程序名字为 alacarte

2. fedora 20 winetricks 显示问题解决
    winetricks 列表区域显示较小问题，修改文件 /usr/share/zenity/zenity.ui
    找到（1020行）：

```xml
    <object class="GtkScrolledWindow" id="zenity_tree_window">
```

    在这个块内的 

```xml
<property name="shadow_type">in</property> 
```

后面加上

```xml
    <property name="expand">True</property>
```