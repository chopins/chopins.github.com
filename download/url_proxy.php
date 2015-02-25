<?php

function replace_url($matchs) {
    global $fetch_info, $proxy_url;

    if (strpos($matchs[2], 'http://') !== 0 && strpos($matchs[2], 'https://') !== 0) {
        if (strpos($matchs[2], '//') === 0) {
            $matchs[2] = $fetch_info['scheme'] . '://' . $matchs[2];
        } else if (strpos($matchs[2], '/') === 0) {
            $matchs[2] = $fetch_info['scheme'] . '://' . $fetch_info['host'] . $matchs[2];
        } else {
            $path = dirname($fetch_info['path']);
            $matchs[2] = $fetch_info['scheme'] . '://' . $fetch_info['host'] . $path . '/' . $matchs[2];
        }
    }

    return $matchs[1] . $proxy_url . base64_encode($matchs[2]) . $matchs[3];
}

if (isset($_GET['d'])) {
    $header = '';
    foreach ($_SERVER as $field => $fv) {
        if ($field == 'HTTP_HOST') {
            continue;
        }
        if ($field == 'HTTP_REFERER') {
            continue;
        }
        if (strpos($field, 'HTTP_') === 0) {
            $filename = str_replace(' ', '-', ucwords(strtolower(str_replace(array('HTTP_', '_'), array('', ' '), $field))));
            $header .= "$filename: $fv\r\n";
        }
    }
    $body = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $body = file_get_contents('php://input');
    }
    $opts = array(
        'http' => array(
            'method' => $_SERVER['REQUEST_METHOD'],
            'header' => $header,
            'content' => $body,
            'ignore_errors' => true,
            'follow_location' => 0
        )
    );
    $context = stream_context_create($opts);
    $proxy_url = 'http://proxy.toknot.com/url.php?d=';
    $fetch_url = base64_decode($_GET['d']);
    if (strpos($fetch_url, 'http://') !== 0 && strpos($fetch_url, 'https://') !== 0) {
        $fetch_url = 'http://'.$fetch_url;
    }

    $fetch_info = parse_url($fetch_url);

    //$page = file_get_contents($fetch_url, false, $context);

    $fp = fopen($fetch_url, 'r', false, $context);
    $stat = @stream_get_meta_data($fp);

    $is_text = $is_css = false;
    $encode = '';
    if (isset($stat['wrapper_data'])) {
        foreach ($stat['wrapper_data'] as $i => $header) {
            if(strpos($header, 'Content-Encoding') === 0) {
                list(,$encode) = explode(':', $header);
                $encode = trim($encode);
                continue;
            }
            if (strpos($header, 'Content-Type: text/html') !== false) {
                $is_text = true;
            }
            if (strpos($header, 'Content-Type: text/css') === 0) {
                $is_css = true;
            }
            if (strpos($header, 'Location:') === 0) {
                list(, $location_url) = explode(':', $header);
                $location_url = replace_url(array('', '', trim($location_url), ''));
                header("Location: $location_url");
                continue;
            }
            @header($header);
        }
    }
    
    $page = '';
    if (!$fp) {
        exit;
    }
    while (!feof($fp)) {
        $page .= fread($fp, 8192);
    }

    if($encode == 'gzip') {
        $page = gzdecode($page);
    } else if($encode == 'deflate') {
        $page = gzinflate($page);
    }
    
    $page = preg_replace_callback('/(\s+href=[\'"])([^\'^"]+)([\'"][\s>])/im', 'replace_url', $page);
    $page = preg_replace_callback('/(\s+src=[\'"])([^\'^"]+)([\'"][\s>])/im', 'replace_url', $page);


    if ($is_css) {
        $page = preg_replace_callback('/(\s*url\([\'])([^\'^"]+)([\']\))/im', 'replace_url', $page);
    }
    echo $page;
//    echo str_replace(array('<','>'),array('&lt;','&gt;'),$page);
    //echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/><script>document.write(atob("'.base64_encode($page) . '"));</script></head></html>';
    exit;
}
?>
<html>
    <head>
        <title>Toknot Proxy</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    </head>
    <body>
        <h3><a href="http://toknot.com">Toknot</a></h3>
        输入域名或IP:<input type="text" value="" id="d" style="width:600px;">
        <input type="button" value="Query" onclick="getIP(this);"><br />
        <script type="text/javascript">
            if (typeof btoa != 'function') {
                alert('你的浏览器不支持 btoa 函数请更换最新版本浏览器');
            }
            function getIP(obj) {
                var d = document.getElementById('d').value;
                d = encodeURIComponent(btoa(d));
                window.location.href = 'url.php?d=' + d;
            }
            document.body.onload = function () {
                document.getElementById('d').onfocus = function () {
                    if (document.addEventListener) {
                        document.addEventListener("keypress", enterEvent, true);
                    } else {
                        document.attachEvent("onkeypress", enterEvent);
                    }
                };
                document.getElementById('d').onblur = function () {
                    if (document.addEventListener) {
                        document.removeEventListener("keypress", enterEvent, true);
                    } else {
                        document.detachEvent("onkeypress", enterEvent);
                    }
                };
                function enterEvent(evt) {
                    if (evt.keyCode == 13) {
                        getIP();
                    }
                }
            };
        </script>
    </body>
</html>
