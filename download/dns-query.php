<?php
ini_set('error_reporting', 0);
ini_set('log_errors', 1);
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
    public $dohHost = 'https://dns.alidns.com/dns-query';
    private $logfp;
    public static $domainDns = [
        'CF' => [
            'github.com',
            'google.com',
            'gstatic.com'
        ]
    ];
    public static $dohDnsList = [
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
        $this->logfp = fopen(RDIR . '/logs/dns.log-' . date('Y-m-d'), 'ab');
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

            header('Content-Type: application/dns-message', true);
            $body = $this->getRecord($curlInfo);
            header("Content-Length: " . $curlInfo['size_download']);
            echo $body;
        }
    }
    public function log(...$datas)
    {
        $msg = '';
        foreach ($datas as $data) {
            $msg .= is_string($data) ? $data : json_encode($data, JSON_PRETTY_PRINT);
        }
        fwrite($this->logfp, date('[H:i:s]') . $msg . PHP_EOL);
    }
    public function saveData($t, $data)
    {
        return;
        file_put_contents(RDIR . '/data/dns.' . $t, $data);
    }

    public function buildData()
    {
        $result = $this->transId;
        $flag  = 1 < 15;
        if ($this->unsupport) {
            $flag |= 2;
        }
        $h = [$flag, 0, 0, 0, 0];
        $result .= pack('n*', ...$h);
        if ($this->unsupport) {
            return $result;
        }
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
    public static function toIPv6($data, $i)
    {
        return  join(':', array_map('dechex', unpack('n8', $data, $i)));
    }

    public function parseData($queryData)
    {
        $headers = unpack('n6', $queryData);
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
        $count = $packet['questionCount'];
        if ($packet['flags']['qr']) {
            $count += $packet['answerCount'];
        }
        $i = $start;
        $parseAuthority = $parseAdditional =  false;
        for ($n = 0; $n < $count; $n++) {
            $name = self::getDomainFromOffset($queryData, $i);

            $rrtype = unpack('n2', $queryData, $i);
            $queryName = ['name' => $name, 'type' => $rrtype[1], 'class' => $rrtype[2]];
            $i += 4;
            if ($parseAdditional) {
                if (self::RR_TYPE[$queryName['type']] == 'OPT') {
                    $queryName['udp_size'] = $queryName['class'];
                    $data1 = unpack('C2', $queryData, $i);
                    $queryName['ext-code'] = $data1[1];
                    $queryName['version'] = $data1[2];
                    $i += 2;
                    $data2 = unpack('n2', $queryData, $i);
                    $queryName['z'] = $data2[1];
                    $queryName['option-data-length'] = $data2[2];
                    $i += 4;
                } else if(self::RR_TYPE[$queryName['type']] == 'A') {

                }


                $packet['additional'][] = $queryName;
            } else if ($parseAuthority) {
                $packet['authority'][] = $queryName;
            } else if ($packet['flags']['qr'] && $n >= $packet['questionCount']) {
                $ttl = unpack('N', $queryData, $i)[1];
                $i += 4;
                $dataLen = unpack('n', $queryData, $i)[1];
                $i += 2;
                $queryName['ttl'] = $ttl;
                $queryName['rdatalen'] = $dataLen;

                if (self::RR_TYPE[$queryName['type']] == 'CNAME') {
                    $ck = $i;
                    $data = self::getDomainFromOffset($queryData, $ck, $dataLen);
                } else if (self::RR_TYPE[$queryName['type']] == 'A') {
                    $data = long2ip(unpack('N', $queryData, $i)[1]);
                } else if (self::RR_TYPE[$queryName['type']] == 'AAAA') {
                    $data = self::toIPv6($queryData, $i);
                } else {
                    $data = substr($queryData, $i, $dataLen);
                }
                $i += $dataLen;


                $queryName['rdata'] = $data;
                $packet['answers'][] = $queryName;
            } else {
                $packet['questions'][] = $queryName;
            }

            if ($packet['authorityCount'] > 0 && ($n == $count - 1) && !$parseAuthority) {
                $count += $packet['authorityCount'];
                $parseAuthority = true;
            }
            if ($packet['additionalCount'] > 0 && ($n == $count - 1) && !$parseAdditional) {
                $count += $packet['additionalCount'];
                $parseAdditional = true;
            }

            $start = $i;
        }
        if($packet['flags']['qr']) {
            $this->saveData('A', $queryData);
        } else {
            $this->saveData('Q', $queryData);
        }
        return $packet;
    }

    public function switchDohDns()
    {
        $this->log('Query:', $this->queryName);
        foreach (self::$domainDns as $dns => $domain) {
            foreach ($domain as $name) {
                if (str_ends_with($this->queryName[0]['name'], $name)) {
                    $this->dohHost = self::$dohDnsList[$dns];
                    $this->log('Switch Dns:', $this->dohHost);
                    return true;
                }
            }
        }
        return true;
    }

    public function getRecord(&$curlInfo)
    {
        $packet = $this->parseData($this->queryData);
        $this->queryName = $packet['questions'];
        $this->switchDohDns();
        $ch = curl_init($this->dohHost);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 1,
            CURLOPT_POSTFIELDS => $this->queryData,
            CURLOPT_HTTPHEADER => [
                'content-type: application/dns-message',
                'accept: application/dns-message'
            ],
        ]);
        $this->log("Connect {$this->dohHost}");
        $ret = curl_exec($ch);

        $curlInfo = curl_getinfo($ch);
        if ($curlInfo['http_code'] == 0) {
            $error = curl_error($ch) . '(' . curl_errno($ch) . ')';
            $this->log("Network Error: {$this->dohHost} $error");
            return $this->buildServerErrorData();
        } else if ($curlInfo['http_code'] != 200) {
            $this->log("Connect {$this->dohHost} Error: HTTP {$curlInfo['http_code']}");
            return $this->buildServerErrorData();
        }
        if ($ret) {
            $this->log("Query From {$this->dohHost} Success");
            $this->cacheQueryRecord($ret);
            return $ret;
        }
        $this->log("Connect {$this->dohHost}  Unknow Error");
        return $this->buildServerErrorData();;
    }

    public function cacheQueryRecord($data)
    {
        $record = $this->parseData($data);
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
        fclose($this->logfp);
    }
}

$q = new DnsQuery;
if (PHP_SAPI == 'cli') {
    $data = file_get_contents('./data/dns.A');
    $r = $q->parseData($data);
    print_r($r);
}
