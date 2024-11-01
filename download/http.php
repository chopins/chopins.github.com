<?php

/**
 * Http Request by Curl (http://toknot.com)
 *
 * @copyright  Copyright (c) 2011 - 2024 Toknot.com
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/toknot
 */

/**
 * Http 请求 Body 数据类型
 */
enum HttpRequestBodyType: string
{
    case JSON = 'json';
    case XML = 'xml';
    case RAW = 'raw';
    case FORM = 'form';
    case FILE = 'file';
}


/**
 * @param string $uri
 * @param string|array $data
 * @param string|array $query
 * 
 * @return HTTP
 */
function GET(string $uri, string|array $data = '', string|array $query = '')
{
    $obj = HTTP::fetch();
    if ($data) {
        $obj->custom('GET', $uri, $query, $data);
    } else {
        $obj->get($uri, $query);
    }
    return $obj;
}
/**
 * @param string $uri
 * @param string|array $data
 * @param string|array $query
 * 
 * @return HTTP
 */
function PUT(string $uri, string|array $data, string|array $query = '')
{
    $obj = HTTP::fetch();
    $forceFile = ($obj::$requestBodyType == HttpRequestBodyType::FILE || $obj::$requestBodyType == HttpRequestBodyType::FILE->value);
    if (is_array($data)) {
        $obj->custom('PUT', $uri, $query, $data);
    } else if (!$forceFile && is_string($data) && !file_exists($data)) {
        $obj->custom('PUT', $uri, $query, $data);
    } else {
        $obj->put($uri, $data, $query);
    }
    return $obj;
}
/**
 * @param string $uri
 * @param string|array $data
 * @param string|array $query
 * 
 * @return HTTP
 */
function POST(string $uri, string|array $data, string|array $query = '')
{
    $obj = HTTP::fetch();
    $obj->post($uri, $data, $query);
    return $obj;
}
/**
 * @param string $uri
 * @param string|array $query
 * 
 * @return HTTP
 */
function DELETE(string $uri, string|array $query = '')
{
    $obj = HTTP::fetch();
    $obj->delete($uri, $query);
    return $obj;
}
/**
 * @param string $uri
 * @param string|array $query
 * 
 * @return HTTP
 */
function HEAD(string $uri, string|array $query = '')
{
    $obj = HTTP::fetch();
    $obj->head($uri, $query);
    return $obj;
}
/**
 * Http Request By curl
 */
class HTTP
{
    /**
     * @var string 用户名
     */
    public static string $user = '';
    /**
     * @var string 用户密码
     */
    public static string $password = '';
    /**
     * @var string 请求协议
     */
    public static string $scheme = 'http';
    /**
     * @var string 主机域名或IP
     */
    public static string $host = '';
    /**
     * @var int 请求端口，默认将根据 $scheme 进行设置
     */
    public static int $port = 0;
    /**
     * @var array 请求头列表
     */
    public static array $requestHeader = [];

    /**
     * @var string|HttpRequestBodyType 请求时发送的body数据类型
     */
    public static string|HttpRequestBodyType $requestBodyType;
    /**
     * @var string 位于调用行时，激活执行的 token 值
     */
    public static string $enableTag = '@';
    /**
     * @var string 设置 User Agent
     */
    public static ?string $userAgent = '';
    /**
     * @var string 设置 oauth2 token
     */
    public static string $oauth2Token = '';

    public static bool $showHead = true;
    public static bool $showBody = true;


    /**
     * @var string http 请求方法
     */
    public string $method = 'GET';

