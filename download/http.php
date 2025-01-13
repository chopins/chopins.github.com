<?php
declare(strict_types=1);
namespace Toknot;
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
    case FORM_MULTIPART = 'form-multipart';
    case FILE = 'file';
    case FORM_URL = 'form-url';
}

function RUN()
{
    return HTTP::init()->run();
}
/**
 * @param string $path  请求的文件路径，不包括 scheme, host, port部分
 * @param string|array $data  请求时发送 Body 数据
 * @param string|array $query URL 查询参数
 *
 * @return HTTP
 */
function GET(string $path, string|array $query = '', string|array $data = '')
{
    $obj = HTTP::init();
    if ($data) {
        $obj->custom('GET', $path, $query, $data);
    } else {
        $obj->get($path, $query);
    }
    return $obj;
}
/**
 * @param string $path   请求的文件路径，不包括 scheme, host, port部分
 * @param string|array $data 请求时发送 Body 数据
 * @param string|array $query URL 查询参数
 *
 * @return HTTP
 */
function PUT(string $path, string|array $query = '', string|array $data)
{
    $obj = HTTP::init();
    $forceFile = ($obj::$requestBodyType == HttpRequestBodyType::FILE || $obj::$requestBodyType == HttpRequestBodyType::FILE->value);
    if (is_array($data)) {
        $obj->custom('PUT', $path, $query, $data);
    } else if (!$forceFile && is_string($data) && !file_exists($data)) {
        $obj->custom('PUT', $path, $query, $data);
    } else {
        $obj->put($path, $data, $query);
    }
    return $obj;
}
/**
 * @param string $path 请求的文件路径，不包括 scheme, host, port部分
 * @param string|array $data 请求时发送 Body 数据
 * @param string|array $query URL 查询参数
 *
 * @return HTTP
 */
function POST(string $path, string|array $data = '', string|array $query = '')
{
    $obj = HTTP::init();
    $obj->post($path, $data, $query);
    return $obj;
}
/**
 * @param string $path 请求的文件路径，不包括 scheme, host, port部分
 * @param string|array $query URL 查询参数
 *
 * @return HTTP
 */
function DELETE(string $path, string|array $query = '')
{
    $obj = HTTP::init();
    $obj->delete($path, $query);
    return $obj;
}
/**
 * @param string $path 请求的文件路径，不包括 scheme, host, port部分
 * @param string|array $query URL 查询参数
 *
 * @return HTTP
 */
function HEAD(string $path, string|array $query = '')
{
    $obj = HTTP::init();
    $obj->head($path, $query);
    return $obj;
}

/**
 * @param string $path 请求的文件路径，不包括 scheme, host, port部分
 * @param string|array $query URL 查询参数
 *
 * @return HTTP
 */
function OPTIONS(string $path, string|array $query = '')
{
    $obj = HTTP::init();
    $obj->options($path, $query);
    return $obj;
}

/**
 * @param string $path 请求的文件路径，不包括 scheme, host, port部分
 * @param string|array $query URL 查询参数
 *
 * @return HTTP
 */
