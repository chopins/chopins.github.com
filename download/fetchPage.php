<?php

/**
 * Toknot (http://toknot.com)
 *
 * @copyright  Copyright (c) 2011 - 2007 Toknot.com
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/toknot
 */

/**
 * 抓取页面
 */
class FetchPage {

    private $userAgent = 'Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0';
    private $referer = '';
    private $curlOpt = array();
    private $content = '';
    private $url;

    public function __construct($url, $referer = '') {
        $this->setReferer($referer);
        $this->url = $url;
        $this->getPage($url);
    }

    public function setReferer($referer) {
        $this->referer = $referer;
    }

    public function getContent() {
        return $this->content;
    }

    public function setCurlOpt($url) {
        $this->curlOpt = array(CURLOPT_URL => $url,
            CURLOPT_COOKIEJAR => "php://memory",
            CURLOPT_COOKIEFILE => "php://memory",
// CURLOPT_COOKIE => $this->cookie,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_REFERER => $this->referer,
//CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_ENCODING => '',
            CURLOPT_RETURNTRANSFER => true
        );
    }

    private function getPage($url) {
        do {
            Tools::msg("GET: $url");
            $ch = curl_init();
            $this->setCurlOpt($url);
            curl_setopt_array($ch, $this->curlOpt);
            $this->content = curl_exec($ch);
        } while (!$this->content && sleep(1) === 0);
    }

}

/*
 * 多页面抓取
 */

class LoopFetch {

    private $maskChar = '@@MASK@@';
    private $urls = [];
    private $referer = '';
    private $result = [];

    public function __construct($referer = '') {
        $this->referer = $referer;
    }

    public function setMask($mask) {
        $this->maskChar = $mask;
    }

    public function setUrl($url, $params) {
        foreach ($params as $p) {
            $this->urls[] = Tools::replaceMask($url, $p, $this->maskChar);
        }
    }

    public function getFetchResult() {
        return $this->result;
    }

    protected function result($url, $page, $callback) {
        $content = $page->getContent();
        $this->result[] = $callback($content, $url, $this->referer);
    }

    public function fetch($callback) {
        foreach ($this->urls as $url) {
            $page = new FetchPage($url, $this->referer);
            $this->result($url, $page, $callback);
        }
    }

}

class XMLError extends Exception {

    public $maxlen = 60;

    public function __construct(libXMLError $error, HTMLDOM $dom) {
        $lines = explode(PHP_EOL, $dom->getHtml());
        $errorline = $lines[$error->line - 1];
        $currorOffset = $error->column - 1;
        if (mb_strlen($errorline) > $this->maxlen) {
            $errorline = mb_substr($errorline, $error->column - $this->maxlen, $this->maxlen * 2);
            $currorOffset = $currorOffset - $error->column + $this->maxlen;
        }

        $cursor = str_repeat('-', $currorOffset) . '^';
        $msg = trim($error->message) . " in line $error->line : $error->column ($error->code) \n$errorline\n$cursor\n";
        $this->message = $msg;
        parent::__construct($this->message, $error->code);
    }

}

class HTMLDOM extends DOMDocument {

    private $content = '';

    public function __construct($content = '', $addBody = false) {
        parent::__construct();
        $this->content = $content;
        if ($addBody) {
            $this->addBody();
        }
        $this->correctionHTML();
        $this->importHTML();
    }

    protected function addBody() {
        $this->content = "<html><head></head><body>{$this->content}</body></html>";
    }

    public function updateHTML($content) {
        $this->content = $content;
    }

    public function getHtml() {
        return $this->content;
    }

    public function reloadHTML() {
        $this->importHTML();
    }

    public function getXPathDom() {
        return new DOMXPath($this);
    }

