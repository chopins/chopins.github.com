<?php
$errno = 0;
$errstr = 0;
$fp = fsockopen("proxy.toknot.com", 50010, $errno, $errstr, 30);
echo 'connect';
if($fp) {
    echo 'scu';
}