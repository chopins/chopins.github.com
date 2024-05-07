<?php

if ($_SERVER['SERVER_NAME'] != '127.0.0.1') {
    include '../../auth.php';
}

const MY_EOL = "\n";
const BOOK_LIST = __DIR__ . '/data/all_book_list';
const BOOK_DIR = __DIR__ . '/data/book/';
const BOOK_DATA_SEP = '#=#=#=#=#';
new class()
{
    public function __construct()
    {
        $act = $_GET['a'] ?? 'a';
        $bid = $_GET['b'] ?? '';
        $cid = $_GET['c'] ?? 0;
        switch ($act) {
            case 'b':
                return $this->book($bid);
            case 'c':
                return $this->chapter($bid, $cid);
            case 'g':
                return $this->get();
            case 'u':
                return $this->upload();
            case 'p':
                return $this->parse($bid);
            case 'd':
                return $this->del($bid);
            case 'r':
                return $this->rename($bid);
            default:
                $this->all();
        }
    }

    public function all()
    {
        $all =  @file(BOOK_LIST);
        if (empty($all)) {
            $this->page("<h3>没有书籍</h3>");
        }
        $list = '<ul>';
        foreach ($all as $v) {
            list($name, $id) = explode(BOOK_DATA_SEP, $v);
            $list .= '<li><a href="?a=b&b=' . trim($id) . '">' . $name . '</a></li>';
        }
        $list .= '</ul>';
        $this->page($list);
    }
    public function del($bid)
    {
        $all =  @file(BOOK_LIST);
        foreach ($all as $i => $v) {
            list(, $id) = explode(BOOK_DATA_SEP, $v);
            if (trim($id) == $bid) {
                unset($all[$i]);
                unlink(BOOK_DIR . $bid . '/book');
                unlink(BOOK_DIR . $bid . '/chapter');
                rmdir(BOOK_DIR . $bid);
                file_put_contents(BOOK_LIST, join('', $all));
            }
        }
        header("Location: n.php?a=a");
    }
    public function rename($bid)
    {
        $n = $_REQUEST['n'] ?? '';
        if (!$n) {
            header("Location: n.php?a=a");
            return;
        }

        $all =  @file(BOOK_LIST);
        foreach ($all as $i => $v) {
            list(, $id) = explode(BOOK_DATA_SEP, $v);
            if (trim($id) == $bid) {
                $all[$i] = $n . BOOK_DATA_SEP . $bid . MY_EOL;
                break;
            }
        }

        $c = file(BOOK_DIR . '/' . $bid . '/chapter');
        $c[0] = $n . MY_EOL;
        file_put_contents(BOOK_DIR . '/' . $bid . '/chapter', join('', $c));
        file_put_contents(BOOK_LIST, join('', $all));
        header("Location: n.php?a=a");
        return true;
    }
    protected function checkBook($bid)
    {
        $all =  file(BOOK_LIST);
        if (empty($all)) {
            return true;
        }
        foreach ($all as $v) {
            list(, $id) = explode(BOOK_DATA_SEP, $v);
            if (trim($id) == $bid) {
                return false;
            }
        }
        return true;
    }

    public function upload()
    {
        if (empty($_FILES)) {
            $this->page('');
        }
        $name = basename($_FILES['f']['name'], '.txt');
        $bid = md5_file($_FILES['f']['tmp_name']);
        if (!$this->checkBook($bid)) {
            header("Location: n.php?a=p&b=$bid&n=" . urlencode($name));
        }
        mkdir(BOOK_DIR . $bid, 0700);
        if (move_uploaded_file($_FILES['f']['tmp_name'], BOOK_DIR . $bid . '/book')) {
            file_put_contents(BOOK_LIST, $name . BOOK_DATA_SEP . $bid . MY_EOL, FILE_APPEND);
            header("Location: n.php?a=p&b=$bid&n=" . urlencode($name));
        }
    }
    public function get()
    {
        set_time_limit(0);
        $url = $_REQUEST['u'] ?? '';
        if (!$url) {
            $this->page('');
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        $name = null;
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$name) {
            echo $header;
            if (stripos($header, 'content-disposition: attachment') === 0) {
                list(, $name) = explode('=', $header);
                $name = str_replace(["'", "\r", "\n", '"', ';', ',', ' '], '', $name);
            }
            return strlen($header);
        });
        $content = curl_exec($ch);
        curl_close($ch);

        if (!$content) {
            $this->page('get error');
        }
        $bid = md5($content);
        $name = $name ?? $bid;

        file_put_contents(BOOK_DIR . $bid . '.txt', $content);

        if (!$this->checkBook($bid)) {
            header("Location: n.php?a=p&b=$bid&n=" . urlencode($name));
        }
        mkdir(BOOK_DIR . $bid, 0774);
        if (rename(BOOK_DIR . $bid . '.txt', BOOK_DIR . $bid . '/book')) {
            file_put_contents(BOOK_LIST, $name . BOOK_DATA_SEP . $bid . MY_EOL, FILE_APPEND);
            header("Location: n.php?a=p&b=$bid&n=" . urlencode($name));
        }
        $this->page('move file error');
    }

    protected function covert($bid)
    {
        $f = BOOK_DIR . $bid . '/book';
        $s = file_get_contents($f);
        $charset =  mb_detect_encoding($s, ['GB18030', 'BIG-5', 'UTF-8']);

        if ($charset != 'UTF-8') {
            $s = mb_convert_encoding($s, 'UTF-8',  $charset);
            file_put_contents($f, $s);
        }
    }

    public function parse($bid)
    {
        $this->covert($bid);
        $name = $_GET['n'] ?? '';
        $content = file(BOOK_DIR . $bid . '/book');
        $count = count($content);
        $f = new SplFileObject(BOOK_DIR . '/' . $bid . '/chapter', 'w+');
        $chpaters = preg_grep("/^[\s\t]*第([0-9一二三四五六七八九十百千万零〇]+)[章回节][^\n]*$/u", $content);
        $i = 0;
        $f->fwrite($name . MY_EOL, strlen($name . MY_EOL));
        foreach ($chpaters as $pos => $title) {
            $prev = $i > 0 ? ($pos - 1) . MY_EOL : '';
            $t = $prev . trim($title) . BOOK_DATA_SEP . $pos . '_';
            $f->fwrite($t, strlen($t));
            $i++;
        }
        $f->fwrite($count - 1, strlen($count - 1));
        header("Location: n.php?a=b&b=$bid");
    }

    public function book($bid)
    {
        if (empty($bid)) {
            header("Location: n.php?a=a");
        }
        $split = file(BOOK_DIR . $bid . '/chapter');
        $bookname = array_shift($split);

        $list = "<h3>$bookname<form style=\"display: inline-block;\" method=\"post\" action=\"n.php?a=r&b=$bid\"><input type=\"text\" name=\"n\" style=\"width:20vw;\"><button type=\"submit\">改名</button></form></h3><ul>";
        $chpater = [];

        foreach ($split as $v) {
            $chpater[] = explode(BOOK_DATA_SEP, $v);
        }
        $cn = count($chpater);
        foreach ($chpater as $i => $c) {
            $list .= '<li><a href="?a=c&b=' . $bid . '&c=' . ($i + 1) . '">' . $c[0] . '</a></li>';
        }
        $list .= '</ul>';
        $this->page($list);
    }
    public function chapter($bid, $cid)
    {
        if (empty($cid)) {
            header("Location: n.php?a=b&b=$bid");
            exit;
        }

        $c = new SplFileObject(BOOK_DIR . '/' . $bid . '/chapter');
        $content = '<h3>' . trim($c->fgets()) . '</h3>';
        $c->seek($cid);
        list($ctitle, $pos) = explode(BOOK_DATA_SEP, $c->fgets());
        list($s, $e) = explode('_', $pos);
        $f = new SplFileObject(BOOK_DIR . '/' . $bid . '/book');
        $f->seek($s);
        $read = $_GET['read'] ?? 0;
        $pid = $cid - 1;
        $nid = $cid + 1;

        $content .= '<div class="chapter"><a id="pre-chapter" cid="' . $pid . '" href="?a=c&b=' . $bid . '&c=' . $pid
            . '">上一页</a><a href="?a=b&b=' . $bid . '">目录</a><a id="next-chapter" href="?a=c&b='
            . $bid . '&c=' . $nid . '" cid="' . $nid . '" bid="' . $bid . '">下一页</a><button onclick="startSpeak(this)">阅读</button><input type="text" id="maxPlayNum" value="' . $read . '" style="width: 1.5rem;"><select id="voices"><option>----</option></select><audio id="playChapter" autoplay controls></audio></div><div id="chapter">';
        $chaptercontent = '';
        do {
            $chaptercontent .= $f->fgets();
        } while ($f->key() <= $e);

        if ($read) {
            echo $chaptercontent;
            return;
        }
        $content .= $chaptercontent;
        if ($read > 0) {
            $read = "startSpeak();";
        }
        $content .= '</div><div class="chapter"><a cid="' . $pid . '" href="?a=c&b=' . $bid . '&c=' . $pid
            . '">上一页</a><a href="?a=b&b=' . $bid . '">目录</a><a href="?a=c&b='
            . $bid . '&c=' . $nid . '" cid="' . $nid . '" bid="' . $bid . '">下一页</a></div><script>
        window.onload = function() {
            window.chapterAudio = $("#playChapter");
            parseTalk();
            chapterAudio.addEventListener("ended", function(e) {
                chapterAudioPlayed = false;
                playChunkCount++;
                if (AudioBuffer.length > 0) {
                    audioPlay();
                } else {
                    if(playChunkCount < currentChunkCount) {
                        setTimeout(audioPlay, 1000);
                    } else {
                        nextChapter();
                    }
                }
            });
            $("select").map(s=> s.addEventListener("click", function(e) {
                if(e.target.tagName== "OPTION") {
                    e.target.parentNode.style.backgroundColor = e.target.style.backgroundColor;
                } else {
                    setTalkVoice(e.target);
                }
            }));
            getOnlineVoices();' . $read . '} </script>';

        $this->page($content);
    }

    private function page($html, $charset = 'utf-8')
    {
?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Novel</title>
            <meta charset="<?= $charset ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-sacle=1, maximum-scale=1">
            <style>
                .main {
                    margin: 0 auto;
                    width: 99vw;
                    color: #999;
                }

                .main div {
                    padding-bottom: 10px;
                }

                input {
                    width: 60vw;
                    height: 1.5rem;
                    border: 1px solid #CCC;
                    margin-right: 1rem;
                }

                button {
                    line-height: 1.5rem;
                    border: 1px solid #CCC;
                    padding: 0 1rem;
                }

                a {
                    text-decoration-line: none;
                    color: #555;
                }

                ul {
                    padding-left: 1rem;
                }

                .chapter {
                    margin: 0 auto;
                    width: 96vw;
                }

                .chapter a {
                    display: inline-block;
                    margin-right: 1rem;
                    padding: 0.1rem .2rem;
                }

                .chapter button {
                    margin-right: 1rem;
                }

                .chapter select {
                    width: 20vw;
                }

                #chapter {
                    background-color: #F6EEEE;
                }

                h3 {
                    margin: 0;
                }

                #chapter {
                    color: #3c3c3c;
                    line-height: 1.3rem;
                }

                .talk {
                    background-color: #DDD;
                }

                #pop {
                    display: block;
                    top: 1px;
                    left: 1px;
                    border: 1px solid #999;
                    padding: .2rem;
                    background-color: #EEE;
                    display: none;
                    position: absolute;
                }
            </style>
            <script>
                function $(id) {
                    let rname = id.substr(1);
                    let NodeCollection = function(nodes) {
                        this.map = (f) => Array.from(nodes).map(f);
                        this.length = nodes.length;
                        return this;
                    }
                    if (/[ *,\[>~\+|:]/.test(id)) {
                        return NodeCollection(document.querySelectorAll(id));
                    }
                    switch (id[0]) {
                        case '#':
                            return document.getElementById(rname);
                        case '.':
                            return NodeCollection(document.getElementsByClassName(rname));
                        default:
                            console.log(id)
                            return NodeCollection(document.getElementsByTagName(id));
                    }
                }

                function guid() {
                    return crypto.randomUUID().replaceAll('-', '');
                }

                var MSOnlineSpeak = {
                    audioBuffer : [],
                    chapterTextChunk : [],
                    
                }
            </script>
            <script>
                var chkspeak = null;
                var onlineVoicesList = [];
                var AudioBuffer = [];
                var currentChunkCount = 0;
                var playChunkCount = 0;
                var curClickTalk = null;
                var StartPlayAudioEvent = new CustomEvent('PlayAudio', {
                    bubbles: false,
                    cancelable: false
                });

                var chapterAudioPlayed = false;
                var onlineVoiceStyles = {
                    "advertisement_upbeat": '用兴奋和精力充沛的语气推广产品或服务。',
                    "affectionate": '以较高的音调和音量表达温暖而亲切的语气。 说话者处于吸引听众注意力的状态。 说话者的个性往往是讨喜的。',
                    "angry": '表达生气和厌恶的语气。',
                    "assistant": '数字助理用的是热情而轻松的语气。',
                    "calm": '以沉着冷静的态度说话。 语气、音调和韵律与其他语音类型相比要统一得多。',
                    "chat": '表达轻松随意的语气。',
                    "cheerful": '表达积极愉快的语气。',
                    "customerservice": '以友好热情的语气为客户提供支持。',
                    "depressed": '调低音调和音量来表达忧郁、沮丧的语气。',
                    "disgruntled": '表达轻蔑和抱怨的语气。 这种情绪的语音表现出不悦和蔑视。',
                    "documentary-narration": '用一种轻松、感兴趣和信息丰富的风格讲述纪录片，适合配音纪录片、专家评论和类似内容。',
                    "embarrassed": '在说话者感到不舒适时表达不确定、犹豫的语气。',
                    "empathetic": '表达关心和理解。',
                    "envious": '当你渴望别人拥有的东西时，表达一种钦佩的语气。',
                    "excited": '表达乐观和充满希望的语气。 似乎发生了一些美好的事情，说话人对此非常满意。',
                    "fearful": '以较高的音调、较高的音量和较快的语速来表达恐惧、紧张的语气。 说话人处于紧张和不安的状态。',
                    "friendly": '表达一种愉快、怡人且温暖的语气。 听起来很真诚且满怀关切。',
                    "gentle": '以较低的音调和音量表达温和、礼貌和愉快的语气。',
                    "hopeful": '表达一种温暖且渴望的语气。 听起来像是会有好事发生在说话人身上。',
                    "lyrical": '以优美又带感伤的方式表达情感。',
                    "narration-professional": '以专业、客观的语气朗读内容。',
                    "narration-relaxed": '为内容阅读表达一种舒缓而悦耳的语气。',
                    "newscast": '以正式专业的语气叙述新闻。',
                    "newscast-casual": '以通用、随意的语气发布一般新闻。',
                    "newscast-formal": '以正式、自信和权威的语气发布新闻。',
                    "poetry-reading": '在读诗时表达出带情感和节奏的语气。',
                    "sad": '表达悲伤语气。',
                    "serious": '表达严肃和命令的语气。 说话者的声音通常比较僵硬，节奏也不那么轻松。',
                    "shouting": '就像从遥远的地方说话或在外面说话，但能让自己清楚地听到',
                    "sports_commentary": '用轻松有趣的语气播报体育赛事。',
                    "sports_commentary_excited": '用快速且充满活力的语气播报体育赛事精彩瞬间。',
                    "whispering": '说话非常柔和，发出的声音小且温柔',
                    "terrified": '表达一种非常害怕的语气，语速快且声音颤抖。 听起来说话人处于不稳定的疯狂状态。',
                    "unfriendly": '表达一种冷淡无情的语气。'
                }


                var onlineVoiceRoles = {
                    'Girl': '女孩',
                    'Boy': '男孩',
                    'YoungAdultFemale': '年轻的成年女性',
                    'YoungAdultMale': '年轻的成年男性',
                    'OlderAdultFemale': '年长的成年女性',
                    'OlderAdultMale': '年长的成年男性',
                    'SeniorFemale': '年老女性',
                    'SeniorMale': '年老男性'
                }

                var onlineVoiceZHNames = {
                    'zh-CN-XiaoxiaoNeural': ['青年女-晓晓', '#FFE2E2'],
                    "zh-CN-XiaoyiNeural": ['少年女-小艺', '#FFF9BC'],
                    "zh-CN-YunjianNeural": ['中年男-云建', '#C9FFB5'],
                    "zh-CN-YunxiNeural": ['青年男-云西', '#B0FFF8'],
                    "zh-CN-YunxiaNeural": ['少年男-云夏', '#B1BFFF'],
                    "zh-CN-YunyangNeural": ['播音男-云言', '#F6C3FF'],
                    "zh-CN-liaoning-XiaobeiNeural": ['东北女', '#EEE'],
                    "zh-CN-shaanxi-XiaoniNeural": ['陕西女', '#EEE'],
                    "zh-HK-HiuGaaiNeural": ["广府话中年女", '#EEE'],
                    "zh-HK-HiuMaanNeural": ["广府话青年女", '#EEE'],
                    "zh-HK-WanLungNeural": ["广府话男", '#EEE'],
                    "zh-TW-HsiaoChenNeural": ["台湾青年女", '#EEE'],
                    "zh-TW-YunJheNeural": ["台湾男", '#EEE'],
                    "zh-TW-HsiaoYuNeural": ["台湾中年女", '#EEE', '+40%'],
                    get: function(n) {
                        return this[n] ? this[n] : [n, '#EEE'];
                    }
                }



                function setTalkVoice(select) {
                    if (!curClickTalk) {
                        return;
                    }
                    curClickTalk.setAttribute('name', select.value);
                    setTimeout(() => curClickTalk.style.backgroundColor = select.style.backgroundColor, 100);
                }

                function setTalkVoiceStyle(obj) {
                    if (!curClickTalk) {
                        return;
                    }
                    if (curClickTalk.firstElementChild.tagName == 'PROSODY') {
                        curClickTalk.innerHTML = '<mstts:express-as style="' + obj.value + '">' + curClickTalk.innerHTML + '</mstts:express-as>';
                    } else {
                        curClickTalk.firstElementChild.setAttribute('style', obj.value);
                    }
                }

                function setTalkVoiceRoles(obj) {
                    if (!curClickTalk) {
                        return;
                    }
                    if (curClickTalk.firstElementChild.tagName == 'PROSODY') {
                        curClickTalk.innerHTML = '<mstts:express-as role="' + obj.value + '">' + curClickTalk.innerHTML + '</mstts:express-as>';
                    } else {
                        curClickTalk.firstElementChild.setAttribute('role', obj.value);
                    }
                }

                function parseTalk() {
                    if (onlineVoicesList.length <= 0) {
                        return setTimeout(parseTalk, 1000);
                    }
                    let chapterContent = $('#chapter').textContent;
                    var n = $("#voices").value;
                    let selectVoice = onlineVoicesList.find(v => v.Name == n).Name;
                    let c = chapterContent.replaceAll("\n", '</span></p><p><span>')
                        .replaceAll(/"([^"]+)"/mg, '“$1”')
                        .replaceAll(/'([^']+)'/mg, '“$1”')
                        .replaceAll("“", '</span><voice class="talk" name="' + selectVoice + '"><prosody pitch="+0Hz" rate ="+0%" volume="+0%">“')
                        .replaceAll("”", '”</prosody></voice><span>');

                    $('#chapter').innerHTML = "<p><span>" + c + '</span></p>';
                    $('#chapter p').map(p => p.textContent.trim().length == 0 && $("#chapter").removeChild(p));

                    //$('#popVoiceStyles').innerHTML = Object.entries(onlineVoiceStyles).map(([v,k]) => '<option value="'+v+'">' + k + '</option>').join('');
                    //$('#popVoiceRoles').innerHTML = Object.entries(onlineVoiceRoles).map(([v,k]) => '<option value="'+v+'">' + k + '</option>').join('');
                    $('#chapter').addEventListener('click', function(e) {
                        if (e.target.tagName == 'PROSODY' || e.target.tagName == 'VOICE' || e.target.tagName == 'MSTTS:EXPRESS-AS') {
                            curClickTalk = e.target.tagName == 'PROSODY' ? e.target.parentNode : e.target;
                            if (curClickTalk.tagName == 'MSTTS:EXPRESS-AS') {
                                curClickTalk = curClickTalk.parentNode;
                            }
                            $('#pop').style.display = "block";
                            $('#pop').style.top = e.pageY + 'px';
                            $('#pop').style.left = e.pageX + 'px';
                        } else {
                            $('#pop').style.display = 'none';
                            curClickTalk = null;
                        }
                    });
                }

                function getpaste(obj) {
                    if (typeof Clipboard === undefined) {
                        return;
                    }
                    navigator.permissions.query({
                        name: 'clipboard-read'
                    }).then(result => {
                        if (result.state == 'granted' || result.state == 'prompt') {
                            //读取剪贴板
                            navigator.clipboard.readText().then(text => {
                                obj.value = text;
                            })
                        } else {
                            alert('请允许读取剪贴板！')
                        }
                    })
                }

                // function cancelSpeak() {
                //     window.speechSynthesis.cancel();
                //     clearInterval(chkspeak);
                // }

                // function pauseSpeak() {
                //     window.speechSynthesis.pause()
                // }

                // function speak(text, voice) {
                //     let voices = window.speechSynthesis.getVoices();
                //     let uttr = new SpeechSynthesisUtterance(text);
                //     for (let v of voices) {
                //         if (v.name.includes(voice)) {
                //             uttr.voice = v;
                //         }
                //     }
                //     window.speechSynthesis.speak(uttr);
                //     chkspeak = setInterval(() => {
                //         if (!window.speechSynthesis.speaking) {
                //             clearInterval(chkspeak);
                //             nextChapter();
                //         }
                //     }, 1000);
                // }

                function nextChapter() {
                    let url = $('#next-chapter').href;
                    let nextCid = parseInt($('#next-chapter').getAttribute('cid'));
                    let bid = $('#next-chapter').getAttribute('bid');
                    let n = parseInt($('#maxPlayNum').value) - 1;
                    if (n > 0) {
                        fetch(url + '&read=' + n).then(function(res) {
                            history.pushState('', '', res.url);
                            return res.text();
                        }).then(html => {
                            $('#chapter').innerHTML = html;
                            chapterAudioPlayed = false;
                            currentChunkCount = 0;
                            playChunkCount = 0;
                            isStartSpeak = false;
                            $('#pre-chapter').href = "?a=c&b=" + bid + '&c=' + (nextCid - 1);
                            $('#pre-chapter').setAttribute('cid', nextCid - 1);
                            $('#next-chapter').href = "?a=c&b=" + bid + '&c=' + (nextCid + 1);
                            $('#next-chapter').setAttribute('cid', nextCid + 1);
                            $('#maxPlayNum').value = n;
                            startSpeak();
                        });
                        //window.location.href = url + '&read=' + n;
                    }
                }


                var NotDispatchEvent = false;

                function onlineSpeak(chapterText, selectVoice) {
                    if (chapterText.length == 0) {
                        console.log('not text');
                        return;
                    }
                    let url = "wss://speech.platform.bing.com/consumer/speech/synthesize/readaloud/edge/v1?trustedclienttoken=6A5AA1D4EAFF4E9FB37E23D68491D6F4";

                    let reviceData = [];
                    let requestId = guid();
                    let audioEnd = false,
                        turnEnd = false;
                    let wssSock = new WebSocket(url);
                    wssSock.addEventListener('open', function(e) {
                        let audioOutputFormat = `Content-Type:application/json; charset=utf-8\r\nPath:speech.config\r\n\r\n{"context":{"synthesis":{"audio":{"metadataoptions":{"sentenceBoundaryEnabled":"false","wordBoundaryEnabled":"false"},"outputFormat":"${selectVoice.SuggestedCodec}"}}}}`;
                        wssSock.send(audioOutputFormat);
                        let time = (new Date).toString() + 'Z';
                        let text = chapterText.shift();
                        //let ssml = `X-RequestId:${requestId}\r\nContent-Type:application/ssml+xml\r\nPath:ssml\r\n\r\n<speak version='1.0' xmlns='http://www.w3.org/2001/10/synthesis' xml:lang='${selectVoice.Locale}'><voice  name='${selectVoice.Name}'><prosody pitch='+0Hz' rate ='+0%' volume='+0%'>${text}</prosody></voice></speak>`;
                        let ssml = `X-RequestId:${requestId}\r\nContent-Type:application/ssml+xml\r\nPath:ssml\r\n\r\n<speak version='1.0' xmlns='http://www.w3.org/2001/10/synthesis' xml:lang='${selectVoice.Locale}'>${text}</speak>`;
                        wssSock.send(ssml);
                    });
                    wssSock.addEventListener('message', function(e) {
                        if (typeof e.data == 'string') {
                            if (/Path:turn\.end/.test(e.data)) {
                                turnEnd = true;
                            } else if (/Path:turn\.start/.test(e.data)) {} else if (/Path:response/.test(e.data)) {}
                        } else if (typeof e.data == 'object' && e.data instanceof Blob) {
                            if (e.data.size == 105) {
                                audioEnd = true;
                            } else {
                                reviceData.push(e.data);
                            }
                        }
                        if (audioEnd && turnEnd) {
                            AudioBuffer.push(reviceData);
                            wssSock.close(1000, 'CLOSE_NORMAL');
                            if (!NotDispatchEvent) {
                                NotDispatchEvent = true;
                                chapterAudio.dispatchEvent(StartPlayAudioEvent);
                            }

                            //onlineSpeak(chapterText, selectVoice);
                        }

                    });
                    wssSock.addEventListener('error', function(e) {

                    });
                    wssSock.addEventListener('close', function(e) {

                    })
                }

                async function getAudioData() {
                    let audioData = AudioBuffer.shift()
                    if (!audioData) {
                        return new Promise((resolve, reject) => reject('no audio data'));
                    }
                    if (audioData.length == 0) {
                        return new Promise((resolve, reject) => reject('no audio data'));
                    }
                    let reviceMedia = audioData.map(d => d.slice(130));
                    let mediaType = (await audioData[0].slice(2, 130).text()).match(/Content-Type:([^\r\n]+)\r\n/)[1];

                    return new Blob(reviceMedia, {
                        type: mediaType
                    });
                }

                function audioPlay() {
                    if (chapterAudioPlayed) {
                        return;
                    }
                    console.log('audioPlay');
                    chapterAudioPlayed = true;
                    window.URL.revokeObjectURL(chapterAudio.src);
                    getAudioData().then(async function(v) {
                        if (!v) {
                            if (currentChunkCount > currentChunkCount) {
                                console.log("no play")
                                return setTimeout(audioPlay, 1000);
                            }
                            console.log('no media');
                            return;
                        }
                        chapterAudio.src = window.URL.createObjectURL(v);
                        chapterAudio.play().then(ok => '', e => alert('play error:' + e.constructor.name));
                    }, (m) => {
                        if (chapterAudio.ended) {
                            chapterAudioPlayed = false;
                            setTimeout(audioPlay, 1000)
                        }
                    });
                }

                function getOnlineVoices() {
                    let voices = sessionStorage.getItem('voices');
                    if (false && voices) {
                        onlineVoicesList = JSON.parse(voices);
                        $("#popVoices").innerHTML = $("#voices").innerHTML = onlineVoicesList.map(v => {
                            let item = onlineVoiceZHNames.get(v.ShortName);
                            return "<option value=\"" + v.Name + "\" style=\"background-color:" + item[1] + ";\">" + item[0] + "</option>"
                        }).join("");
                        return;
                    }
                    fetch('https://speech.platform.bing.com/consumer/speech/synthesize/readaloud/voices/list?trustedclienttoken=6A5AA1D4EAFF4E9FB37E23D68491D6F4')
                        .then(response => response.json())
                        .then(data => {
                            let h = data.map(function(v) {
                                if (v.Locale.includes('zh-CN') || v.Locale.includes('zh-TW')) {
                                    onlineVoicesList.push(v);
                                    let item = onlineVoiceZHNames.get(v.ShortName);
                                    return "<option value=\"" + v.Name + "\" style=\"background-color:" + item[1] + ";\">" + onlineVoiceZHNames.get(v.ShortName)[0] + "</option>";
                                }
                                return '';
                            }).join("");
                            $("#popVoices").innerHTML = $("#voices").innerHTML = h;
                            sessionStorage.setItem("voices", JSON.stringify(onlineVoicesList));
                        });
                }

                function getVoices() {
                    let voices = window.speechSynthesis.getVoices();
                    let h = "";
                    for (let v of voices) {
                        if (v.lang == "zh-CN") {
                            h += "<option value=\"" + v.name + "\">" + v.name + "</option>";
                        }
                    }
                    document.getElementById("voices").innerHTML = h;
                    return voices;
                }
                var isStartSpeak = false;

                function startSpeak(obj) {
                    if (isStartSpeak) {
                        //alert('已经开始');
                        //return;
                    }
                    isStartSpeak = true;
                    var n = $("#voices").value;
                    let selectVoice = onlineVoicesList.find(v => v.Name == n);
                    if (!selectVoice) return;
                    chapterAudio.addEventListener('PlayAudio', function(e) {
                        audioPlay();
                    });
                    let pl = $('#chapter p').map(p => Array.from(p.children));
                    let ps = [];
                    let temp = '';
                    for (let k = 0; k < pl.length; k++) {
                        for (let s of pl[k]) {
                            if (s.tagName == 'VOICE') {
                                ps.push(`<voice  name='${selectVoice.Name}' effect="eq_car"><prosody pitch='+0Hz' rate ='+0%' volume='+0%'>${temp}</prosody></voice>`)
                                //ps.push(temp);
                                temp = '';
                                let vnode = s.cloneNode(true);
                                vnode.removeAttribute('class');
                                vnode.removeAttribute('style');
                                ps.push(vnode.outerHTML);
                            } else {
                                temp += s.textContent.trim();
                            }
                        }
                        if (k > 0 && k % 4 == 0) {
                            ps.push(`<voice  name='${selectVoice.Name}' effect="eq_car"><prosody pitch='+0Hz' rate ='+0%' volume='+0%'>${temp}</prosody></voice>`)
                            temp = '';
                        }
                    }

                    // do {
                    //     let ptext = pl.splice(0, 4).map(e => e.textContent).join("").trim();
                    //     ptext && ps.push(ptext);
                    // } while (pl.length > 0);
                    currentChunkCount = ps.length;

                    onlineSpeak(ps, selectVoice);
                }
            </script>
        </head>

        <body>
            <div class="main">
                <div>
                    <div><a href="?a=a" style="font-size:1.5rem;">全部书籍</a></div>
                    <div>
                        <form enctype="multipart/form-data" action="?a=u" method="post"><input type="file" name="f"><button type="submit">上传</button></form>
                    </div>
                    <div>
                        <form action="n.php?a=g" method="post"><input type="text" value="" name="u" placeholder="输入下载URL" onclick="getpaste(this)"><button type="submit">下载</button></form>
                    </div>
                </div>
                <?= $html ?>
            </div>
            <div id="pop"><select id="popVoices" onchange="setTalkVoice(this)"><!-- </select><select style="width: 10rem;" onchange="setTalkVoiceStyle(this)" id="popVoiceStyles"></select><select style="width: 10rem;" onchange="setTalkVoiceRoles(this)" id="popVoiceRoles"></select>--></div>
        </body>

        </html>
<?php
        exit;
    }
}
?>