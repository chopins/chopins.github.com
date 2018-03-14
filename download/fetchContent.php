<?php

/**
 * Toknot (http://toknot.com)
 *
 * @copyright  Copyright (c) 2011 - 2007 Toknot.com
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/toknot
 */
class FetchPage {

    private $userAgent = 'Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0';
    private $referer = '';
    private $curlOpt = array();
    private $content = '';
    private $dom;

    public function __construct($url, $referer = '') {
        $this->setReferer($referer);
        $this->getPage($url);
    }

    public function loadHTML($charset) {

        $dom = new DOMDocument();
        $dom->encoding = $charset;
        try {
            $dom->loadHTML($this->content, LIBXML_HTML_NOIMPLIED | LIBXML_COMPACT | LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE);
        } catch (DOMException $e) {
            $this->dump($e->getCode());
            $this->dump($e->getMessage());
            exit;
        }
        $this->dom = $dom;
        return $dom;
    }

    public function getDOM() {
        $clear = explode('</html>', $this->content, 2);
        $this->content = trim($clear[0]) . '</html>';
        $ch = mb_detect_encoding($this->content, 'gb2312,gbk,big5,gb18030,utf-8,UNICODE');
        if (strcasecmp($ch, 'utf-8')) {
            $this->content = iconv($ch, 'utf-8', $this->content);
            preg_match('/charset=("|\')*([a-z0-9\-]+)("|\')*/im', $this->content, $m);
            $this->content = preg_replace('/charset=("|\')*([a-z0-9\-]+)("|\')*/im', 'charset=${1}utf-8${3}', $this->content);
        }

        //解决链接中的&符号问题
        $this->content = preg_replace('/&([^;]{0,9})/', '&amp;${1}', $this->content);

        return $this->loadHTML($ch);
    }

    public function setReferer($referer) {
        $this->referer = $referer;
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
        $ch = curl_init();
        $this->setCurlOpt($url);
        curl_setopt_array($ch, $this->curlOpt);
        $this->content = curl_exec($ch);
    }

    public function xpathDom() {
        return new DOMXPath($this->dom);
    }

}

class fetch {

    private $indexDom;
    private $indexXpathDom;
    private $saveFile;
    private $indexXpath;
    private $contentXpath;
    private $listBoxXpath;
    private $page = 1;
    private $url = '';
    private $host = '';
    private $path = '';
    private $contentId = '';
    private $indexUrls = array();

    /**
     * 
     * 
     * @param string $url       目录索引页地址，如果是多页索引目录，需要将连接中的页码使用`@@NUM@@``代替
     * @param string $saveFile  保存文件
     * @param string $page      总页数
     */
    public function __construct($url, $saveFile, $page = 1) {
        if (file_exists($save)) {
            echo "$save is exists";
            echo "是否覆盖（y|n):";
            $ask = trim(fgets(STDIN));
            if ($ask == 'y') {
                unlink($save);
            } else {
                exit('无法创建文件，退出');
            }
        }

        if ($page > 1 && strpos($url, '@@NUM@@') < 1) {
            die('must have "@@NUM@@" string hold page number');
        }
        $this->url = $url;
        $this->path = parse_url($url, PHP_URL_PATH);
        list($this->host) = explode($this->path, $this->url);
        $this->page = $page;
        $this->saveFile = $saveFile;
    }

    protected function exec($url, $referer, $params) {
        $indexPage = new FetchPage($url, $referer);
        $this->indexDom = $indexPage->getDOM();
        $this->getIndex($params);
    }

    protected function msg($msg) {
        echo "$msg" . PHP_EOL;
    }

    /**
     * 执行索引目录链接抓取
     * 
     * @params  array 传入自定义的 xpath 格式与索引链接一样
     * @return null
     */
    public function indexes($params = array()) {
        $current = $this->host;
        if ($this->page > 1) {
            for ($i = 1; $i <= $this->page; $i++) {
                $this->msg("get page $i");
                $url = $this->replaceNum($this->url, $i);
                $this->exec($url, $current, $params);
                $current = $url;
            }
        } else {
            $this->msg("get page $this->url");
            $this->exec($this->url, $current, $params);
        }
    }

    /*
     * 获取索引目录列表中的地址和标题，需要在 $this->save()调用后执行
     * 
     * @return array    单个元素为 array(url, title)
     */

    public function getIndexUrls() {
        return $this->indexUrls;
    }

    /**
     * 设置索引目录中要处理的链接的 xpath
     * 
     * @param string $xpath
     */
    public function setIndexXpath($xpath) {
        if (strpos($xpath, '@@NUM@@') < 1) {
            die('xpath string error, it like: "/html/body/div[@@NUM@@]/a" for /html/body/div[15]/p/a');
        }
        $this->indexXpath = $xpath;
    }

    /**
     * 设置内容的 xpath
     * 
     * @param string $xpath
     */
    public function setContentXpath($xpath) {
        $this->contentXpath = $xpath;
    }