    private function importHTML() {
        libxml_use_internal_errors(true);

        $this->loadHTML($this->content, LIBXML_DTDATTR | LIBXML_HTML_NOIMPLIED | LIBXML_COMPACT | LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE);
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            libxml_clear_errors();
            throw new XMLError($error, $this);
        }
    }

    public function replaceCallback($m) {
        return htmlentities($m[1]) . $m[2];
    }

    private function correctionHTML() {
        $ch = mb_detect_encoding($this->content, 'gb2312,gbk,big5,gb18030,utf-8,UNICODE');
        if (strcasecmp($ch, 'utf-8')) {
            $this->content = iconv($ch, 'utf-8', $this->content);
            $m = [];
            preg_match('/charset=("|\')*([a-z0-9\-]+)("|\')*/im', $this->content, $m);
            $this->content = preg_replace('/charset=("|\')*([a-z0-9\-]+)("|\')*/im', 'charset=${1}gbk${3}', $this->content);
        }

        //解决如果<title>等包含了中文字符的标签出现在字符编码申明前时中文乱码问题
        $this->content = str_replace('<head>', '<head><meta charset="utf-8" />', $this->content);

        //解决链接中的&符号问题
        $this->content = preg_replace('/(<[^>^<]+)&([^;]{0,9})([^<^>]+>)/', '${1}&amp;${2}${3}', $this->content);

        //解决一些字符为转为HTML实体的问题
        $this->content = preg_replace_callback('/(<+)(<)/i', array($this, 'replaceCallback'), $this->content);
        $this->content = preg_replace_callback('/(>+)(>)/i', array($this, 'replaceCallback'), $this->content);
        $this->content = preg_replace_callback('/(<+)([^a-z^\/])/i', array($this, 'replaceCallback'), $this->content);
    }

    public function nodes() {
        return new Nodes($this);
    }

}

class HTMLElement {

    protected $dom;
    protected $xpathDom;

    public function __construct(HTMLDOM $dom) {
        $this->dom = $dom;
        $this->xpathDom = $dom->getXPathDom();
    }

    public function getById($id) {
        return $this->dom->getElementById($id);
    }

    public function getNodeByXpath($xpath) {
        return $this->xpathDom->query($xpath)->item(0);
    }

    public function getAttrById($id, $name) {
        $node = $this->getById($id);
        return $node->getAttribute($name);
    }

    public function getContentById($id) {
        $node = $this->getById($id);
        return $node->textContent;
    }

    public function getAttrByXpath($xpath, $name) {
        $node = $this->getNodeByXpath($xpath);
        return $node->getAttribute($name);
    }

    public function getContentByXpath($xpath) {
        $node = $this->getNodeByXpath($xpath);
        return $node->textContent;
    }

}

class Nodes extends HTMLElement {

    private $len;
    private $start = 1;

    public function getNodes($xpath) {
        return $this->xpathDom->query($xpath);
    }

    public function getSequenceNodes($xpath, $len, $start = 1) {
        $ret = [];
        for ($i = $start; $i <= $len; $i++) {
            $rxpath = Tools::replaceNum($xpath, $i);
            $ret[] = $this->getNodeByXpath($rxpath);
        }
        return $ret;
    }

    public function setOffset($start) {
        $this->start = $start;
    }

    public function detectionIndexesLen($xpath) {
        $this->len = $this->getNodes($xpath)->length;
    }

    public function getCount() {
        return $this->len;
    }

    public function getNodesData($xpath, $params = []) {
        $ret = [];

        for ($i = $this->start; $i <= $this->len; $i++) {
            $rxpath = Tools::replaceMask($xpath, $i);
            $node = $this->getNodeByXpath($rxpath);
            $content = $node->textContent;
            $attrs = [];
            foreach ($params as $a) {
                $attrs[$a] = $node->getAttribute($a);
            }
            $ret[] = [$content, $attrs];
        }
        return $ret;
    }

    public function getNodesContent($xpath) {
        $ret = [];

        for ($i = $this->start; $i < $this->len; $i++) {
            $rxpath = Tools::replaceMask($xpath, $i);
            $ret[] = $this->getContentByXpath($rxpath);
        }
        return $ret;
    }

}

class PageDOM extends HTMLDOM {

    public function __construct($url, $referer, $addBody = false) {
        $p = new FetchPage($url, $referer);
        parent::__construct($p->getContent(), $addBody);
    }
}

class Tools {

    public static function replaceMask($str, $i, $mask = '@@NUM@@') {
        return str_replace($mask, "$i", $str);
    }

