---
layout: blog_contents
title: 使用PHP获取服务器SSL证书信息
categories: blog
---

使用PHP时，通常不需要自己手动去获取HTTPS服务器的证书信息，因为PHP会自动帮我们进行证书验证。但是当我们需要寻找一些特定的IP时，知道该服务器的证书信息就十分有必要了。  
这里介绍的获取证书的方法需要PHP加载了openssl扩展。
下面是获取该服务器的证书可以验证的域名：

```php

    //使用stream函数获取证书资源

    //参数的技术信息见http://cn2.php.net/context.ssl.php
    $g = stream_context_create(array("ssl" => array("capture_peer_cert" => true))); 
    $r = stream_socket_client("ssl://$ip:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $g);
    $cont = stream_context_get_params($r);

    //使用openssl扩展解析证书，这里使用x509证书验证函数
    $cerInfo = openssl_x509_parse($cont["options"]["ssl"]["peer_certificate"]);

    //只输出该证书可以验证的域名信息
    $d = str_replace('DNS:', '', $cerInfo['extensions']['subjectAltName']);

```
