#!/bin/env php
<?php
date_default_timezone_set('UTC');

$stroe = __DIR__ . '/dnsdata/';
if (!is_dir($stroe)) {
    mkdir($stroe);
}

$block = dns_get_record('_netblocks.google.com', DNS_TXT);

if (!$block && is_array($block)) {
    exec('nslookup -q=txt _netblocks.google.com 8.8.8.8', $output);
    $txt = explode('"', $output[4]);
    $block[0]['txt'] = $txt[1];
} elseif ($block === false) {
    die("Get Google DNS record error\n");
}


$txt = $block[0]['txt'];

$blockList = explode('ip4:', $txt);
array_shift($blockList);

$fp = fopen("{$stroe}iplist.txt", 'w');

$default_zone = <<<EOF
\$TTL    86400
@       1D IN SOA @ root (
                          42          ; serial (d. adams)
                          3H          ; refresh 
                          15M        ; retry      
                          1W         ; expiry  
                          1D )        ; minimum   
                        1D IN NS         ns1.google.com
                        1D IN NS         ns2.google.com
                        1D IN NS         ns3.google.com
                        1D IN NS         ns4.google.com

EOF;
$nsgoogleinfo = <<<EOF

ns1 IN A 216.239.32.10
ns2 IN A 216.239.34.10
ns3 IN A 216.239.36.10
ns4 IN A 216.239.38.10

EOF;


$upgoogle = writer_default('google.com');
fwrite($upgoogle, $nsgoogleinfo);

$gstatic = writer_default('gstatic.com');
$googleusercontent = writer_default('googleusercontent.com');
$youtube = writer_default('youtube.com');
$ggpht = writer_default('ggpht.com');
$clientsgoogle = writer_default('clients.google.com');
$googleapis = writer_default('googleapis.com');
$appspot = writer_default('appspot.com');
$googlevideo = writer_default('googlevideo.com');
$ytimg = writer_default('ytimg.com');

