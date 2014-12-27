---
layout: blog_contents
title: Named 服务器简单配置
categories: blog
---


由于墙的原因，在天朝在默认状态下访问无法访问google服务。但是由于google IP块较多，GFW 并不能封锁所有IP，因此网上主流推荐在本地绑定IP的方法来解决这个问题。但是由于google域名太多，导致host维护难度增加。所以决定配置一个本地DNS解析服务器。   
系统是Fedora 20, 所以直接使用 yum 安装，运行`#yum install named`即可。    
安装完毕后，开始配置。  
首先是`/etc/name.conf`, 下面是配置文件   

```
    options {
        //如果需要向其他机器提供DNS服务，需要这么配置
        listen-on port 53 { any; }; 
        listen-on-v6 port 53 { ::1; };
        directory   "/var/named";
        dump-file   "/var/named/data/cache_dump.db";
        statistics-file "/var/named/data/named_stats.txt";
        memstatistics-file "/var/named/data/named_mem_stats.txt";
        //如果需要向其他机器提供DNS服务，需要这么配置
        allow-query     { any; };
        //是否递归查询
        recursion yes;
        dnssec-enable yes;
        dnssec-validation yes;
        dnssec-lookaside auto;
        //是否转发，就是遇到其他域名的时转发给下面列表的DNS服务器来解析
        //下面两项根据情况修改
        forward only;
        forwarders {8.8.8.8;
                    8.8.4.4;
                    209.244.0.4;
                    195.46.39.39;
                    195.46.39.40;
                    208.71.35.137;
                    89.233.43.71;
                    89.104.194.142;
                    74.82.42.42;
                    109.69.8.51;
                    };
        /* Path to ISC DLV key */
        bindkeys-file "/etc/named.iscdlv.key";
        managed-keys-directory "/var/named/dynamic";
        pid-file "/run/named/named.pid";
        session-keyfile "/run/named/session.key";
    };
    logging {
            channel default_debug {
                    file "data/named.run";
                    severity dynamic;
            };
    };
    zone "." IN {
        type hint;
        file "named.ca";
    };
    //这一块是加入需要解析的google域名
    zone "google.com" IN {
        type master;
        //下面是域名相关记录配置文件，实际地址位于/var/named/google.com.zone
        //配置见下面
        file "google.com.zone";
    };
    include "/etc/named.rfc1912.zones";
    include "/etc/named.root.key";
```

上面配置好后，在`/var/named/google.com.zone`文件，下面是样例配置

```
$TTL 3600                                                                                                                              
$ORIGIN google.com.
@   IN SOA google.com. root.google.com. ( 201411121 3600 36000 604800 3600 ) 
@   IN NS @ 
;
@ IN A 173.194.127.195
* IN A 173.194.127.194
* IN A 173.194.127.200
```

上面配置文件每行解释如下：  

    1. 有效期，由于是本地自己解析，可以不用管  
    2. 空行  
    3. 配置域名，注意后面域名结尾有一个点   
    4. SOA 配置，分别是域名，邮箱地址，5个配置数字， 注意空格与行尾空格  
    5. nameserver 主机名，这里使用了@，就是google.com   
    6. 注释行   
    7. `@`的A记录，就是`google.com这`个域名的A记录  
    8. `*`的A记录，就是通配`*.google.com`的所有域名的A记录，比如`www.google.com`,`mail.google.com`等域名  
    9. `*`的另一个A记录，与第8行的记录随机返回  

配置好后，需要注意检查文件文件权限，把文件改成`root`用户，`named`组，文件权限改成`0640`  
配置完毕后启动`named`服务，在root用户下面执行`systemctl start named`  
这样就在本地可以把DNS服务器改成`127.0.0.1`了，如果需要给其他机器提供服务，需要打开防火墙的相关配置

下载[getGoogleIp.php](http://toknot.com/download/getGoogleIp.php)获取google可用IP信息，本脚本需要系统安装有PHP并且带有PCNTL与POSIX扩展，脚本将自动生成named zone配置。   
python版本获取Google可用IP信息[getGoogleIp.py](http://toknot.com/download/getGoogleIp.py)