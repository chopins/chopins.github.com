---
layout: blog_contents
title: Shell 脚本切换用户
categories: blog
---


如果脚本内部需要切换其他用户执行某些命令可以如下操作: 

```
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
密码行必须独立在一行
如果不想保存密码在脚本中，可以如下操作: 

```
#!/bin/bash
read -s -p "Enter Password:" PASS
su <<<EOF
$PASS
whoami
#other command
exit
EOF
```

脚本会提示要求输入密码，然后执行效果与第一个一样，密码同样必须单独在一行
#注意：上面的定向符号`<<<EOF`,根据情况可能需要修改为`<<EOF`