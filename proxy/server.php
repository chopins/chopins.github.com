<?php

date_default_timezone_set('UTC');
define('SERVER_PROXY_LOG_FILE', sys_get_temp_dir() . '/proxy_run.log');
define('SERVER_DATA_BUFFER_FILE', sys_get_temp_dir() . '/' . uniqid('_buff_', time()));

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    file_put_contents('/tmp/run', "$errstr $errfile $errline $errno", FILE_APPEND);
});
new ServerShellProxy($argv[1], $argv[2]);

class ServerShellProxy {

    private $remote;
    private $writeable = false;
    private $readable = false;

    public function __construct($ip, $port) {
        $this->connectTunnel($ip, $port);
        return;
        $st = $this->connectTarget($ip, $port);
        if ($st) {
            $this->writeable = true;
            $this->readable = false;
            $this->transprotData();
        }
    }

    public function connectTunnel($ip, $port) {
        $num = 0;
        echo SERVER_PROXY_LOG_FILE;
        file_put_contents(SERVER_PROXY_LOG_FILE, "Connect $ip:$port\n");
        while ($num < 5) {
            $fp = fsockopen($ip, $port, $errno, $errstr, 3);
            file_put_contents(SERVER_PROXY_LOG_FILE, "$errstr($errno)\n", FILE_APPEND);
            if ($fp) {
                break;
            }
            $num++;
        }
        if(!$fp) {
            fwrite(STDERR, "connect locatl server: $errstr");
            exit;
        }
        fwrite($fp, 'Ok');
        while(true) {
            $read = $write = array($fp);
            $except = null;
            stream_select($read, $write, $except, 2000);
        }
    }

    public function connectTarget($ip, $port) {
        $this->remote = @fsockopen($ip, $port, $errno, $errstr, 3);
        if ($this->remote) {
            file_put_contents(SERVER_PROXY_LOG_FILE, "recv data\n");
            fwrite(STDERR, 200);
            return true;
        } else {
            fwrite(STDERR, 400);
            return false;
        }
    }

    public function transprotData() {
        while (!feof($this->remote)) {
            if ($this->readable && !$this->writeable) {
                file_put_contents(SERVER_PROXY_LOG_FILE, "recv data\n", FILE_APPEND);
                $data = stream_socket_recvfrom($this->remote, 1024);
                if (!empty($data)) {
                    file_put_contents(SERVER_PROXY_LOG_FILE, "recv data send\n", FILE_APPEND);
                    file_put_contents(SERVER_PROXY_LOG_FILE, fread(STDIN, 8));
                    $len = sprintf('%04d', strlen($data));
                    fwrite(STDOUT, $len . $data);
                    file_put_contents(SERVER_PROXY_LOG_FILE, "write recv data send\n", FILE_APPEND);
                    file_put_contents(SERVER_PROXY_LOG_FILE, fread(STDIN, 8));
                } else {
                    fread(STDIN, 8);
                    fwrite(STDOUT, '0008-0-0-0-0');
                    fread(STDIN, 8);
                    $this->readable = false;
                    $this->writeable = true;
                }
            }
            if ($this->writeable && !$this->readable) {
                $len = fread(STDIN, 4);
                file_put_contents(SERVER_PROXY_LOG_FILE, $len, FILE_APPEND);
                if ((int) $len == 0) {
                    $this->readable = true;
                    $this->writeable = false;
                    continue;
                }
                $indata = fread(STDIN, (int) $len);
                if ($indata == "-0-0-0-0") {
                    $this->readable = true;
                    $this->writeable = false;
                    continue;
                }
                file_put_contents(SERVER_PROXY_LOG_FILE, $indata, FILE_APPEND);
                fwrite($this->remote, $indata);
            }
        }
        stream_socket_shutdown($this->remote, STREAM_SHUT_RD);
        fclose($this->remote);
        exit;
    }

}
