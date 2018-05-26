<?php
/**
 * Toknot (http://toknot.com)
 *
 * @copyright  Copyright (c) 2011 - 2007 Toknot.com
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/toknot
 */
define('DATA_DIR', __DIR__ . '/data/');

set_error_handler(function () {
    $args = func_get_args();
    throw new SubDOMException($args[1], $args[0]);
});

set_exception_handler(function ($e) {
    echo '<pre>';
    echo $e;
    echo '<pre>';
});

function __autoload($class)
{
    $realpath = __DIR__ . str_replace(array('Masterminds', '\\'), array('', DIRECTORY_SEPARATOR), $class) . '.php';
    if (file_exists($realpath)) {
        include $realpath;
    }
}

class SubDOMException extends DOMException
{

    public static $context = '';

    public function __construct($msg, $code)
    {
        $arr = explode("\n", self::$context);
        preg_match('/line: ([\d]+)/', $msg, $m);
        $pn = $m[1] - 1;
        $next = $m[1] + 1;
        $msg .= "<br />{$pn}:" . htmlspecialchars($arr[$pn]) .
        "<br />{$m[1]}:" . htmlspecialchars($arr[$m[1]]) .
        "<br />{$next}:" . htmlspecialchars($arr[$next]);
        parent::__construct($msg, $code);
    }

}

class XXTEA
{

    const DELTA = 0x9E3779B9;

    protected static function long2str($v, $w)
    {
        $len = count($v);
        $n = $len << 2;
        if ($w) {
            $m = $v[$len - 1];
            $n -= 4;
            if (($m < $n - 3) || ($m > $n)) {
                return false;
            }

            $n = $m;
        }
        $s = array();
        for ($i = 0; $i < $len; $i++) {
            $s[$i] = pack("V", $v[$i]);
        }
        if ($w) {
            return substr(join('', $s), 0, $n);
        } else {
            return join('', $s);
        }
    }

    protected static function str2long($s, $w)
    {
        $v = unpack("V*", $s . str_repeat("\0", (4 - strlen($s) % 4) & 3));
        $v = array_values($v);
        if ($w) {
            $v[count($v)] = strlen($s);
        }
        return $v;
    }

    protected static function int32($n)
    {
        return ($n & 0xffffffff);
    }