$g = stream_context_create(array("ssl" => array("capture_peer_cert" => true)));
$disablefork = false;
$childnum = 0;
$ischild = $skip = false;
if (function_exists('pcntl_fork')) {
    pcntl_signal(SIGCHLD, function() {
        echo 'cld';
        $GLOBALS['childnum'] --;
    });
}
foreach ($blockList as $ipblock) {
    list($ip_net, $maskbit) = explode('/', $ipblock);
    $start = ip2long($ip_net) + 1;
    $maxip = $start | (4294967295 >> $maskbit);

    if (strpos($ip_net, '173') === 0) {
        continue;
    }
    if (strpos($ip_net, '74') === 0) {
        continue;
    }
    if (strpos($ip_net, '72') === 0) {
        continue;
    }

    $startip = long2ip($start);
    $endip = long2ip($maxip - 1);
    $str = "IP BLOCK:{$ipblock} IN { {$startip} -- {$endip} }";
    //echo "\n\033[1m{$str}\033[0m\n";

    fwrite($fp, "$str\n");
    for ($i = $start; $i < $maxip; $i++) {
        $ip = long2ip($i);
        if (!$disablefork && function_exists('pcntl_fork')) {
            if ($childnum >= 200) {
                while ($childnum > 0) {
                    pcntl_wait($status);
                    $childnum--;
                }
            }
            $pid = pcntl_fork();
            if ($pid > 0) {
                $childnum++;
                $ischild = false;
                continue;
            } else if ($pid < 0) {
                $disablefork = true;
                $ischild = false;
            } else {
                $ischild = true;
            }
        }
        $r = @stream_socket_client("ssl://$ip:443", $errno, $errstr, 2, STREAM_CLIENT_CONNECT, $g);
        if ($r) {
            $cont = stream_context_get_params($r);
            $cerInfo = openssl_x509_parse($cont["options"]["ssl"]["peer_certificate"]);
            $d = str_replace(array('DNS:', ' '), '', $cerInfo['extensions']['subjectAltName']);
            //echo "\n\033[1;32mIP $ip Valid Domain:\033[0m {$d}\n";
            fwrite($fp, "IP $ip Valid Domain: {$d}\n");
            $ipkey = $cerInfo['extensions']['subjectKeyIdentifier'];

            $dms = explode(',', $d);
            writer_a_rec($upgoogle, 'google.com', '@', 'B2:2F:73:DA:F5:BA:E8:29:2A:CF:46:FD:ED:94:86:7E:1D:D7:C6:30');
            check_ip_domain($upgoogle, 'www.google.com', null, '1C:9B:D9:53:86:31:BD:BA:2E:84:20:34:4C:94:08:42:61:4F:BC:A5');
            $gm_check_key = 'AD:34:D3:F1:AB:19:A3:A6:F0:95:19:F3:11:FC:60:D0:03:30:F7:F2';
            check_ip_domain($upgoogle, 'mail.google.com', null, $gm_check_key);
            check_ip_domain($upgoogle, 'inbox.google.com', null, $gm_check_key);
            check_ip_domain($upgoogle, 'accounts.google.com', null, '52:22:A0:83:88:16:D5:6B:52:71:93:E5:A7:A3:4D:92:04:F4:B4:B5');
            check_ip_domain($upgoogle, 'm.google.com', null, '4B:B0:88:82:B5:30:D2:70:51:F7:A7:36:E2:0A:23:24:89:56:1F:09');
            check_ip_domain($upgoogle, 'checkout.google.com', null, '4C:95:00:A2:42:BE:C4:46:FB:30:87:00:BB:E9:0E:0F:44:1C:31:CA');
            check_ip_domain($upgoogle, 'talk.google.com', null, '23:A9:E5:D1:E0:C4:FC:C5:4B:AB:2D:DA:DC:9E:BC:B1:30:1B:C9:82');
            $gc_check_key = 'B2:2F:73:DA:F5:BA:E8:29:2A:CF:46:FD:ED:94:86:7E:1D:D7:C6:30';
            check_ip_domain($upgoogle, 'plus.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'play.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'id.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'groups.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'images.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'code.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'map.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'maps.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'news.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'upload.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'drive.google.com', '*.google.com', $gc_check_key);
            check_ip_domain($upgoogle, 'encrypted.google.com', '*.google.com', $gc_check_key);
            check_ip_list($upgoogle, 'encrypted-tbn', 0, 3, '.google.com', '*.google.com', $gst_check_key);
            check_ip_list($upgoogle, 'drive', 0, 9, '.google.com', '*.google.com', $gst_check_key);
            writer_a_rec($upgoogle, '*.google.com', '*', 'B2:2F:73:DA:F5:BA:E8:29:2A:CF:46:FD:ED:94:86:7E:1D:D7:C6:30');

            writer_a_rec($clientsgoogle, '*.clients.google.com', '*');

            $gst_check_key = '86:48:A7:95:30:1B:24:A6:E5:D8:E2:50:1E:A1:9B:C0:03:FE:C3:9C';
            writer_a_rec($gstatic, 'gstatic.com', '@', $gst_check_key);
            check_ip_domain($gstatic, 'ssl.gstatic.com', '*.gstatic.com', $gst_check_key);
            check_ip_domain($gstatic, 'fonts.gstatic.com', '*.gstatic.com', $gst_check_key);
            check_ip_domain($gstatic, 'csi.gstatic.com', '*.gstatic.com', $gst_check_key);
            check_ip_domain($gstatic, 'maps.gstatic.com', '*.gstatic.com', $gst_check_key);
            check_ip_domain($gstatic, 'www.gstatic.com', '*.gstatic.com', $gst_check_key);

            check_ip_list($gstatic, 'g', 0, 3, '.gstatic.com', '*.gstatic.com', $gst_check_key);
            check_ip_list($gstatic, 'mt', 0, 7, '.gstatic.com', '*.gstatic.com', $gst_check_key);
            check_ip_list($gstatic, 't', 0, 3, '.gstatic.com', '*.gstatic.com', $gst_check_key);
            check_ip_list($gstatic, 'encrypted-tbn', 0, 3, '.gstatic.com', '*.gstatic.com', $gst_check_key);
            writer_a_rec($gstatic, '*.gstatic.com', '*',$gst_check_key);

            writer_a_rec($googleusercontent, 'googleusercontent.com', '@');
            writer_a_rec($googleusercontent, '*.googleusercontent.com', '*', 'F3:68:50:66:D5:73:D0:1E:35:B9:33:B7:E1:54:7F:6C:73:AB:E8:F0');

            writer_a_rec($youtube, 'youtube.com');
            check_ip_domain($youtube, 'www.youtube.com', '*.youtube.comm', $gc_check_key);
            check_ip_domain($youtube, 'accounts.youtube.com', '*.youtube.comm', $gc_check_key);
            check_ip_domain($youtube, 'help.youtube.com', '*.youtube.comm', $gc_check_key);
            check_ip_domain($youtube, 'm.youtube.com', '*.youtube.comm', $gc_check_key);
            check_ip_domain($youtube, 'insight.youtube.com', '*.youtube.comm', $gc_check_key);
            //writer_a_rec($youtube, '*.youtube.com', 'B2:2F:73:DA:F5:BA:E8:29:2A:CF:46:FD:ED:94:86:7E:1D:D7:C6:30');

            writer_a_rec($ggpht, 'ggpht.com', '@');
            $ggpht_check_key = 'F3:68:50:66:D5:73:D0:1E:35:B9:33:B7:E1:54:7F:6C:73:AB:E8:F0';
            check_ip_list($ggpht, 'lh', 1, 6, '.ggpht.com', '*.ggpht.com', $gst_check_key);
            check_ip_list($ggpht, 'gm', 1, 4, '.ggpht.com', '*.ggpht.com', $gst_check_key);
            check_ip_list($ggpht, 'geo', 1, 3, '.ggpht.com', '*.ggpht.com', $gst_check_key);
            writer_a_rec($ggpht, '*.ggpht.com', '*',$gst_check_key);

            $gapi_check_key = '71:E5:C2:3A:56:1F:2C:AE:19:CB:51:FD:FD:FF:C4:45:D2:DD:EB:75';
            check_ip_domain($googleapis, 'ajax.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'fonts.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'chart.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'maps.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'wwww.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'play.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'translate.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'youtube.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'content.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'bigcache.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'storage.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'android.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'redirector-bigcache.googleapis.com', '*.googleapis.com', $gapi_check_key);
            check_ip_domain($googleapis, 'commondatastorage.googleapis.com', '*.googleapis.com', $gapi_check_key);

            check_ip_domain($googleapis, 'bigcache.googleapis.com', '*.googleapis.com', $gapi_check_key);

            check_ip_list($googleapis, 'mt', 0, 3, '.googleapis.com', '*.googleapis.com', $gst_check_key);
            check_ip_list($googleapis, 'mt', 0, 3, '.googleapis.com', '*.googleapis.com', $gst_check_key);
            check_ip_list($googleapis, 'mt', 0, 3, '.googleapis.com', '*.googleapis.com', $gst_check_key);
            writer_a_rec($googleapis, '*.googleapis.com', '*', $gapi_check_key);

            writer_a_rec($appspot, '*.appspot.com');

            writer_a_rec($googlevideo, '*.googlevideo.com', '*', 'B7:16:7A:63:F9:7F:90:F1:91:D8:77:CB:BF:30:D2:F7:D2:A8:DB:50');

            writer_a_rec($ytimg, 'ytimg.com', '@');
            check_ip_domain($ytimg, 'i.ytimg.com', '*.ytimg.com', $gc_check_key);
            check_ip_list($ytimg, 'i', 1, 4, '.ytimg.com', '*.ytimg.com', $gc_check_key);
            check_ip_domain($ytimg, 's.ytimg.com', '*.ytimg.com', $gc_check_key);

            writer_a_rec($ytimg, '*.ytimg.com', '*', 'B2:2F:73:DA:F5:BA:E8:29:2A:CF:46:FD:ED:94:86:7E:1D:D7:C6:30');
        }
        if ($ischild) {
            exit;
        }
    }
}
if (function_exists('pcntl_wait')) {
    pcntl_wait($start);
}

