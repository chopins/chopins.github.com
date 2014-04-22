<?php
$errno = 0;
$errstr = 0;
$fp = fsockopen("proxy.toknot.com", 80, $errno, $errstr, 30);
echo 'connect';
if($fp) {
    $http = 'GET /proxy_server.php HTTP/1.0\r\nHost: proxy.toknot.com\r\nAccept: */*\r\n\r\n';
    fwrite($fp, $http, strlen($http));
    while(!feof($fp)) {
        echo fread($fp, 1024);
    }
    $address = stream_socket_get_name($fp, false);
    var_dump($address);
    fclose($fp);
    sleep(1);
    list($ip,$port) = explode(':', $address);
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
    socket_bind($socket, $ip, $port);
    socket_listen($socket);
    $acp = socket_accept($socket);
     while(!feof($acp)) {
        echo socket_read($acp, 1024);
    }
}