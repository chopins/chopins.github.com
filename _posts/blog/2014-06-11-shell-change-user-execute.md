---
layout: blog_contents
title: Shell 脚本切换用户
categories: blog
---

如果脚本内部需要切换其他用户执行某些命令可以如下操作 

```shell
#!/bin/bash
su <<<EOF
123456
#the upside is root password
#below is shell command
whoami
#other command
exit
EOF
```

如果是root用户，切户用户就可以降密码行去掉