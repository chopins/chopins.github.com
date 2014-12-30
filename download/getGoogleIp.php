#!/bin/env php
<?php
date_default_timezone_set('UTC');

$per_ip_block_process_num = 5;

if (!extension_loaded('openssl')) {
    exit('need openssl extension to support https' . PHP_EOL);
}

if (!extension_loaded('pcntl')) {
    exit('need pcntl extension to support multi-process' . PHP_EOL);
}

$start_time = time();
$stroe = $_SERVER['PWD'] . '/dnsdata/';
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
$all_skip = false;
$ip_list_fp = fopen("{$stroe}iplist.txt", 'w');
$ban_ip_fp = fopen("{$stroe}ban_ip.list", 'a+');
$ban_ip_list = array_unique(file("{$stroe}ban_ip.list"));
file_put_contents("{$stroe}ban_ip.list", join($ban_ip_list));
$ban_ip_count = count($ban_ip_list);
$process_num_file = fopen("{$stroe}process_num.size", 'w');
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
$ischild = false;

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


//stream_set_blocking($child_sock, 0);

$sum_ip_num = 0;

foreach ($blockList as $ipblock) {
    list($ip_net, $maskbit) = explode('/', $ipblock);
    $start = ip2long($ip_net) + 1;
    $maxip = $start | (4294967295 >> $maskbit);
    $sum_ip_num += ($maxip - $start);
    $startip = long2ip($start);
    $endip = long2ip($maxip - 1);
    $str = "IP BLOCK:{$ipblock} IN { {$startip} -- {$endip} }";
    //echo "\n\033[1m{$str}\033[0m\n";

    fwrite($ip_list_fp, "$str\n");
    $pid = pcntl_fork();
    if ($pid > 0) {
        continue;
    } else if ($pid < 0) {
        exit('pcntl error');
    }
    $child_pool = array_fill(0, $per_ip_block_process_num, 0);
    list($parent_sock, $child_sock) = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    stream_set_blocking($parent_sock, 0);
    for ($i = $start; $i < $maxip; $i = $i + 100) {
        if ($i > 0 && $i % 1000 == 0 && all_record_ok()) {
            break;
        }
        $longip_list = range($i, $i + 100);
        $dif_iplist = array_map('long2ip', $longip_list);
        $available_ip = array_diff($dif_iplist, $ban_ip_list);
        if (empty($available_ip)) {
            continue;
        }
        foreach ($available_ip as $ip) {
            $isfull = true;
            do {
                $read = array($parent_sock);
                if (@stream_select($read, $write, $e, 0, 100000) > 0) {
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
                usleep(100000);
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


            $r = @stream_socket_client("ssl://$ip:443", $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $g);
            if ($r) {
                $cont = stream_context_get_params($r);
                $cerInfo = openssl_x509_parse($cont["options"]["ssl"]["peer_certificate"]);

                if (empty($cerInfo['extensions']['subjectKeyIdentifier'])) {
                    fwrite($ban_ip_fp, $ip . PHP_EOL);
                    process_exit();
                } else if (empty($cerInfo['extensions']['subjectAltName'])) {
                    fwrite($ban_ip_fp, $ip . PHP_EOL);
                    process_exit();
                }
                $d = str_replace(array('DNS:', ' '), '', $cerInfo['extensions']['subjectAltName']);
                if (strpos($d, 'postini') !== false) {
                    fwrite($ban_ip_fp, $ip . PHP_EOL);
                    process_exit();
                }

                fwrite($ip_list_fp, "IP $ip Valid Domain: {$d}\n");
                $ipsn = $cerInfo['serialNumber'];
                fclose($r);

                $dms = explode(',', $d);
                check_cert_info();
            } else {
                fwrite($ban_ip_fp, $ip . PHP_EOL);
            }

            if ($ischild) {
                process_exit();
            }
        }
    }
    exit;
}

if (function_exists('pcntl_wait')) {
    $sum_ip_num = $sum_ip_num - $ban_ip_count;
    $start_time_date = date('Y-m-d H:i:s T', $start_time);
    echo "Count Ip:$sum_ip_num,Start time:$start_time_date\n";
    file_put_contents("{$stroe}ipcount", $sum_ip_num);
    pcntl_wait($start);
}

function process_exit() {
    extract($GLOBALS);
    fwrite($process_num_file, 1);
    fwrite($child_sock, posix_getpid() . PHP_EOL);
    $time_con = time() - $start_time;
    $h = floor($time_con / 3600);
    $muinte = $time_con % 3600;
    $m = floor($muinte / 60);
    $s = $muinte % 60;
    $size = filesize("{$stroe}process_num.size");
    echo "\rComplete Ip:$size,Run time:($h:$m:$s)";
    exit;
}

function check_cert_info() {
    extract($GLOBALS);

    $all_skip = false;
    $gc_sn = '8924155373108256736';
    $www_sn = '3560456681580786076';
    $gm_sn = '7810596706032786260';
    $account_sn = '3059601525857341613';
    $checkout_sn = '2412261313296579961';
    $google_m_sn = '7422046862831836265';
    $guc_sn = '1423458468341525840';
    $gst_sn = '4629298540228774186';
    $gapi_sn = '9025913279998864902';
    writer_a_rec($upgoogle, 'google.com', '@', $gc_sn);

    check_ip_domain($upgoogle, 'www.google.com', null, $www_sn);
    $mail_list = array('mail', 'inbox');
    foreach ($mail_list as $mail_pre) {
        check_ip_domain($upgoogle, "{$mail_pre}.google.com", null, $gm_sn);
    }

    check_ip_domain($upgoogle, 'accounts.google.com', null, $account_sn);
    check_ip_domain($upgoogle, 'm.google.com', null, $google_m_sn);
    check_ip_domain($upgoogle, 'checkout.google.com', null, $checkout_sn);

    $gc_list = array('talk', 'plus', 'play', 'id', 'groups', 'images', 'code', 'map', 'maps', 'news', 'upload', 'dirve', 'encrypted', 'translate', 'clients2');
    foreach ($gc_list as $domain_pre) {
        check_ip_domain($upgoogle, "{$domain_pre}.google.com", '*.google.com', $gc_sn);
    }

    check_ip_list($upgoogle, 'encrypted-tbn', 0, 3, '.google.com', '*.google.com', $gc_sn);

    writer_a_rec($upgoogle, '*.google.com', '*', $gc_sn);

    writer_a_rec($clientsgoogle, '*.clients.google.com', '*');

    writer_a_rec($gstatic, 'gstatic.com', '@', $gst_sn);
    $gstatc_list = array('ssl', 'fonts', 'csi', 'maps', 'www');
    foreach ($gstatc_list as $gstatic_pre) {
        check_ip_domain($gstatic, "{$gstatic_pre}.gstatic.com", '*.gstatic.com', $gst_sn);
    }

    check_ip_list($gstatic, 'g', 0, 3, '.gstatic.com', '*.gstatic.com', $gst_sn);
    check_ip_list($gstatic, 'mt', 0, 7, '.gstatic.com', '*.gstatic.com', $gst_sn);
    check_ip_list($gstatic, 't', 0, 3, '.gstatic.com', '*.gstatic.com', $gst_sn);
    check_ip_list($gstatic, 'encrypted-tbn', 0, 3, '.gstatic.com', '*.gstatic.com', $gst_sn);
    writer_a_rec($gstatic, '*.gstatic.com', '*', $gst_sn);

    writer_a_rec($googleusercontent, 'googleusercontent.com', '@');
    writer_a_rec($googleusercontent, '*.googleusercontent.com', '*', $guc_sn);

    writer_a_rec($youtube, 'youtube.com');
    $youtube_list = array('www', 'accounts', 'help', 'm', 'insight');
    foreach ($youtube_list as $youtube_pre) {
        check_ip_domain($youtube, "{$youtube_pre}.youtube.com", '*.youtube.comm', $gc_sn);
    }

    writer_a_rec($youtube, '*.youtube.com', '*', $gc_sn);

    writer_a_rec($ggpht, 'ggpht.com', '@');
    check_ip_list($ggpht, 'lh', 3, 6, '.ggpht.com', '*.ggpht.com', $guc_sn);
    check_ip_list($ggpht, 'gm', 1, 4, '.ggpht.com', '*.ggpht.com', $guc_sn);
    check_ip_list($ggpht, 'geo', 1, 3, '.ggpht.com', '*.ggpht.com', $guc_sn);
    writer_a_rec($ggpht, '*.ggpht.com', '*', $guc_sn);

    $api_list = array('ajax', 'fonts', 'chart', 'maps', 'www', 'play', 'translate', 'youtube', 'content', 'bigcache', 'storage', 'android', 'redirector-bigcache', 'commondatastorage');
    $api_suffix = '.googleapis.com';
    foreach ($api_list as $api_pre) {
        check_ip_domain($googleapis, "{$api_pre}{$api_suffix}", "*{$api_suffix}", $gapi_sn);
    }

    check_ip_list($googleapis, 'mt', 0, 3, '.googleapis.com', '*.googleapis.com', $gst_sn);
    check_ip_list($googleapis, 'mt', 0, 3, '.googleapis.com', '*.googleapis.com', $gst_sn);
    check_ip_list($googleapis, 'mt', 0, 3, '.googleapis.com', '*.googleapis.com', $gst_sn);
    writer_a_rec($googleapis, '*.googleapis.com', '*', $gapi_sn);

    writer_a_rec($appspot, '*.appspot.com');

    writer_a_rec($googlevideo, '*.googlevideo.com', '*', $gc_sn);

    writer_a_rec($ytimg, 'ytimg.com', '@');
    check_ip_domain($ytimg, 'i.ytimg.com', '*.ytimg.com', $gc_sn);
    check_ip_list($ytimg, 'i', 1, 4, '.ytimg.com', '*.ytimg.com', $gc_sn);
    check_ip_domain($ytimg, 's.ytimg.com', '*.ytimg.com', $gc_sn);

    writer_a_rec($ytimg, '*.ytimg.com', '*', $gc_sn);
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

function all_record_ok() {
    extract($GLOBALS);
    $google_pre = array('@', '*', 'www', 'mail', 'inbox', 'accounts', 'm', 'checkout', 'talk', 'plus', 'play', 'id', 'groups', 'images', 'code', 'map', 'maps', 'news', 'upload', 'dirve', 'encrypted', 'translate', 'clients2');
    $gstatic_pre = array('@', '*', 'ssl', 'fonts', 'csi', 'maps', 'www');

    foreach ($google_pre as $rec) {
        if (!check_record_num($upgoogle, $rec)) {
            return false;
        }
    }
    foreach ($gstatic_pre as $rec) {
        if (!check_record_num($gstatic, $rec)) {
            return false;
        }
    }
    $guc = array('@', '*');
    foreach ($guc as $rec) {
        if (!check_record_num($googleusercontent, $rec)) {
            return false;
        }
    }
    $youtube_pre = array('@', '*', 'www', 'accounts', 'help', 'm', 'insight');
    foreach ($youtube_pre as $rec) {
        if (!check_record_num($youtube, $rec)) {
            return false;
        }
    }

    if (!check_record_num($ggpht, '*')) {
        return false;
    }
    if (!check_record_num($ggpht, '@')) {
        return false;
    }
    if (!check_record_num($clientsgoogle, '*')) {
        return false;
    }
    $api_pre = array('*', '@', 'ajax', 'fonts', 'chart', 'maps', 'www', 'play', 'translate', 'youtube', 'content', 'bigcache', 'storage', 'android', 'redirector-bigcache', 'commondatastorage');
    foreach ($api_pre as $rec) {
        if (!check_record_num($googleapis, $rec)) {
            return false;
        }
    }
    if (!check_record_num($appspot, '*')) {
        return false;
    }
    if (!check_record_num($googlevideo, '*')) {
        return false;
    }
    if (!check_record_num($ytimg, '*')) {
        return false;
    }
    return true;
}

function writer_a_rec(&$fp, $domain, $rec = '*', $key = null) {
    extract($GLOBALS);
    if ($all_skip) {
        return;
    }
    if (check_record_num($fp, $rec)) {
        return;
    }
    if ($key === null) {
        if (in_array($domain, $dms)) {
            fwrite($fp, "$rec IN A $ip\n");
        }
        return;
    }

    if (in_array($domain, $dms) && $ipsn == $key) {
        fwrite($fp, "$rec IN A $ip\n");
    }
}

function check_ip($domain) {
    extract($GLOBALS);
    $opts = array(
        'http' => array(
            'method' => "GET",
            'header' => "Host: $domain\r\nConnection: close\r\n",
            'follow_location' => 0,
            'ignore_errors' => 1,
            'timeout' => 3,
            'protocol_version' => 1.1,
            'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:33.0)'
        )
    );
    $context = stream_context_create($opts);
    $fp = @fopen("https://$ip/", 'r', false, $context);
    if (!$fp) {
        return false;
    }

    $meta = stream_get_meta_data($fp);
    if ($meta['unread_bytes'] == 0) {
        fclose($fp);
        fwrite($ban_ip_fp, $ip . PHP_EOL);
        $all_skip = true;
        return false;
    }
    fread($fp, $meta['unread_bytes']);
    fclose($fp);
    if (count($meta['wrapper_data']) < 3) {
        return false;
    }
    list(, $http_code) = explode(' ', $meta['wrapper_data'][0]);

    if (!in_array($http_code, array('200', '301', '302'))) {
        return false;
    }

    return true;
}

function check_ip_list(&$fp, $pre, $start, $end, $check_suffix, $domain, $key = null) {
    extract($GLOBALS);
    if ($all_skip) {
        return;
    }
    for ($i = $start; $i < $end + 1; $i++) {
        $check_domain = $pre . $i . $check_suffix;
        check_ip_domain($fp, $check_domain, $domain, $key);
    }
}

function check_record_num(&$fp, $rec) {
    $meta = stream_get_meta_data($fp);
    $f = fopen($meta['uri'], 'r');
    $num = 0;
    while (!feof($f)) {
        $line = fgets($f);
        if (strpos($line, "$rec IN A") !== false) {
            $num++;
            if ($num > 10) {
                return true;
            }
        }
    }
    return false;
}

function check_ip_domain(&$fp, $check, $domain = null, $key = null) {
    extract($GLOBALS);
    usleep(100000);
    if ($all_skip) {
        return;
    }
    if ($domain === null) {
        $domain = $check;
    }

    if (!in_array($domain, $dms)) {
        return;
    }

    $domain_split = explode('.', $check);
    if (count($domain_split) == 2) {
        $rec = '@';
    } else {
        $rec = $domain_split[0];
    }
    if (check_record_num($fp, $rec)) {
        return;
    }
    if (check_ip($check)) {
        writer_a_rec($fp, $domain, $rec, $key);
    }
}
