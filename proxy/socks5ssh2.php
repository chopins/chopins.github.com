<?php

date_default_timezone_set('UTC');
new socks5('127.0.0.1:8999');

class socks5 {

    private $socks;
    private $acceptConnect;
    private $protocolVersion;
    private $sshProxy;
    private $sshHost;
    private $sshPort = 22;
    private $sshUser;
    private $sshPass;
    private $sshServerCommand;

    public function __construct($address) {
        $this->sshHost = '192.168.7.33';
        $this->sshUser = 'root';
        $this->sshPass = 'paidai';
        $this->sshServerCommand = '/root/bin/php /root/server.php';
        $this->startListen($address);
    }

    public function readByte() {
        $b = $this->readBinary(array('byte' => 'C'));
        return $b['byte'];
    }

    public function write($format, $p1, $p2) {
        $d = pack($format, $p1, $p2);
        fwrite($this->acceptConnect, $d);
    }

    public function readBinary($structure) {
        $length = 0;
        $unpack = '';
        foreach ($structure as $name => $format) {
            if ($length !== 0) {
                $unpack .= '/';
            }
            $unpack .= $format . $name;
            if ($format === 'C') {
                ++$length;
            } else if ($format === 'n') {
                $length += 2;
            } else if ($format === 'N') {
                $length += 4;
            } else {
                throw new InvalidArgumentException('Invalid format given');
            }
        }
        $response = $this->readLength($length);
        return unpack($unpack, $response);
    }

    public function readLength($bytes) {
        return fread($this->acceptConnect, $bytes);
    }

    public function readByteAssert($expect) {
        $byte = $this->readByte();
        if ($byte !== $expect) {
            throw new UnexpectedValueException('Unexpected byte encountered');
        }
        return $byte;
    }

    public function readNull() {
        return $this->readByteAssert(0x00);
    }

    public function readChar() {
        return $this->readLength(1);
    }

    public function escape($bytes) {
        $ret = '';
        for ($i = 0, $l = strlen($bytes); $i < $l; ++$i) {
            if ($i !== 0) {
                $ret .= ' ';
            }
            $ret .= sprintf('0x%02X', ord($bytes[$i]));
        }
        return $ret;
    }

    public function setProtocolVersion($version) {
        if ($version !== null) {
            $version = (string) $version;
            if (!in_array($version, array('4', '4a', '5'), true)) {
                throw new InvalidArgumentException('Invalid protocol version given');
            }
            if ($version !== '5' && $this->auth !== null) {
                throw new UnexpectedValueException('Unable to change protocol version to anything but SOCKS5 while authentication is used. Consider removing authentication info or sticking to SOCKS5');
            }
        }
        $this->protocolVersion = $version;
    }

    public function onConnection() {
        $version = $this->readByte();
        $auth = null;
        if ($version === 0x04) {
            return $this->handleSocks4(4);
        } elseif ($version === 0x05) {
            return $this->handleSocks5($auth);
        }
        throw new UnexpectedValueException('Unexpected/unknown version number');
    }

    public function handleSocks5($auth = null) {
        $num = $this->readByte();
        $methods = $this->readLength($num);
        if ($auth === null && strpos($methods, "\x00") !== false) {
            $this->write('C2', 0x05, 0x00);
            $methods = 0x00;
        }
        $data = $this->readBinary(array(
            'version' => 'C',
            'command' => 'C',
            'null' => 'C',
            'type' => 'C'
        ));
        if ($data['version'] !== 0x05) {
            throw new UnexpectedValueException('Invalid SOCKS version');
        }
        if ($data['command'] !== 0x01) {
            throw new UnexpectedValueException('Only CONNECT requests supported');
        }
        if ($data['type'] === 0x03) {
            $len = $this->readByte();
            $host = $this->readLength($len);
        } else if ($data['type'] === 0x01) {
            $addr = $this->readLength(4);
            $host = inet_ntop($addr);
        } else if ($data['type'] === 0x04) {
            $addr = $this->readLength(16);
            $host = inet_ntop($addr);
        } else {
            throw new UnexpectedValueException('Invalid target type');
        }
        $data = $this->readBinary(array('port' => 'n'));
        $status = $this->connectTarget($host, $data['port']);
        if ($status) {
            fwrite($this->acceptConnect, pack('C4Nn', 0x05, 0x00, 0x00, 0x01, 0, 0));
            $this->transportData();
        } else {
            fwrite($this->acceptConnect, pack('C4Nn', 0x05, 0x01, 0x00, 0x01, 0, 0));
            $this->endConnection();
        }
    }

