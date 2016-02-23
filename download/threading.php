<?php

if (!extension_loaded('pthreads')) {
    die('require pthreads extension');
}

/**
 * PHP 线程状态类
 * 
 * <code>
 * $thread = new Threading;
 * $thread->workerCall('func_name', null, $params);
 * 
 * class MyThread extends Threading {
 *      public function run() {
 *          echo 'test thread';
 *      }
 * }
 * $mythread = new MyThread;
 * $mythread->start();
 * </code>
 *
 * @author chopin
 */
class Threading extends Thread {

    private $complete = false;
    private $data = '';
    private $callback = '';
    private $callbackParams = '';
    private $name = '';

    /**
     * 获取线程是否完成
     *
     * @return boolean
     */
    final public function getComplete() : bool {
        return $this->complete;
    }

    /**
     * 设置线程是否完成 继承本类后，在方法中调用设置
     */
    final protected function setComplate() {
        $this->complete = true;
    }

    /**
     * 获取线程数据，此数据是序列化后的数据
     */
    final public function getData() : string {
        return $this->data;
    }

    /**
     * 设置线程数据
     * 
     * @param mixed $value            
     */
    final public function setData($value) {
        $this->data = serialize($value);
    }

    public function run() {
        $callback = unserialize($this->callback);
        if (is_callable($callback)) {
            call_user_func_array($callback, unserialize($this->callbackParams));
        }
        $this->setComplate();
    }

    /**
     * 传入一个函数在另一个线程中执行
     * 
     * @param callable $callback  函数名
     * @param PoolManager|null $pool 线程池管理对象
     * @param mixed $param  回调函数参数列表    
     */
    final public function workerCall(callable $callback, PoolManager $pool = null, ...$params) {
        if($callback instanceof Closure) {
            exit('第一个参数只能传函数名');
            return false;
        }
        $this->callback = serialize($callback);
        $this->callbackParams = serialize($params);
        if ($pool) {
            $pool->addWorker($this);
        } else {
            $this->start();
        }
    }
    
    /**
     * 设置线程名
     */
    final public function setThreadName(string $name = '') {
        $this->name = $name;
    }
    
    /**
     * 获取线程名
     */
    final public function getThreadName() : string {
        if (empty($this->name)) {
            return 'Thread-' . Thead::getCurrentThreadId();
        } else {
            return $this->name;
        }
    }

}

/**
 * PHP 线程池
 * <code>
 * $pool = new PoolManager(8);
 * $pool->setPoolName('Test Pool');
 * 
 * $thread = new Threading;
 * $thread->workerCall('func_name', $pool, $params);
 * 
 * </code>
 * @author chopin
 */
final class PoolManager extends SplObjectStorage {

    private $size = 0;
    private $callback;
    private $loopSleepTime = 200000;
    private $name = '';

    /**
     *
     * @param int $size
     *            设置线程池大小
     */
    public function __construct(int $size) {
        $this->size = $size;
    }

    /**
     * 设置线程池名字
     */
    public function setPoolName(string $name) {
        $this->name = $name;
    }
    
    /**
     * 获取线程池名字
     */
    public function getPoolName() : string {
        return $this->name;
    }
    
    /**
     * 设置loop期间调用函数
     *
     * @param callable $call
     *            可调用函数
     */
    public function loopCall(callable $call) {
        $this->callback = $call;
    }

    /**
     * 设置Loop Sleep 时间，单位毫秒
     * 
     * @param int $time Loop Sleep 时间，单位毫秒
     */
    public function setLoopSleepTime(int $time) {
        $this->loopSleepTime = $time * 1000;
    }

    private function checkLoop(int $size = 0) {
        while ($this->count() > $size) {
            foreach ($this as $w) {
                if ($w->getComplete()) {
                    if (is_callable($this->callback)) {
                        ($this->callback)($w);
                    }
                    $this->detach($w);
                } 
            }
            usleep($this->loopSleepTime);
        }
    }

    /**
     * 最后循环等待
     */
    public function loop() {
        $this->checkLoop();
    }

    /**
     * 添加一个线程类到线程池
     *
     * @param StateWorker $worker
     *            线程类
     */
    public function addWorker(Threading $worker) {
        $this->checkLoop($this->size);

        $worker->start();

        $this->attach($worker);
    }

}

