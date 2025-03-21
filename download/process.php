<?php

class ChildProcess
{
    public function __construct(
        public readonly Closure $loop,
        public readonly int $interval = 200000,
        public readonly bool $selfLoop = false,
        public readonly ?string $title = null,
        public readonly ?Closure $exit = null,
    ) {}
}

class ChildProcessList extends ArrayObject
{
    public function __construct(ChildProcess ...$child)
    {
        parent::__construct($child);
    }
}

class Process
{
    public static int $mainTimeout = 100000;
    private array $processList = [];
    private SysvMessageQueue $queue;
    private ?ChildProcess $child = null;
    private int $pid = 0;
    private int $ppid = 0;
    public function __construct(ChildProcessList $children, string $pidfile = '', string $processTitle = '')
    {
        $this->pid = posix_getpid();
        $key = $this->genKey($pidfile, $processTitle);
        if (msg_queue_exists($key)) {
            trigger_error("key $key of msg queue is exists", E_USER_WARNING);
        }
        $this->queue = msg_get_queue($key);
        if ($processTitle) {
            cli_set_process_title($processTitle);
        }
        $this->createChild($children);
    }

    public function mainExit()
    {
        echo "main process exit\n";
    }

        /**
     * 子进程在循环内接受主进程通知退出的消息
     * 子进程需在循环内调用本方法完成退出
     */
    public function receiveExit()
    {
        $message = null;
        $state = msg_receive($this->queue, $this->pid, $type, 1, $message, false, MSG_IPC_NOWAIT);
        if ($state) {
            $this->childExit();
        }
    }
    /**
     * 子进程退出，通知主进程
     */
    public function childExit()
    {
        msg_send($this->queue, $this->ppid, $this->pid, false);
        if($this->child->exit) {
            $efun = $this->child->exit;
            $efun();
        }
        exit;
    }

    protected function genKey(string $pidfile = '', string $processTitle = '')
    {
        if ($processTitle) {
            $projectId = substr($processTitle, 0, 1);
        } else {
            $projectId = substr($this->pid, -1);
        }
        if ($pidfile) {
            $key = ftok($pidfile, $projectId);
        } else {
            $key = ftok(__FILE__, $projectId);
        }
        return  $key + $this->pid;
    }

    protected function mainSignalHandler(int $sig)
    {
        foreach ($this->processList as $pid => $s) {
            msg_send($this->queue, $pid, 'E', false);
        }
    }

    protected function mainLoop()
    {
        $msgSize = strlen(max($this->pid, ...array_keys($this->processList)));
        pcntl_signal(SIGTERM, [$this, 'mainSignalHandler'], false);
        pcntl_signal(SIGINT, [$this, 'mainSignalHandler'], false);
        while (true) {
            pcntl_signal_dispatch();
            $msgState = msg_receive($this->queue, $this->pid, $type, $msgSize, $message, false, MSG_IPC_NOWAIT);
            if ($msgState) {
                $pid = $message;
                unset($this->processList[$pid]);
                pcntl_waitpid($pid, $status, WUNTRACED);
            } else {
                usleep(self::$mainTimeout);
            }
            if (count($this->processList) <= 0) {
                msg_remove_queue($this->queue);
                $this->mainExit();
                exit;
            }
        }
    }

    protected function childLoop()
    {
        $func = $this->child->loop;
        if ($this->child->selfLoop) {
            $func($this);
        } else {
            do {
                $this->receiveExit();
                $ret = $func($this);
                usleep($this->child->interval);
            } while ($ret);
        }
    }


    protected function childSignalHandler(int $sig) {}

    protected function createChild(ChildProcessList $children)
    {
        foreach ($children as $i => $child) {
            $pid = pcntl_fork();
            if ($pid === 0) {
                $this->ppid = $this->pid;
                $this->child = $child;
                if ($child->title) {
                    cli_set_process_title($child->title);
                }
                pcntl_signal(SIGTERM, [$this, 'childSignalHandler'], false);
                pcntl_signal(SIGINT, [$this, 'childSignalHandler'], false);
                $this->pid = posix_getpid();
                $this->childLoop();
                $this->childExit();
            } else if ($pid > 0) {
                $this->processList[$pid] = 1;
            } else {
                throw new \RuntimeException("fork $i process error");
            }
        }
        $this->mainLoop();
    }
}

$childs = new ChildProcessList(
    new ChildProcess(
        function () {
            sleep(1);
            return true;
        },
        title: 'test-1'
    ),
    new ChildProcess(
        function () {
            sleep(1);
            return true;
        },
        title: 'test-2'
    ),
    new ChildProcess(
        function () {
            sleep(1);
            return true;
        },
        title: 'test-3'
    ),
    new ChildProcess(
        function () {
            sleep(1);
            return true;
        },
        title: 'test-4'
    ),
);
new Process($childs);
