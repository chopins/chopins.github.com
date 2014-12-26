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
$serid = date('YmdH');
$default_zone = <<<EOF
\$TTL    86400
@ 1D IN SOA @ root ( $serid  7200 36000 604800  86400 )
@ 1D IN NS         ns1.google.com
@ 1D IN NS         ns2.google.com
@ 1D IN NS         ns3.google.com
@ 1D IN NS         ns4.google.com

EOF;

$nsgoogleinfo = <<<EOF
\$TTL    86400
@ 1D IN SOA @ root ( $serid  7200 36000 604800  86400 )
@ 1D IN NS         ns1
@ 1D IN NS         ns2
@ 1D IN NS         ns3
@ 1D IN NS         ns4
        
ns1 IN A 216.239.32.10
ns2 IN A 216.239.34.10
ns3 IN A 216.239.36.10
ns4 IN A 216.239.38.10

EOF;


$upgoogle = writer_google_default();


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
static $childnum = 0;
$ischild = $skip = false;
$child_pool = array_fill(0, 200, 0);
declare(ticks = 1);

function tick_handler() {
    pcntl_signal_dispatch();
}

function childexit($signo) {
    pcntl_wait($status);
}

if (function_exists('pcntl_fork')) {
    pcntl_signal(SIGCHLD, SIG_IGN);
    pcntl_signal(SIGCLD, SIG_IGN);
}

list($parent_sock, $child_sock) = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
stream_set_blocking($parent_sock, 0);
//stream_set_blocking($child_sock, 0);

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

            $isfull = true;
            do {
                $read = array($parent_sock);
                if (@stream_select($read, $write, $e, 0, 10000) > 0) {
                    foreach ($read as $chread) {
                        if (($pid = fgets($chread))) {
                            foreach ($child_pool as $pk => $v) {
                                if ($v == $pid) {
                                    $child_pool[$pk] = 0;
                                }
                            }
                        }
                    }
                }
                
                foreach ($child_pool as $pk => $v) {
                    if ($v === 0) {
                        $isfull = false;
                        $emptykey = $pk;
                        break;
                    }
                }
                usleep(10000);
            } while ($isfull);

            $pid = pcntl_fork();

            if ($pid > 0) {
                $childnum++;
                $ischild = false;
                
                $child_pool[$emptykey] = $pid;
                usleep(10000);
                continue;
            } else if ($pid < 0) {
                $disablefork = true;
                $ischild = false;
                echo 'err';
                die;
            } else {
                //fclose($parent_sock);
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
            if(empty($cerInfo['extensions']['subjectKeyIdentifier'])) {
                continue;
            }
            $ipkey = $cerInfo['extensions']['subjectKeyIdentifier'];

            $dms = explode(',', $d);
            writer_a_rec($upgoogle, 'google.com', '@', '07:9A:CE:FB:13:97:D9:C7:E2:8E:DD:3B:F2:16:36:B5:9A:EF:B6:99');
            check_ip_domain($upgoogle, 'www.google.com', null, '9E:9F:AE:7B:6E:20:8A:68:22:ED:81:36:DA:97:5E:50:2A:73:A3:C3');
            $gm_check_key = '4A:38:1F:6A:25:E2:0A:C9:4A:EC:05:0E:17:3C:32:FA:89:56:28:B6';
            check_ip_domain($upgoogle, 'mail.google.com', null, $gm_check_key);
            check_ip_domain($upgoogle, 'inbox.google.com', null, $gm_check_key);
            check_ip_domain($upgoogle, 'accounts.google.com', null, '58:1E:61:FF:39:01:FD:E1:43:02:05:6C:84:95:2F:2A:10:32:BD:4C');
            check_ip_domain($upgoogle, 'm.google.com', null, '27:42:2D:12:83:34:C4:88:EE:68:1C:71:FF:E7:31:B7:5E:CA:4B:F0');
            check_ip_domain($upgoogle, 'checkout.google.com', null, '42:2B:E3:5B:05:9F:74:E6:E5:EB:84:B9:0A:34:9D:C6:79:08:2C:F9');
            check_ip_domain($upgoogle, 'talk.google.com', null, '07:9A:CE:FB:13:97:D9:C7:E2:8E:DD:3B:F2:16:36:B5:9A:EF:B6:99');
            $gc_check_key = '07:9A:CE:FB:13:97:D9:C7:E2:8E:DD:3B:F2:16:36:B5:9A:EF:B6:99';
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
            check_ip_domain($upgoogle, 'translate.google.com', '*.google.com', $gc_check_key);
            check_ip_list($upgoogle, 'encrypted-tbn', 0, 3, '.google.com', '*.google.com', $gc_check_key);
            check_ip_list($upgoogle, 'drive', 0, 9, '.google.com', '*.google.com', $gc_check_key);
            writer_a_rec($upgoogle, '*.google.com', '*', 'B2:2F:73:DA:F5:BA:E8:29:2A:CF:46:FD:ED:94:86:7E:1D:D7:C6:30');

            writer_a_rec($clientsgoogle, '*.clients.google.com', '*');

            $gst_check_key = 'A5:F4:B3:DD:B9:29:9C:2E:50:A1:2A:17:45:3C:9C:70:01:EF:56:2A';
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
            writer_a_rec($gstatic, '*.gstatic.com', '*', $gst_check_key);

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
            writer_a_rec($ggpht, '*.ggpht.com', '*', $gst_check_key);

            $gapi_check_key = 'C5:88:7C:4C:4D:AC:7F:AA:48:B5:D6:2B:AA:34:DD:97:B4:2A:3B:5E';
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
            fclose($r);
        }

        if ($ischild) {
            fwrite($child_sock, posix_getpid() . PHP_EOL);
            //fgets($child_sock);
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

function writer_google_default() {
    global $stroe, $nsgoogleinfo;
    $writerfp = fopen("{$stroe}google.com.zone", 'w');
    fwrite($writerfp, $nsgoogleinfo);
    return $writerfp;
}

function writer_a_rec(&$fp, $domain, $rec = '*', $key = null) {
    global $dms, $ipkey, $ip, $skip;
    if ($skip) {
        $skip = false;
        return;
    }
    //echo posix_getpid() .'|' . time() .PHP_EOL;
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
    if (!$fp) {
        $skip = true;
        return false;
    }
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
