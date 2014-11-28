---
layout: blog_contents
title: PHP DNS相关函数的问题
categories: blog
---

PHP DNS查询在 linux 等系统上会因为使用的库不一样而存在不同的行为。  
目前按库函数使用的优先级从高到低是`dns_search`，`res_nsearch`，`res_search`,其中主要存在于`res_search`在查询时MX记录时会进行搜索查询，导致MX记录查询结果不符合预期。为了避免这种情况需要在查询域名后加上一个句点`.`号。  
要确定PHP使用的是那种库，可以在PHP编译前的`configure`时的查看类似下面的信息  

```
checking for res_nsearch... no
checking for __res_nsearch... yes
checking for dns_search... no
checking for __dns_search... no
checking for dns_search in -lresolv... no
checking for __dns_search in -lresolv... no
```
上面的检查结果是`dns_search`，`res_nsearch`都不存在，下面的结果是`res_nsearch`存在

```
checking for res_nsearch... no
checking for __res_nsearch... no
checking for res_nsearch in -lresolv... no
checking for __res_nsearch in -lresolv... yes
```
根据上面的结果PHP将使用`res_nsearch`相关函数。在查询MX记录时只需要输入域名即可。例如`getmxrr('126.com',$mix)`

如果使用了`res_search`相关函数，在查询MX记录是在域名后增加句点`.`号。  
例如域名`126.comk`这么一个错误域名，当使用`getmxrr('126.comk',$mix)`来查询时，会返回`mail.b-io.co`等类似错误记录。但是当在域名后面增加一个句点`.`后`getmxrr('126.comk.',$mix)`查询就正常了。  
这个问题存在于`dns_get_record`与`getmxrr`两个函数在获取域名MX记录时。 

`res_search`与`res_nsearch`一般位于相同包中