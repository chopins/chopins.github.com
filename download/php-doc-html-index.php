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
<!DOCTYPE html>

<head>
    <title>PHP Documents</title>
    <meta charset="utf8">
    <style>
        :root {
            --font-family-sans-serif: "Fira Sans", "Source Sans Pro", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            --font-family-mono: "Fira Mono", "Source Code Pro", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            --dark-grey-color: #333;
            --dark-blue-color: #4F5B93;
            --medium-blue-color: #7A86B8;
            --light-blue-color: #E2E4EF;
            --dark-magenta-color: #793862;
            --medium-magenta-color: #AE508D;
            --light-magenta-color: #CF82B1;
            --background-color: var(--dark-grey-color);
            --background-text-color: #CCC;
            --content-background-color: #F2F2F2;
            --content-text-color: var(--dark-grey-color);
        }

        body {
            background-color: var(--dark-grey-color);
            font-family: var(--font-family-sans-serif);
            font-weight: 400;
            font-size: 1rem;
            line-height: 1.5rem;
            padding: 20px;
        }
        #c1 {
            background-color: var(--content-background-color);
        }

        a {
            color: var(--dark-blue-color);
            text-decoration: none;
            border-bottom: 1px solid;
            font-weight: 400;
        }
        a:hover {
            color:var(--dark-magenta-color);
        }
        ul {
            list-style-type: disc;
        }
        datalist option {
            font-size: 1em;
        }
    </style>
    <script>
        var files = <?= json_encode($search) ?>;
        document.addEventListener('DOMContentLoaded', function() {
            let datalist = filelist = '';
            for (f of files) {
                let n = f.replace(/\.\/((book|function|class)*\.)*/, '').replace('.html', '').replace(/-/g, '_');
                datalist += '<option value="' + f + '">' + n + '</option>';
                filelist += '<li><a href="' + f + '">' + n + '</a></li>';
            }
            document.getElementById('file-html').innerHTML = datalist;
            document.getElementById('file-list').innerHTML = filelist;
            document.getElementById('filter-file').addEventListener('keydown', function(e) {
                if (e.code == 'Enter' && files.indexOf(this.value) >= 0) {
                    window.location.href = this.value;
                }
            });
            document.getElementById('totop').addEventListener('click', function() {
                window.scrollTo(0, 0);
            });
        });
    </script>
</head>

<body>
    <div id="c1">
    <button style="position: fixed;bottom:20px;left:450px;font-size:1em;" id="totop">回顶部</button>
    <ul>
        <li><a href="/index.html">首页</a></li>
        <li><a href="./language.types.html">类型</a></li>
        <li><a href="./wrappers.html">支持的协议</a></li>
        <li><a href="./errorfunc.configuration.html">PHP.ini 运行时配置</a></li>
        <li><a href="./reserved.variables.html">预定义变量</a></li>
        <li><a href="./reserved.variables.server.html">预定义变量 $_SERVER</a></li>
        <li><a href="./reserved.constants.html">预定义常量</a></li>
        <li><a href="./reserved.classes.html">预定义类</a></li>
    </ul>
    <input list="file-html" id="filter-file" style="font-size:1em;margin-left:10px;" placeholder="回车打开链接">
    <datalist id="file-html">
    </datalist>
    <ul id="file-list">
    </ul>
    </div>
</body>

</html>