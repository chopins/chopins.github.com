---
layout: blog_contents
title: 无expect登录ssh服务器脚本
categories: blog
---

本脚本不需要 expect 可以完成简单认证并登录 ssh 服务器，

脚本原理是利用 ssh 的 SSH_ASKPASS 环境变量来实现密码认证,脚本代码如下:

```shell
    #!/bin/bash
    tf="/tmp/sshconnect"
    trap 'rm -rf $tf;wait;exit' INT 
    trap 'rm -rf $tf;wait;exit' TERM
    nt=`date +%s`
    echopass=0

    if [ -f $tf ];then
        ftime=`stat -c %X $tf`
        ((vt=$nt-$ftime))
        if [ $vt -gt 1 ];then
            rm -rf $tf 
            store=''
        else
            store=`cat $tf`
        fi  

    else
        store=''
    fi

    if [ -z $store ];then
        if [ $# -eq 0 ];then
            echo 'no server given'
            exit 500 
        else
            h=$1
        fi  
    else
        h=$store
        echopass=1
    fi

    ##################################
    # 以下增加服务器登录信息
    #
    if [ $h == 'server_name1' ];then
        host='user1@server_address1'
        pass='password1'
    elif [ $h == 'server_name2' ];then
        host='user2@server_address2'
        pass='password2'
    else
        echo 'given server name invaild'
        exit 500
    fi

    #################################
    if [ $echopass == 1 ];then
        echo $pass
        rm -rf $tf
        exit
    fi
    echo "$h" > $tf
    setsid env SSH_ASKPASS="$selffile" DISPLAY='none:0' ssh $host 
    rm -rf $tf

```