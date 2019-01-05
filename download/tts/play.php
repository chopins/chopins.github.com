<?php

/* 
 * Toknot play muisc
 */

if($argc < 2) {
    echo "用法: php {$argv[0]} [音频文件夹] [音频文件序号]\n   文件序号为可选参数";
    die;
}
$dir = realpath($argv[1]);

$offset = false;
if($argc > 2) {
    $offset = $argv[2];
    echo "Open offset:$offset" . PHP_EOL;
}
$playHistory = [];
$i =0;
while(true) {
    $darr = scandir($dir);
    foreach($darr as $f) {
        if($f === '.' || $f=== '..') {
            continue;
        }
        $i++;
        if($offset !==false && $i != $offset) {
            continue;
        }
        $offset = false;
        
        if(in_array($f, $playHistory)) {
            continue;
        }
        $playHistory[] = $f;
        $file = "$dir/$f";
        system("mplayer $file");
    }
}
