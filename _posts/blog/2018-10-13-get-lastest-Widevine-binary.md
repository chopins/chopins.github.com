---
layout: blog_contents
title: 手动获取 Widevine 最新版本方法（Linux, manual download)
categories: blog
---

1. 查看最新版本  

  https://dl.google.com/widevine-cdm/current.txt
  
2. 根据系统架构下载，分别是`ia32`, `x64`两种 

  https://dl.google.com/widevine-cdm/${WIDEVINE_VERSION}-linux-${WIDEVINE_ARCH}.zip   
  其中`${WIDEVINE_VERSION}`替换成版本号，`${WIDEVINE_ARCH}`为系统架构
  
3. 对于 Firefox，需已在配置文件目录创建`gmp-widevinecdm/<VERSION>`文件夹，然后将第二步下载的包文件解压到此文件夹，   
   然后打开`about:config`, 添加修改如下项目
   
   * 增加 `media.gmp-widevinecdm.abi` (string) = `x86_64-gcc3`
   * 增加 `media.gmp-widevinecdm.version` (string) = `<VERSION>`
   * 修改为 `media.gmp-widevinecdm.autoupdate = false`,即关闭自动更新
