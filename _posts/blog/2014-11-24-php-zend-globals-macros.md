---
layout: blog_contents
title: PHP Zend 引擎全局宏介绍
categories: blog
---

Zend引擎的全局宏定义在`/php-src/Zend/zend_globals_macros.h` 文件中，这里主要记录`EG`宏与`CG`宏，宏关联的数据结构定义在`/php-src/Zend/zend_globals.h`文件中.
  
###`CG`宏  
本宏关联的数据结构定义为`_zend_compiler_globals`. 宏中包含了以下主要数据，这些数据都是在Zend解释PHP代码过程中定义 : 
 
1. function_table  定义的函数的符号表 
2. class_table  定义的类的符号表  
3. filenames_table 文件名列表，是PHP Zend引擎打开的文件
4. auto_globals 自动全局变量符号表,这个表存放了超全局变量，比如$_SESSION, $GLOBALS之类的

###`EG`宏  
本宏关联的数据结构定义为`_zend_executor_globals`. 宏中包含了以下主要数据: 

1. included_files 包含的文件列表  
2. function_table 执行过程中定义的函数符号表  
3. class_table  定义的类的符号表  
4. zend_constants 定义的常量表
5. ini_directives ini文件定义信息
6. modified_ini_directives 更新后的ini定义信息
7. symbol_table 变量符号表

以上只是部分信息，另外还有`LANG_SCNG`与`INI_SCNG`宏

###`TSRMLS_C`宏  
以`TSRMLS`开头的宏都定义在`/php-src/TSRM/TSRM.h`文件中，
其他相关的有： 
`TSRMLS_C`定义为`tsrm_ls`线程存储器  
`TSRMLS_CC`对应了`, TSRMLS_C`可以用于函数传参    
`TSRMLS_D`对应了`void ***tsrm_ls`  
`TSRMLS_DC`对应`, TSRMLS_D`  
`tsrm_ls`在执行`TSRMLS_FETCH`指向了函数`ts_resource_ex`，函数定义在`/php-src/TSRM/TSRM.c`中，启用线程安全后`TSRMLS_FETCH()`将会被首先执行
当未启用`ZTS`即Zend线程安全的时候这些宏被设置为空