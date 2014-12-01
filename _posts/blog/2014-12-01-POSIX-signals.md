---
layout: blog_contents
title: POSIX信号
categories: blog
---

以下是信号代码解释：  
Action定义如下：  

....<b>Term</b>  默认会终止进程   
....<b>Ign</b>   默认忽略   
....<b>Core</b>  默认终止进程并将内存信息转储到硬盘  
....<b>Stop</b>  默认停止进程  
....<b>Cont</b>  如果进程停止，默认继续执行进程  


    Signal          Value    Action   Comment
    ──────────────────────────────────────────────────────────────────────
....<b>SIGHUP</b>         1      Term    控制终端挂断或控制进程死掉时会收到这个信号  
....<b>SIGINT</b>         2      Term    来自键盘的中断信号，通常是`Ctrl+c`
....<b>SIGQUIT</b>        3      Core    来自键盘的退出信号,通常是`Ctrl+\`,默认情况下会将内存中的信息转储到硬盘    
....<b>SIGILL</b>         4      Core    非法指令信号  
....<b>SIGABRT</b>        6      Core    来自 `abort(3)` 的中止信号  
....<b>SIGFPE</b>         8      Core    错误的算术操作时内核发送给进程的信号  
....<b>SIGKILL</b>        9      Term    杀死进程信号  
....<b>SIGSEGV</b>       11      Core    无效的内存引用，所谓的段错误  
....<b>SIGPIPE</b>       13      Term    损坏的管道,写入了一个没有读取者的管道，就是管道断了  
....<b>SIGALRM</b>       14      Term    来自 `alarm(2)` 发送的时钟信号  
....<b>SIGTERM</b>       15      Term    终止信号  
....<b>SIGUSR1</b>    30,10,16   Term    用户定义信号 1  
....<b>SIGUSR2</b>    31,12,17   Term    用户定义信号 2   
....<b>SIGCHLD</b>    20,17,18   Ign     子进程停止或中止信号  
....<b>SIGCONT</b>    19,18,25   Cont    进程如果停止了继续执行信号    
....<b>SIGSTOP</b>    17,19,23   Stop    停止进程，可继续  
....<b>SIGTSTP</b>    18,20,24   Stop    在终端内停止进程，通常前台进程在终端执行时按`Ctrl+z`来停止，可继续  
....<b>SIGTTIN</b>    21,21,26   Stop    后台进程从终端读  
....<b>SIGTTOU</b>    22,22,27   Stop    后台进程从终端输出  
....<b>SIGBUS</b>     10,7,10    Core    总线错误 (错误的内存访问)  
....<b>SIGPOLL</b>               Term    轮询通知信号 (Sys V).  
                                      与 SIGIO 相同  
....<b>SIGPROF</b>    27,27,29   Term    系统资源定时器过期  
....<b>SIGSYS</b>     12,31,12   Core    传递了错误的参数给程序，通常是无效调用导致 (SVr4)  
....<b>SIGTRAP</b>       5       Core    跟踪/断点捕获信号  
....<b>SIGURG</b>     16,23,21   Ign     Socket上达到紧急条件 (4.2BSD)   
....<b>SIGVTALRM</b>  26,26,28   Term    虚拟 alarm 时钟 (4.2BSD)  
....<b>SIGXCPU</b>    24,24,30   Core    CPU 时间超出限制 (4.2BSD)  
....<b>SIGXFSZ</b>    25,25,31   Core    文件大小突破限制 (4.2BSD)  
....<b>SIGIOT</b>         6      Core    IOT 捕获.与 SIGABRT 类似  
....<b>SIGEMT</b>      7,-,7     Term     
....<b>SIGSTKFLT</b>   -,16,-    Term    协处理器栈错误 (未使用)  
....<b>SIGIO</b>      23,29,22   Term    I/O 现在可用信号 (4.2BSD)  
....<b>SIGCLD</b>      -,-,18    Ign     与 SIGCHLD 信号相同  
....<b>SIGPWR</b>     29,30,19   Term    电源错误 (System V)  
....<b>SIGINFO</b>     29,-,-            与 SIGPWR 类似  
....<b>SIGLOST</b>     -,-,-     Term    文件锁丢失 (unused)  
....<b>SIGWINCH</b>   28,28,20   Ign     窗口尺寸改变信号 (4.3BSD, Sun)，比如终端大小改变时  
....<b>SIGUNUSED</b>   -,31,-    Core    与 SIGSYS 相同  


其他信息见`$ man 7 signal`