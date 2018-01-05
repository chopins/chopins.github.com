---
layout: blog_contents
title: grub2 引导 dos 镜像方法
categories: blog
---

GRUB2 启动 U盘 DOS 镜像的方法
1. Linux系统安装`syslinux`

2. 将`memdisk`文件复制 U 盘，Fedora 系统`syslinux`安装后`memdisk`一般位于`/usr/share/syslinux/memdisk`，其他系统可能位于`/usr/lib/syslinux/memdisk` 

3. 下载`FreeDOS`或者其他 DOS 镜像，[Free DOS](http://www.freedos.org/download/) 下载 USB "Lite" installer 版本，解压下载的文件，将里面的，`img`文件复制到 U 盘

4、重启系统，进入 GRUB2 命令行模式，就是在显示`系统菜单项列表`时按`c`键 

5、执行命令`search --file /dos/memdisk --set=root`, 这里表示`memdisk`存储在U盘的`dos`文件夹内 

6、执行`linux16 /dos/memdisk` 

7、执行`initrd16 /dos/FD12LITE.img`, 这里需要改成下载的 DOS 的`img`镜像文件名字 

8、执行 boot 

9、如果是 FreeDOS，会有安装提示，无需安装，这里选择`Return DOS`即可，进入 DOS 

