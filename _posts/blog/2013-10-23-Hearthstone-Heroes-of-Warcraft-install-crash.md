---
layout: blog_contents
title: wine暴雪炉石传说安装崩溃问题解决
categories: blog
---

__wine-1.7.6已经完美支持__

以下是winehq.org上的解决方法:

>The downloader will crash when downloading Blizzard games. In order to fix this, the dbghelp lib  needs to be disabled through winecfg.
> 
>__General Instructions__  
> 
>   Add the dbghelp lib as override  
>   Then set this lib to disable  
> 
>__Detailed Instructions__ *
> 
>   1. Run winecfg. (If you have multiple prefixes, make sure it is the correct prefix) 
>   2. In the 'Libraries' tab, type dbghelp into the 'New override for library' box.
>   3. Click 'Add', then 'Yes' when it asks if you are sure.
>   4. Click on 'dbghelp' in the 'Existing_overrides' list.
>   5. Click 'Edit'.
>   6. Set to 'disabled'.
>   7. Click 'OK', then 'OK'.
>   8. Battle.net Client should now run.
> 
>* andrew m contributed these instructions

[原文地址](http://appdb.winehq.org/objectManager.php?sClass=version&iId=28875&iTestingId=80577)  
就是运行 winecfg, 在函数库中，先添加dbghelp， 然后点击编辑，将这个库停用，即可完成安装，并运行  
intel集成显卡的64位系统运行时出现opengl 错误，需要安装mesa-dri-drivers.i686
