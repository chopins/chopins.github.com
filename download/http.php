<?php
function run()
{

#run
http("GET $host/buy-order-list
$token
"
);

}
parseTags();
color();
run();
function checkType($h, $type)
{
    if (stripos($h, 'Content-Type:') === 0) {
        if (is_array($type)) {
            foreach ($type as $v) {
                return stripos($h, $v) > 13;
            }
        } else {
            return stripos($h, $type) > 13;
        }
        return true;
    }
    return false;
}
function hasRun()
{
    global $tagLines;
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    if (in_array($trace[1]['line'] - 1, $tagLines)) {
        return true;
    }
    return false;
}

function color()
{
    if (isset($_SERVER['ComSpec']) && $_SERVER['ComSpec'] == 'C:\Windows\system32\cmd.exe') {
        $ansi = "\x1b";
    } else {
        $ansi = "\033";
    }

    $code = ['C_BLUE' => 34, 'C_GREEN' => 32, 'C_MAGENTA' => 35];
    define('C_END', "{$ansi}[0m");
    foreach ($code as $k => $n) {
        define($k, "{$ansi}[0;{$n}m");
    }
}

function http($head, $data = null, $output = true)
{
    if (!hasRun()) {
        return false;
    }

    $op = [];
    $hst = explode("\n", $head);
    $hs = [];
    foreach ($hst as $v) {
        $hs[] = trim($v);
    }
    $cols = exec('tput cols');
    list($m, $url) = explode(' ', $hs[0], 2);
    $linesep = str_repeat('-', $cols);
    echo C_BLUE . "$linesep\n$m $url\n$linesep\n" . C_END;
    if (strtoupper($m) == 'POST') {
        $op[CURLOPT_POST] = 1;
    }
    array_shift($hs);
    $op[CURLOPT_HTTPHEADER] = $hs;

    $op[CURLOPT_RETURNTRANSFER] = 1;
    if (isset($data)) {
        $op[CURLOPT_POSTFIELDS] = $data;
    }
    $responseHeader = new class {
        var $isJson = false;
        var $isXml = false;
        var $contentlength = 0;
    };

    $op[CURLOPT_HEADERFUNCTION] = function ($ch, $h) use (&$responseHeader) {
        if (checkType($h, ['text/json', 'application/json'])) {
            $responseHeader->isJson = true;
        } else if (checkType($h, ['application/xml'])) {
            $responseHeader->isXml = true;
        } else if (stripos($h, 'Content-Length:') === 0) {
            $responseHeader->contentlength = trim(substr($h, 15));
        }
        if (strpos($h, ':')) {
            echo C_MAGENTA . str_replace(':', ':' . C_END, $h);
        } else {
            echo C_GREEN . $h . C_END;
        }
        return strlen($h);
    };

    $ch = curl_init(trim($url));
    curl_setopt_array($ch, $op);
    $ret = curl_exec($ch);

    if ($responseHeader->isJson) {
        $json = json_decode($ret, true);
        if ($json) {
            $output && print_r($json);
            return $json;
        } else {
            echo $ret;
        }
    } else if ($responseHeader->isXml) {
        $xml = simplexml_load_string($ret);
        if ($xml) {
            $output && print_r($xml);
            return $xml;
        } else {
            echo $ret;
        }
    } else if ($responseHeader->contentlength < 100) {
        echo $ret;
    } else {
        echo 'save to: file://' . realpath('./output.html');
        file_put_contents('./output.html', $ret);
    }

    curl_close($ch);
    return $ret;
}

function parseTags()
{
    global $tagLines;
    $all = token_get_all(file_get_contents(__FILE__, false));
    $tagLines = [];
    foreach ($all as $token) {
        if ($token[0] == T_COMMENT) {
            if ($token[1] == '#run') {
                $tagLines[] = $token[2];
            }
        }
    }
}