function writer_default($domain) {
    global $stroe, $default_zone;
    $writerfp = fopen("{$stroe}{$domain}.zone", 'w');
    fwrite($writerfp, $default_zone);
    return $writerfp;
}

function writer_a_rec(&$fp, $domain, $rec = '*', $key = null) {
    global $dms, $ipkey, $ip, $skip;
    if ($skip) {
        $skip = false;
        return;
    }

    if ($key === null) {
        if (in_array($domain, $dms)) {
            fwrite($fp, "$rec IN A $ip\n");
        }
        return;
    }
    if (in_array($domain, $dms) && $ipkey == $key) {
        fwrite($fp, "$rec IN A $ip\n");
    }
}

function check_ip($domain) {
    global $ip, $skip;
    $opts = array(
        'http' => array(
            'method' => "GET",
            'header' => "Host: $domain\r\nConnection: close\r\n",
            'follow_location' => 0,
            'ignore_errors' => 1
        )
    );
    $context = stream_context_create($opts);
    $fp = @fopen("https://$ip/", 'r', false, $context);
    $meta = stream_get_meta_data($fp);
    fclose($fp);
    if (strpos($meta['wrapper_data'][0], 'HTTP/1.0 200') === false && strpos($meta['wrapper_data'][0], 'HTTP/1.0 302') === false && strpos($meta['wrapper_data'][0], 'HTTP/1.0 301') === false) {
        $skip = true;
        return false;
    }

    $skip = false;
    return true;
}

function check_ip_list(&$fp, $pre, $start, $end, $check_suffix, $domain, $key = null) {
    for ($i = $start; $i < $end + 1; $i++) {
        $check_domain = $pre . $i . $check_suffix;
        check_ip_domain($fp, $check_domain, $domain, $key);
    }
}

function check_ip_domain(&$fp, $check, $domain = null, $key = null) {
    if ($domain === null) {
        $domain = $check;
    }
    $domain_split = explode('.', $check);
    if (count($domain_split) == 2) {
        $rec = '@';
    } else {
        $rec = $domain_split[0];
    }
    
    check_ip($check);
    writer_a_rec($fp, $domain, $rec, $key);
}
