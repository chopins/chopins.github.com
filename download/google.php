<?php
date_default_timezone_set('Etc/GMT-8');
set_time_limit(0);
$url = 'http://'.$_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];

$dir = pathinfo($url);
$filename = basename($dir['dirname']);
$range = 0;
if(isset($_SERVER['HTTP_RANGE'])){
    $range = $_SERVER['HTTP_RANGE'];
}


$da = $connect = $cookie = '';
$header = '';
foreach($_SERVER as  $field => $fv) {
    if(strpos($field, 'HTTP_') === 0) {
        $filename = str_replace(' ','-',ucwords(strtolower(str_replace(array('HTTP_','_'), array('',' '), $field))));
        $header .= "$filename: $fv\r\n";
    }
}
$body = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $body = file_get_contents('php://input');
}

$opts = array(
    'http' => array(
        'method' => "GET",
        'header' => $header,
        'content' => $body
    )
);
$context = stream_context_create($opts);

$fp = @fopen('http://proxy.toknot.com/down.php?url='.  urlencode($url).'&da='.urlencode($da).'&range='.  urlencode($range),'r', false, $context);

if(!$fp) {
    $err = error_get_last();
    $status = explode('HTTP/1.1',$err['message']);
	$status = end($status);
    @header("Status: $status");
    echo "PROXY:$status";
    die;
}
$stat = @stream_get_meta_data($fp);

if (isset($stat['wrapper_data'])) {
    foreach ($stat['wrapper_data'] as $i => $header) {
        @header($header);
    }
} else {
    @header("Content-type: application/vnd.android.package-delta;charset=utf-8");
    @header("Accept-Ranges:bytes");
}
//@header('Content-Disposition: attachment; filename="' . $filename . '.apk"');
@fpassthru($fp);
@fclose($fp);