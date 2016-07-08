---
layout: blog_contents
title: php setTimeout 函数
categories: blog
---

__技术依赖__

 * 需要PHP 5.5 以上，支持生成器
 * 需要支持 ticks
 
__实现代码__

```php

<?php
//enable ticks
declare (ticks = 1);

//setTimeout event list
$timeoutQueue = new SplObjectStorage;
register_tick_function(function() {
    global $timeoutQueue;
    foreach ($timeoutQueue as $gen) {
        $v = $gen->current();
        if (is_callable($v)) {
            $v();
            $timeoutQueue->detach($gen);
        }
        $gen->next();
    }
});


/**
 * 
 * @global SplObjectStorage $timeoutQueue
 * @param Generator $gen
 */
function clearTimeout(Generator $gen) {
    global $timeoutQueue;
    $timeoutQueue->detach($gen);
}

/**
 * Calls a function or executes a code snippet after a specified delay.
 * 
 * @global SplObjectStorage $timeoutQueue
 * @param callable $callback
 * @param int $time
 * @return Generator
 */
function setTimeout(callable $callback, int $time) : Generator{
    global $timeoutQueue;
    $end = microtime(true) * 1000 + $time;
    $f = function($callback, $end) {
        while (true) {
            if (microtime(true)*1000 >= $end) {
                yield $callback;
                break;
            } else {
                yield;
            }
        }
    };
    $gen = $f($callback, $end);
    $timeoutQueue->attach($gen);
    return $gen;
}
```


