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
$clientsgoogle = fopen("{$stroe}clients.google.com.zone", 'w');
$googleapis = fopen("{$stroe}googleapis.com.zone", 'w');
$appspot = fopen("{$stroe}appspot.com.zone", 'w');
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

fwrite($upgoogle, $default_zone);
fwrite($upgoogle, $nsgoogleinfo);
fwrite($gstatic, $default_zone);
fwrite($googleusercontent, $default_zone);
fwrite($youtube, $default_zone);
fwrite($ggpht, $default_zone);
fwrite($clientsgoogle, $default_zone);
fwrite($googleapis, $default_zone);
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
	if(strpos($ip_net, 173) === 0) {
		continue;
	}
	if(strpos($ip_net, 74) === 0) {
		continue;
	}
	if(strpos($ip_net, 72) === 0) {
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
            $d = str_replace(array('DNS:',' '), '', $cerInfo['extensions']['subjectAltName']);
            //echo "\n\033[1;32mIP $ip Valid Domain:\033[0m {$d}\n";
			fwrite($fp, "IP $ip Valid Domain: {$d}\n");
			$ipkey = $cerInfo['extensions']['subjectKeyIdentifier'];
			if($GLOBALS['update']) {
				$dms = explode(',', $d);
				if(in_array('google.com', $dms) && $ipkey == 'B2:2F:73:DA:F5:BA:E8:29:2A:CF:46:FD:ED:94:86:7E:1D:D7:C6:30') {
					fwrite($upgoogle, "@ IN A $ip\n");
				}
				if(in_array('www.google.com', $dms) && $ipkey == '1C:9B:D9:53:86:31:BD:BA:2E:84:20:34:4C:94:08:42:61:4F:BC:A5') {
					fwrite($upgoogle, "www IN A $ip\n");
				}
				if(in_array('mail.google.com', $dms) && $ipkey == 'AD:34:D3:F1:AB:19:A3:A6:F0:95:19:F3:11:FC:60:D0:03:30:F7:F2') {
					fwrite($upgoogle, "mail IN A $ip\n");
				}
				if(in_array('inbox.google.com', $dms) 
						&& $ipkey == 'AD:34:D3:F1:AB:19:A3:A6:F0:95:19:F3:11:FC:60:D0:03:30:F7:F2') {
					fwrite($upgoogle, "inbox IN A $ip\n");
				}
				if(in_array('accounts.google.com', $dms) 
						&& $ipkey == '52:22:A0:83:88:16:D5:6B:52:71:93:E5:A7:A3:4D:92:04:F4:B4:B5') {
					fwrite($upgoogle, "accounts IN A $ip\n");
				}
				if(in_array('m.google.com', $dms) && $ipkey == '4B:B0:88:82:B5:30:D2:70:51:F7:A7:36:E2:0A:23:24:89:56:1F:09') {
					fwrite($upgoogle, "m IN A $ip\n");
				}
				if(in_array('checkout.google.com', $dms) && $ipkey == '4C:95:00:A2:42:BE:C4:46:FB:30:87:00:BB:E9:0E:0F:44:1C:31:CA') {
					fwrite($upgoogle, "checkout IN A $ip\n");
				}
				if(in_array('talk.google.com', $dms) && $ipkey == '23:A9:E5:D1:E0:C4:FC:C5:4B:AB:2D:DA:DC:9E:BC:B1:30:1B:C9:82') {
					fwrite($upgoogle, "talk IN A $ip\n");
				}
				if(in_array('*.google.com', $dms) && $ipkey == 'B2:2F:73:DA:F5:BA:E8:29:2A:CF:46:FD:ED:94:86:7E:1D:D7:C6:30') {
					fwrite($upgoogle, "* IN A $ip\n");
				} 
				
				if(in_array('*.clients.google.com', $dms)) {
					fwrite($clientsgoogle, "@ IN A $ip\n");
				}
				if(in_array('gstatic.com', $dms) && $ipkey == '86:48:A7:95:30:1B:24:A6:E5:D8:E2:50:1E:A1:9B:C0:03:FE:C3:9C') {
					fwrite($gstatic, "@ IN A $ip\n");
				}
				
				if($d == '*.gstatic.com,*.metric.gstatic.com,gstatic.com' && $ipkey == '86:48:A7:95:30:1B:24:A6:E5:D8:E2:50:1E:A1:9B:C0:03:FE:C3:9C') {
					fwrite($gstatic, "* IN A $ip\n");
				} 
				if(in_array('googleusercontent.com', $dms)) {
					fwrite($googleusercontent, "@ IN A $ip\n");
				}
				
				if(in_array('youtube.com', $dms)) {
					fwrite($youtube, "@ IN A $ip\n");
				}
				if(in_array('*.youtube.com', $dms) && $ipkey == 'B2:2F:73:DA:F5:BA:E8:29:2A:CF:46:FD:ED:94:86:7E:1D:D7:C6:30') {
					fwrite($youtube, "* IN A $ip\n");
				}
				
				if(in_array('ggpht.com', $dms)) {
					fwrite($ggpht, "@ IN A $ip\n");
				}
				if(in_array('*.ggpht.com', $dms) && $ipkey == 'F3:68:50:66:D5:73:D0:1E:35:B9:33:B7:E1:54:7F:6C:73:AB:E8:F0') {
					fwrite($ggpht, "* IN A $ip\n");
					fwrite($googleusercontent, "* IN A $ip\n");
				}
				if(in_array('*.googleapis.com', $dms) 
						&& $ipkey == '71:E5:C2:3A:56:1F:2C:AE:19:CB:51:FD:FD:FF:C4:45:D2:DD:EB:75') {
					fwrite($googleapis, "* IN A $ip\n");
				}
				if(in_array('*.appspot.com', $dms)) {
					fwrite($appspot, "* IN A $ip\n");
				}
			}
        } else {
            //echo "\n\033[1;31mCan not create SSL connect to {$ip}\033[0m\n";
            //echo '+';
        }
        if ($ischild) {
            exit;
        }
    }
}
if(function_exists('pcntl_wait')) {
    pcntl_wait($start);
}

