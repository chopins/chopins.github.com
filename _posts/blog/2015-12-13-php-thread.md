---
layout: blog_contents
title: PHP 线程管理
categories: blog
---

通过封装pthreads扩展相关类，得到了两个使用起来比较简单的类  


```php
<?php

if(!extension_loaded('pthreads')) {
    die('require pthreads extension');
}

/**
 * PHP 线程状态类
 *
 * @author chopin
 */
class Threading extends Thead
{

    private $complete = false;

    private $data = '';

    private $callback = '';

    /**
     * 获取线程是否完成
     *
     * @return boolean
     */
    final public function getComplete(): bool
    {
        return $this->complete;
    }

    /**
     * 设置线程是否完成 继承本类后，在方法中调用设置
     */
    final protected function setComplate()
    {
        $this->complete = true;
    }

    /**
     * 获取线程数据，此数据是序列化后的数据
     */
    final public function getData(): String
    {
        return $this->data;
    }

    /**
     * 设置线程数据
     * 
     * @param mixed $value            
     */
    final public function setData(mixed $value)
    {
        $this->data = serialize($value);
    }

    public function run()
    {
        $callback = unserialize($this->callback);
        if (is_callable($callback)) {
            $callback();
        }
        $this->setComplate();
    }

    /**
     * 传入一个函数在另一个线程中执行
     * 
     * @param callable $callback            
     */
    final public function workerCall(callable $callback,$pool=null)
    {
        $this->callback = serialize($callback);
        if($pool) {
           $pool->addWorker($this);
        } else {
           $this->start();
        }
    }
}

/**
 * PHP 线程池
 *
 * @author chopin
 */
class PoolManager extends SplObjectStorage
{

    private $size = 0;

    private $callback;

    /**
     *
     * @param int $size
     *            设置线程池大小
     */
    public function __construct(int $size)
    {
        $this->size = $size;
    }

    /**
     * 设置loop期间调用函数
     *
     * @param callable $call
     *            可调用函数
     */
    public function loopCall(callable $call)
    {
        $this->callback = $call;
    }

    private function checkLoop($size = 0)
    {
        while ($this->count() > $size) {
            foreach ($this as $w) {
                if ($w->getComplete()) {
                    if (is_callable($this->callback)) {
                        ($this->callback)($w);
                    }
                    $this->detach($w);
                }
            }
            usleep(100);
        }
    }

    /**
     * 最后循环等待
     */
    public function loop()
    {
        $this->checkLoop();
    }

    /**
     * 添加一个线程类到线程池
     *
     * @param StateWorker $worker
     *            线程类
     */
    public function addWorker(Threading $worker)
    {
        $this->checkLoop($this->size);
        $worker->start();
        $this->attach($worker);
    }
}
```

用法： 

```php
<?php
class myThread extends Threading
{
    public function run()
    {
        //你的业务逻辑
        $this->setComplate();
    }
}
$pool = new PoolManager(5);
$pool->addWorker(new myThread);
$pool->addWorker(new myThread);
$pool->addWorker(new myThread);
$pool->addWorker(new myThread);
$pool->addWorker(new myThread);

//下面的线程会在前面的执行完毕才会加入
$pool->addWorker(new myThread);

$w = new Threading;
$w->workerCall(function () { //你的业务逻辑}, $pool);

//等待所有线程执行结束
$pool->loop();

```