    /**
     * @var string 发起 http 时的 url
     */
    public string $url = '';
    /**
     * @var int http 响应状态码
     */
    public int $httpCode = 0;
    /**
     * @var string http 响应状态信息
     */
    public string $httpMsg = '';
    /**
     * @var int http 响应 body 长度
     */
    public int $contentLength = 0;
    /**
     * @var bool http 响应 body 是否 JSON
     */
    public bool $isJson = false;
    /**
     * @var bool http 响应 body 是否 XML
     */
    public bool $isXml = false;
    /**
     * @var bool http 响应 body 是否 HTML
     */
    public bool $isHtml = false;
    /**
     * @var bool http 响应 body 是否 Text
     */
    public bool $isText = false;
    /**
     * @var string http 响应的HTTP 版本
     */
    public string $httpVersion = 'HTTP/1.1';
    /**
     * @var string 发送 http 请求的 body 内容
     */
    public string $requestBody = '';
    /**
     * @var array http 响应的头列表
     */
    public array $responseHeader = [];
    /**
     * @var string http 响应的 body 内容
     */
    public string $responseBody = '';
    /**
     * @var array 需要设置的 curl 选项
     */
    public array $curlOptions = [];
    /**
     * @var int 请求时 curl 错误码
     */
    public int $curlErrno = 0;
    /**
     * @var string 请求时 curl 错误信息
     */
    public string $curlError = '';
    /**
     * @var int 请求执行时间
     */
    public int $execTime = 0;
    /**
     * @var int 请求发起连接时间
     */
    public int $connectTime = 0;
    /**
     * @var int DNS解析时间
     */
    public int $nsLookupTime = 0;
    /**
     * @var int 重定向次数
     */
    public int $redirectCount = 0;
    /**
     * @var string 最后一次重定向URL
     */
    public string $locationUrl = '';
    /**
     * @var string 最后请求的URL
     */
    public string $lastUrl = '';
    /**
     * @var array 重定向URL列表
     */
    public array $redirectUrls = [];
    /**
     * @var string 连接IP
     */
    public string $connectIp = '';

    /**
     * @var int 连接端口
     */
    public int $connectPort = 0;

    /**
     * @var bool 是否自定义请求方法
     */
    private bool $isCustomMethod = false;

    /**
     * @var CurlHandle
     */
    private ?CurlHandle $curl = null;
    /**
     * @var HTTP
     */
    private static HTTP $obj;
    /**
     * @var array
     */
    private array $colors = [];
    /**
     * @var bool
     */
    private bool $isCLI = true;
    /**
     * @var array
     */
    private static array $runFlagLines = [];
    private bool $run = false;
    private int $lastCalledLine = 0;

    private function __construct($host)
    {
        self::$host = $host;
        $this->isCLI = PHP_SAPI == 'cli';
        self::$requestBodyType = HttpRequestBodyType::RAW;
        self::checkRun(false);
        $this->color();
    }

    /**
     * @param string $host
     * 
     * @return HTTP
     */
    public static function fetch($host = '')
    {
        if (!isset(self::$obj)) {
            self::$obj = new static($host);
        }
        if ($host) {
            self::$obj::$host = $host;
        }
        self::$obj->isJson = false;
        self::$obj->isXml = false;
        self::$obj->isHtml = false;
        self::$obj->isText = false;
        return self::$obj;
    }

    private function buildUrl(string $path = '/', $queryData = null)
    {
        $query = '';
        if ($queryData) {
            $query =  is_array($queryData) ? http_build_query($queryData) : $queryData;
            $query = (strpos($path, '?') === false ? '?' : '&') . $query;
        }
        if (strpos($path, '/') !== 0) {
            $path = "/$path";
        }
        $port = self::$port == 0 ? '' :  ':' . self::$port;
        $this->url = self::$scheme . "://" . self::$host . "{$port}{$path}{$query}";
    }

