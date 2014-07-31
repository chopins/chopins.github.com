---
layout: blog_contents
title: wineconsole 运行字体错误解决方法
categories: blog
---

如果运行wine的 wineconsole 出现如下错误: 

```
err:wineconsole:WCUSER_SetFont wrong font
err:wineconsole:WCUSER_SetFont wrong font
err:wineconsole:WCUSER_SetFont wrong font
err:wineconsole:WCUSER_SetFont wrong font
err:wineconsole:WCUSER_SetFont wrong font
err:wineconsole:WCUSER_SetFont wrong font
err:wineconsole:WCUSER_SetFont wrong font
err:wineconsole:WCUSER_SetFont wrong font
err:wineconsole:WCUSER_SetFont wrong font
err:wineconsole:WINECON_Fatal Couldn't find a decent font, aborting
```

只需要修改注册表: `HKEY_LOCAL_MACHINE/Software/Microsoft/Windows NT/CurrentVersion/FontSubstitutes`
的 `MS Shell Dlg` 和 `MS Shell Dlg2` 的值数据为可用字体即可,复制 `HKEY_LOCAL_MACHINE/Software/Microsoft/Windows NT/CurrentVersion/Font` 下面的一改字体名字到这里即可

Battle.net 的Agent.exe 也会因为这个字体问题导致启动失败，
并不需要修改 终端的 `LANG` 环境变量
