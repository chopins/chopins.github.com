<?php

date_default_timezone_set('UTC');
new socks5('127.0.0.1:9050');

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
    private $readable = false;
    private $writeable = false;
    private $remoteLocalName = 0;
    private $localPort = 0;
    private $ts;

    public function __construct($address) {
        $this->sshHost = '192.168.7.33';
        $this->sshHost = 'page.toknot.com';
        $this->sshUser = 'root';
        $this->sshUser = 'mytoknot';
        $this->sshPass = 'paidai';
        $this->sshPass = 'ARXCCV@qq1122';
        $this->httpRequest();
        echo $this->remoteLocalName;
        $this->tunnelServer();
        $this->sshServerCommand = "/root/bin/php /root/server.php";
        $this->sshServerCommand = '/usr/local/php5_3/bin/php -n /var/chroot/home/content/09/12374909/data/server.php';
        list($host, $port) = explode(':', $this->remoteLocalName);
        $this->connectTarget($host, $port);
        pcntl_wait($status);
        return;
        $this->startListen($address);
    }

    public function tunnelServer() {
        $pid = pcntl_fork();
        if($pid > 0) return;
        $num = 0;
        while($num < 3) {
            $s = @stream_socket_server("0.0.0.0:{$this->localPort}");
            if($s) {
                break;
            }
            sleep(1);
            $num++;
        }
        echo 'start tunnel';
        while ($acceptConnect = stream_socket_accept($s, -1)) {
            echo 'connect success';
        }
    }

    public function httpRequest() {
        $fp = fsockopen($this->sshHost, 80);
        if ($fp) {
            fwrite($fp, "GET /host.php HTTP/1.1\r\nHost: {$this->sshHost}\r\nConnection: Close\r\n\r\n");
        }
        $name = stream_socket_get_name($fp, false);
        echo $name;
        list(, $this->localPort) = explode(':', $name);
        $headerEnd = $chunked = false;
        while (!feof($fp)) {
            $r = fgets($fp);
            if (trim($r) == 'Transfer-Encoding: chunked') {
                $chunked = true;
            }
            if ("\r\n" == $r) {
                $headerEnd = true;
            }
            if ($headerEnd) {
                if ($chunked) {
                    fgets($fp);
                    $port = fgets($fp);
                } else {
                    $port = fgets($fp);
                }
                break;
            }
        }
        $this->remoteLocalName = $port;
        stream_socket_shutdown($fp, STREAM_SHUT_RD);
        fclose($fp);
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
            $this->readable = true;
            $this->writeable = false;
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
            if ($pid > 0) {
                $this->acceptConnect = null;
                usleep(10000);
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

        $stdio = $this->sshProxy->getStdIO();
        while (!feof($this->acceptConnect)) {
            if ($this->readable && !$this->writeable) {
                $data = stream_socket_recvfrom($this->acceptConnect, 1024);
                echo "read\n";
                if (!empty($data)) {
                    echo "read data\n";
                    $len = sprintf('%04d', strlen($data));
                    fwrite($stdio, $len . $data);
                } else {
                    fwrite($stdio, '0008-0-0-0-0');
                    echo "read end\n";
                    $this->writeable = true;
                    $this->readable = false;
                }
            }
            if (!$this->readable && $this->writeable) {
                fwrite($stdio, '-1-1-1-1');
                echo "write\n";
                $len = fread($stdio, 4);
                if (empty($len)) {
                    continue;
                }
                if ((int) $len == 0) {
                    continue;
                }
                $outdata = fread($stdio, (int) $len);
                fwrite($stdio, '-1-1-1-1');
                if ($outdata == "-0-0-0-0") {
                    echo "write end\n";
                    $this->readable = true;
                    $this->writeable = false;
                    continue;
                }
                echo "write data\n";
                fwrite($this->acceptConnect, $outdata);
            }
        }
        $this->endConnection();
        exit(500);
    }

}

class SSH2Proxy {

    private $sshConnection;
    private $stderr;
    private $stdio;
    private $buffFile;

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
        echo $cmd ."\n";
        $shell = ssh2_exec($this->sshConnection, $cmd);
        $execStatus = fread($shell, 3);

        if (!empty($execStatus) && $execStatus !== '200') {
            throw new UnexpectedValueException('server command exec error');
        }
        stream_set_blocking($shell, true);
        $this->stderr = ssh2_fetch_stream($shell, SSH2_STREAM_STDERR);
        $this->stdio = ssh2_fetch_stream($shell, SSH2_STREAM_STDIO);
        $connectStatus = fread($this->stderr, 100);
        $connectStatusCode = substr($connectStatus, 0, 3);
        $this->buffFile = substr($connectStatus, 3);
        return $connectStatusCode == 200;
    }

    public function getStdError() {
        return $this->stderr;
    }

    public function getBuffFile() {
        return $this->buffFile;
    }

    public function getStdIO() {
        return $this->stdio;
    }

    public function endSSH2() {
        ssh2_exec($this->sshConnection, 'exit');
    }

}