    public function endConnection() {
        stream_socket_shutdown($this->acceptConnect, STREAM_SHUT_RD);
        fclose($this->acceptConnect);
        $this->sshProxy->endSSH2();
    }

    public function startListen($address) {
        pcntl_signal(SIGCHLD, SIG_IGN);
        $this->socks = stream_socket_server($address);
        while ($this->acceptConnect = stream_socket_accept($this->socks, -1)) {
            $pid = pcntl_fork();
            if($pid > 0) {
               
            } else {
                $this->onConnection();
            }
        }
        pcntl_wait($status);
    }

    public function connectTarget($host, $port) {
        $this->sshProxy = new SSH2Proxy();
        $this->sshProxy->connectSSHServer($this->sshHost, $this->sshPort);
        $this->sshProxy->authSSHUser($this->sshUser, $this->sshPass);
        return $this->sshProxy->createConnectShell($this->sshServerCommand, $host, $port);
    }

    public function transportData() {
        echo "transport data\n";
        //stream_set_blocking($this->acceptConnect, 0);
        $stdio = $this->sshProxy->getStdIO();
        while (true) {
            $st = @stream_get_meta_data($this->acceptConnect);
            if (!$st) {
                return;
            }
            while (!feof($this->acceptConnect)) {
                echo "start read\n";
                $d = fread($this->acceptConnect, 9216);
                $len = sprintf('%04d', strlen($d));
                fwrite($stdio, $len . $d);
                echo "write part end\n";
                
                $write = $read = array($this->acceptConnect);
                $except = array();
                if (stream_select($read, $write, $except, 200000) > 0) {
                    if (empty($read) && !empty($write)) {
                        break;
                    }
                }
            }
            echo "write end\n";
            fwrite($stdio, "0008-0-0-0-0");
            while (true) {
                $len = fread($stdio, 4);
                if((int)$len == 0) {
                    continue;
                }
                $outdata = fread($stdio, (int)$len);
                echo "$len\n";
                if ($outdata == "-0-0-0-0") {
                    break;
                }
                fwrite($this->acceptConnect,$outdata, strlen($outdata));
            }
            $this->endConnection();
            exit(500);
        }
    }

}

class SSH2Proxy {

    private $sshConnection;
    private $stderr;
    private $stdio;

    public function connectSSHServer($host, $port = 22) {
        $this->sshConnection = ssh2_connect($host, $port);
        if (!$this->sshConnection) {
            throw UnexpectedValueException('connect ssh server error');
        }
    }

    public function authSSHUser($user, $pass) {
        if (!ssh2_auth_password($this->sshConnection, $user, $pass)) {
            throw new UnexpectedValueException('ssh auth failure');
        }
    }

    public function createConnectShell($cmd, $ip, $port) {
        $cmd = "$cmd $ip $port";
        $shell = ssh2_exec($this->sshConnection, $cmd);
        $execStatus = fread($shell, 3);
        if (!empty($execStatus) && $execStatus !== '200') {
            throw new UnexpectedValueException('server command exec error');
        }
        stream_set_blocking($shell, true);
        $this->stderr = ssh2_fetch_stream($shell, SSH2_STREAM_STDERR);
        $this->stdio = ssh2_fetch_stream($shell, SSH2_STREAM_STDIO);
        $connectStatus = fread($this->stderr, 300);
        return $connectStatus == 200;
    }

    public function getStdError() {
        return $this->stderr;
    }

    public function getStdIO() {
        return $this->stdio;
    }
    public function endSSH2() {
        ssh2_exec($this->sshConnection, 'exit');
    }
}
