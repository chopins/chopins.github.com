#!/bin/env php
<?php

date_default_timezone_set('UTC');

$block = dns_get_record('_netblocks.google.com', DNS_TXT);

if(!$block) {
	die("Get Google DNS record error, Set DNS server to 8.8.8.8 for your host\n");
}

$txt = $block[0]['txt'];

$blockList = explode('ip4:', $txt);
array_shift($blockList);

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
    list($ip_prefix, $maskbit) = explode('0/', $ipblock);
    $maxip = 4294967295 - (4294967295 >> (32 - 26) << (32 - 26));
    $start = 1;
    echo "\n\033[1mIP BLOCK:{$ipblock} IN { {$ip_prefix}{$start} -- {$ip_prefix}{$maxip} }\033[0m\n";
    for ($i = $start; $i < $maxip; $i++) {
        $ip = "{$ip_prefix}{$i}";
        if (!$disablefork && function_exists('pcntl_fork')) {
            if ($childnum >= 5) {
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
        $r = @stream_socket_client("ssl://$ip:443", $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $g);
        if ($r) {
            $cont = stream_context_get_params($r);
            $cerInfo = openssl_x509_parse($cont["options"]["ssl"]["peer_certificate"]);
            $d = str_replace('DNS:', '', $cerInfo['extensions']['subjectAltName']);
            echo "\n\033[1;32mIP $ip Valid Domain:\033[0m {$d}\n";
        } else {
            //echo "\n\033[1;31mCan not create SSL connect to {$ip}\033[0m\n";
            echo '+';
        }
        if ($ischild) {
            exit;
        }
    }
}

function fork() {
    
}
