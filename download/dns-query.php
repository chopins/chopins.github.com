<?php
ini_set('error_reporting', 0);
ini_set('log_errors', 1);
ini_set("html_errors", 0);
ini_set('display_errors', 1);
define('RDIR', __DIR__);
ini_set('error_log', RDIR . '/logs/php-error.logs');
class DnsQuery
{
    public $accept = 'json';
    public $requestType = 'json';
    public $transId = '';
    public $rrtype = self::RR_ANY;
    public $rropcode = 0;
    public $unsupport = false;
    public $queryName = [];
    public $queryData = '';
    public $dnsHost = self::DNS_HOSTS['Default'];
    public $enableDOH = true;
    public $timeout = 3;
    public $localRR = [
        'host.godaddy.com' => [
            1 => ['35.154.51.163', '65.2.72.240']
        ],
    ];
    private static $logfp;
    public static $logs = [];
    public static $requestDatetime;
    public static $domainDns = [
        'CF' => [
            'github.com',
            'google.com',
            'gstatic.com',
            'elastic.co'
        ]
    ];
    const DNS_HOSTS = [
        'Default' => 'udp://127.0.0.53:53',
        'CF' => 'https://1.1.1.1/dns-query',
        'TX' => 'https://doh.pub/dns-query',
    ];
    const P_H_ID = 1;
    const P_H_FLAG = self::P_H_ID + 1;
    const P_H_QUESTION = self::P_H_FLAG + 1;
    const P_H_ANSWER = self::P_H_QUESTION + 1;
    const P_H_AUTHORITY = self::P_H_ANSWER + 1;
    const P_H_ADDITIONAL = self::P_H_AUTHORITY + 1;
    const P_QUERIES = self::P_H_ADDITIONAL + 1;
    const P_ANSWERS = self::P_QUERIES + 1;
    const P_AUTHORITY = self::P_ANSWERS + 1;
    const P_ADDITIONAL = self::P_AUTHORITY + 1;
    const P_RR_NAME = self::P_ADDITIONAL + 1;
    const P_RR_TYPE = self::P_RR_NAME + 1;
    const P_RR_CLASS = self::P_RR_TYPE + 1;
    const P_RR_TTL = self::P_RR_CLASS + 1;
    const P_RR_DATA_LEN = self::P_RR_TTL + 1;
    const P_RR_DATA = self::P_RR_DATA_LEN + 1;

    const P_RR_OPT_RCODE = self::P_RR_TTL;
    const P_RR_OPT_UDP_SIZE = self::P_RR_CLASS;
    const P_RR_OPT_E_V = self::P_RR_OPT_RCODE + 1;
    const P_RR_OPT_Z = self::P_RR_OPT_E_V + 1;
    const P_RR_OPT_DATA = self::P_RR_OPT_Z + 1;

    const CLASS_IN = 1;
    const CLASS_CS = 2;
    const CLASS_CH = 3;
    const CLASS_HS = 4;
    const RR_ANY = 0;
    const RR_A = 1;
    const RR_NS = 2;
    const RR_MD = 3;
    const RR_MF = 4;
    const RR_CNAME = 5;
    const RR_SOV = 6;
    const RR_MB = 7;
    const RR_MG = 8;
    const RR_MR = 9;
    const RR_NULL = 10;
    const RR_WKS = 11;
    const RR_PTR = 12;
    const RR_HINFO = 13;
    const RR_MINFO = 14;
    const RR_MX = 15;
    const RR_TXT = 16;
    const RR_AAAA = 28;
    const RR_SRV = 33;
    const RR_MAPTR = 35;
    const RR_APL = 36;
    const RR_DSIG = 37;
    const RR_DNAME = 39;
    const RR_OPT = 41;
    const RR_DS = 43;
    const RR_RRSIG = 46;
    const RR_DNSKEY = 48;
    const RR_CDS = 59;
    const RR_OPENPGPKEY = 61;
    const RR_SVCB = 65;
    const RR_HTTPS = 66;
    const RR_CAA = 257;
    const RR_TYPE = [
        self::RR_ANY,
        self::RR_A,
        self::RR_NS,
        self::RR_MD,
        self::RR_MF,
        self::RR_CNAME,
        self::RR_SOV,
        self::RR_MB,
        self::RR_MG,
        self::RR_MR,
        self::RR_NULL,
        self::RR_WKS,
        self::RR_PTR,
        self::RR_HINFO,
        self::RR_MINFO,
        self::RR_MX,
        self::RR_TXT, //16
        self::RR_AAAA,
        self::RR_SRV,
        self::RR_MAPTR,
        self::RR_APL,
        self::RR_DSIG,
        self::RR_DNAME,
        self::RR_OPT,
        self::RR_DS,
        self::RR_RRSIG,
        self::RR_DNSKEY,
        self::RR_CDS,
        self::RR_OPENPGPKEY,
        self::RR_SVCB,
        self::RR_HTTPS,
        self::RR_CAA,
    ];

