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
    if(count($darr) <= $playHistory) {
        echo "已播放完毕。按q键退出，按c键继续";
        $enter = trim(fgets(STDIN));
        if($enter == 'c') {
            continue;
        } else {
            exit;
        }
    }
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
        $file = "$dir/$f";
        if(file_exists($file)) {
            $playHistory[] = $f;
            system("mplayer $file");
        }
    }
}