    private function buildBody($data)
    {
        if (is_string(self::$requestBodyType)) {
            HttpRequestBodyType::from(self::$requestBodyType);
        }
        if (self::$requestBodyType->value == 'json') {
            $this->requestBody = is_array($data) ? json_encode($data) : $data;
            self::$requestHeader[] = 'Content-Type: application/json';
        } else if (self::$requestBodyType->value == 'xml') {
            $this->requestBody = is_array($data) ? self::xmlEncode($data) : $data;
            self::$requestHeader[] = 'Content-Type: application/xml';
        } else if (is_array($data)) {
            $hasFile = false;
            foreach ($data as $v) {
                if ($v instanceof CURLFile || $v instanceof CURLStringFile) {
                    $hasFile = true;
                    break;
                }
            }
            $this->requestBody = $hasFile ? $data : http_build_query($data);
            return;
        }
        self::$requestHeader[] = 'Content-Length: ' . strlen($this->requestBody);
    }
    protected static function xmlEncode(array $data)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        foreach ($data as $key => $v) {
            if (is_array($v)) {
                $v = self::xmlEncode($v);
            }
            $xml .= "<{$key}>{$v}</{$key}>";
        }
    }
    public function custom($method, $uri, $query = '', $data = '')
    {
        $this->method = $method;
        $this->isCustomMethod = true;
        $this->buildUrl($uri, $query);
        $this->buildBody($data);
        if ($data) {
            $this->curlOptions[CURLOPT_POSTFIELDS] = $this->requestBody;
        }
        return $this->request();
    }
    public function get(string $path, $query = '')
    {
        $this->buildUrl($path, $query);
        $this->method = 'GET';
        return $this->request();
    }

    public function post($path, $data, $query = '')
    {
        $this->url = $this->buildUrl($path, $query);
        $this->method = 'POST';
        $this->buildBody($data);
        $this->curlOptions[CURLOPT_POSTFIELDS] = $this->requestBody;
        return $this->request();
    }

    public function put($path, $file, $query = '')
    {
        $this->url = $this->buildUrl($path, $query);
        $this->method = 'PUT';
        if (is_string($file) && is_file($file)) {
            $this->curlOptions[CURLOPT_INFILE] = fopen($file, 'rb');
            $this->curlOptions[CURLOPT_INFILESIZE] = filesize($file);
        } else if (is_resource($file)) {
            $this->curlOptions[CURLOPT_INFILE] = $file;
            $this->curlOptions[CURLOPT_INFILESIZE] = fstat($file)['size'];
        }
        return $this->request();
    }

    public function delete($path, $query = '')
    {
        $this->url = $this->buildUrl($path, $query);
        $this->isCustomMethod = true;
        $this->method = 'DELETE';
        return $this->request();
    }

    public function head($path, $query = '')
    {
        $this->url = $this->buildUrl($path, $query);
        $this->isCustomMethod = true;
        $this->method = 'HEAD';
        return $this->request();
    }

    public function patch($path, $query = '')
    {
        $this->url = $this->buildUrl($path, $query);
        $this->isCustomMethod = true;
        $this->method = 'PATCH';
        return $this->request();
    }

    public function options($path, $query = '')
    {
        $this->url = $this->buildUrl($path, $query);
        $this->isCustomMethod = true;
        $this->method = 'OPTIONS';
        return $this->request();
    }

    public function trace($path, $query = '')
    {
        $this->url = $this->buildUrl($path, $query);
        $this->isCustomMethod = true;
        $this->method = 'TRACE';
        return $this->request();
    }

    protected function request()
    {
        $this->run = self::checkRun();
        if (!$this->run) {
            return;
        }
        if (self::$userAgent) {
            $this->curlOptions[CURLOPT_USERAGENT] = self::$userAgent;
        } else if (self::$userAgent === null) {
            $this->curlOptions[CURLOPT_USERAGENT] = '';
        }
        if (self::$oauth2Token) {
            $this->curlOptions[CURLOPT_XOAUTH2_BEARER] = self::$oauth2Token;
        }
        if (self::$user) {
            $this->curlOptions[CURLOPT_USERNAME] = self::$user;
            $this->curlOptions[CURLOPT_PASSWORD] = self::$password;
            $this->curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_ANY;
        }
        if ($this->isCustomMethod) {
            $this->curlOptions[CURLOPT_CUSTOMREQUEST] = $this->method;
            $this->isCustomMethod = false;
        } else if ($this->method == 'GET') {
            $this->curlOptions[CURLOPT_HTTPGET] = true;
        } else if ($this->method == 'POST') {
            $this->curlOptions[CURLOPT_POST] = true;
        } else if ($this->method == 'PUT') {
            $this->curlOptions[CURLOPT_PUT] = true;
        }
        $this->curlOptions[CURLOPT_FOLLOWLOCATION] = true;
        $this->curlOptions[CURLOPT_HEADERFUNCTION] = function ($ch, $h) {
            $this->responseHeader[] = $h;
            return strlen($h);
        };
        $this->curlOptions[CURLOPT_HTTPHEADER] = self::$requestHeader;
        $this->curlOptions[CURLOPT_RETURNTRANSFER] = 1;
        $this->curl = curl_init($this->url);
        curl_setopt_array($this->curl, $this->curlOptions);
        $this->responseBody = curl_exec($this->curl);
        if ($this->responseBody === false) {
            $this->getNetworkError();
        }
        $this->getCurlInfo();
        return $this;
    }

    private function getCurlInfo()
    {
        $info = curl_getinfo($this->curl);
        $this->execTime = $info['total_time'];
        $this->connectTime = $info['connect_time'];
        $this->nsLookupTime = $info['namelookup_time'];

        $this->redirectUrls = [];
        $this->connectIp = $info['primary_ip'];
        $this->connectPort = $info['primary_port'];
        $this->contentLength = $info['size_download'];
        $this->lastUrl = $info['url'];

        $this->httpCode = $info['http_code'];

        if (!$this->httpCode) {
            return;
        }


        $this->redirectCount = $info['redirect_count'];
        if (isset($info['redirect_url'])) {
            $this->locationUrl = $info['redirect_url'];
        }

        if (isset($info['content_type'])) {
            $this->getResposeType($info['content_type']);
        }
        if (isset($info['http_version'])) {
            $ver = [
                CURL_HTTP_VERSION_1_0 => 'HTTP/1.0',
                CURL_HTTP_VERSION_1_1 => 'HTTP/1.1',
                CURL_HTTP_VERSION_2 => 'HTTP/2',
                CURL_HTTP_VERSION_2_0 => 'HTTP/2',
                CURL_HTTP_VERSION_2TLS => 'HTTPS/2',
                CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE => 'HTTP/2'
            ];
            if (defined('CURL_HTTP_VERSION_3')) {
                $ver[CURL_HTTP_VERSION_3] = 'HTTP/3';
                $ver[CURL_HTTP_VERSION_3ONLY] = 'HTTP/3';
            }
            $this->httpVersion = $ver[$info['http_version']];
        }
    }

    private function getNetworkError()
    {
        $this->curlErrno = curl_errno($this > curl);
        $this->curlError = curl_error($this->curl);
    }

    protected function getResposeType($header)
    {
        if (isHave($header, 'text/json') || isHave($header, 'application/json')) {
            $this->isJson = true;
        } else if (
            isHave($header, 'application/xml')
            || isHave($header, 'text/xml')
            || isHave($header, 'application/atom+xml')
        ) {
            $this->isXml = true;
        } else if (isHave($header, 'text/plain')) {
            $this->isText = true;
        } else if (isHave($header, 'text/html')) {
            $this->isHtml = true;
        }
    }

    public function show()
    {
        if (!$this->run) {
            return $this;
        }
        if ($this->isCLI) {
            $this->showStd();
        } else {
            $this->showHTML();
        }
        return $this;
    }

    protected function showStd()
    {
        $cols = exec('tput cols');
        echo str_repeat('-', $cols);
        echo "{$this->colors['BLUE']}{$this->method} {$this->url} {$this->colors['END']}" . PHP_EOL;
        if (self::$showHead) {
            foreach ($this->responseHeader as $i => $header) {
                if (strpos($header, ':') === false) {
                    echo $this->colors['GREEN'] . $header . $this->colors['END'];
                } else {
                    echo $this->colors['MAGENTA'] . str_replace(':', ':' . $this->colors['END'], $header);
                }
            }
            echo $this->colors['END'];
        }
        if (self::$showBody) {
            if ($this->isJson) {
                $json = json_decode($this->responseBody, true);
                $json ? print_r($json) : print($this->responseBody);
            } else if ($this->isXml) {
                $xml = simplexml_load_string($this->responseBody);
                $xml ? print_r($xml) : print($this->responseBody);
            } else if ($this->contentLength <= 500 && $this->httpCode == 200) {
                echo $this->responseBody;
            } else {
                echo 'save to: file://' . realpath('./output.html');
                file_put_contents('./output.html', $this->responseBody);
            }
            echo PHP_EOL;
        }
    }

    protected function color()
    {
        $ansi = isset($_SERVER['ComSpec']) && $_SERVER['ComSpec'] == 'C:\Windows\system32\cmd.exe' ? "\x1b" : "\033";

        if (PHP_SAPI != 'cli') {
            $code = ['BLUE' => 'red', 'GREEN' => 'green', 'MAGENTA' => 'MAGENTA'];
            $this->colors['END'] = "</p>";
            foreach ($code as $k => $n) {
                $this->colors[$k] = "<p style='color:$n'>";
            }
        } else {
            $code = ['BLUE' => 34, 'GREEN' => 32, 'MAGENTA' => 35];
            $this->colors['END'] = "{$ansi}[0m";
            foreach ($code as $k => $n) {
                $this->colors[$k] = "{$ansi}[0;{$n}m";
            }
        }
    }

    protected function showHTML()
    {
?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>API Request</title>
            <?php if ($this->isJson) { ?>
                <script src="./jquery/jquery.min.js"></script>
                <script src="./jquery/jquery.jsonview.min.js"></script>
                <link href="jquery/jquery.jsonview.min.css" type="text/css" rel="stylesheet">
                <script>
                    $(function() {
                        $("#outcode").JSONView(JSON.parse($("#outcode").text()), {
                            collapsed: true,
                            bigNumbers: true
                        });
                    });
                </script>
            <?php } ?>
        </head>

        <body>
            <?php
            echo "{$this->colors['BLUE']}{$this->method} {$this->url} {$this->colors['END']}";
            if (self::$showHead) {
                foreach ($this->responseHeader as $i => $header) {
                    if ($i == 0) {
                        echo $this->colors['GREEN'] . $header . $this->colors['END'];
                    } else {
                        echo $this->colors['MAGENTA'] . str_replace(':', ':' . $this->colors['END'] . '<p>', $header) . $this->colors['END'];
                    }
                }
            }
            ?>

            <pre id="outcode"><?= self::$showBody ? $this->responseBody : '' ?></pre>
        </body>

        </html>
<?php
    }


    protected static function checkRun($parse = true)
    {
        if ($parse) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $callline = array_column($trace, 'line');
            if (array_intersect($callline, self::$runFlagLines)) {
                return true;
            }
            return false;
        }

        $all = token_get_all(file_get_contents(get_included_files()[0], false));
        $cnt = count($all);
        self::$runFlagLines = [];
        for ($i = 0; $i < $cnt; $i++) {
            $token = $all[$i];
            if ($token == self::$enableTag) {
                $i = $i + 1;
                $token = $all[$i];
                if (is_array($token) && $token[0] == T_STRING) {
                    self::$runFlagLines[] = $token[2];
                } else if (is_array($token) && $token[0] == T_WHITESPACE && strpos($token[1], PHP_EOL) === false) {
                    self::$runFlagLines[] = $token[2];
                }
            }
        }
    }


    public function __destruct()
    {
        if ($this->curl instanceof CurlHandle) {
            curl_close($this->curl);
        }
    }
}

function isBegin($hay, $needle)
{
    return stripos($hay, $needle) === 0;
}
function isHave($hay, $needle)
{
    return stripos($hay, $needle) !== false;
}
HTTP::fetch('localhost');
