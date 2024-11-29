<?php
class TimeHash
{
    protected static function hash(string $algo, string $str, ?string $key = null)
    {
        if ($key !== null) {
            if (in_array($algo, hash_hmac_algos())) {
                return  hash_hmac($algo, $str, $key);
            }
            $str .= $key;
        }
        return hash($algo, $str);
    }

    /**
     * 根据当前时间生成散列值
     * 
     * @param string $algo      要使用的散列算法
     * @param string $str       要进行散列运算的消息。
     * @param string|null $key  生成信息摘要时所使用的密钥，为 null 时，不使用密钥，当算法不支持 HMAC 方法时，密钥将会附加到 $str 后进行生成
     * @param int $expire       信息摘要的有效时间（秒）（向前、向后的时间）
     * 
     * @return string
     * @throws ValueError
     */
    public static function get(string $algo, string $str, ?string $key = null, int $expire = 5): string
    {
        if ($expire < 0 || $expire > 36) {
            throw new ValueError('Argument #4 $expire must be between 0 and 18 (inclusive)');
        }

        $base = $expire * 2;
        $dectime = time();
        if ($expire === 0) {
            return self::hash($algo, $str . $dectime);
        }
        $time = base_convert($dectime, 10, $base);
        $time[-1] = '0';
        return self::hash($algo, $str . $time);
    }

    /**
     * 根据当前时间校验散列值
     * 
     * @param string $algo      要使用的散列算法
     * @param string $str       要进行散列运算的消息。
     * @param string $sign      要校验的信息摘要
     * @param string|null $key  生成信息摘要时所使用的密钥
     * @param int $expire       信息摘要的有效时间（秒）（向前、向后的时间）
     * 
     * @return bool
     * @throws ValueError
     */
    public static function verify(string $algo, string $str, string $sign, ?string $key = null, int $expire = 5): bool
    {
        if ($expire < 0 || $expire > 36) {
            throw new ValueError("Argument #5 $expire must be between 0 and 18 (inclusive)");
        }
        $base = $expire * 2;
        $dectime = time();
        if ($expire === 0) {
            return self::hash($algo, $str . $dectime) === $sign;
        }
        $time = base_convert($dectime, 10, $base);
        $end = $time[-1];
        $time[-1] = '0';
        if (self::hash($algo, $str . $time) === $sign) {
            return true;
        }
        if ($end < $expire) {
            $dectime = $dectime - $expire;
        } else if ($base - $end < $expire) {
            $dectime = $dectime + $expire;
        } else {
            return false;
        }
        $time = base_convert($dectime, 10, $base);
        $time[-1] = '0';
        return self::hash($algo, $str . $time) === $sign;
    }
}