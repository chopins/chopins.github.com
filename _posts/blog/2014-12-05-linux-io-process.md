---
layout: blog_contents
title: Linux查看IO最高的进程
categories: blog
---

首先可以先停止系统日志:
`/etc/init.d/rsyslog stop`

然后开启block_dump：

```
echo 1 > /proc/sys/vm/block_dump
dmesg | egrep “READ|WRITE|dirtied” | egrep -o ‘([a-zA-Z]*)’ | sort | uniq -c | sort -rn | head 
```

问题解决后，关闭`block_dump`

```
echo 0 > /proc/sys/vm/block_dump
/etc/init.d/rsyslog start
```