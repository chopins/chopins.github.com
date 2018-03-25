---
layout: blog_contents
title: Fedora 环境下配置fcitx输入法
categories: blog
---

fcitx输入法并非 GNOME 默认的输入法，所以需要手动安装、配置

1、使用 Wayland 时，在 `/etc/environment` 添加如下内容:

```
export GTK_IM_MODULE=fcitx
export QT_IM_MODULE=fcitx
export XMODIFIERS=@im=fcitx

```
重新登录或重启系统即可

2、在gnome-shell 最新版本使用 xorg 时，修改 `/etc/X11/xinit/xinput.d/fcitx.conf`。将`XIM`，`GTK_IM_MODULE`，`QT_IM_MODULE`的值修改为`fcitx`，然后将fcitx添加到自动启动中（可使用`gnome-tweak-tool`添加`）重新登录或重启系统。

    如果输入法已经启动，但是无法输入，需要在`/etc/X11/xinit/xinput.d/fcitx.conf`文件中添加`DISABLE_IMSETTINGS=true`，以禁止`imsettings`进行输入法设置。
    
    因为fcitx是依赖于`imsettings`的，所以安装`fcitx`后，`imsettings`也会被安装。而`imsettings`在安装时会向`X11`添加`xinput`初始化输入法脚本，该脚本会在`xorg`启动时被运行，从而进行输入法相关设置（`imsettings-switch`会被启动）。
    
    但是因为一些原因输入法并不会被正确设置。另外因为`imsettings`的`xinput`脚本会进行环境变量清理，所以用户添加的`GTK_IM_MODULE`将会失效。从而导致输入法无法使用。

    另一个为经过测试的方法是：在`/etc/X11/xinit/xinput.d/fcitx.conf`添加`IMSETTINGS_MODULE=fcitx`,以使`imsettings-switch`能自动设置输入法。
