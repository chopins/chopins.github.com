---
layout: blog_contents
title: Nginx 模块说明
categories: blog
---

###rtsig module

激活nginx的rtsig事件模块，这个一般在编译时会自动检查一个系统最适合的事件模型，其他的还有 select, poll 事件模型

###ngx\_http\_ssl\_module

本模块提供对HTTPS必要的支持，如果你的服务器有SSL需求需要在编译时开启本模块

###ngx\_http\_spdy\_module

本模块将让nginx提供SPDY协议支持，SPDY将作为下一代HTTP协议的基础，需要在编译时手动开启

###ngx\_http\_realip\_module
本模块允许将客户端地址设置为客户端请求头中指定字段值

###ngx\_http\_addition\_module

本模块是一个过滤器，它能在响应数据的头或尾添加一文本。

###ngx\_http\_xslt\_module

本模块能使用 XSLT 样式表将 XML 响应数据进行转换。

###ngx\_http\_image\_filter\_module

本模块是一个用来转换JPEG, GIF, 和PNG 图片格式的过滤模块

###ngx\_http\_geoip\_module

本模块允许你依据客户端IP来使用MaxMind数据库，然后定位当期访问用户的地址信息

###ngx\_http\_sub\_module

这是一个过滤模块，它允许指定一个字符串来替换另一个来改变响应

###ngx\_http\_dav\_module

本模块的提供了通过WebDAV协议来实现文件自动化管理的功能，这个模块主要通过HTTP和WebDAV的 PUT, DELETE, MKCOL, COPY, 和 MOVE方法来进行处理。

###ngx\_http\_flv\_module

本模块提供了FLV视频文件的服务器端伪流媒体支持

###ngx\_http\_mp4\_module

本模块提供了 mp4 文件的服务器端伪流媒体支持，支持的文件类型有 .mp4, .m4v, 或者 .m4a等文件扩展

###ngx\_http\_gunzip\_module

这是一个过滤模块，它会在客户端不支持"gzip"编码模式时解压缩应头中含有“Content-Encoding: gzip”的响应数据，这个模块在想使用压缩以减少存储与I/O开销时十分有用

###ngx\_http\_gzip\_static\_module

这个模块允许发送预压缩的以".gz"为文件扩展名的文件来代替普通文件

###ngx\_http\_auth\_request\_module

这个模块实现了一个基于子请求结果的客户端验证。如果子请求返回一个2XX的响应代码，这次访问将被允许，如果返回401或403，本次访问将被禁止并显示响应相应的错误代码。如何其他的响应代码将会被以错误代码形式返回

###ngx\_http\_random\_index\_module

这个模块对于以斜线字符"/"结尾的请求将会随机在服务器的目录中选择一个文件作为index文件

###ngx\_http\_secure\_link\_module

这个模块被用来检查验证一个链接的请求，保护资源不被未授权访问并且限制一个链接的有效期。

###ngx\_http\_degradation\_module

允许在内存不足的情况下返回204或444码

###ngx\_http\_stub\_status\_module
获取nginx自上次启动以来的工作状态

###ngx\_http\_charset\_module
重新编码web页面，但只能是一个方向–服务器端到客户端，并且只有一个字节的编码可以被重新编码

###ngx\_http\_gzip\_module
该模块将会使用"gzip"模式压缩响应数据，它常常能帮助减少一半甚至更多的传输尺寸

###ngx\_http\_ssi\_module
模块提供了一个在输入端处理处理服务器包含文件（SSI）的过滤器，目前支持SSI命令的列表是不完整的

###ngx\_http\_userid\_module
该模块用来处理用来确定客户端后续请求的cookies

###ngx\_http\_access\_module
该模块提供了一个简单的基于主机的访问控制。允许/拒绝基于ip地址

###ngx\_http\_auth\_basic\_module
该模块是可以使用用户名和密码基于http基本认证方法来保护你的站点或其部分内容

###ngx\_http\_autoindex\_module
该模块用于自动生成目录列表，只在ngx\_http\_index\_module模块未找到索引文件时发出请求。

###ngx\_http\_geo\_module
根据客户端IP来创建一些变量

###ngx\_http\_map\_module
根据其他变量的值来创建一些变量

###ngx\_http\_split\_clients\_module
该模块用来基于某些条件划分用户。条件如：ip地址、报头、cookies等等

###ngx\_http\_referer\_module
该模块用来过滤请求，拒绝报头中Referer值不正确的请求

###ngx\_http\_rewrite\_module
该模块允许使用正则表达式改变URI，并且根据变量来转向以及选择配置。如果在server级别设置该选项，那么他们将在 location之前生效。如果在location还有更进一步的重写规则，location部分的规则依然会被执行。如果这个URI重写是因为location部分的规则造成的，那么 location部分会再次被执行作为新的URI。 这个循环会执行10次，然后Nginx会返回一个500错误。）

###ngx\_http\_proxy\_module
有关代理服务器

###ngx\_http\_fastcgi\_module
该模块允许Nginx 与FastCGI 进程交互，并通过传递参数来控制FastCGI 进程工作。 ）FastCGI一个常驻型的公共网关接口

###ngx\_http\_uwsgi\_module
该模块用来支持uwsgi协议，uWSGI服务器相关

###ngx\_http\_scgi\_module
该模块用来启用SCGI协议支持，SCGI协议是CGI协议的替代。它是一种应用程序与HTTP服务接口标准。它有些像FastCGI但他的设计 更容易实现。

###ngx\_http\_memcached\_module
该模块用来提供简单的缓存，以提高系统效率

###ngx\_http\_limit\_conn\_module
该模块可以针对条件，进行会话的并发连接数控制

###ngx\_http\_limit\_req\_module
该模块允许你对于一个地址进行请求数量的限制用一个给定的session或一个特定的事件

###ngx\_http\_empty\_gif\_module
该模块在内存中常驻了一个1*1的透明GIF图像，可以被非常快速的调用

###ngx\_http\_browser\_module
该模块用来创建依赖于请求报头的值。如果浏览器为modern ，则$modern\_browser等于modern\_browser\_value指令分配的值；如 果浏览器为old，则$ancient\_browser等于 ancient\_browser\_value指令分配的值；如果浏览器为 MSIE中的任意版本，则 $msie等于1

###ngx\_http\_upstream\_ip\_hash\_module
该模块用于简单的负载均衡




