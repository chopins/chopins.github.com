<?php
if(!defined('DATA_ROOT')) {
    define('DATA_ROOT', dirname($_SERVER['SCRIPT_FILENAME']));
}

if(!defined('CA_KEY_PASS')) {
    define('CA_KEY_PASS', sha1( $_SERVER['SCRIPT_FILENAME'] . 'toknot-ca- key-pass' . php_uname('n')));
}
class IssueCert
{
    const CA_DATA = DATA_ROOT . '/CA';
    const CERT_DATA = DATA_ROOT . '/CERT';
    public function __construct()
    {
        $this->checkDir();
        $this->checkRootCA();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->getPost($dns, $subject);
            $this->issueServerCert($dns, $subject);
        } else {
            CertSubject::form();
        }
    }

    public function getPost(&$dns, &$subject)
    {
        $dns = $_POST['dns'];
        $subject = new CertSubject($subject['C'], $subject['ST'], $subject['L'], $subject['O'], $subject['OU'], $subject['CN'], $subject['email']);
    }

    public function getSerialHex()
    {
        return sha1(microtime() . ' seria-hex ' . openssl_random_pseudo_bytes(10));
    }

    public function issueServerCert(array $dns, CertSubject $subject)
    {
        $config = [
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        $cn = sha1($subject->CN);

        $spkey = openssl_pkey_new($config);
        openssl_pkey_export_to_file($spkey, self::CERT_DATA . "/$cn.key");
        $altName = 'DNS:' . join(', DNS:');
        $config['x509_extensions'] = [
            'authorityKeyIdentifier' => 'keyid,issuer',
            'basicConstraints' => 'CA:FALSE',
            'keyUsage' => 'digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment',
            'extendedKeyUsage' => 'serverAuth',
            "subjectAltName" => $altName
        ];
        $config['req_extensions'] = [
            'subjectAltName' => $altName
        ];
        $csr = openssl_csr_new($subject->toArray(), $spkey, $config);
        $csrSigned = openssl_csr_sign($csr, $capem, $cakey, 365, $config, 0, $this->getSerialHex());
        openssl_x509_export_to_file($csrSigned, self::CERT_DATA . "$cn.crt");
        $this->showDownload($cn);

    }



    public function checkRootCA()
    {
        if (!file_exists(self::CA_DATA . '/ca.key') || file_exists(self::CA_DATA . '/ca.crt')) {
            return $this->createCA();
        }
    }

    public function createCA()
    {
        $dh = [
            "countryName" => "CN",
            "stateOrProvinceName" => "Guizhou",
            "localityName" => "Xinyi",
            "organizationName" => "Toknot",
            "organizationalUnitName" => "Toknot Dev",
            "commonName" => "Toknot Root CA",
            "emailAddress" => "x@toknot.com"
        ];
        $option = [
            'private_key_bits' => 4096,
            'digest_alg' => 'sha256',
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'encrypt_key_cipher' => OPENSSL_CIPHER_AES_256_CBC,
            'encrypt_key' => true
        ];
        $prikey = openssl_pkey_new($option);
        openssl_pkey_export_to_file($prikey, self::CA_DATA . '/ca.key', CA_KEY_PASS, $option);

        $option['x509_extensions'] = [
            'subjectKeyIdentifier' => 'hash',
            'authorityKeyIdentifier' => 'keyid:always,issuer',
            'basicConstraints' => 'critical, CA:true',
            'keyUsage' => 'critical, digitalSignature, cRLSign, keyCertSign'
        ];
        $csr = openssl_csr_new($dh, $prikey, $option);
        $csrSigned = openssl_csr_sign($csr, null, $prikey, 3650, $option, 0, $this->getSerialHex());
        openssl_x509_export_to_file($csrSigned, self::CA_DATA . '/ca.crt');
    }

    public function checkDir()
    {
        if (!is_dir(DATA_ROOT)) {
            throw new RuntimeException('Directory ' . DATA_ROOT . ' is not exits');
        }
        if (file_exists(self::CA_DATA) && !is_dir(self::CA_DATA)) {
            throw new RuntimeException(self::CA_DATA . ' is not directory');
        }
        if (!file_exists(self::CA_DATA)) {
            mkdir(self::CA_DATA, 0600);
        }
        if (!file_exists(self::CERT_DATA)) {
            mkdir(self::CERT_DATA);
        }
    }

    public function showDownload($cn)
    {
        $key = file_get_contents(self::CERT_DATA . "/$cn.key");
        $crt = file_get_contents(self::CERT_DATA. "/$cn.crt");
    }
}

class CertSubject
{
    public function __construct(
        public readonly string $C,
        public readonly string $ST,
        public readonly string $L,
        public readonly string $O,
        public readonly string $OU,
        public readonly string $CN,
        public readonly string $email
    ) {}
    public function toArray()
    {
        return [
            "countryName" => $this->C,
            "stateOrProvinceName" => $this->ST,
            "localityName" => $this->L,
            "organizationName" => $this->O,
            "organizationalUnitName" => $this->OU,
            "commonName" => $this->CN,
            "emailAddress" => $this->email
        ];
    }

    public static function form()
    {
        echo <<<HTML
        <html><head><title>Add</title></head>
            <body>
        <form action="/" method="POST">
        <h3>Distinguished Name</h3>
        <div><label for="C">countryName</label><input name="C" type="text"></div>
        <div><label for="ST">stateOrProvinceName</label><input name="ST" type="text"></div>
        <div><label for="L">localityName</label><input name="L" type="text"></div>
        <div><label for="O">organizationName</label><input name="O" type="text"></div>
        <div><label for="OU">organizationalUnitName</label><input name="OU" type="text"></div>
        <div><label for="CN">commonName</label><input name="CN" type="text"></div>
        <div><label for="email">emailAddress</label><input name="email" type="text"></div>
        <h3>subject Alt Name</h3>
        <div><label for="dns[]">DNS</label><input name="dns[]" type="text"></div>
        <div><label for="dns[]">DNS</label><input name="dns[]" type="text"></div>
        <div><label for="dns[]">DNS</label><input name="dns[]" type="text"></div>
        <div><label for="dns[]">DNS</label><input name="dns[]" type="text"></div>
        </form></body></html>
        HTML;
    }
}
