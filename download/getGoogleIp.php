#!/bin/env php
<?php

date_default_timezone_set('UTC');

$block = dns_get_record('_netblocks.google.com', DNS_TXT);

if(!$block && is_array($block)) {
	exec('nslookup -q=txt _netblocks.google.com 8.8.8.8', $output);
    $txt = explode('"',$output[4]);
    $block[0]['txt'] = $txt[1];
} elseif($block === false) {
    die("Get Google DNS record error\n");
}


$txt = $block[0]['txt'];

$blockList = explode('ip4:', $txt);
array_shift($blockList);
$fp = fopen('./iplist.txt', 'w');
$g = stream_context_create(array("ssl" => array("capture_peer_cert" => true)));
$disablefork = false;
$childnum = 0;
$ischild = false;
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
	
	$startip = long2ip($start);
	$endip = long2ip($maxip - 1);
	$str = "IP BLOCK:{$ipblock} IN { {$startip} -- {$endip} }";
	echo "\n\033[1m{$str}\033[0m\n";
	fwrite($fp, "$str\n");
    for ($i = $start; $i < $maxip; $i++) {
        $ip = long2ip($i);
        if (!$disablefork && function_exists('pcntl_fork')) {
            if ($childnum >= 20) {
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
            $d = str_replace('DNS:', '', $cerInfo['extensions']['subjectAltName']);
            echo "\n\033[1;32mIP $ip Valid Domain:\033[0m {$d}\n";
			fwrite($fp, "IP $ip Valid Domain: {$d}\n");
        } else {
            //echo "\n\033[1;31mCan not create SSL connect to {$ip}\033[0m\n";
            echo '+';
        }
        if ($ischild) {
            exit;
        }
    }
}
if(function_exists('pcntl_wait')) {
    pcntl_wait($start);
}

