---
layout: blog_contents
title: tcp_timestamps 导致NAT网络无法连接服务器的问题
categories: blog
---

很长一段时间，我的Linux都无法通过公司网络访问公司网站，还发现Andriod系统也无法访问。

经过查询，发现是 Linux net.ipv4.tcp\_timestamps 设置导致的问题。
我查询到的原因是有人在linux 2.6.32内核源码中发现tcp_\tw\_recycle/tcp\_timestamps都开启的条件下，60s内同一源ip主机的socket connect请求中的timestamp必须是递增的。

>主机client1和client2通过NAT网关（1个ip地址）访问serverN，由于timestamp时间为系统启动到当前的时间，因此，client1和client2的timestamp不相同；根据上述syn包处理源码，在tcp\_tw\_recycle和tcp\_timestamps同时开启的条件下，timestamp大的主机访问serverN成功，而timestmap小的主机访问失败

因此在不修改服务器的配置下，我关闭了我本地的`net.ipv4.tcp_timestamps`发现访问正常了。

如果需要修改服务器：
> 个人建议关闭tcp\_tw\_recycle选项，而不是timestamp；因为 在tcp timestamp关闭的条件下，开启tcp_tw_recycle是不起作用的；而tcp timestamp可以独立开启并起作用。

按照上面的修改，会关闭TIME_WAIT的快速回收功能，时间变成了60s