    public const G_CACHE_NAME_TYPE = [
        self::RR_A,
        self::RR_NS,
        self::RR_CNAME,
        self::RR_MX,
        self::RR_AAAA,
        self::RR_HTTPS,
    ];
    const G_RR_DATA_NAME = [self::RR_CNAME, self::RR_MX, self::RR_NS];
    public function __construct()
    {
        self::$requestDatetime = new DateTime();
        self::$logfp = fopen(RDIR . '/logs/dns.log-' . date('Y-m-d'), 'ab');
        if (PHP_SAPI != 'cli') {
            if ($_SERVER['HTTP_ACCEPT'] == 'application/dns-message') {
                $this->accept = 'dns-msg';
            }
            if ($_SERVER['HTTP_CONTENT_TYPE'] == 'application/dns-message') {
                $this->requestType = 'dns-msg';
            }

            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $this->queryData = $this->base64_decode($_GET['dns']);
            } else {
                $this->queryData = file_get_contents("php://input");
            }
            // $this->saveData('Q', $this->queryData);
            header('Content-Type: application/dns-message', true);
            $this->dnsClient();
        }
    }
    public static function log(...$datas)
    {
        $msg = '';
        foreach ($datas as $d) {
            $msg .= is_string($d) ? $d : json_encode($d, JSON_PRETTY_PRINT);
        }
        self::$logs[] = self::$requestDatetime->format('[H:i:s-u]') . $msg . PHP_EOL;
    }
    public function saveData($t, $data)
    {
        file_put_contents(RDIR . '/data/dns.' . $t, $data);
    }

    public function dnsClient()
    {
        $packet = $this->parseDNSPackage($this->queryData, $qstate);
        $this->transId = $packet[self::P_H_ID];
        $this->queryName = $packet[self::P_QUERIES];

        $ret = $this->getCache($packet);

        if ($ret) {
            header("Content-Length: " . strlen($ret));
            echo $ret;
            return;
        }

        $this->switchDns();

        do {
            if ($this->enableDOH) {
                $ret = $this->DOHClient($body, $responseSize, $responseInfo);
            } else {
                $ret = $this->TcpUdpClient($body, $responseSize);
            }
            if (!$ret) {
                $this->dnsHost = self::DNS_HOSTS['Default'];
                $this->enableDOH = false;
            }
        } while (!$ret);

        if (!$ret) {
            $body = $this->buildServerErrorData();
        } else {
            if (count($this->queryName) == 1) {
                $this->cacheResult($body);
            }
            //$packet = $this->parseDNSPackage($body, $astate);
        }

        header("Content-Length: " . $responseSize);
        echo $body;
    }

    public function getMinTTL($packet)
    {
        $min = 36000;
        foreach ($packet[self::P_ANSWERS] as $answer) {
            if ($answer[self::P_RR_TYPE] == self::RR_A || $answer[self::P_RR_TYPE] == self::RR_AAAA) {
                if ($answer[self::P_RR_TTL] < $min) {
                    $min = $answer[self::P_RR_TTL];
                }
            }
        }
        return $min;
    }

    public function getCache($queryPacket)
    {
        $localRecord = '';
        if ($this->localServer($localRecord, $queryPacket)) {
            return $localRecord;
        }
        $type = $this->queryName[0][self::P_RR_TYPE] . '-' . $this->queryName[0][self::P_RR_NAME];
        $file = RDIR . '/data/dns.' . $type;
        if (file_exists($file)) {
            $data = file_get_contents($file);
            $packet = $this->parseDNSPackage($data);
            $min = $this->getMinTTL($packet);
            if (filemtime($file) + $min < time()) {
                unlink($file);
                return false;
            }
            return pack('n', $this->transId) . substr($data, 2);
        }
        return false;
    }
    public function localServer(&$recordData, $queryPacket)
    {
        if (count($this->queryName) > 1) {
            return false;
        }
        $type = $this->queryName[0][self::P_RR_TYPE];
        if ($type != self::RR_A) {
            return false;
        }
        $name = $this->queryName[0][self::P_RR_NAME];
        if (isset($this->localRR[$name][$type])) {
            $packet = [];
            $packet[self::P_QUERIES] = $queryPacket[self::P_QUERIES];
            $packet[self::P_ANSWERS] = [];
            foreach ($this->localRR[$name][$type] as $r) {
                $packet[self::P_ANSWERS][] = [
                    self::P_RR_NAME => $name,
                    self::P_RR_TYPE => $type,
                    self::P_RR_CLASS => 1,
                    self::P_RR_TTL => 3600,
                    self::P_RR_DATA => $r
                ];
            }
            $packet[self::P_ADDITIONAL] = [
                self::P_RR_NAME => "\0",
                self::P_RR_TYPE => self::RR_OPT,
                self::P_RR_OPT_UDP_SIZE => 1480,
                self::P_RR_OPT_RCODE => 0,
                self::P_RR_OPT_E_V => 0,
                self::P_RR_OPT_Z => 0,
                self::P_RR_OPT_DATA => '',
            ];
            $recordData = $this->buildDNSResponse($packet);
            return true;
        }
        return false;
    }

    public function buildName($labelist, $name, &$rLabels = null)
    {
        $labels = explode('.', $name);
        $rLabels = array_reverse($labels, true);
        $ptrMaxLen = 0;
        $ptrName = '';
        foreach ($labelist as $name => $ls) {
            $ptrLen = 0;
            $findNum = 0;
            foreach ($rLabels as $i => $label) {
                if (current($ls[0]) === $label) {
                    $ptrLen += strlen($label) + 1;
                } else {
                    break;
                }
                if (next($ls[0]) === false) {
                    break;
                }
            }
            if ($ptrLen > $ptrMaxLen) {
                $ptrMaxLen = $ptrLen;
                $ptrName = $name;
            }
        }

        $binary = '';
        if ($ptrName) {
            $ptrAdd = substr($ptrName, 0, -1 * $ptrMaxLen);
            $preLen = $ptrAdd ? strlen($ptrAdd) + 1 : 0;
            $ptrPos = $labelist[$ptrName][1] + $preLen;

            $realLabels = explode('.', substr($name, 0, -1 * $ptrMaxLen));
            foreach ($realLabels as $l) {
                $binary .= chr(strlen($l)) . $l;
            }
            $binary .= "\xc0" . chr($ptrPos);
        } else { //没有指针
            foreach ($labels as $l) {
                $binary .= chr(strlen($l)) . $l;
            }
            $binary .= "\0";
        }
        return $binary;
    }

    public function buildDNSResponse($packet)
    {
        $labelist = [];
        $offset = 12;
        $binary = '';
        foreach ($packet as $zone => $answer) {
            foreach ($answer as $a) {
                $rbinary = $this->buildName($labelist, $a[self::P_RR_NAME], $rLabels);

                $rbinary .= pack('n2', $a[self::P_RR_TYPE], $a[self::P_RR_CLASS]);
                if ($zone != self::P_QUERIES) {
                    $rbinary .= $this->buildRRData($labelist, $a, strlen($rbinary));
                }
                if (in_array($a[self::P_RR_TYPE], self::G_CACHE_NAME_TYPE)) {
                    $labelist[$a[self::P_RR_NAME]] = [$rLabels, $offset];
                }
                $offset += strlen($rbinary);
            }
            $binary .= $rbinary;
        }
        return $binary;
    }

    public function buildRRDala(&$labelist, $a, $offset)
    {
        $rType = $a[self::P_RR_TYPE];
        $binary = '';

        $binary .= pack('N', $a[self::P_RR_TTL]);

        if ($rType == self::RR_A) {
            $binary .= pack('n', 4);
            $binary .= pack('N', ip2long($a[self::P_RR_DATA]));
        } else if ($rType == self::RR_AAAA) {
            $binary .= pack('n', 8);
            $binary .= pack('n8', hexdec(str_replace(':', $a[self::P_RR_DATA])));
        } else if (in_array($rType, self::G_RR_DATA_NAME)) {
            $name = rtrim($this->buildName($labelist, $a[self::P_RR_DATA], $rLabels), "\0");
            $binary .= pack('n', strlen($name));
            $binary .= $name;
            $labelist[$a[self::P_RR_DATA]] = [$rLabels, $offset + 6];
        } else if ($rType == self::RR_OPT) {
            $binary .= pack('N', $a[self::P_RR_OPT_E_V]);
            $binary .= pack('n', $a[self::P_RR_OPT_Z]);
            $optDataLen = strlen($a[self::P_RR_OPT_DATA]);
            $binary .= pack('n', $optDataLen);
            if ($optDataLen > 0) {
                $binary .= $a[self::P_RR_OPT_DATA];
            }
        }

        return $binary;
    }

    public function buildQuestionsZone($queryPacket)
    {
        $startOffset = 12;
        $binary = '';
        foreach ($queryPacket[self::P_QUERIES] as $i => $question) {
            $labels = explode('.', $question[self::P_RR_NAME]);

            $domain = [];
            foreach ($labels as $k => $label) {
                $ptrPos = strpos($binary, $label);
                if ($ptrPos !== false) {
                    $domain[] = "\xc0" . chr($ptrPos - 1 + $startOffset);
                }
                $domain[] = chr(strlen($label)) . $label;
            }

            $domain[] = "\0";
            $domain[] = pack('n2', $question[self::P_RR_TYPE], $question[self::P_RR_CLASS]);
            $binary .= join('', $domain);
        }
        return $binary;
    }

    public function buildDNS1AResponse($recordList, $type, $queryPacket)
    {

        $binary = $this->buildQuestionsZone($queryPacket);

        foreach ($recordList as $record) {
            $domain = "\xc0\x0c";
            $domain .= pack('n2', 1, 1);
            $domain .= pack('N', 3600);
            $domain .= pack('n', 4);
            $domain .= pack('N', ip2long($record));
            $binary .= $domain;
        }
        $binary .= "\0" . pack('n2', 41, 1480) . pack('N', 0) . pack('n', 0);

        $flag = 1 << 15;
        if ($queryPacket['flags']['rd']) {
            $flag |= 1 << 8;
        }
        if (strlen($binary) + 12 > $queryPacket[self::P_ADDITIONAL][0][self::P_RR_OPT_UDP_SIZE]) {
            $flag |= 1 << 9;
        }
        $headers = [$this->transId, $flag, $queryPacket[self::P_H_ANSWER], count($recordList), 0, 1];

        return pack("n*", ...$headers) . $binary;
    }

    public function cacheResult($body)
    {
        $type = $this->queryName[0]['type'] . '-' . $this->queryName[0]['name'];
        $this->saveData($type, $body);
    }

    public static function bset($bit, $size)
    {
        return ($bit & (1 << $size)) > 0 ? 1 : 0;
    }

    public function buildFlags()
    {
        $flags = [];
        $flags['qr'] = $bit << 15;
    }

    public function parseFlags($bit)
    {
        $flags = [];
        $flags['qr'] = self::bset($bit, 15);
        $flags['opcode'] = $flags['qr'] ? (($bit >> 11) ^ 16) : ($bit >> 11);
        $flags['aa'] = self::bset($bit, 10);
        $flags['tc'] = self::bset($bit, 9);
        $flags['rd'] = self::bset($bit, 8);
        $flags['ra'] = self::bset($bit, 7);
        $flags['z'] = 0;
        $flags['rcode'] = $bit & 15;
        return $flags;
    }

    public static function getDomainFromOffset($queryData, &$i, $maxLen = 254)
    {
        $start = $i;
        $labels = [];
        do {
            if ($queryData[$i] == "\0") {
                $i++;
                break;
            }
            if ($queryData[$i] == "\xc0") {
                $i++;
                $ptrOffset = ord($queryData[$i]);
                $labels[] = self::getDomainFromOffset($queryData, $ptrOffset);
                $i++;
                break;
            } else {
                $byteInt = ord($queryData[$i]);
                $i++;
                $labels[] = substr($queryData, $i, $byteInt);
                $i += $byteInt;
                continue;
            }
            $i++;
        } while (($i - $start) < $maxLen && isset($queryData[$i]));
        return join('.', $labels);
    }
    public static function toIPv6($data, &$i)
    {
        return  join(':', array_map('dechex', self::unpack('n8', $data, $i)));
    }
    public static function unpack($format, $string, &$offset = 0)
    {
        $bitSize = ['n' => 2, 'N' => 4, 'C' => 1, 'H' => 1];
        try {
            $ret = unpack($format, $string, $offset);
        } catch (ValueError $e) {
            self::log("Offset:$offset", $e->getTraceAsString());
        }
        $len  = $bitSize[$format[0]];
        if (strlen($format) > 1) {
            $len = $len * substr($format, 1);
        }
        $offset += $len;
        return $ret;
    }

    public function parseDNSPackage($queryData, &$parseStatus = true)
    {
        $parseStatus = true;
        $headers = self::unpack('n6', $queryData);
        $packet = [];
        $packet[self::P_H_ID] = $headers[1];
        $packet[self::P_H_FLAG] = $this->parseFlags($headers[2]);
        $packet[self::P_H_QUESTION] = $headers[3];
        $packet[self::P_H_ANSWER] = $headers[4];
        $packet[self::P_H_AUTHORITY] = $headers[5];
        $packet[self::P_H_ADDITIONAL] = $headers[6];
        $packet[self::P_QUERIES] = [];
        $packet[self::P_ANSWERS] = [];
        $packet[self::P_AUTHORITY] = [];
        $packet[self::P_ADDITIONAL] = [];
        $start = 12;
        $queryDataLen = strlen($queryData);
        $count = $packet[self::P_H_ANSWER] + $packet[self::P_H_ANSWER] + $packet[self::P_H_AUTHORITY] + $packet[self::P_H_ADDITIONAL];
        $authorityOffset = $packet[self::P_H_ANSWER] + $packet[self::P_H_ANSWER];
        $additionalOffset = $count - $packet[self::P_H_ADDITIONAL];
        $i = $start;

        for ($rrs = 0; $rrs < $count; $rrs++) {

            if ($i > $queryDataLen) {
                $parseStatus = false;
                self::log("OutRangeCheck:read pos $i >= data len $queryDataLen ");
                break;
            }
            $name = self::getDomainFromOffset($queryData, $i);

            $rrtype = self::unpack('n2', $queryData, $i);
            $queryName = [self::P_RR_NAME => $name, self::P_RR_TYPE => $rrtype[1], self::P_RR_CLASS => $rrtype[2]];
            $typeName = $queryName[self::P_RR_TYPE];

            if ($rrs >= $packet[self::P_H_ANSWER]) {
                $ttl = self::unpack('N', $queryData, $i)[1];
                $queryName[self::P_RR_TTL] = $ttl;
                $RDLen = self::unpack('n', $queryData, $i)[1];

                if ($typeName == self::RR_OPT) {
                    $queryName[self::P_RR_OPT_UDP_SIZE] = $queryName[self::P_RR_CLASS];
                    $queryName[self::P_RR_OPT_RCODE] = $queryName[self::P_RR_TTL];
                }
                $queryName[self::P_RR_DATA_LEN] = $RDLen;

                if (in_array($typeName, self::G_RR_DATA_NAME)) {
                    $data = self::getDomainFromOffset($queryData, $i, $RDLen);
                } else if ($typeName == self::RR_A) {
                    $data = long2ip(self::unpack('N', $queryData, $i)[1]);
                } else if ($typeName == self::RR_AAAA) {
                    $data = self::toIPv6($queryData, $i);
                } else {
                    $data = self::unpack("H{$RDLen}", $queryData, $i);
                }
                $queryName[self::P_RR_DATA] = $data;
            }

            if ($packet[self::P_H_ADDITIONAL] && $rrs >= $additionalOffset) {
                $packet[self::P_ADDITIONAL][] = $queryName;
            } else if ($packet[self::P_H_AUTHORITY] && $rrs >= $authorityOffset) {
                $packet[self::P_AUTHORITY][] = $queryName;
            } else if ($packet[self::P_H_ANSWER] && $rrs >= $packet[self::P_H_ANSWER]) {
                $packet[self::P_ANSWERS][] = $queryName;
            } else {
                $packet[self::P_QUERIES][] = $queryName;
            }

            $start = $i;
        }
        if ($queryDataLen != $i) {
            self::log("EndCheck, Last pos $i != data len $queryDataLen ");
            $parseStatus = false;
        }
        return $packet;
    }

    public function switchDns()
    {
        self::log('Query:', $this->queryName);
        foreach (self::$domainDns as $dns => $domain) {
            foreach ($domain as $name) {
                if (str_ends_with($this->queryName[0][self::P_RR_NAME], $name)) {
                    $this->dnsHost = self::DNS_HOSTS[$dns];
                    self::log('Switch Dns:', $this->dnsHost);
                    return true;
                }
            }
        }
        $this->enableDOH = str_starts_with($this->dnsHost, 'https');
        return true;
    }


    public function TcpUdpClient(&$response, &$responseSize)
    {
        self::log("DNSConncet:Connect {$this->dnsHost}");
        $fp = stream_socket_client($this->dnsHost, $errno, $error, $this->timeout);
        if (!$fp) {
            self::log("Network Error: {$this->dnsHost} $error($errno)");
            return false;
        }
        $response = '';
        if (stream_socket_sendto($fp, $this->queryData)) {
            do {
                $response .=  stream_socket_recvfrom($fp, 512);
                if (strlen($response) < 512) {
                    break;
                }
                $this->parseDNSPackage($response, $parseStatus);
                if (!$parseStatus) {
                    continue;
                }
            } while (false);
            $responseSize = strlen($response);
            self::log("Connect {$this->dnsHost} Success");
            fclose($fp);
            return true;
        }
        fclose($fp);
        return false;
    }

    public function DOHClient(&$response, &$responseSize, &$responseInfo)
    {
        $ch = curl_init($this->dnsHost);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_POSTFIELDS => $this->queryData,
            CURLOPT_HTTPHEADER => [
                'content-type: application/dns-message',
                'accept: application/dns-message'
            ],
        ]);
        self::log("DOH:Connect {$this->dnsHost}");
        $response = curl_exec($ch);

        $responseInfo = curl_getinfo($ch);
        $responseSize = $responseInfo['size_download'];
        if ($responseInfo['http_code'] == 0) {
            $error = curl_error($ch) . '(' . curl_errno($ch) . ')';
            self::log("Network Error: {$this->dnsHost} $error");
            return false;
        } else if ($responseInfo['http_code'] != 200) {
            self::log("Connect {$this->dnsHost} Error: HTTP {$responseInfo['http_code']}");
            return false;
        }
        if ($ret) {
            self::log("Query From {$this->dnsHost} Success");
            return true;
        }
        self::log("Connect {$this->dnsHost}  Unknow Error");
        return false;
    }


    public function buildServerErrorData()
    {
        $flag  = (1 < 15) | 2;
        $h = [$this->transId, $flag, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $result .= pack('n*', ...$h);
        return $result;
    }

    public function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function base64url_decode($data)
    {
        $data = strtr($data, '-_', '+/');
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode($data);
    }
    public function __destruct()
    {
        fwrite(self::$logfp, implode('', self::$logs));
        fclose(self::$logfp);
    }
}
