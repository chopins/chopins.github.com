<?php

function GET($uri, $data = '', $query = '')
{
    $obj = HTTPRequest::fetch();
    if ($data) {
        $obj->custom('GET', HTTP_HOST, $uri, $query, $data);
    } else {
        $obj->get(HTTP_HOST, $uri, $query);
    }
}
function PUT($uri, $data, $query = '')
{
    $obj = HTTPRequest::fetch();
    if (is_string($data) && !file_exists($data)) {
        $obj->custom('PUT', HTTP_HOST, $uri, $query, $data);
    } else {
        $obj->put(HTTP_HOST, $uri, $data, $query);
    }
}
function POST($uri, $data, $query = '')
{
    HTTPRequest::fetch()->post(HTTP_HOST, $uri, $data, $query);
}

class HTTPRequest
{
    public string $user = '';
    public string $password = '';
    public string $scheme = 'http';
    public string $host = '';
    public int $port = 0;
    public string $method = 'GET';
    private bool $isCustomMethod = false;
    public string $url = '';
    public int $httpCode = 0;
    public string $httpMsg = '';
    public int $contentlength = 0;
    public bool $isJson = false;
    public bool $isXml = false;
    public string $httpVersion = 'HTTP/1.1';
    public array $requestHeader = [];
    public string $requestBody = '';
    public string $requestBodyType = 'raw';
    public array $responseHeader = [];
    public string $responseBody = '';
    public array $curlOptions = [];
    private $curl;
    private static $obj = null;

    public function __construct($host)
    {
        $this->host = $host;
    }
    /**
     * @return self
     */
    public static function fetch($host = '')
    {
        if (!self::$obj) {
            self::$obj = new static($host);
        }
        if ($host) {
            self::$obj->host = $host;
        }
        return self::$obj;
    }

    private function buildUrl(string $path = '/', $queryData = null)
    {
        $auth = $query = '';
        if ($user) {
            $auth = "{$this->user}:{$this->password}@";
        }
        if ($queryData) {
            $query =  is_array($queryData) ? http_build_query($queryData) : $queryData;
            $query = (strpos($path, '?') === false ? '?' : '&') . $query;
        }
        if (strpos($path, '/') !== 0) {
            $path = "/$path";
        }
        $port = $this->port == 0 ? '' : ":{$this->port}";
        $this->url = "{$this->scheme}://{$auth}{$this->host}{$port}{$path}{$query}";
    }

    private function buildBody($data)
    {
        if ($this->requestBodyType == 'json') {
            $this->requestBody = is_array($data) ? json_encode($data) : $data;
            $this->requestHeader[] = 'Content-Type: application/json';
        } else if ($this->requestBodyType == 'xml') {
            $this->requestBody = is_array($data) ? self::xmlEncode($data) : $data;
            $this->requestHeader[] = 'Content-Type: application/xml';
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
        $this->requestHeader[] = 'Content-Length: ' . strlen($this->requestBody);
    }
    public static function xmlEncode(array $data)
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

    public function request()
    {
        if ($this->isCustomMethod) {
            $op[CURLOPT_CUSTOMREQUEST] = $this->method;
            $this->isCustomMethod = false;
        } else {
            $curlMethodKey = constant("CURLOPT_{$this->method}");
            $op[$curlMethodKey] = 1;
        }
        $op[CURLOPT_HEADERFUNCTION] = function ($ch, $h) {
            $this->responseHeader = $h;
            return strlen($h);
        };
        $this->curlOptions[CURLOPT_HTTPHEADER] = $this->requestHeader;
        $this->curlOptions[CURLOPT_RETURNTRANSFER] = 1;
        $this->curl = curl_init($this->url);
        curl_setopt_array($this->curl, $this->curlOptions);
        $this->responseBody = curl_exec($this->curl);
    }

    public function setResposeHeader()
    {
        foreach ($this->responseHeader as $i => $header) {
            if ($i === 0) {
                list($this->httpVersion, $this->httpCode, $this->httpMsg) = explode(' ', $h, 3);
                continue;
            }
            if (isBegin($header, 'Content-Type:') &&  (isHave($header, 'text/json') || isHave($header, 'application/json'))) {
                $this->isJson = true;
            } else if (isBegin($header, 'Content-Type:') &&  isHave($header, 'application/xml')) {
                $this->isXml = true;
            } else if (isBegin($header, 'Content-Length:')) {
                $this->contentlength = trim(substr($h, 15));
            }
        }
    }

    public function show() {}

    public function __destruct()
    {
        curl_close($this->curl);
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
