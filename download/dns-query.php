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
    public $rrtype = 'ANY';
    public $rropcode = 0;
    public $unsupport = false;
    public $queryName = [];
    public $queryData = '';
    public $dnsHost = self::DNS_HOSTS['Default'];
    public $enableDOH = true;
    public $timeout = 3;
    private static $logfp;
    public static $logs = [];
    public static $requestDatetime;
    public static $domainDns = [
        'CF' => [
            'github.com',
            'google.com',
            'gstatic.com'
        ]
    ];
    const DNS_HOSTS = [
        'Default' => 'udp://127.0.0.53:53',
        'CF' => 'https://1.1.1.1/dns-query',
        'CF' => 'https://dns.alidns.com/dns-query',
        'TX' => 'https://doh.pub/dns-query',
    ];
    const RR_CLASS = ['', 'IN', 'CS', 'CH', 'HS'];
    const RR_TYPE = [
        'ANY',
        'A',
        'NS',
        'MD',
        'MF',
        'CNAME',
        'SOA',
        'MB',
        'MG',
        'MR',
        'NULL',
        'WKS',
        'PTR',
        'HINFO',
        'MINFO',
        'MX',
        'TXT', //16
        28 => 'AAAA',
        33 => 'SRV',
        '',
        'NAPTR',
        'APL',
        'DSIG',
        '',
        'DNAME',
        '',
        'OPT',
        '',
        'DS',
        '',
        '',
        'RRSIG',
        '',
        'DNSKEY', //48
        59 => 'CDS',
        61 => 'OPENPGPKEY',
        65 => 'SVCB',
        'HTTPS',
        255 => '255',
        257 => 'CAA'
    ];
    public function __construct()
    {
        self::$requestDatetime = new DateTime();
        self::$logfp = fopen(RDIR . '/logs/dns.log-' . date('Y-m-d'), 'wb');
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
        self::log('Query Packet: state:', $qstate, ':data:', $packet);
        $this->queryName = $packet['questions'];
        $this->switchDns();
        header('Content-Type: application/dns-message', true);

        if ($this->enableDOH) {
            $ret = $this->DOHClient($body, $responseSize, $responseInfo);
        } else {
            $ret = $this->TcpUdpClient($body, $responseSize);
        }
        $this->saveData('A', $body);
        if (!$ret) {
            $body = $this->buildServerErrorData();
        } else {
            $packet = $this->parseDNSPackage($body, $astate);
            self::log("Answer Packet:state:", $astate, ':data:', $packet);
        }

        header("Content-Length: " . $responseSize);
        echo $body;
    }

    public static function bset($bit, $size)
    {
        return ($bit & (1 << $size)) > 0 ? 1 : 0;
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
            $len = $len * substr($format,1);
        }
        $offset += $len;
        return $ret;
    }

    public function parseDNSPackage($queryData, &$parseStatus = true)
    {
        $parseStatus = true;
        $headers = self::unpack('n6', $queryData);
        $packet = [];
        $packet['transId'] = $headers[1];
        $packet['flags'] = $this->parseFlags($headers[2]);
        $packet['questionCount'] = $headers[3];
        $packet['answerCount'] = $headers[4];
        $packet['authorityCount'] = $headers[5];
        $packet['additionalCount'] = $headers[6];
        $packet['questions'] = [];
        $packet['answers'] = [];
        $packet['authority'] = [];
        $packet['additional'] = [];
        $start = 12;
        $queryDataLen = strlen($queryData);
        $count = $packet['questionCount'] + $packet['answerCount'] + $packet['authorityCount'] + $packet['additionalCount'];
        $authorityOffset = $packet['questionCount'] + $packet['answerCount'];
        $additionalOffset = $count - $packet['additionalCount'];
        $i = $start;
        $this->log("playloadSize $queryDataLen");
        for ($rrs = 0; $rrs < $count; $rrs++) {

            if ($i > $queryDataLen) {
                $parseStatus = false;
                self::log("read pos $i >= data len $queryDataLen ");
                break;
            }
            $name = self::getDomainFromOffset($queryData, $i);

            $rrtype = self::unpack('n2', $queryData, $i);
            $queryName = ['name' => $name, 'type' => $rrtype[1], 'class' => $rrtype[2]];
            $typeName = self::RR_TYPE[$queryName['type']];

            if ($rrs >= $packet['questionCount']) {
                $ttl = self::unpack('N', $queryData, $i)[1];
                $queryName['ttl'] = $ttl;
                $RDLen = self::unpack('n', $queryData, $i)[1];

                if ($typeName == 'OPT') {
                    $queryName['payload-size'] = $queryName['class'];
                    $queryName['rcode-flags'] = $queryName['ttl'];
                }
                $queryName['rdlen'] = $RDLen;

                if (in_array($typeName, ['CNAME', 'MX', 'NS', 'TXT'])) {
                    $data = self::getDomainFromOffset($queryData, $i, $RDLen);
                } else if ($typeName == 'A') {
                    $data = long2ip(self::unpack('N', $queryData, $i)[1]);
                } else if ($typeName == 'AAAA') {
                    $data = self::toIPv6($queryData, $i);
                } else {
                    $data = self::unpack("H{$RDLen}", $queryData, $i);
                }
                $queryName['rdata'] = $data;
            }

            if ($packet['additionalCount'] && $rrs >= $additionalOffset) {
                $packet['additional'][] = $queryName;
            } else if ($packet['authorityCount'] && $rrs >= $authorityOffset) {
                $packet['authority'][] = $queryName;
            } else if ($packet['answerCount'] && $rrs >= $packet['questionCount']) {
                $packet['answers'][] = $queryName;
            } else {
                $packet['questions'][] = $queryName;
            }

            $start = $i;
        }
        if ($queryDataLen != $i) {
            self::log("read pos $i != data len $queryDataLen ");
            $parseStatus = false;
        }
        return $packet;
    }

    public function switchDns()
    {
        self::log('Query:', $this->queryName);
        foreach (self::$domainDns as $dns => $domain) {
            foreach ($domain as $name) {
                if (str_ends_with($this->queryName[0]['name'], $name)) {
                    $this->dnsHost = self::DNS_HOSTS[$dns];
                    self::log('Switch Dns:', $this->dohHost);
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
                if(strlen($response) < 512) {
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
        $result = $this->transId;
        $flag  = (1 < 15) | 2;
        $h = [$flag, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
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

try {
    $q = new DnsQuery;
} catch(Throwable $e) {
    DnsQuery::log($e->getTraceAsString());
}
if (PHP_SAPI == 'cli') {
    $data = file_get_contents('./data/dns.A');
    $r = $q->parseDNSPackage($data);
    print_r($r);
}
