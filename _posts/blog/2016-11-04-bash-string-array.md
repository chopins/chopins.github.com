---
layout: blog_contents
title: Bash 脚本字符串与数组相关
categories: blog
---

###字符串分割成数组
使用bash的`read`命令分割字符串方法
`IFS='.' read -r -a STR <<< 'this.needle.split.string'`

分割后得到`STR`数组:`(this needle split string)`  
`IFS`定义的是分割符号  
`${STR[@]`分割后的数组

###数组常用方法

定义方法:`ARR1=(foo1 foo2 foo3 foo4 foo5)`,`ARR2=(1 2 3 4 5)`
数组个数：`${#ARR1[@]}`
数组输出全部：`${ARR1[@]}`
###数组元素访问
获取数组一部分：`${ARR1[@]:N1:N2}`, 其中`N1`为开始索引，从0开始,返回值包括该元素,`N1`为获取长度,如果长度省略，将获取后续所有　　
以下为例子：　

* `${ARR1[@]:0:1}`　为　`(foo1)` 
* `(foo1 foo2)`是`${ARR1[@]:0:2}`  
* `(foo2 foo3 foo4 foo5)`为`${ARR1[@]:1}`  
* `(foo3 foo4)` 为　`${ARR1[@]:2:2}` 

###数组循环

```
for item in ${ARR[@]};do
    echo $item
do
```