    protected static function mx($sum, $y, $z, $p, $e, $k)
    {
        return ((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ (($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
    }

    protected static function fixk($k)
    {
        if (count($k) < 4) {
            for ($i = count($k); $i < 4; $i++) {
                $k[$i] = 0;
            }
        }
        return $k;
    }

    // $str is the string to be encrypted.
    // $key is the encrypt key. It is the same as the decrypt key.
    public static function encrypt($str, $key)
    {
        if ($str == "") {
            return "";
        }
        $v = self::str2long($str, true);
        $k = self::fixk(self::str2long($key, false));
        $n = count($v) - 1;
        $z = $v[$n];
        $q = floor(6 + 52 / ($n + 1));
        $sum = 0;
        while (0 < $q--) {
            $sum = self::int32($sum + self::DELTA);
            $e = $sum >> 2 & 3;
            for ($p = 0; $p < $n; $p++) {
                $y = $v[$p + 1];
                $z = $v[$p] = self::int32($v[$p] + self::mx($sum, $y, $z, $p, $e, $k));
            }
            $y = $v[0];
            $z = $v[$n] = self::int32($v[$n] + self::mx($sum, $y, $z, $p, $e, $k));
        }
        return self::long2str($v, false);
    }

    // $str is the string to be decrypted.
    // $key is the decrypt key. It is the same as the encrypt key.
    public static function decrypt($str, $key)
    {
        if ($str == "") {
            return "";
        }
        $v = self::str2long($str, false);
        $k = self::fixk(self::str2long($key, false));
        $n = count($v) - 1;
        $y = $v[0];
        $q = floor(6 + 52 / ($n + 1));
        $sum = self::int32($q * self::DELTA);
        while ($sum != 0) {
            $e = $sum >> 2 & 3;
            for ($p = $n; $p > 0; $p--) {
                $z = $v[$p - 1];
                $y = $v[$p] = self::int32($v[$p] - self::mx($sum, $y, $z, $p, $e, $k));
            }
            $z = $v[$n];
            $y = $v[0] = self::int32($v[0] - self::mx($sum, $y, $z, $p, $e, $k));
            $sum = self::int32($sum - self::DELTA);
        }
        return self::long2str($v, true);
    }

}

// public functions
// $str is the string to be encrypted.
// $key is the encrypt key. It is the same as the decrypt key.
function xxtea_encrypt($str, $key)
{
    return XXTEA::encrypt($str, $key);
}

// $str is the string to be decrypted.
// $key is the decrypt key. It is the same as the encrypt key.
function xxtea_decrypt($str, $key)
{
    return XXTEA::decrypt($str, $key);
}

class To
{

    protected $key = '1234561234561234';
    protected $userAgent = 'Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0';
    protected $referer = '';
    protected $method = 'GET';
    protected $headers = array();
    protected $curlOpt = array();
    protected $cookie = '';
    protected $host = '';
    protected $scheme = 'http';
    protected $url = '';
    protected $contentType = '';
    protected $contentCharset = '';
    protected $ContentTypeHeader = '';
    protected $contentDisposition = '';
    protected $content = '';
    protected $allheader = array();
    protected $requestType = '';
    protected $urlHash = '';
    protected $firstUrlChar = '';
    protected $mineTypeList = array('js' => 'javascript', 'css' => 'css', 'img' => 'image');
    protected $cache = null;
    protected $cacheFile = '';
    protected $attachmentFile = '';
    protected $requestFolder;
    protected $isbeacon = false;
     

    public function __construct()
    {
        $url = $this->getRequest();
        if ($url) {
            $this->noscript();
            $this->urlHash = md5($url);
            $this->firstUrlChar = substr($this->urlHash, 0, 1);
            $this->forwardStatic();
            $this->open($url);
        } else {
            $this->homePage();
        }
    }

    public function forwardStatic()
    {
        $savePath = "/static/{$this->requestType}/{$this->firstUrlChar}/{$this->urlHash}.{$this->requestType}";
        if (file_exists(DATA_DIR . $savePath)) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: /data{$savePath}");
            exit;
        }
    }

    public function noscript()
    {
        if (isset($_GET['t'])) {
            $this->requestType = $_GET['t'];
        }
        if (!$this->requestType || $this->requestType != 'js') {
            return;
        }

        $blackList = array('www.t66y.com');
        if (in_array($this->host, $blackList)) {
            exit();
        }
        return false;
    }

    public function dump($mix)
    {
        echo '<pre>';
        var_dump($mix);
        echo '</pre>';
    }

    public function getVar($key)
    {
        return isset($_SERVER[$key]) ? $_SERVER[$key] : '';
    }

    public function feild2Var($field)
    {
        return 'HTTP_' . str_replace('-', '_', strtoupper($field));
    }

    public function addHeader($field)
    {
        $phpVar = $this->getVar($field);
        $value = $this->getVar($phpVar);
        if ($value) {
            $this->headers[] = "$field: $value";
        }
    }

    public function envVar()
    {
        $this->userAgent = $this->getVar('HTTP_USER_AGENT');
        $this->method = $this->getVar('REQUEST_METHOD');
        $this->cookie = $this->getVar('HTTP_COOKIE');
    }

    public function setOpenHeader()
    {
        $this->addHeader('Accept');
        $this->addHeader('Accept-Charset');
        $this->addHeader('Accept-Encoding');
        $this->addHeader('Accept-Language');
        $this->addHeader('Connection');
        $this->addHeader('If-Modified-Since');
        $this->addHeader('If-None-Match');
        $this->addHeader('DNT');
        $this->addHeader('Cache-Control');
    }

    public function setCurlOpt($url)
    {
        $this->setOpenHeader();
        $this->openCache();
        $this->curlOpt = array(CURLOPT_URL => $url,
            CURLOPT_COOKIEJAR => DATA_DIR . '/cookie/' . $this->host,
            CURLOPT_COOKIEFILE => DATA_DIR . '/cookie/' . $this->host,
            CURLOPT_COOKIE => $this->cookie,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_REFERER => $this->referer,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_ENCODING => '',
            CURLOPT_HEADERFUNCTION => array($this, 'responseHeader'),
        );
        if(!$this->isbeacon) {
            $this->curOpt[CURLOPT_FILE] = $this->cache;
        }
        if ($this->method == 'POST') {
            $this->curlOpt[CURLOPT_POST] = true;
        }
    }

    public function openCache()
    {
        if (!is_dir(DATA_DIR . 'static/cache')) {
            mkdir(DATA_DIR . 'static/cache', 0777, true);
        }
        $this->cacheFile = DATA_DIR . "static/cache/{$this->urlHash}";
        $this->cache = fopen($this->cacheFile, 'w+');
    }

    public function responseHeader($ch, $header)
    {
        //if (!$this->excludeHeader($header)) {
        // $this->dump($header);
        //header($header);
        // }

        $this->allheader[] = $header;
        if (stripos($header, 'Content-Type:') === 0) {
            $this->ContentTypeHeader = trim($header);
            list(, $type) = explode(' ', $header);
            $this->parseContentType($type);
        } elseif (strpos($header, 'Content-Disposition: attachment') === 0) {
            $this->contentDisposition = trim($header);
        } elseif (strpos($header, 'Location: ') === 0) {
            list(, $url) = explode(': ', $header);
            $url = trim($url);
            if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) {
                $url = $this->rPath($url);
            }
            $this->setUrlInfo($url);
        }
        return strlen($header);
    }

    public function parseContentType($str)
    {
        $tmp = explode(';', trim($str));
        $this->contentType = trim($tmp[0]);
        if (!empty($tmp[1])) {
            list(, $this->contentCharset) = explode('=', trim($tmp[1]));
        }
    }

    public function excludeHeader($header)
    {
        $exclude = array('HTTP/', 'Set-Cookie:', 'Location:', 'Content-Length:');

        foreach ($exclude as $k) {
            if (stripos($header, $k) === 0) {
                return true;
            }
        }
    }

    public function open($url)
    {
        $ch = curl_init();
        $this->setCurlOpt($url);
        curl_setopt_array($ch, $this->curlOpt);
        $res = curl_exec($ch);

        if (!$res) {
            $this->homePage("Can not open '$url'");
            exit;
        }
        if($this->isbeacon) {
            exit;
        }
        
        $this->buildResult();

        $this->content = $this->encrypt($this->content);
        $this->displayPage();
    }

    public function fixedExcludeLink($src)
    {
        $exclude = array('#', 'data:');
        foreach ($exclude as $k) {
            if (stripos($src, $k) === 0) {
                return true;
            }
        }
    }

    public function rPath($src)
    {
        if (stripos($src, './') === 0) {
            $src = "{$this->scheme}://{$this->host}{$this->requestFolder}" . ltrim($src, '.');
        } elseif (($c = substr_count($src, '../')) > 0) {
            $path = $this->requestFolder;
            for ($i = 0; $i < $c; $i++) {
                $path = dirname($path);
            }
            $src = "{$this->scheme}://{$this->host}{$path}/" . ltrim($src, './');
        } else {
            $src = "{$this->scheme}://{$this->host}/{$src}";
        }
        return $src;
    }

    public function buildUrl($src, $type = '')
    {
        if (stripos($src, '//') === 0) {
            $src = $this->scheme . ':' . $src;
        } elseif (stripos($src, 'http://') !== 0 && stripos($src, 'https://') !== 0) {
            $src = $this->rPath($src);
        }
        return '/to.php?d=' . $this->urlencrypt($src) . '&r=' . $this->urlencrypt($this->url) . '&t=' . $type;
    }

    public function replaceUrl($node, $attr, $type = '')
    {
        $src = $node->getAttribute($attr);
        if ($this->fixedExcludeLink($src)) {
            return;
        }
        $ensrc = $this->buildUrl($src, $type);
        $node->setAttribute($attr, $ensrc);
    }

    public function urlencrypt($str)
    {
        return urlencode($this->encrypt($str));
    }

    public function buildResult()
    {
        if ($this->requestType && isset($this->mineTypeList[$this->requestType]) &&
            stripos($this->ContentTypeHeader, $this->mineTypeList[$this->requestType]) === false) {
            header('HTTP/1.1 406 Not Acceptable');
            var_dump($this->requestType, $this->ContentTypeHeader);
            exit;
        }
        
        if ($this->contentType == 'text/html') {
            $this->content = file_get_contents($this->cacheFile, false);
            fclose($this->cache);
            unlink($this->cacheFile);
            return $this->buildHTMLResult();
        } elseif ($this->contentType == 'text/css') {
            return $this->buildCSS();
        } elseif ($this->contentDisposition) {
            return $this->saveAttachment();
        }
        $this->buildStatic();
        exit;
    }

    public function saveAttachment()
    {
        list(, $f) = explode(';', $this->contentDisposition, 2);
        list(, $fn) = explode('=', $f, 2);
        $this->attachmentFile = mb_convert_encoding(urldecode($fn), 'utf-8');
        $saveDir = "/static/attachment/{$this->firstUrlChar}/";
        if (!is_dir(DATA_DIR . $saveDir)) {
            mkdir(DATA_DIR . $saveDir, 0777, true);
        }
        rename($this->cacheFile, DATA_DIR . $saveDir . $this->attachmentFile);
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /data{$saveDir}{$this->attachmentFile}");
        fclose($this->cache);
        exit;
    }

    public function buildStatic()
    {
        fclose($this->cache);
        if (strpos($this->contentType, 'javascript') !== false) {
            $path = 'js';
        } elseif (strpos($this->contentType, 'image') !== false) {
            $path = 'img';
        } else {
            $path = 'file';
        }

        $savePath = $this->saveStatic($path);
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /data{$savePath}");
        exit;
    }

    public function saveStatic($path)
    {
        $savePath = "/static/{$path}/{$this->firstUrlChar}/{$this->urlHash}.{$path}";
        if (!file_exists(DATA_DIR . $savePath)) {
            $saveDir = dirname(DATA_DIR . $savePath);
            if (!is_dir($saveDir)) {
                mkdir($saveDir, 0777, true);
            }
            rename($this->cacheFile, DATA_DIR . $savePath);
        }
        return $savePath;
    }

    public function buildCSS()
    {
        $content = file_get_contents($this->cacheFile);
        $content = preg_replace_callback('/(url\([\'"]{0,1})([^\)^(^\'^"]+)([\'"]{0,1}\))/im', function ($m) {
            return $m[1] . $this->buildUrl($m[2]) . $m[3];
        }, $content);
        file_put_contents($this->cacheFile, $content);
        $savePath = $this->saveStatic('css');
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /data{$savePath}");
        exit;
    }

    public function loadHTML($charset)
    {
        libxml_use_internal_errors(true);
        include_once __DIR__ . '/HTML5.php';
        if (stripos($this->content, '<!DOCTYPE html>') !== false) {
            $dom = new \Masterminds\HTML5();
            return $dom->loadHTML($this->content);
        } else {
            $dom = new DOMDocument();
        }
        $dom->encoding = $charset;
        try {
            SubDOMException::$context = $this->content;
            $dom->loadHTML($this->content, LIBXML_NOENT | LIBXML_DTDLOAD | LIBXML_COMPACT | LIBXML_PARSEHUGE);
        } catch (DOMException $e) {
            $this->dump($e->getCode());
            $this->dump($e->getMessage());
            exit;
        }
        return $dom;
    }

    public function getHTMLCharset($dom)
    {
        $meta = $dom->getElementsByTagName('meta');

        foreach ($meta as $m) {
            if ($m->getAttribute('charset')) {
                $this->contentCharset = $m->getAttribute('charset');
                if (strcasecmp($this->contentCharset, 'utf-8') !== 0) {
                    $m->setAttribute('charset', 'utf-8');
                    $this->contentCharset = 'utf-8';
                }
            } elseif ($m->hasAttribute('http-equiv') && strtolower($m->getAttribute('http-equiv')) == 'content-type') {
                $this->parseContentType($m->getAttribute('content'));
                if (strcasecmp($this->contentCharset, 'utf-8') !== 0) {
                    $m->setAttribute('content', 'text/html; utf-8');
                    $this->contentCharset = 'utf-8';
                }
            }
        }
    }

    public function replaceCallback($m)
    {
        return htmlentities($m[1]) . $m[2];
    }

    public function inBlack($src)
    {
        $host = parse_url($src, PHP_URL_HOST);
        $black = array('cnzz.com', 'viidii.info', 'google-analytics.com', 'googlesyndication.com', 'hm.baidu.com');
        foreach ($black as $d) {
            if (strpos($host, $d) !== false) {
                return true;
            }
        }
        return false;
    }

    public function buildHTMLResult()
    {
        $clear = explode('</html>', $this->content, 2);
        $this->content = trim($clear[0]) . '</html>';
        $ch = mb_detect_encoding($this->content, 'gb2312,gbk,big5,gb18030,utf-8,UNICODE');
        if (strcasecmp($ch, 'utf-8')) {
            $this->content = iconv($ch, 'utf-8', $this->content);
            preg_match('/charset=("|\')*([a-z0-9\-]+)("|\')*/im', $this->content, $m);
            $this->content = preg_replace('/charset=("|\')*([a-z0-9\-]+)("|\')*/im', 'charset=${1}utf-8${3}', $this->content);
        }

        //解决如果<title>等包含了中文字符的标签出现在字符编码申明前时中文乱码问题
        $injection = <<<EOF
 <head><meta charset="utf-8" /><meta id="--tk-origin-domain" host="{$this->host}" url="{$this->url}" path="{$this->requestFolder}" scheme="{$this->scheme}"><script>var urlinfo = {host:'{$this->host}',url:'{$this->url}', path : "{$this->requestFolder}",scheme : "{$this->scheme}"};
 navigator.__tk_sendBeacon = function(url,data){data = data || '';console.log(url);url=parent._TKProxy.padUrl(parent.rpath(url,urlinfo))+'&isbeacon=1';console.log(url);return navigator.sendBeacon(url,data);}</script><style>img {max-width:100%;}input[type="image"]{max-width:100%;}</style>
EOF;
        $this->content = str_replace('<head>', "<head>{$injection}", $this->content);

        //解决链接中的&符号问题
        /* $this->content = preg_replace('/(<[a-z]+[^>^<]+)&([^<^>]+[a-z]+\/?>)/', '${1}&amp;${2}', $this->content); */

        //解决一些字符为转为HTML实体的问题
        // $this->content = preg_replace_callback('/(<+)(<)/i', array($this, 'replaceCallback'), $this->content);
        // $this->content = preg_replace_callback('/(>+)(>)/i', array($this, 'replaceCallback'), $this->content);
        // $this->content = preg_replace_callback('/(<+)([^a-z^\/])/i', array($this, 'replaceCallback'), $this->content);

        $dom = $this->loadHTML($ch);

        $this->getHTMLCharset($dom);

        $imgList = $dom->getElementsByTagName('img');

        foreach ($imgList as $img) {
            $this->replaceUrl($img, 'src', 'img');
            $srcset = $img->getAttribute('srcset');
            if ($srcset) {
                $srcsets = explode(',', $srcset);
                $newSrcSets = '';
                foreach ($srcsets as $ss) {
                    list($srcUrl, $times) = explode(' ', $ss);
                    $newSrcSets .= $this->buildUrl($srcUrl) . ' ' . $times . ' ';
                }
                $img->setAttribute('srcset', $newSrcSets);
            }
        }
        $scriptList = $dom->getElementsByTagName('script');
        foreach ($scriptList as $script) {
            $src = $script->getAttribute('src');
            if ($src) {
                if ($this->inBlack($src)) {
                    $script->parentNode->removeChild($script);
                    continue;
                }
                $this->replaceUrl($script, 'src', 'js');
                $script->setAttribute('isreplace', 1);
            } else {
                // $script->textContent = preg_replace_callback('/([\w]\.src\s?=\s?)([\'"]?)([^;^\'^"]+)([\'"]?);/im', function($matches) {
                //     $r = empty($matches[2]) ? 'parent._TKProxy.padUrl(' . $matches[3] . ')' : $this->buildUrl($matches[3]);
                //     return $matches[1] . $matches[2] . $r . $matches[4] . ';';
                //}, $script->textContent);
                $script->textContent = str_replace('.sendBeacon', '.__tk_sendBeacon', $script->textContent);
            }
        }

        $styleList = $dom->getElementsByTagName('style');
        foreach ($styleList as $style) {
            $style->textContent = preg_replace_callback('/url\(([\'\"]?)([^\'^"]+)([\'\"]?)\)/i', function ($matches) {
                return 'url(' . $matches[1] . $this->buildUrl($matches[2]) . $matches[3] . ')';
            }, $style->textContent);
        }

        $formList = $dom->getElementsByTagName('form');
        foreach ($formList as $form) {
            $this->replaceUrl($form, 'action');
            $form->setAttribute('target', '_blank');
        }

        $linkList = $dom->getElementsByTagName('link');
        foreach ($linkList as $link) {
            if ($link->getAttribute('rel') === 'stylesheet') {
                $this->replaceUrl($link, 'href', 'css');
            } elseif ($link->getAttribute('ref') == 'preload') {
                $type = $link->getAttribute('as');
                $this->replaceUrl($link, 'href', $type);
            }
        }
        $aList = $dom->getElementsByTagName('a');
        foreach ($aList as $a) {
            $this->replaceUrl($a, 'href');
            $a->setAttribute('target', '_blank');
        }

        $metaList = $dom->getElementsByTagName('meta');
        foreach ($metaList as $meta) {
            if (strtolower($meta->getAttribute('http-equiv')) === 'refresh') {
                $content = $meta->getAttribute('content');
                $str = explode('=', $content, 2);
                $replace = $str[0] . '=' . $this->buildUrl($str[1]);
                $meta->setAttribute('content', $replace);
            }
        }
        $this->content = $dom->saveHTML();
    }

    public function getRequest()
    {
        if (empty($_COOKIE['hk'])) {
            return 0;
        }
        if(!empty($_GET['p'])) {
            return "https://www.google.com{$_GET['p']}";
        }
        if (empty($_GET['d'])) {
            return false;
        }
        if (!empty($_GET['r'])) {
            $this->referer = $this->decrypt($_GET['r']);
        }
        if(!empty($_GET['isbeacon'])) {
            $this->isbeacon = true;
        }
        
        $url = $this->decrypt($_GET['d']);
        $this->setUrlInfo($url);
        return $url;
    }

    public function setUrlInfo($url)
    {
        $urlInfo = parse_url($url);
        $this->url = $url;
        $this->host = $urlInfo['host'];
        $this->scheme = $urlInfo['scheme'];
        $this->requestFolder = parse_url($this->url, PHP_URL_PATH);
        if (strrpos($this->requestFolder, '/') !== strlen($this->requestFolder)) {
            $this->requestFolder = dirname($this->requestFolder);
        }
    }

    public function decrypt($str)
    {
        $en = XXTEA::decrypt(base64_decode($str), $this->key);
        return $en;
    }

    public function encrypt($str)
    {
        return base64_encode(XXTEA::encrypt($str, $this->key));
    }

    public function cookie()
    {
        if (empty($_COOKIE['hk'])) {
            setcookie('hk', uniqid(), 24 * 365 * 3600 + time());
            return $this->key;
        }
        return '';
    }

    public function displayPage()
    {
        $key = $this->cookie();
        ?>
        <html>
            <head>
                <meta charset="utf-8" />
                <meta name="viewport" content="width = device-width, initial-scale = 1, maximum-scale = 1, user-scalable = no" />
                <title>Toknot Poxy</title>
            </head>
            <body style="padding: 0;margin: 0;">
                <script>window.enkey = '<?=$key?>';</script>
                <script src="/xxtea.min.js"></script>
                <script src="/main.js"></script>
                <div id="header">
                    <h3 style="float: left; width: 300px;margin:0px;"><a href="http://toknot.com">Toknot</a></h3>
                    <input type = "url" id = "url" style = "border:1px solid;color: #000;font-size:16px;height:25px;width:65%;">
                    <button onclick = "_TKProxy.openUrl();" type = "button">打开</button></div>
                <iframe id="displayPage" height="100%" width="100%" allowfullscreen="true" scrolling="no" src="javascript:void(0);"></iframe>
                <script>
                    var c = _TKProxy.decrypt('<?=$this->content?>');
                    var dp = document.getElementById('displayPage');
                    var xy = window.screen;
                    dp.height = (xy.availHeight - document.getElementById('header').offsetHeight) + 'px';
                    dp.width = xy.availWidth + 'px';
                    var resizeEvent = false;
                    var time = 2000;
                    var urlinfo = {};
                    function autocheck() {
                        if (num > 5) {
                            time = 10000;
                        } else if (num > 10) {
                            time = num * time;
                        }
                        if (dp.contentDocument.bdoy && !resizeEvent) {
                            dp.contentDocument.body.onresize = function () {
                                console.log('onresize');
                                resize(dp);
                            };
                            resizeEvent = true;
                        }
                        console.log('resize');
                        resize(dp);
                        num = num + 1;
                        var urlmeta = dp.contentDocument.getElementById('--tk-origin-domain');
                        urlinfo.host = urlmeta.getAttribute('host');
                        url.url = urlmeta.getAttribute('url');
                        url.scheme = urlmeta.getAttribute('scheme');
                        url.path = urlmeta.getAttribute('path');

                        setTimeout(autocheck, time);
                        replaceSrc(urlinfo,dp, blackLink);
                    }
                    try {
                        dp.contentDocument.write(c);
                    } catch (ex) {
                        console.log(ex);
                    } finally {
                        //dp.src = "data:text/html;charset=utf-8," + c;
                        var host = window.location.host;
                        var blackLink = ['cnzz.com', 'google-analytics.com', 'googlesyndication.com', 'hm.baidu.com'];
                        var num = 0;
                        autocheck();
                    }

                </script></body></html>
        <?php
}

    public function homePage($message = '')
    {
        $key = $this->cookie();
        ?>
        <html>
            <head>
                <title>Toknot Proxy</title>
                <meta http-equiv="Content-Type" content="text / html;
                      charset = UTF-8"/>
                <meta name="viewport"
                      content="width = device-width, initial-scale = 1, maximum-scale = 1, user-scalable = no" />
                <script>window.enkey = '<?=$key?>';</script>
                <script src="/xxtea.min.js"></script>
                <script src="/main.js"></script>
            </head>
            <body style="padding: 0;margin: 0;">
                <h3 style="float: left; width: 300px;margin:0px;"><a href="http://toknot.com">Toknot</a></h3>
                <input type = "url" id = "url" style = "border:1px solid;color: #000;font-size:16px;height:25px;width:65%;">
                <button onclick = "_TKProxy.openUrl();" type = "button">打开</button>
                <b style="color:red;"><?=$message?></b>
               <!-- <iframe frameborder = "0" id = "viewPage" width = "100%" height = "100%" scrolling = "no" allowfullscreen = "true" src = "javascript:void(0);" style = "border: none;"></iframe>-->
            </body>
        </html>
        <?php
}

}

new To();