    public static function msg($msg) {
        echo "$msg" . PHP_EOL;
    }

    /**
     * 还原字符串中的 html 实体字符
     * 
     * @param string $str
     * @return string
     */
    public static function strip_htmlenties($str) {
        $enties = get_html_translation_table(HTML_ENTITIES);
        return str_replace($enties, array_keys($enties), $str);
    }

    public static function path2Url($url, $referer) {
        $info = parse_url($url);
        $refererInfo = parse_url($referer);
        $path = $refererInfo['path'];
        list($host) = explode($path, $referer);

        //完整URL
        if (!empty($info['scheme'])) {
            return $url;
        }

        //绝对路径
        if (strpos($url, '/') === 0) {
            return $host . $url;
        }

        //相对路径
        if (strrpos($path, '/') === (strlen($path) - 1)) {
            return $host . $path . $url;
        }
        return $host . dirname($path) . $url;
    }

}

$bs = '丨亅丿乛一乙乚丶八勹匕冫卜厂刀刂儿二匚阝丷几卩冂力冖凵人亻入十厶亠匸讠廴又艹屮彳巛川辶寸大飞干工弓廾广己彐彑巾口马门宀女犭山彡尸饣士扌氵纟巳土囗兀夕小忄幺弋尢夂子贝比灬长车歹斗厄方风父戈卝户火旡见斤耂毛木肀牛牜爿片攴攵气欠犬日氏礻手殳水瓦尣王韦文毋心牙爻曰月爫支止爪白癶歺甘瓜禾钅立龙矛皿母目疒鸟皮生石矢示罒田玄穴疋业业用玉耒艸臣虫而耳缶艮虍臼米齐肉色舌覀页先行血羊聿至舟衣竹自羽糸糹貝采镸車辰赤辵豆谷見角克里卤麦身豕辛言邑酉豸走足青靑雨齿長非阜金釒隶門靣飠鱼隹風革骨鬼韭面首韋香頁音髟鬯鬥高鬲馬黄鹵鹿麻麥鳥魚鼎黑黽黍黹鼓鼠鼻齊齒龍龠';

$bsArr = [];
for ($i = 0; $i < mb_strlen($bs); $i++) {
    $bsArr[] = mb_substr($bs, $i, 1);
}

file_put_contents(__DIR__ . '/data/词.txt', '');

Tools::msg("部首共计：" . count($bsArr) . "个");

$f = new LoopFetch('http://www.zdic.net/c/cibs/');
$f->setUrl('http://www.zdic.net/c/cibs/bs/?bs=@@MASK@@', $bsArr);

$f->fetch(function($content, $url, $referer) {
    $dom = new HTMLDOM($content, true);
    $indexes = $dom->nodes();
    $indexes->detectionIndexesLen('/html/body/div/li');
    $list = $indexes->getNodesContent('/html/body/div/li[@@NUM@@]/a');
    $ret = '';
    $len = $indexes->getCount();
    Tools::msg("汉字：$len 个");
    foreach ($list as $i => $node) {
        $page = 1;
        $num = 1;
        $getFlag = true;
        do {
            $url = Tools::path2Url('sc/?z=' . $node . '|' . $num, 'http://www.zdic.net/c/cibs/ci/');
            $subDom = new PageDOM($url, $referer, true);
            $indexesSub = $subDom->nodes();
            if ($getFlag) {
                $recourd = $indexesSub->getContentByXpath('/html/body/div/h2');
                list(, $count, ) = explode(' ', $recourd);
                $page = ceil($count / 300);
                $getFlag = false;
            }
            $page--;
            $num++;
            $indexesSub->detectionIndexesLen('/html/body/div/li');

            Tools::msg("$i 、词:" . $indexesSub->getCount() . '个');
            $ci = $indexesSub->getNodesContent('/html/body/div/li[@@NUM@@]/a');
            $ret = implode(PHP_EOL, $ci) . PHP_EOL;
            file_put_contents(__DIR__ . '/data/词.txt', $ret, FILE_APPEND);
        } while ($page > 0);
    }
    return $list;
});

