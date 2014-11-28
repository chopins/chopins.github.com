#!/bin/env php
<?php

date_default_timezone_set('UTC');
$GLOBALS['update'] = 0;
$stroe = __DIR__.'/dnsdata/';
if(!is_dir($stroe)) {
	mkdir($stroe);
}
if(isset($argv[1]) && $argv[1] == 'dns') {
	$GLOBALS['update'] = 1;
}
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
$fp = fopen("{$stroe}iplist.txt", 'w');
$upgoogle = fopen("{$stroe}google.com.zone", 'w');
$gstatic = fopen("{$stroe}gstatic.com.zone", 'w');
$googleusercontent = fopen("{$stroe}googleusercontent.com.zone", 'w');
$youtube = fopen("{$stroe}youtube.com.zone", 'w');
$ggpht = fopen("{$stroe}ggpht.com.zone", 'w');
$default_zone = <<<EOF
\$TTL    86400
@       1D IN SOA @ root (
                          42          ; serial (d. adams)
                          3H          ; refresh 
                          15M        ; retry      
                          1W         ; expiry  
                          1D )        ; minimum   
                        1D IN NS         @

EOF;
fwrite($upgoogle, $default_zone);
fwrite($gstatic, $default_zone);
fwrite($googleusercontent, $default_zone);
fwrite($youtube, $default_zone);
fwrite($ggpht, $default_zone);
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
            $d = str_replace(array('DNS:',' '), '', $cerInfo['extensions']['subjectAltName']);
            //echo "\n\033[1;32mIP $ip Valid Domain:\033[0m {$d}\n";
			fwrite($fp, "IP $ip Valid Domain: {$d}\n");
			if($GLOBALS['update']) {
				$dms = explode(',', $d);
				
				if(in_array('google.com', $dms)) {
					fwrite($upgoogle, "@ A IN $ip\n");
				}
				if(in_array('www.google.com', $dms)) {
					fwrite($upgoogle, "www A IN $ip\n");
				}
				if(in_array('mail.google.com', $dms)) {
					fwrite($upgoogle, "mail A IN $ip\n");
				}
				if(in_array('inbox.google.com', $dms)) {
					fwrite($upgoogle, "inbox A IN $ip\n");
				}
				if(in_array('accounts.google.com', $dms)) {
					fwrite($upgoogle, "accounts A IN $ip\n");
				}
				if(in_array('m.google.com', $dms)) {
					fwrite($upgoogle, "m A IN $ip\n");
				}
				if(in_array('*.google.com', $dms)) {
					fwrite($upgoogle, "* A IN $ip\n");
				}
				if(in_array('gstatic.com', $dms)) {
					fwrite($gstatic, "@ A IN $ip\n");
				}
				if(in_array('*.gstatic.com', $dms)) {
					fwrite($gstatic, "* A IN $ip\n");
				}
				if(in_array('googleusercontent.com', $dms)) {
					fwrite($googleusercontent, "@ A IN $ip\n");
				}
				if(in_array('*.googleusercontent.com', $dms)) {
					fwrite($googleusercontent, "* A IN $ip\n");
				}
				if(in_array('youtube.com', $dms)) {
					fwrite($youtube, "@ A IN $ip\n");
				}
				if(in_array('youtube.com', $dms)) {
					fwrite($youtube, "@ A IN $ip\n");
				}
				if(in_array('*.youtube.com', $dms)) {
					fwrite($youtube, "* A IN $ip\n");
				}
				if(in_array('ggpht.com', $dms)) {
					fwrite($ggpht, "@ A IN $ip\n");
				}
				if(in_array('*.ggpht.com', $dms)) {
					fwrite($ggpht, "* A IN $ip\n");
				}
			}
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

