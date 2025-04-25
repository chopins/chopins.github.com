<?php
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
    public $dnsHost = 'udp://127.0.0.53:53';
    public $enableDOH = true;
    public $timeout = 3;

    private static $logfp;
    public static $logs = [];
    public static $requestDatetime;
    public static $DNS_DOMAIN_MAP = [
        'CF' => [
            'github.com',
            'google.com',
            'gstatic.com',
            'elastic.co'
        ]
    ];
    public static $LOCAL_RR_LIST = [
        'host.godaddy.com' => [
            self::RR_A => ['35.154.51.163', '65.2.72.240']
        ],
    ];
    public static $DNS_HOSTS = [
        'Default' => 'udp://127.0.0.53:53',
        'CF' => 'https://1.1.1.1/dns-query',
        'TX' => 'https://doh.pub/dns-query',
    ];

    /**
     * https://www.rfc-editor.org/rfc/rfc9180.html#name-kem-ids
     * KEM IDs:
     * 0x0000 	Reserved
     * 0x0010 	DHKEM(P-256, HKDF-SHA256)
     * 0x0011 	DHKEM(P-384, HKDF-SHA384)
     * 0x0012 	DHKEM(P-521, HKDF-SHA512)
     * 0x0020 	DHKEM(X25519, HKDF-SHA256
     * 0x0021 	DHKEM(X448, HKDF-SHA512)
     *
     * https://www.rfc-editor.org/rfc/rfc9180.html#name-kdf-ids
     * KDF IDs:
     * 0x0000 	Reserved
     * 0x0001 	HKDF-SHA256
     * 0x0002 	HKDF-SHA384
     * 0x0003 	HKDF-SHA512
     *
     * https://www.rfc-editor.org/rfc/rfc9180.html#name-aead-ids
     * AEAD IDs:
     * 0x0000 	Reserved
     * 0x0001 	AES-128-GCM
     * 0x0002 	AES-256-GCM
     * 0x0003 	ChaCha20Poly1305
     * 0xFFFF 	Export-only
     */
    const ECH_TPL = [
        'configId' => 1, //random
        'kemId' => 32,
        'pubKey' => '',
        'ciphers' => [
            ['kdfId' => 1, 'aeadId' => 1],
        ],
        'maxNameLen' => 0,
        'pubName' => '',
        'extensions' => [], // ['type'=> '', 'data' => ]
    ];
    const S_NAME_END = "\0";
    const S_PTR = "\xc0";
    const P_H_ID = 0;
    const P_H_FLAG = self::P_H_ID + 1;
    const P_H_FLAG_QR = 0;
    const P_H_FLAG_OPCODE = self::P_H_FLAG_QR + 1;
    const P_H_FLAG_AA = self::P_H_FLAG_OPCODE + 1;
    const P_H_FLAG_TC = self::P_H_FLAG_AA + 1;
    const P_H_FLAG_RD = self::P_H_FLAG_TC + 1;
    const P_H_FLAG_RA = self::P_H_FLAG_RD + 1;
    const P_H_FLAG_Z = self::P_H_FLAG_RA + 1;
    const P_H_FLAG_RCODE = self::P_H_FLAG_Z + 1;
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

    const P_RR_OPT_UDP_SIZE = self::P_RR_CLASS;
    const P_RR_OPT_RCODE = self::P_RR_TTL;
    const P_RR_OPT_E_V = self::P_RR_OPT_RCODE + 1;
    const P_RR_OPT_DO = self::P_RR_OPT_E_V + 1;
    const P_RR_OPT_Z = self::P_RR_OPT_DO + 1;
    const P_RR_OPT_OPTION = self::P_RR_OPT_Z + 1;
    const P_RR_OPT_OPTION_CODE = 0;
    const P_RR_OPT_OPTION_DATA = self::P_RR_OPT_OPTION_CODE + 1;

    const P_RR_HTTPS_PRIORITY = self::P_RR_TTL + 1;
    const P_RR_HTTPS_TARGET_NAME = self::P_RR_HTTPS_PRIORITY + 1;
    const P_RR_HTTPS_PARAMS = self::P_RR_HTTPS_TARGET_NAME + 1;
    const P_RR_HTTPS_PARAMS_KEY = 0;
    const P_RR_HTTPS_PARAMS_VALUE = 1;

    const RR_HTTPS_MANDATORY = 0;
    const RR_HTTPS_ALPN = 1;
    const RR_HTTPS_NO_DEFAULT_ALPN = 2;
    const RR_HTTPS_PORT = 3;
    const RR_HTTPS_IPV4HINT = 4;
    const RR_HTTPS_ECH = 5;
    const RR_HTTPS_IPV6HINT = 6;

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
        if (!defined('RDIR')) {
            define('RDIR', dirname(realpath($_SERVER['SCRIPT_FILENAME'])));
        }

        if (!is_dir(RDIR . '/logs')) {
            mkdir(RDIR . '/logs');
        }
        if (!is_dir(RDIR . '/data')) {
            mkdir(RDIR . '/data');
        }

        register_shutdown_function([$this, 'shutdown']);
        self::$requestDatetime = new DateTime();
        self::$logfp = fopen(RDIR . '/logs/dns.log-' . date('Y-m-d'), 'ab');
        if (PHP_SAPI != 'cli') {
            if ($_SERVER['HTTP_ACCEPT'] == 'application/dns-message') {
                $this->accept = 'dns-msg';
            }
            if (isset($_SERVER['HTTP_CONTENT_TYPE']) && $_SERVER['HTTP_CONTENT_TYPE'] == 'application/dns-message') {
                $this->requestType = 'dns-msg';
            }

            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $this->queryData = $this->base64_decode($_GET['dns']);
            } else {
                $this->queryData = file_get_contents("php://input");
            }
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
        self::log('Query:', $this->queryName);
        $ret = $this->getDNSCache($packet);

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
                $this->dnsHost = self::$DNS_HOSTS['Default'];
                $this->enableDOH = false;
            }
        } while (!$ret);

        if (!$ret) {
            $body = $this->buildServerErrorData();
        } else {
            if (count($this->queryName) == 1) {
                $this->cacheDNSRecord($body);
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

    public function cacheDNSRecord($body)
    {
        $type = $this->queryName[0][self::P_RR_TYPE] . '-' . $this->queryName[0][self::P_RR_NAME];
        $this->saveData($type, $body);
    }

    public function getDNSCache($queryPacket)
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
                self::log($this->queryName[0][self::P_RR_NAME] . " cache expired");
                return false;
            }
            self::log($this->queryName[0][self::P_RR_NAME] . ' use cache');
            return pack('n', $this->transId) . substr($data, 2);
        }
        return false;
    }
    public static function initPacketArray()
    {
        return [
            self::P_H_ID => 0,
            self::P_H_FLAG => 0,
            self::P_H_QUESTION => 0,
            self::P_H_ANSWER => 0,
            self::P_H_AUTHORITY => 0,
            self::P_H_ADDITIONAL => 0,
            self::P_QUERIES => [],
            self::P_ANSWERS => [],
            self::P_AUTHORITY => [],
            self::P_ADDITIONAL => [],
        ];
    }
    public static function initHeadFlags()
    {
        return [
            self::P_H_FLAG_QR => 1,
            self::P_H_FLAG_OPCODE => 0,
            self::P_H_FLAG_AA => 0,
            self::P_H_FLAG_TC => 0,
            self::P_H_FLAG_RD => 0,
            self::P_H_FLAG_RA => 0,
            self::P_H_FLAG_Z => 0,
            self::P_H_FLAG_RCODE => 0
        ];
    }
    public function localServer(&$recordData, $queryPacket)
    {
        $type = $this->queryName[0][self::P_RR_TYPE];
        $name = $this->queryName[0][self::P_RR_NAME];
        $UDPSize = $queryPacket[self::P_ADDITIONAL][0][self::P_RR_OPT_UDP_SIZE];

        if (!isset(self::$LOCAL_RR_LIST[$name][$type])) {
            return false;
        }
        $packet = self::initPacketArray();
        $packet[self::P_QUERIES] = $queryPacket[self::P_QUERIES];
        foreach ($this->queryName as $query) {
            $name = $query[self::P_RR_NAME];
            $type = $query[self::P_RR_TYPE];
            foreach (self::$LOCAL_RR_LIST[$name][$type] as $r) {
                $packet[self::P_ANSWERS][] = [
                    self::P_RR_NAME => $name,
                    self::P_RR_TYPE => $type,
                    self::P_RR_CLASS => 1,
                    self::P_RR_TTL => 3600,
                    self::P_RR_DATA => $r
                ];
            }
        }
        if(empty($packet[self::P_ANSWERS])) {
            return false;
        }

        $packet[self::P_ADDITIONAL][] = [
            self::P_RR_NAME => self::S_NAME_END,
            self::P_RR_TYPE => self::RR_OPT,
            self::P_RR_OPT_UDP_SIZE => 65494,
            self::P_RR_OPT_RCODE => 0,
            self::P_RR_OPT_E_V => 0,
            self::P_RR_OPT_DO => 0,
            self::P_RR_OPT_Z => 0,
            self::P_RR_OPT_OPTION => [],
        ];

        $flags = self::initHeadFlags();
        $flags[self::P_H_FLAG_QR] = 1;
        $flags[self::P_H_FLAG_OPCODE] = $queryPacket[self::P_H_FLAG][self::P_H_FLAG_OPCODE];
        $flags[self::P_H_FLAG_RD] = $queryPacket[self::P_H_FLAG][self::P_H_FLAG_RD];
        $flags[self::P_H_FLAG_RA] = 1;

        $recordData = $this->buildDNSResponse($flags, $packet, $UDPSize);
        return true;
    }

    public function buildName($labelist, $name, &$rLabels = null, $unend = false)
    {
        if ($name == self::S_NAME_END) {
            return $name;
        }
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
                if (!empty($l)) {
                    $binary .= chr(strlen($l)) . $l;
                }
            }
            $binary .= self::S_PTR . chr($ptrPos);
        } else { //没有指针
            foreach ($labels as $l) {
                $binary .= chr(strlen($l)) . $l;
            }
            if (!$unend) {
                $binary .= self::S_NAME_END;
            }
        }
        return $binary;
    }

    public function buildDNSResponse($flagArray, $packet, $UDPSize)
    {
        $labelist = [];
        $offset = 12;
        $binary = '';
        foreach ($packet as $zone => $answer) {
            foreach ($answer as $a) {
                $rbinary = $this->buildName($labelist, $a[self::P_RR_NAME], $rLabels);

                $rbinary .= pack('n2', $a[self::P_RR_TYPE], $a[self::P_RR_CLASS]);
                if ($zone != self::P_QUERIES) {
                    $rbinary .= $this->buildRRData($labelist, $a, $offset + strlen($rbinary));
                }
                if (in_array($a[self::P_RR_TYPE], self::G_CACHE_NAME_TYPE)) {
                    $labelist[$a[self::P_RR_NAME]] = [$rLabels, $offset];
                }
                $offset += strlen($rbinary);
                $binary .= $rbinary;
            }
        }

        if (strlen($binary) + 12 > $UDPSize) {
            $flagArray[self::P_H_FLAG_TC] = 1;
        }

        $flag = $this->setFlags($flagArray);
        $headers = [
            $this->transId,
            $flag,
            count($packet[self::P_QUERIES]),
            count($packet[self::P_ANSWERS]),
            count($packet[self::P_AUTHORITY]),
            count($packet[self::P_ADDITIONAL])
        ];

        return pack("n*", ...$headers) . $binary;
    }

    public function buildRRData(&$labelist, $a, $offset)
    {
        $rType = $a[self::P_RR_TYPE];
        $binary = '';

        $binary .= pack('N', $a[self::P_RR_TTL]);

        if ($rType == self::RR_A) {
            $binary .= pack('n', 4);
            $binary .= pack('N', ip2long($a[self::P_RR_DATA]));
        } else if ($rType == self::RR_AAAA) {
            $binary .= pack('n', 8);
            $binary .= self::ipv6long($a[self::P_RR_DATA]);
        } else if (in_array($rType, self::G_RR_DATA_NAME)) {
            $name = $this->buildName($labelist, $a[self::P_RR_DATA], $rLabels, true);
            $binary .= pack('n', strlen($name));
            $binary .= $name;
            $labelist[$a[self::P_RR_DATA]] = [$rLabels, $offset + 6];
        } else if ($rType == self::RR_OPT) {
            $binary = pack('C2', $a[self::P_RR_OPT_RCODE], $a[self::P_RR_OPT_E_V]);
            $binary .= pack('n', ($a[self::P_RR_OPT_DO] << 15) | $a[self::P_RR_OPT_Z]);
            if (empty($a[self::P_RR_OPT_OPTION])) {
                $binary .= pack('n', 0);
            } else {
                $optionBinary = '';
                foreach ($a[self::P_RR_OPT_OPTION] as $option) {
                    $optionBinary .= pack('n2', $option[self::P_RR_OPT_OPTION_CODE], strlen($option[self::P_RR_OPT_OPTION_DATA]));
                    $optionBinary .= $option[self::P_RR_OPT_OPTION_DATA];
                }
                $binary .= pack('n', strlen($optionBinary)) . $optionBinary;
            }
        } else if ($rType == self::RR_HTTPS) {
            $svcHttpsBinary = pack('n', $a[self::P_RR_HTTPS_PRIORITY]);
            $svcHttpsBinary .= $this->buildName($labelist, $a[self::P_RR_HTTPS_TARGET_NAME], null, true);
            foreach ($a[self::P_RR_HTTPS_PARAMS] as $param) {
                $svcHttpsBinary .= pack('n', $param[self::P_RR_HTTPS_PARAMS_KEY]);
                if ($param[self::P_RR_HTTPS_PARAMS_KEY] ==  self::RR_HTTPS_NO_DEFAULT_ALPN) {
                    $svcHttpsBinary .= pack('n', 0);
                    continue;
                } else if (
                    $param[self::P_RR_HTTPS_PARAMS_KEY] == self::RR_HTTPS_MANDATORY
                    || $param[self::P_RR_HTTPS_PARAMS_KEY] == self::RR_HTTPS_PORT
                ) {
                    $paramValue = pack('n*', ...$param[self::P_RR_HTTPS_PARAMS_KEY]);
                    $svcHttpsBinary .= pack('n', strlen($paramValue)) . $paramValue;
                    continue;
                } else if ($param[self::P_RR_HTTPS_PARAMS_KEY] == self::RR_HTTPS_ECH) {
                    $echconfig = $this->buildECHConfig($param[self::P_RR_HTTPS_PARAMS_VALUE]);
                    $svcHttpsBinary .= pack("n", strlen($echbinary)) . $echconfig;
                    continue;
                }
                $paramValue = '';
                foreach ($param[self::P_RR_HTTPS_PARAMS_VALUE] as $v) {
                    switch ($param[self::P_RR_HTTPS_PARAMS_KEY]) {
                        case self::RR_HTTPS_ALPN:
                            $paramValue .= chr(strlen($v)) . $v;
                            break;
                        case self::RR_HTTPS_IPV4HINT:
                            $paramValue .= ip2long($v);
                            break;
                        case self::RR_HTTPS_IPV6HINT:
                            $paramValue .= self::ipv6long($v);
                            break;
                    }
                }
                $svcHttpsBinary .= pack('n', strlen($paramValue)) . $paramValue;
            }
            $binary .= pack('n', strlen($svcHttpsBinary)) . $svcHttpsBinary;
        }

        return $binary;
    }

    public function buildECHConfig($value)
    {
        $id = pack('n2', 0xfe0d); //version
        $binary = chr($value['configId']) . pack('n2', $value['kemId'], strlen($value['pubKey'])) . $value['pubKey'];
        $binary .= pack('n', count($value['ciphers']) * 4);
        foreach ($value['ciphers'] as $cipher) {
            $binary .= pack('n2', $cipher['kdfId'], $cipher['aeadId']);
        }
        $binary .= chr($value['maxNameLen']) . chr(strlen($value['pubName'])) . $value['pubName'];
        if ($value['extensions']) {
            foreach ($value['extensions'] as $ext) {
                $binary .= pack('n', $ext['type'], strlen($ext['data']));
                $binary .= $ext['data'];
            }
        } else {
            $binary .= pack('n', 0);
        }
        $data = $id . pack('n', strlen($binary)) . $binary;
        return pack('n', strlen($data)) . $data;
    }

    public static function bset($bit, $size)
    {
        return ($bit & (1 << $size)) > 0 ? 1 : 0;
    }

    public function setFlags($flags)
    {
        if ($flags[self::P_H_FLAG_QR]) {
            $flag = 1 << 15;
        }
        if ($flags[self::P_H_FLAG_OPCODE]) {
            $flag |= $flags[self::P_H_FLAG_OPCODE] << 11;
        }
        if ($flags[self::P_H_FLAG_AA]) {
            $flag |= 1 << 10;
        }
        if ($flags[self::P_H_FLAG_TC]) {
            $flag |= 1 << 9;
        }
        if ($flags[self::P_H_FLAG_RD]) {
            $flag |= 1 << 8;
        }
        if ($flags[self::P_H_FLAG_RA]) {
            $flag |= 1 << 7;
        }
        if ($flags[self::P_H_FLAG_RCODE]) {
            $flag |= $flags[self::P_H_FLAG_RCODE];
        }
        return $flag;
    }

    public function parseFlags($bit)
    {
        $flags = self::initHeadFlags();
        $flags[self::P_H_FLAG_QR] = self::bset($bit, 15);
        $flags[self::P_H_FLAG_OPCODE] = $flags[self::P_H_FLAG_QR] ? (($bit >> 11) ^ 16) : ($bit >> 11);
        $flags[self::P_H_FLAG_AA] = self::bset($bit, 10);
        $flags[self::P_H_FLAG_TC] = self::bset($bit, 9);
        $flags[self::P_H_FLAG_RD] = self::bset($bit, 8);
        $flags[self::P_H_FLAG_RA] = self::bset($bit, 7);
        $flags[self::P_H_FLAG_RCODE] = $bit & 15;
        return $flags;
    }

    public static function getDomainFromOffset($queryData, &$i, $maxLen = 254)
    {
        $start = $i;
        $labels = [];
        do {
            if ($queryData[$i] == self::S_NAME_END) {
                $i++;
                break;
            }
            if ($queryData[$i] == self::S_PTR) {
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
    public static function ipv6long($ipv6)
    {
        return pack('n8', hexdec(str_replace(':', $ipv6)));
    }
    public static function long2ipv6($data, &$i)
    {
        return  join(':', array_map('dechex', self::unpack('n8', $data, $i)));
    }
    public static function unpack($format, $string, &$offset = 0)
    {
        $bitSize = ['n' => 2, 'N' => 4, 'C' => 1, 'H' => 1];
        try {
            $ret = unpack($format, $string, $offset);
        } catch (ValueError $e) {
            self::log("Offset:$offset", $e->__toString());
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
        $packet = self::initPacketArray();
        $packet[self::P_H_ID] = $headers[1];
        $packet[self::P_H_FLAG] = $this->parseFlags($headers[2]);
        $packet[self::P_H_QUESTION] = $headers[3];
        $packet[self::P_H_ANSWER] = $headers[4];
        $packet[self::P_H_AUTHORITY] = $headers[5];
        $packet[self::P_H_ADDITIONAL] = $headers[6];

        $queryDataLen = strlen($queryData);
        $count = $packet[self::P_H_QUESTION] + $packet[self::P_H_ANSWER] + $packet[self::P_H_AUTHORITY] + $packet[self::P_H_ADDITIONAL];
        $authorityOffset = $packet[self::P_H_QUESTION] + $packet[self::P_H_ANSWER];
        $additionalOffset = $count - $packet[self::P_H_ADDITIONAL];

        $pos = 12;
        for ($rrs = 0; $rrs < $count; $rrs++) {

            if ($pos > $queryDataLen) {
                $parseStatus = false;
                self::log("OutRangeCheck:read pos $pos >= data len $queryDataLen ");
                break;
            }
            $name = self::getDomainFromOffset($queryData, $pos);

            $rrtype = self::unpack('n2', $queryData, $pos);
            $queryName = [self::P_RR_NAME => $name, self::P_RR_TYPE => $rrtype[1], self::P_RR_CLASS => $rrtype[2]];
            $typeName = $queryName[self::P_RR_TYPE];

            if ($rrs >= $packet[self::P_H_QUESTION]) {
                $ttl = self::unpack('N', $queryData, $pos)[1];
                $queryName[self::P_RR_TTL] = $ttl;
                $RDLen = self::unpack('n', $queryData, $pos)[1];

                if ($typeName == self::RR_OPT) {
                    $queryName[self::P_RR_OPT_UDP_SIZE] = $queryName[self::P_RR_CLASS];
                    $queryName[self::P_RR_OPT_RCODE] = $queryName[self::P_RR_TTL];
                }
                $queryName[self::P_RR_DATA_LEN] = $RDLen;

                if (in_array($typeName, self::G_RR_DATA_NAME)) {
                    $data = self::getDomainFromOffset($queryData, $pos, $RDLen);
                } else if ($typeName == self::RR_A) {
                    $data = long2ip(self::unpack('N', $queryData, $pos)[1]);
                } else if ($typeName == self::RR_AAAA) {
                    $data = self::long2ipv6($queryData, $pos);
                } else {
                    $data = self::unpack("H{$RDLen}", $queryData, $pos);
                }
                $queryName[self::P_RR_DATA] = $data;
            }

            if ($packet[self::P_H_ADDITIONAL] && $rrs >= $additionalOffset) {
                $packet[self::P_ADDITIONAL][] = $queryName;
            } else if ($packet[self::P_H_AUTHORITY] && $rrs >= $authorityOffset) {
                $packet[self::P_AUTHORITY][] = $queryName;
            } else if ($packet[self::P_H_ANSWER] && $rrs >= $packet[self::P_H_QUESTION]) {
                $packet[self::P_ANSWERS][] = $queryName;
            } else {
                $packet[self::P_QUERIES][] = $queryName;
            }
        }
        if ($queryDataLen != $pos) {
            self::log("EndCheck, Last pos $pos != data len $queryDataLen ");
            $parseStatus = false;
        }
        return $packet;
    }

    public function switchDns()
    {
        foreach (self::$DNS_DOMAIN_MAP as $dns => $domain) {
            foreach ($domain as $name) {
                if (str_ends_with($this->queryName[0][self::P_RR_NAME], $name)) {
                    $this->dnsHost = self::$DNS_HOSTS[$dns];
                    self::log('Switch Dns:', $this->dnsHost);
                    return true;
                }
            }
        }
        $this->enableDOH = str_starts_with($this->dnsHost, 'https://');
        return true;
    }


    public function TcpUdpClient(&$response, &$responseSize)
    {
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
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_POSTFIELDS => $this->queryData,
            CURLOPT_HTTPHEADER => [
                'content-type: application/dns-message',
                'accept: application/dns-message'
            ],
        ]);

        if(function_exists('curl_share_init_persistent')) {
            $sh = curl_share_init_persistent([CURL_LOCK_DATA_DNS, CURL_LOCK_DATA_CONNECT, CURL_LOCK_DATA_SSL_SESSION]);
            curl_setopt($ch, CURLOPT_SHARE, $sh);
        }

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

    public function shutdown()
    {
        if (count(self::$logs) > 0) {
            $this->__destruct();
        }
    }
}