    /**
     * 设置内容节点的 id, 如果有的话
     * 
     * @param string $id
     */
    public function setContentId($id) {
        $this->contentId = $id;
    }

    /**
     * 设置索引目录列表所在节点的 xpath，以便统计该页需要处理的链接数
     * 
     * @param string $xpath
     */
    public function setListBoxXpath($xpath) {
        $this->listBoxXpath = $xpath;
    }

    protected function replaceNum($str, $i) {
        return str_replace('@@NUM@@', "$i", $str);
    }

    /**
     * 还原字符串中的 html 实体字符
     * 
     * @param string $str
     * @return string
     */
    protected function strip_htmlenties($str) {
        $enties = get_html_translation_table(HTML_ENTITIES);
        return str_replace($enties, array_keys($enties), $str);
    }

    protected function saveContent($title, $content, $params) {
        $content = $this->strip_htmlenties($content);
        file_put_contents($this->saveFile, "{$title}\n" . implode(PHP_EOL, $params) . "{$content}\n", FILE_APPEND);
    }

    protected function getContentNum() {
        $nodes = $this->indexXpathDom->query($this->listBoxXpath);
        $this->max = $nodes->length;
    }

    public function getIndex($params = array()) {

        $this->indexXpathDom = new DOMXPath($this->indexDom);

        if (empty($this->listBoxXpath)) {
            die('listBoxXpath empty');
        }

        if (empty($this->indexXpath)) {
            die('index xpath empty');
        }

        $this->getContentNum();
        $this->msg("has $this->max contents");

        for ($i = 1; $i <= $this->max; $i++) {
            $nXpath = $this->replaceNum($this->indexXpath, $i);
            $nodes = $this->indexXpathDom->query($nXpath);
            $contetnUrl = $nodes->item(0)->getAttribute('href');
            $contentTitle = $nodes->item(0)->textContent;
            $passContent = array();
            foreach ($params as $p) {
                $passContent = $this->indexXpathDom->query($this->replaceNum($p, $i))->item(0)->textContent;
            }
            $this->indexUrls[] = [$this->path2Url($contetnUrl), $contentTitle, $this->url, $passContent];
        }
    }

    protected function path2Url($url) {
        $info = parse_url($url);
        if (!empty($info['scheme'])) {
            return $url;
        }
        //绝对路径
        if (strpos($url, '/') === 0) {
            return $this->host . $url;
        }

        //相对路径
        if (strrpos($this->path, '/') === (strlen($this->path) - 1)) {
            return $this->host . $this->path . $url;
        }
        return $this->host . dirname($this->path) . $url;
    }

    /**
     * 获取所有内容
     * 
     * @param array $params
     */
    public function getAllContent($params = array()) {
        $xpathDom = null;
        foreach ($this->indexUrls as $u) {
            $content = $this->getContent($u[0], $u[1], $u[2], $xpathDom);
            $passContent = array();
            foreach ($params as $xpath) {
                $passContent[] = $xpathDom->query($xpath)->item(0)->textContent;
            }
            $this->saveContent($u[1], $content, $passContent);
        }
    }

    /**
     * 获取单页内容
     * 
     * @param string $url
     * @param string $title
     * @return string
     */
    public function getContent($url, $title, $referer, &$contentXpathDom) {
        $this->msg("get $title at $url");
        $contentPage = new FetchPage($url, $referer);
        $contentDom = $contentPage->getDOM();
        if ($this->contentId) {
            $content = $contentDom->getElementById($this->contentId);
            return $content->textContent;
        }
        $contentXpathDom = new DOMXPath($contentDom);
        $nodes = $contentXpathDom->query($this->contentXpath);
        if (!$nodes->length) {
            die('no content');
        }

        return $nodes->item(0)->textContent;
    }

}

/*
  $cat = new fetch('http://m.4gbook.net/sort-1-@@NUM@@/', '', 1119);
  $cat->setListBoxXpath('/html/body/div[3]/p');
  $cat->setIndexXpath('/html/body/div[3]/p[@@NUM@@]/a[2]');
  $cat->noContent();
  $cat->indexes();
  $booklist = $cat->getIndexUrls();

  foreach ($booklist as $b) {

  $save = __DIR__ . '/data/' . $b[1] . '.txt';
  echo "get $argv[1] to $save";
  $path = trim(parse_url($b[0], PHP_URL_PATH), '/');
  list(, $bookid) = explode('-', $path);
 */
$save = __DIR__ . '/data/' . $argv[1] . '.txt';

$f = new fetch("http://m.4gbook.net/wapbook-24479_@@NUM@@/", $save, 43);

$f->setListBoxXpath('/html/body/div[2]/ul/li');
$f->setIndexXpath('/html/body/div[2]/ul/li[@@NUM@@]/a');
$f->setContentId('nr');
$f->setContentXpath('/html/body/div[2]/div[4]');

$f->indexes();
$f->getAllContent();
//}