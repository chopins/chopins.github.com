<?php

$file = $_SERVER['REQUEST_URI'];
chdir(__DIR__);
if (empty($file) || $file == '/') {
    include __DIR__ . '/index.html';
} else {
    if(strcasecmp($file, '_SERVER')) {
        return include 'reserved.variables.server.html';
    }
    $file  = strtolower(str_replace(array('/', '_', '.php'), array('', '-', ''), $file));
    $file1 = 'function.' . $file . '.html';
    $file2 =  $file . '.html';
    $file3 = 'class.' . $file . '.html';
    $file4 = 'book.' . $file . '.html';
    $file5 = 'ref.' . $file . '.html';
    if (file_exists($file1)) {
        include $file1;
    } else if (file_exists($file2)) {
        include $file2;
    } else if (file_exists($file3)) {
        include $file3;
    } else if (file_exists($file4)) {
        include $file4;
    } else if (file_exists($file5)) {
        include $file5;
    } else {
        echo <<<EOF
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>函数和方法列表</title>

 </head>
 <body><div class="manualnavbar" style="text-align: center;">
 <div class="prev" style="text-align: left; float: left;"><a href="indexes.html">索引</a></div>
 <div class="next" style="text-align: right; float: right;"><a href="indexes.examples.html">示例列表</a></div>
 <div class="up"><a href="indexes.html">索引</a></div>
 <div class="home"><a href="index.html">PHP Manual</a></div>
</div><hr /><div id="indexes.functions" class="section">
  <h2 class="title">函数和方法列表</h2>
  <p class="para">手册中所有函数和方法的列表</p>
        <ul class='gen-index index-for-refentry'><ul>
EOF;
        $file2 = 'class.*' . $file . '*.html';
        foreach(glob($file2) as $filename) {
            $classname = ucwords(str_replace(array('class.','.html','-'), array('','','_'),$filename));
            echo "<li><a href='{$filename}' class='index'> $classname</a> - $classname 类</li>";
        }
        $fp = fopen('indexes.functions.html', 'r');
        $matches = array();
        while (!feof($fp)) {
            $line = fgets($fp);
            if(preg_match("/^<li><a href=\".*{$file}.*\.html\" class=\"index\">/i", $line, $matches)) {
                echo $line;
            }
        }

        echo <<<EOF

        </ul></ul>


 </div><hr /><div class="manualnavbar" style="text-align: center;">
 <div class="prev" style="text-align: left; float: left;"><a href="indexes.html">索引</a></div>
 <div class="next" style="text-align: right; float: right;"><a href="indexes.examples.html">示例列表</a></div>
 <div class="up"><a href="indexes.html">索引</a></div>
 <div class="home"><a href="index.html">PHP Manual</a></div>
</div></body></html>
EOF;

    }
}
