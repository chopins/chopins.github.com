<?php

/**
 * Toknot (http://toknot.com)
 *
 * @copyright  Copyright (c) 2011 - 2007 Toknot.com
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/toknot
 */
define('DATA_DIR', __DIR__ . '/data/');
include '../auth.php';
set_error_handler(function () {
    $args = func_get_args();
    //throw new SubDOMException($args[1], $args[0]);
});

set_exception_handler(function ($e) {
    echo '<pre>';
    echo $e;
    echo '<pre>';
});

spl_autoload_register(function ($class) {
    $realpath = __DIR__ . str_replace(array('Masterminds', '\\'), array('', DIRECTORY_SEPARATOR), $class) . '.php';
    if (file_exists($realpath)) {
        include $realpath;
    }
});

/*
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
  } */

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
    protected $noscheme = 1;
    protected $url = '';
    protected $port = 80;
    protected $contentType = '';
    protected $contentCharset = '';
    protected $ContentTypeHeader = '';
    protected $contentDisposition = '';
    protected $content = '';
    protected $allheader = array();
    protected $requestType = '';
    protected $urlHash = '';
    protected $firstUrlChar = '';
    protected $mineTypeList = array('js' => 'javascript', 'css' => 'css', 'img' => 'image', 'stream' => 'octet-stream');
    protected $cache = null;
    protected $cacheFile = '';
    protected $cacheAllFile = [];
    protected $attachmentFile = '';
    protected $requestFolder;
    protected $isbeacon = false;
    protected $nocache = false;
    protected $noscript = false;
    protected $encoding = '';
    public function __construct()
    {
        $url = $this->getRequest();
        if ($url && $this->noscheme) {
        }
        if ($url) {
            $this->envVar();
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
            header("Location: https://proxy.toknot.com/data{$savePath}");
            //header("Location: https://cdn.toknot.com/proxy{$savePath}");
            exit;
        }
    }

    public function noscript()
    {
        if (isset($_GET['n'])) {
            $this->noscript = true;
        }
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

    public function dump()
    {
        $arg = func_get_args();
        echo '<pre>';
        foreach ($arg as $mix) {
            var_dump($mix);
        }
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
        $phpVar = $this->feild2Var($field);
        $value = $this->getVar($phpVar);
        if ($value) {
            $this->headers[] = "$field: $value";
        }
    }

    public function envVar()
    {
        $this->userAgent = $this->getVar('HTTP_USER_AGENT');
        $this->method = $this->getVar('REQUEST_METHOD');
        $this->cookie = '';

        foreach($_COOKIE as $k => $v) {
            if($k=='_r_q' || $k=='_t_k') {
                continue;
            }
            list($domain, $path, $name) = explode('#', $k);
            $path = str_replace(['*', '$'], ['/', '.'] , $path);
            $domain = str_replace('*', '.' , $domain);
            if($path && $path != '/' && $this->requestFolder != $path) {
                continue;
            }
            if(strcasecmp($domain, $this->host) == 0) {
                $this->cookie .= $name . '=' .  urlencode($v);
            } elseif(str_ends_with(strtolower($this->host), strtolower($domain))) {
                $this->cookie .= $name . '=' .  urlencode($v);
            }
        }
    }

    public function setOpenHeader()
    {
        $this->addHeader('Accept');
        $this->addHeader('Accept-Charset');
        //$this->addHeader('Accept-Encoding');
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

        //$ip = gethostbyname(gethostbyname($this->host));
        $this->curlOpt = array(
            CURLOPT_URL => $url,
            //CURLOPT_COOKIEJAR => DATA_DIR . '/cookie/' . $this->host,
            //CURLOPT_COOKIEFILE => DATA_DIR . '/cookie/' . $this->host,
            CURLOPT_COOKIE => $this->cookie,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_REFERER => $this->referer,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_ENCODING => '',
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_DNS_CACHE_TIMEOUT => 86400,
            CURLOPT_DNS_USE_GLOBAL_CACHE => true,
            //CURLOPT_RESOLVE => array("{$this->host}:{$this->port}:{$ip}"),
            CURLOPT_HEADERFUNCTION => array($this, 'responseHeader'),
        );
        if (!$this->isbeacon) {
            //$this->curlOpt[CURLOPT_RETURNTRANSFER] = true;
            $this->curlOpt[CURLOPT_FILE] = $this->cache;
        }
        if ($this->method == 'POST') {
            $data = $_POST;
            if (!empty($_FILES)) {
                $this->movePostFile($data, $_FILES);
            }
            $this->curlOpt[CURLOPT_POST] = true;
            $this->curlOpt[CURLOPT_POSTFIELDS] = $data;
        }
    }


    public function movePostFile(&$data, $files)
    {
        foreach ($files as $k => $f) {
            if (isset($f['tmp_name']) && !is_array($f['tmp_name'])) {
                $path = DATA_DIR . 'static/cache/' . md5($f['name'] . microtime() . $k . 'post_update_3&^3md');
                if (move_uploaded_file($f['tmp_name'], $path)) {
                    $data[$k] = new CURLFile($path, $f['type'], $f['name']);
                }
                $this->cacheAllFile[] = $path;
            } else {
                $this->movePostFile($data, $f);
            }
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

    public function getHeader($header, $name)
    {
        if (stripos(trim($header), $name) === 0) {
            list(, $v) = explode(' ', $header, 2);
            return trim($v);
        }
        return false;
    }

    public function startWith($haystack, $needle, $case = false)
    {
        if ($case) return strpos($haystack, $needle) === 0;
        return stripos($haystack, $needle) === 0;
    }

    public function responseHeader($ch, $header)
    {
        $this->allheader[] = $header;
        if (($type = $this->getHeader($header, 'Content-Type'))) {
            $this->ContentTypeHeader = trim($header);
            $this->parseContentType($type);
        } elseif (($type = $this->getHeader($header, 'Content-Disposition')) && $this->startWith($type, 'attachment')) {
            $this->contentDisposition = trim($header);
        } elseif (($url = $this->getHeader($header, 'Location'))) {
            if (!$this->startWith($url, 'http://') && !$this->startWith($url, 'https://')) {
                $url = $this->rPath($url);
            }
            $this->setUrlInfo($url);
        } elseif (($c = $this->getHeader($header, 'Cache-control')) && (stripos($c, 'no-cache') || stripos($c, 'no-store'))) {
            $this->nocache = true;
        } else if (($cookie = $this->getHeader($header, 'Set-Cookie'))) {
            $this->responseCookie($cookie);
        } elseif(($ecode = $this->getHeader($header, 'Content-Encoding'))) {
            $this->encoding = $ecode;
        }
        return strlen($header);
    }

    public function responseCookie($cookie)
    {
        $set = explode(';', $cookie);
        $domain = $this->host;
        $nv = '';
        foreach ($set as $v) {
            $fs = explode('=', $v, 2);
            if (strcasecmp(trim($fs[0]), 'domain') === 0) {
                $domain = trim($fs[1]);
            } elseif(strcasecmp(trim($fs[0]), 'path') === 0) {
                $p = str_replace(['/', '.'], ['*', '$'], $fs[1]);
            } else {
                $nv .= $v.';';
            }
        }
        $domain = str_replace('.', '*', $domain);
        $cookie = $domain .'#'. $p . '#' . $nv;
        header("Set-Cookie: $cookie");
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
        register_shutdown_function(function() {
            if($this->cacheFile && file_exists($this->cacheFile)) {
                unlink($this->cacheFile);
            }
           foreach($this->cacheAllFile as $f) {
               if(file_exists($f)) {
                   unlink($f);
               }
           }
        });
        $ch = curl_init();
        $this->setCurlOpt($url);

        curl_setopt_array($ch, $this->curlOpt);
        $res = curl_exec($ch);
        $errno = curl_errno($ch);
        if ($errno == 60) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $res = curl_exec($ch);
        }
        if (!$res) {
            $error = curl_error($ch);
            $this->homePage("Can not open '$url' ($error) {$this->encoding}");
            exit;
        }
        if ($this->isbeacon) {
            exit;
        }
        $this->buildResult();
        //$this->content = json_encode($this->encrypt($this->content, true));
        //$this->content = base64_encode(rawurlencode($this->content));
        echo $this->content;
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
        if (stripos($src, './') === 0 || strpos($src, '/') !== 0) {
            $src = "{$this->scheme}://{$this->host}{$this->requestFolder}/" . ltrim($src, './');
        } elseif (($c = substr_count($src, '../')) > 0) {
            $path = $this->requestFolder;
            for ($i = 0; $i < $c; $i++) {
                $path = dirname($path);
            }
            $src = "{$this->scheme}://{$this->host}{$path}/" . ltrim($src, './');
        } else {
            $src = "{$this->scheme}://{$this->host}/" . ltrim($src, '/');
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
        return '/g.php?d=' . $this->urlencrypt($src) . '&r=' . $this->urlencrypt($this->url) . '&t=' . $type;
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
        //return urlencode(base64_encode($str));
        return $this->encrypt($str);
    }

    public function buildResult()
    {
        $mineType = $isstream = false;
        if (stripos($this->ContentTypeHeader, $this->mineTypeList['stream']) !== false) {
            $isstream = true;
        }
        if ($this->requestType && $isstream) {
            $mineType = $this->mineTypeList[$this->requestType];
        } elseif (isset($this->mineTypeList[$this->requestType])) {
            $mineType = true;
        } elseif ($isstream) {
            $mineType = true;
        }

        if ($this->requestType && !$mineType) {
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
        $fn = trim(mb_convert_encoding(urldecode($fn), 'utf-8'), ";'\"\s\r\n");
        $this->attachmentFile = md5($fn) . substr($fn, strrpos($fn, '.'));
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

    protected function imageType()
    {
        $ext = ['jpg' => 'jpg', 'jpeg' => 'jpg', 'png' => 'png', 'gif' => 'gif'];
        foreach ($ext as $type => $ext) {
            if (strpos($this->contentType, $type) !== false) {
                return $ext;
            }
        }
        return 'img';
    }

    public function buildStatic()
    {
        fclose($this->cache);
        if (strpos($this->contentType, 'javascript') !== false) {
            $path = 'js';
        } elseif (strpos($this->contentType, 'image') !== false) {
            $path = $this->imageType();
        } else {
            $path = 'file';
        }

        $savePath = $this->saveStatic($path);
        if ($this->nocache) {
            echo file_get_contents($savePath);
            unlink($savePath);
            exit;
        }
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: https://proxy.toknot.com/data{$savePath}");
        // header("Location: https://cdn.toknot.com/proxy{$savePath}");
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

        $content = preg_replace_callback('/url\(([\'\"^\(^\)]?)([^\'^"^\)^\(]+)([\'\"^\)^\(]?)\)/i', function ($matches) {
            $matches[1] = empty($matches[1]) ? '\'' : $matches[1];
            $matches[3] = empty($matches[3]) ? '\'' : $matches[3];
            return 'url(' . $matches[1] . $this->buildUrl($matches[2]) . $matches[3] . ')';
        }, $content);

        file_put_contents($this->cacheFile, $content);
        $savePath = $this->saveStatic('css');
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: https://proxy.toknot.com/data{$savePath}");
        exit;
    }

    public function loadHTML($charset)
    {
        include_once __DIR__ . '/HTML5.php';
        if (stripos($this->content, '<!DOCTYPE html>') !== false) {
            $dom = new \Masterminds\HTML5();
            return $dom->loadHTML($this->content);
        } else {
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
        }
        $dom->encoding = $charset;
        try {
            //SubDOMException::$context = $this->content;
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
        if ($this->noscript) {
            $this->content = preg_replace('/<script([^>]*)>/i', '<!--<script$1>', $this->content);
            $this->content = str_ireplace('</script>', '</script>-->', $this->content);
        }
        $clear = explode('</html>', $this->content, 2);
        $this->content = trim($clear[0]) . '</html>';
        $ch = mb_detect_encoding($this->content, 'utf-8,gb2312,gbk,big5,gb18030,UNICODE');
        if (preg_match('/<meta([^>]*)charset=([^>]*)utf-8([^>]*)\/>/', $this->content)) {
            $pageCharset = 'utf-8';
        }
        if (strcasecmp($ch, 'utf-8') && $pageCharset != 'utf-8') {
            $this->content = iconv($ch, 'utf-8', $this->content);
            preg_match('/charset=("|\')*([a-z0-9\-]+)("|\')*/im', $this->content, $m);
            $this->content = preg_replace('/charset=("|\')*([a-z0-9\-]+)("|\')*/im', 'charset=${1}utf-8${3}', $this->content);
        }

        //解决如果<title>等包含了中文字符的标签出现在字符编码申明前时中文乱码问题

        $injection = $this->insertJs();
        $this->content = preg_replace('/<head([^>^<]*)>/im', "<head\$1>{$injection}", $this->content,1);

        return;
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
            $style->textContent = preg_replace_callback('/url\(([\'\"^\(^\)]?)([^\'^"^\)^\(]+)([\'\"^\)^\(]?)\)/i', function ($matches) {
                $matches[1] = empty($matches[1]) ? '\'' : $matches[1];
                $matches[3] = empty($matches[3]) ? '\'' : $matches[3];
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
            $src = $a->getAttribute('href');
            if (strpos($src, '#') === 0) {
                continue;
            }
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

        if ($_GET['_f']) {
            if (($url = $this->pathDirectRequest())) {
                $this->setUrlInfo($url);
                return $url;
            }
        }
        if (!empty($_GET['p'])) {
            return "https://www.google.com{$_GET['p']}";
        }
        if (empty($_GET['d'])) {
            return false;
        }
        if (!empty($_GET['r'])) {
            $this->referer = $this->decrypt($_GET['r']);
        }
        if (!empty($_GET['isbeacon'])) {
            $this->isbeacon = true;
        }
        $url = $this->decrypt($_GET['d']);
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = $this->scheme . '://' . $url;
        }
        $this->setUrlInfo($url);
        return $url;
    }

    protected function pathDirectRequest()
    {
        $urlpath = $_SERVER['REQUEST_URI'];
        $referer = $_SERVER['HTTP_REFERER'];
        $query = parse_url($referer, PHP_URL_QUERY);
        parse_str($query, $queryParams);
        $refererURL = $this->decrypt($queryParams['d']);
        $rp = parse_url($refererURL);
        return $rp['scheme'] . '://' . $rp['host'] . $urlpath;
    }

    public function setUrlInfo($url)
    {
        $urlInfo = parse_url($url);
        $this->url = $url;
        $this->host = $urlInfo['host'];
        if ($urlInfo['scheme']) {
            $this->scheme = $urlInfo['scheme'];
            $this->noscheme = 0;
        }
        $this->requestFolder = parse_url($this->url, PHP_URL_PATH);
        if (isset($urlInfo['port'])) {
            $this->port = $urlInfo['port'];
        } elseif ($this->scheme === 'https') {
            $this->port = 443;
        } else {
            $this->port = 80;
        }
        if (strrpos($this->requestFolder, '/') !== strlen($this->requestFolder) - 1) {
            $this->requestFolder = dirname($this->requestFolder);
        }
    }

    public function decrypt($str)
    {
        return urldecode($str);
        return strrev(urldecode(base64_decode(urldecode(strrev(base64_decode(urldecode($str)))))));
        $en = XXTEA::decrypt(base64_decode($str), $this->key);
        return $en;
    }

    public function encrypt($str, $retArr = false)
    {
        return urlencode($str);
        return urlencode(base64_encode(strrev(urlencode(base64_encode(urlencode(strrev($str)))))));
        $len = mb_strlen($str);
        $max = 20000;
        if ($len > $max) {
            $ret = [];
            for ($i = 0; $i < $len; $i = $i + $max) {
                $chunk = mb_strcut($str, $i, $max);
                $ret[] = base64_encode(XXTEA::encrypt($chunk, $this->key));
            }
            return $ret;
        }
        if ($retArr) {
            return [base64_encode(XXTEA::encrypt($str, $this->key))];
        }
        return base64_encode(XXTEA::encrypt($str, $this->key));
    }

    public function insertJs()
    {
        //$mainJs = file_get_contents(__DIR__ . '/main.js');
        $replaceJs = file_get_contents(__DIR__ . '/replace.js');
        return <<<EOF
<style>img { max-width:100%;} input[type="image"]{max-width:100%;}</style>
<link rel="dns-prefetch" href="https://cdn.toknot.com/">
<script>
(function (w) {
var lh = w.sessionStorage.getItem('__ptk_pre_host__');if(lh != '{$this->host}') {w.localStorage.clear();w.sessionStorage.clear();w.sessionStorage.setItem('__ptk_pre_host__', '{$this->host}');}
var __injs_url_info = {host: '{$this->host}', url: '{$this->url}', path: "{$this->requestFolder}", scheme: "{$this->scheme}", noscript:"{$this->noscript}"};
var log = document.getElementById('log');
$replaceJs
})(window);
</script>
EOF;
    }

    public function homePage($message = '')
    {
?>
        <html>

        <head>
            <title>Toknot Proxy Home</title>
            <link rel="icon" href="https://toknot.com/favicon.ico" type ="image/x-icon">
            <meta http-equiv="Content-Type" content="text / html;
                      charset = UTF-8" />
            <meta name="viewport" content="width = device-width, initial-scale = 1, maximum-scale = 1, user-scalable = no" />
        </head>

        <body style="padding: 0;margin: 0;">
            <h3 style="float: left; width: 300px;margin:0px;"><a href="http://toknot.com">Toknot</a></h3>
            <form action="g.php" method="get">
                <input type="text" id="url" name="d" style="border:1px solid;color: #000;font-size:16px;height:25px;width:65%;">
                <button type="submit">打开</button>
            </form>
            <b style="color:red;"><?= $message ?></b>
            <span id="log"></span>
        </body>

        </html>
<?php
    }
}

new To();
