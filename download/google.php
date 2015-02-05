<?php

date_default_timezone_set('Etc/GMT-8');
set_time_limit(0);
if (PHP_SAPI == 'cli') {
    $url = $argv[1];
    $_SERVER['REQUEST_METHOD'] = 'GET';
} else {
    $url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
if(strpos($url, 'google') !== false) {
    file_put_contents(__DIR__.'/google_proxy_url.txt', $url.PHP_EOL, FILE_APPEND);
}
$dir = pathinfo($url);
$filename = basename($dir['dirname']);
$range = 0;
if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
}


$da = $connect = $cookie = '';
$header = "Host: proxy.toknot.com\r\n";
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
$t = time();
$key = md5('th3is#pas)(#3swodfrd@key' . date('(#Y--m-d-@--H-i%)'));
$vi = md5($t . '#this@ivEDkey', true);
$url = openssl_encrypt($url, 'aes128', $key , 0, $vi);
$referer = '';
if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = base64_encode(openssl_encrypt($_SERVER['HTTP_REFERER'], 'aes128', $key, 0, $vi));
}

$query = urlencode(base64_encode($url)) . '&t=' . $t . '&r='.$referer;

$ip_list_fp = fopen('http://184.168.233.1/down.php?url=' . $query , 'r', false, $context);
if (!$ip_list_fp) {
    $err = error_get_last();
    $status = explode('HTTP/1.1', $err['message']);
    $status = end($status);
    @header("Status: $status");
    echo "Local:$status";
    die;
}

$stat = @stream_get_meta_data($ip_list_fp);

$is_text = false;

if (isset($stat['wrapper_data'])) {
    foreach ($stat['wrapper_data'] as $i => $header) {
        @header($header);
        if (strpos($header, 'Content-Type: text') !== false) {
            $is_text = true;
        }
    }
} else {
    @header("Content-type: application/vnd.android.package-delta;charset=utf-8");
    @header("Accept-Ranges:bytes");
}
if ($is_text) {
    ob_start();
}
$dkey = date('(#Y--m-d-@--H-i%)');
$predkey = date('(#Y--m-d-@--H-i%)', strtotime('-1 Minute'));
$nextdkey = date('(#Y--m-d-@--H-i%)', strtotime('+1 Minute'));

@fpassthru($ip_list_fp);
if ($is_text) {
    $body = @openssl_decrypt(ob_get_contents(), 'aes128', md5('this body passowrd' . $dkey), 0, md5($t . 'this body iv', true));
    if (!$body) {
        $body = @openssl_decrypt(ob_get_contents(), 'aes128', md5('this body passowrd' . $predkey), 0, md5($t . 'this body iv', true));
        if (!$body) {
            $body = @openssl_decrypt(ob_get_contents(), 'aes128', md5('this body passowrd' . $nextdkey), 0, md5($t . 'this body iv', true));
        }
    }
    ob_end_clean();
    echo $body;
}
@fclose($ip_list_fp);