function TRACE(string $path, string|array $query = '')
{
    $obj = HTTP::init();
    $obj->trace($path, $query);
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
     * @var bool 当前实例是否已经显示
     */
    public static bool $isShow = false;

    /**
     * @var string|HttpRequestBodyType 请求时发送的body数据类型
     */
    public static string|HttpRequestBodyType $requestBodyType;
    /**
     * @var string 位于调用行时，激活执行的 token 值
     */
    private static string $runTag = '@';

    /**
     * @var string 位于调用行时，激活执行的并显示的 token 值
     */
    private static string $runTagShow = '@';
    /**
     * @var string 设置 User Agent
     */
    public static ?string $userAgent = '';
    /**
     * @var string 设置 oauth2 token
     */
    public static string $oauth2Token = '';

    /**
     * @var bool 是否显示响应头
     */
    public static bool $showResponseHeader = true;
    /**
     * @var bool 是否显示响应的内容
     */
    public static bool $showResponseBody = true;
    /**
     * @var bool 是否显示请求头
     */
    public static bool $showRequestHeader = false;
    /**
     * @var bool 是否使用表格显示数组结果
     */
    public static bool $showArrayTable = false;
    /**
     * @var array 表格显示规则
     */
    public static array $arrayTableLayout = [];
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
     * @var float http 响应 body 长度
     */
    public float $contentLength = 0;
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
     * @var array 实际发送的请求头
     */
    public array $realRequestHeader = [];
    /**
     * @var string 发送 http 请求的 body 内容
     */
    public string|array $requestBody = '';
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
     * @var float 请求执行时间
     */
    public float $execTime = 0;
    /**
     * @var float 请求发起连接时间
     */
    public float $connectTime = 0;
    /**
     * @var float DNS解析时间
     */
    public float $nsLookupTime = 0;
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
     * @var bool 是否调用请求
     */
    private bool $run = false;

    /**
     * @var bool
     */
    private bool $enableShow = false;
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
    private static array $colors = [];
    /**
     * @var bool
     */
    private static bool $isCLI = true;

    /**
     * @var bool
     */
    private static bool $forceRun = false;
    /**
     * @var array
     */
    private static array $runFlagLines = [];

    /**
     * @var array
     */
    private static array $runFlagShowLines = [];

    /**
     * @var array
     */
    private static array $defaultObjVars = [];

    private function __construct()
    {
        self::$isCLI = PHP_SAPI == 'cli';
        self::$requestBodyType = HttpRequestBodyType::RAW;
        $this->checkRun(false);
        self::$defaultObjVars = get_object_vars($this);
        $this->color();
    }

    /**
     * @param string $host
     *
     * @return HTTP
     */
    public static function init(string $runTag = '', string $runTagShow = ''): HTTP
    {
        if ($runTag) {
            self::$runTag = $runTag;
        }
        if ($runTagShow) {
            self::$runTagShow = $runTagShow;
        }
        if (!isset(self::$obj)) {
            self::$obj = new static();
        }
        self::$obj->reset();
        return self::$obj;
    }

    public function reset()
    {
        foreach (self::$defaultObjVars as $k => $v) {
            $this->$k = $v;
        }
    }

    private function buildUrl(string $path = '/', string|array $queryData = ''): void
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

    private function buildBody(string|array $data): void
    {
        if (is_string(self::$requestBodyType)) {
            HttpRequestBodyType::from(self::$requestBodyType);
        }
        if (self::$requestBodyType == HttpRequestBodyType::JSON) {
            $this->requestBody = is_array($data) ? json_encode($data) : $data;
            self::$requestHeader[] = 'Content-Type: application/json';
        } else if (self::$requestBodyType->value == HttpRequestBodyType::XML) {
            $this->requestBody = is_array($data) ? self::xmlEncode($data) : $data;
            self::$requestHeader[] = 'Content-Type: application/xml';
        } else {
            $this->requestBody = $data;
        }
    }
    protected static function xmlEncode(array $data): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        foreach ($data as $key => $v) {
            if (is_array($v)) {
                $v = self::xmlEncode($v);
            }
            $xml .= "<{$key}>{$v}</{$key}>";
        }
    }
    /**
     * @param string $method
     * @param string $path
     * @param string $query
     * @param string $data
     *
     * @return HTTP
     */
    public function custom(string $method, string $path, string|array $query = '', string|array $data = ''): HTTP
    {
        $this->method = $method;
        $this->isCustomMethod = true;
        $this->buildUrl($path, $query);
        $this->buildBody($data);
        if ($data) {
            $this->curlOptions[CURLOPT_POSTFIELDS] = $this->requestBody;
        }
        return $this->request();
    }
    public function get(string $path, string|array $query = ''): HTTP
    {
        $this->buildUrl($path, $query);
        $this->method = 'GET';
        return $this->request();
    }

    public function post(string $path, string|array $data, string|array $query = ''): HTTP
    {
        $this->buildUrl($path, $query);
        $this->method = 'POST';
        $this->buildBody($data);
        $this->curlOptions[CURLOPT_POSTFIELDS] = $this->requestBody;
        return $this->request();
    }

    public function put(string $path, $file, string|array $query = ''): HTTP
    {
        $this->buildUrl($path, $query);
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

    public function delete(string $path, string|array $query = ''): HTTP
    {
        $this->buildUrl($path, $query);
        $this->isCustomMethod = true;
        $this->method = 'DELETE';
        return $this->request();
    }

    public function head(string $path, string|array $query = ''): HTTP
    {
        $this->buildUrl($path, $query);
        $this->isCustomMethod = true;
        $this->method = 'HEAD';
        return $this->request();
    }

    public function patch(string $path, string|array $query = ''): HTTP
    {
        $this->buildUrl($path, $query);
        $this->isCustomMethod = true;
        $this->method = 'PATCH';
        return $this->request();
    }

    public function options(string $path, string|array $query = ''): HTTP
    {
        $this->buildUrl($path, $query);
        $this->isCustomMethod = true;
        $this->method = 'OPTIONS';
        return $this->request();
    }

    public function trace(string $path, string|array $query = ''): HTTP
    {
        $this->buildUrl($path, $query);
        $this->isCustomMethod = true;
        $this->method = 'TRACE';
        return $this->request();
    }

    protected function request(): HTTP
    {
        $this->run = $this->checkRun();
        if (!$this->run) {
            return $this;
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
        } else if ($this->method == 'POST' && self::$requestBodyType == HttpRequestBodyType::FORM_URL) {
            $this->curlOptions[CURLOPT_POST] = true;
        } else if ($this->method == 'PUT') {
            $this->curlOptions[CURLOPT_PUT] = true;
        }
        if (self::$showRequestHeader) {
            $this->curlOptions[CURLINFO_HEADER_OUT] = 1;
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

        $this->getCurlInfo();
        if ($this->httpCode === 0) {
            $this->getNetworkError();
        }

        if ($this->enableShow) {
            return $this->show();
        }
        return $this;
    }

    private function getCurlInfo(): void
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

        if (self::$showRequestHeader) {
            $this->realRequestHeader = explode("\r\n", $info['request_header']);
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

    private function getNetworkError(): void
    {
        $this->curlErrno = curl_errno($this->curl);
        $this->curlError = curl_error($this->curl);
    }

    protected function getResposeType(string $header): void
    {
        if (self::isHave($header, 'text/json') || self::isHave($header, 'application/json')) {
            $this->isJson = true;
        } else if (
            self::isHave($header, 'application/xml')
            || self::isHave($header, 'text/xml')
            || self::isHave($header, 'application/atom+xml')
        ) {
            $this->isXml = true;
        } else if (self::isHave($header, 'text/plain')) {
            $this->isText = true;
        } else if (self::isHave($header, 'text/html')) {
            $this->isHtml = true;
        }
    }
    public static function isHave(string $hay, string $needle): bool
    {
        return stripos($hay, $needle) !== false;
    }

    public function show(): HTTP
    {
        if (!$this->run) {
            return $this;
        }
        if (self::$isCLI) {
            $this->showConsole();
        } else {
            $this->showHTML();
        }
        self::$isShow = true;
        return $this;
    }

    public function then(callable $callable): HTTP
    {
        if (!$this->run) {
            return $this;
        }
        $callable($this);
        return $this;
    }

    public static function __callStatic(string $name, array $arguments = []): void
    {
        if (isset(self::$colors[$name])) {
            self::out($name, ...$arguments);
        }
    }

    protected static function out(string $color = 'PRESET', string $str = '', bool $nl = false): void
    {
        if (isset(self::$colors[$color])) {
            echo self::$colors[$color] . $str . self::$colors['END'];
        } else {
            echo $str;
        }
        if ($nl) {
            echo PHP_EOL;
        }
    }

    protected function showConsole(): void
    {
        $cols = exec('tput cols');
        self::YELLOW(str_repeat('-', $cols), 1);

        if (!$this->httpCode) {
            self::BLUE("{$this->method} {$this->url} ", true);
            self::RED(curl_error($this->curl), 1);
            return;
        }
        if (self::$showRequestHeader) {
            foreach ($this->realRequestHeader as $i => $header) {
                if (strpos($header, ':') === false) {
                    self::GREEN($header, 1);
                } else {
                    self::MAGENTA(str_replace(':', ':' . self::$colors['END'], $header), 1);
                }
            }
        } else {
            self::BLUE("{$this->method} {$this->url} ", true);
        }
        if (self::$showResponseHeader) {
            foreach ($this->responseHeader as $i => $header) {
                if (strpos($header, ':') === false) {
                    self::GREEN($header);
                } else {
                    self::MAGENTA(str_replace(':', ':' . self::$colors['END'] . self::$colors['PRESET'], $header));
                }
            }
        }
        if (self::$showResponseBody) {
            if ($this->isJson) {
                $json = json_decode($this->responseBody, true);
                $json ? $this->showArrayTable($json) : print($this->responseBody);
            } else if ($this->isXml) {
                $xml = simplexml_load_string($this->responseBody);
                $xml ? $this->showArrayTable($xml) : print($this->responseBody);
            } else if ($this->contentLength <= 500 && $this->httpCode == 200) {
                echo $this->responseBody;
            } else {
                echo 'save to: file://' . realpath('./output.html');
                file_put_contents('./output.html', $this->responseBody);
            }
            echo PHP_EOL;
        }
    }

    protected function color(): void
    {
        $ansi = isset($_SERVER['ComSpec']) && $_SERVER['ComSpec'] == 'C:\Windows\system32\cmd.exe' ? "\x1b" : "\033";

        if (PHP_SAPI != 'cli') {
            $code = ['BLUE' => 'blue', 'GREEN' => 'green', 'MAGENTA' => 'magenta', 'RED' => 'red', 'YELLOW' => 'yellow', 'PRESET' => 'unset'];
            self::$colors['END'] = "</span>";
            foreach ($code as $k => $n) {
                self::$colors[$k] = "<span style='color:$n'>";
            }
        } else {
            $code = ['RED' => 31, 'GREEN' => 32, 'YELLOW' => 33, 'BLUE' => 34, 'MAGENTA' => 35, 'PRESET' => 0];
            self::$colors['END'] = "{$ansi}[0m";
            foreach ($code as $k => $n) {
                self::$colors[$k] = "{$ansi}[0;{$n}m";
            }
        }
    }

    protected function showHTML(): void
    {
        if (!self::$isShow) {
?>
            <!DOCTYPE html>
            <html>

            <head>
                <title>API Request</title>
                <script src="./jquery/jquery.min.js"></script>
                <style>* {font-size: 14px;} html {width: 99%;word-break: break-all;} hr {padding:1px;color:yellow} p { margin: 5px; } </style>
                <script src="./jquery/jquery.jsonview.min.js"></script>
                <link href="./jquery/jquery.jsonview.min.css" type="text/css" rel="stylesheet">
            </head>
            <body>
            <?php
        }
        $outcodeId = md5(microtime() . uniqid());
        echo '<hr />';
        if ($this->isJson) { ?>
                <script>
                    $(function() {
                        $("#<?=$outcodeId?>").JSONView(JSON.parse($("#<?=$outcodeId?>").text()), {
                            collapsed: true,
                            bigNumbers: true
                        });
                    });
                </script>

            <?php }
        self::BLUE("{$this->method} {$this->url} ");

        if (!$this->httpCode) {
            self::RED(curl_error($this->curl), 1);
        }
        if (self::$showResponseHeader) {
            foreach ($this->responseHeader as $i => $header) {
                echo '<p>';
                $header = trim($header);
                if ($i == 0) {
                    self::GREEN($header);
                } else {
                    self::MAGENTA(str_replace(':', ':' . self::$colors['END'] . '<span>', $header));
                }
                echo '</p>';
            }
        }
        if ($this->isHtml && self::$showResponseBody && preg_match('/^\<[A-Z!]+/i', trim($this->responseBody))) {
            ?>
                <iframe width="99%" height="900" id="showBodyIframe" srcdoc=''></iframe>
                <script>
                    $(function() {
                        $('#showBodyIframe').attr('srcdoc', atob('<?= base64_encode($this->responseBody) ?>'));
                    });
                </script>
            <?php } else { ?>
                <pre id="<?=$outcodeId?>"><?= self::$showResponseBody ? $this->responseBody : '' ?></pre>
    <?php }
    }

    public function run(): HTTP
    {
        self::$forceRun = true;
        return $this;
    }

    protected function checkRun(bool $parse = true): bool
    {
        if ($parse) {
            if (self::$forceRun) {
                return true;
            }
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $callline = array_column($trace, 'line');
            if (array_intersect($callline, self::$runFlagLines)) {
                return true;
            }
            $this->enableShow = false;
            if (array_intersect($callline, self::$runFlagShowLines)) {
                $this->enableShow = true;
                return true;
            }
            return false;
        }

        $all = token_get_all(file_get_contents(get_included_files()[0], false));
        $cnt = count($all);
        self::$runFlagLines = [];

        for ($i = 0; $i < $cnt; $i++) {
            $token = $all[$i];
            $findTag = $findTagShow = false;
            if ($token == self::$runTag || (is_array($token) && $token[1] == self::$runTag)) {
                $findTag = true;
            } elseif ($token == self::$runTagShow || (is_array($token) && $token[1] == self::$runTagShow)) {
                $findTagShow = true;
            }
            $next = $i;
            while ($findTag || $findTagShow) {
                $next++;
                if ($next >= $cnt) {
                    break;
                }
                if (!is_array($all[$next])) {
                    break;
                }
                if ($all[$next][0] == T_STRING) {
                    $findTag && self::$runFlagLines[] = $all[$next][2];
                    $findTagShow && self::$runFlagShowLines[] = $all[$next][2];
                    break;
                } else if ($all[$next][0] != T_WHITESPACE) {
                    break;
                }
            }
        }
        return true;
    }

    public function showArrayTable(array $array): void
    {
        if (!self::$showArrayTable) {
            print_r($array);
            return;
        }

        $cols = exec('tput cols');
        $lineSep = str_repeat('=', $cols) . PHP_EOL;
        echo $lineSep;
        foreach (self::$arrayTableLayout as $line) {
            $width = floor($cols / count($line));
            foreach ($line as $k => $n) {
                if ($n == 'string') {
                    echo str_pad(self::$colors['BLUE'] . $k . self::$colors['END'] . " => $array[$k]", $width);
                } else {
                    $field =  $k . ' => ';
                    echo self::$colors['BLUE'] . $k . self::$colors['END'] . ' => ';
                    $this->showList($array[$k], strlen($field));
                }
            }
            echo PHP_EOL;
            echo $lineSep;
        }
    }

    protected function showList(array $array, string $indent): void
    {
        $indentStr = str_repeat(' ', $indent);
        $i = 0;
        foreach ($array as $k => $v) {
            $field = $k . ' => ';
            $i > 0 && print($indentStr);
            $i++;
            echo self::$colors['BLUE'] . $k . self::$colors['END'] . ' => ';
            if (is_array($v)) {
                $this->showList($v, $indent + strlen($field));
            } else {
                echo $v . PHP_EOL;
            }
        }
    }

    public static function file($filename, $filemime = null)
    {
        $mime = mime_content_type($filename);
        if(!$mime && !$filemime) {
            $mime = 'application/octet-stream';
        } elseif(!$mime) {
            $mime = $filemime;
        }
        return new \CURLFile($filename, $mime);
    }

    public function __destruct()
    {
        if ($this->curl instanceof CurlHandle) {
            curl_close($this->curl);
        }
        if (!self::$isCLI) {
            echo '</body></html>';
        }
    }
}
