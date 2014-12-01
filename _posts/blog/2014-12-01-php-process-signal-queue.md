---
layout: blog_contents
title: 简单的PHP多进程信号队列处理
categories: blog
---

PHP本身有一个队列扩展。这里说的是利用系统信号来产生的简单的信号队列。 
在多进程处理的时候，需要控制子进程的数量,通常会使用进程间通信来解决。这里提供一个简单的方案：就是利用PHP数组与信号处理器来实现。信号处理器的主要问题是当不同进程的信号同时达到时，在PHP中是无法互斥计数统计的，所以这里需要利用PHP的数组来达到区分的目的.代码例子如下:

```php
declare(ticks = 1);
$exit_child = array();

function childexit($signo) {
    global $exit_child;
    pcntl_wait($status);
    $key = md5(microtime() . mt_rand(0, 1000000));//唯一key
    $exit_child[$key] = $status;
}
pcntl_signal(SIGCHLD, 'childexit');
pcntl_signal(SIGCLD, 'childexit');
echo count($exit_child); //信号达到数量
```

上面是的代码展示的实现原理，就是利用数组key的不同实现信号的区分。当然由于信号处理问题，这种方法并不是可靠的。