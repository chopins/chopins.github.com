<?php

date_default_timezone_set('UTC');
define('SERVER_PROXY_LOG_FILE', sys_get_temp_dir().'/proxy_run.log');
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    file_put_contents('/tmp/run', "$errstr $errfile $errline $errno" , FILE_APPEND);
});
new ServerShellProxy($argv[1], $argv[2]);

class ServerShellProxy {

    private $remote;

    public function __construct($ip, $port) {
        $st = $this->connectTarget($ip, $port);
        if ($st) {
            file_put_contents(SERVER_PROXY_LOG_FILE, "start transport data\n");
            $this->transprotData();
        }
    }

    public function connectTarget($ip, $port) {
        $this->remote = @fsockopen($ip, $port, $errno, $errstr, 3);
        if ($this->remote) {
            fwrite(STDERR, 200);
            return true;
        } else {
            fwrite(STDERR, 400);
            return false;
        }
    }

    public function transprotData() {
        $st = @stream_get_meta_data($this->remote);
        if (!$st)
            return;
        while (true) {
            $len = fread(STDIN, 4);
            file_put_contents(SERVER_PROXY_LOG_FILE, "len:$len\n", FILE_APPEND);
            if((int)$len == 0) {
                continue;
            }
            $indata = fread(STDIN, (int)$len);
            if ($indata == "-0-0-0-0") {
                break;
            }
            file_put_contents(SERVER_PROXY_LOG_FILE, "write remote\n", FILE_APPEND);
            fwrite($this->remote, $indata, strlen($indata));
        }
        while (!feof($this->remote)) {
            file_put_contents(SERVER_PROXY_LOG_FILE, "read remote\n", FILE_APPEND);
            $response = fread($this->remote, 1024);
            $len = sprintf('%04d', strlen($response));
            fwrite(STDOUT, $len . $response);
        }
        file_put_contents(SERVER_PROXY_LOG_FILE, "write remote end\n", FILE_APPEND);
        fwrite(STDOUT, "0008-0-0-0-0");
        stream_socket_shutdown($this->remote, STREAM_SHUT_RD);
        fclose($this->remote);
        exit;
    }

}
