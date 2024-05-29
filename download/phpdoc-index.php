<?php
$uri = $_SERVER['REQUEST_URI'];
$name = basename($uri);
$name = strtolower(strtr($name, '_', '-'));
if (file_exists("./function.$name.html")) {
    header("Location: /function.$name.html");
} else if (file_exists("./book.$name.html")) {
    header("Location: /book.$name.html");
} else if (file_exists("./class.$name.html")) {
    header("Location: /class.$name.html");
} else if (file_exists("./language.types.$name.html")) {
    header("Location: /language.types.$name.html");
}
$url_len = strlen($uri);

if (strrpos($uri, '://') == ($url_len - 3) && file_exists('./wrappers.' . substr($uri, 1, $url_len - 4) . '.html')) {
    header("Location: /wrappers." . substr($uri, 1, $url_len - 4) . ".html");
}
$variable = trim(strtolower($name), '_$');
if (file_exists("./reserved.variables.$variable.html")) {
    header("Location: /reserved.variables.$variable.html");
} else if (file_exists("./reserved.$variable.html")) {
    header("Location: /reserved.$variable.html");
}

$search = glob("./*$name*.html");
?>
<html>

<body>
    <ul>
        <li><a href="/index.html">首页</a></li>
        <li><a href="./language.types.html">类型</li>
        <li><a href="./wrappers.html">协议</li>
        <li><a href="./reserved.variables.html">预定义变量</a></li>
        <li><a href="./reserved.constants.html">预定义常量</a></li>
        <li><a href="./reserved.classes.html">预定义类</a></li>
    </ul>
    <ul>
        <?php foreach($search as $file) { 
            echo "<li><a href='$file'>$file</a></li>";
        }
        ?>
    </ul>
</body>

</html>
