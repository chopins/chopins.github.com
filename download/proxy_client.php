<?php
$errno = 0;
$errstr = 0;
$ip_list_fp = fsockopen("proxy.toknot.com", 50010, $errno, $errstr, 30);
echo 'connect';
if($ip_list_fp) {
    echo 'scu';
}