---
layout: blog_contents
title: 使用vim时，误按键导致键盘无响应或锁定、僵死问题
categories: blog
---

通常在使用 vim 时误按`Ctrl+S`后，会导致键盘无响应，而切换出vim界面或所在终端，键盘使用正常。 

原因是该快捷键为`tty`模式下的`Scroll Lock` 锁定快捷键。此时在tty模式下，键盘上的`Scroll Lock`灯会亮，并且会拒绝输入。

在桌面环境的终端下运行的 vim 同样会响应此键，拒绝输入。

此时，要解除锁定，请按`Ctrl+Q`
