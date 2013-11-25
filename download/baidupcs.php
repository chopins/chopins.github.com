#!/bin/env php
<?php

class BaiduPCS {

    private $httpTimeout = 5;
    private $httpErrno   = 0;
    private $httpErrstr  = '';
    private $accessToken = '';
    private $url         = '';
    private $ch          = null;

    const CLIENT_ID = '';

    public function __construct($argv, $argc) {
        if ($argc <= 1) {
            return $this->help();
        }
        switch ($argv[1]) {
            case 'upload':
                if (empty($argv[2]) || empty($argv[3])) {
                    $this->argsError();
                }
                $option     = array();
                $optionList = array('-o', '-n', '-p', '-l');
                $localfile  = $remotepath = null;
                for($i = 1;
                $i<$argc; $i++) {
                    if (in_array($argv[$i], $optionList)) {
                        $option[$argv[$i]] = 1;
                        if ($argv[$i] == '-p') {
                            $split          = $argv[$i];
                            $i++;
                            $option[$split] = $argv[$i];
                        }
                    } else if ($argv[$i] == 'upload') {
                        continue;
                    } else {
                        if ($localfile === null) {
                            $localfile = $argv[$i];
                        } else {
                            $remotepath = $argv[$i];
                        }
                    }
                }
                if (empty($remotepath) || empty($localfile)) {
                    $this->argsError();
                }
                $this->doUpload($localfile, $remotepath, $option);
        }
    }

    public function argsError() {
        $this->errMessage('参数错误');
        $this->help();
        exit;
    }

    public function help() {
        echo <<<EOF
Usage：baidupcs command [option]
     command：
        upload [option] localpath remote
            option:
                  -o 
                  -n
                  -p
                  -l
        download [option] remote localpath
             option:
                  -o
                  -n
        quota   
        mkdir
        mv
        rm
        cp
        ls
            option:
                -l
                -r
EOF;
    }

    public function httpInit() {
        $this->ch = curl_init();
        $set      = array('CURLOPT_RETURNTRANSFER'       => 1,
            'CURLOPT_DNS_USE_GLOBAL_CACHE' => 1,
            'CURLOPT_SSL_VERIFYPEER'       => 0,
            'CURLOPT_SSL_VERIFYHOST'       => 0,
            'CURLOPT_LOW_SPEED_LIMIT'      => 256,
            'CURLOPT_LOW_SPEED_TIME'       => 5
        );
        curl_setopt_array($this->ch, $set);
    }

    public function doUpload($localfile, $remotepath, $option) {
        $localfile = realpath($localfile);
        if(!$localfile) {
            return $this->errMessage("{$localfile}文件不存在");
        }

        $hostname         = 'c.pcs.baidu.com';
        $queryParamString = http_build_query(array('method'       => 'upload',
            'access_token' => $this->accessToken,
            'path'         => $this->retPath,
            'ondup'        => $this->ondup
        ));
        $this->url        = "{$this->transprot}{$hostname}/rest/2.0/pcs/file?{$queryParamString}";
        curl_setopt($this->ch, 'CURLOPT_UPLOAD', 1);
        curl_setopt($this->ch, 'CURLOPT_POST', 1);
        $filesize         = filesize($localfile);
        curl_setopt($this->ch, 'CURLOPT_HTTPHEADER', array("Content-length: {$filesize}"));
        $formdata         = array('name' => 'file', 'file' => "@{$localfile}");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $formdata);
    }

    public function oauth() {
        $queryParamString = http_build_query(array('client_id'     => self::CLIENT_ID,
            'response_type' => 'device_code',
            'scope'         => 'basic,netdisk'
        ));
        $hostname         = 'openapi.baidu.com';
        $this->url        = "{$this->transprot}{$hostname}/oauth/2.0/device/code?{$queryParamString}";
    }

    public function errMessage($str) {
        echo "$str\n";
    }

    public function getCommonHeader() {
        $str = "{$this->httpMethod} {$this->queryString} HTTP/1.1\r\n";
        $str .= "Host:{$this->hostname}\r\n";
    }

    public function httpRequest() {

        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_exec($this->ch);
    }

    public function __destory() {
        curl_close($this->ch);
    }

}

return new BaiduPCS($argv, $argc);