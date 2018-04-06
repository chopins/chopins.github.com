<?
class Time {

    protected static $timeoutCallback = [];
    private static $setup = false;

    const TIME_SECOND = 0;
    const TIME_MILLISECOND = 1;
    const TIME_MICROSECOND = 2;
    const TIME_NANOSECOND = 3;

    public static function sigHandler() {
        foreach (self::$timeoutCallback as $i => $handler) {
            if (microtime(true) >= $handler[0]) {
                $handler[1]();
                unset(self::$timeoutCallback[$i]);
            }
        }
        if (count(self::$timeoutCallback) > 0) {
            pcntl_alarm(1);
        }
    }

    public static function endHandler() {
        while (count(self::$timeoutCallback) > 0) {
            usleep(100000);
        }
    }

    protected static function initSignal() {
        pcntl_async_signals(1);
        pcntl_signal(SIGALRM, array(__CLASS__, 'sigHandler'));
        pcntl_alarm(1);
        register_shutdown_function(array(__CLASS__, 'endHandler'));
    }

    public static function setTimeout($func, $time) {
        if (!self::$setup) {
            self::initSignal();
            self::$setup = true;
        }
        self::$timeoutCallback[] = [microtime(true) + $time, $func];
        return count(self::$timeoutCallback) - 1;
    }

    public static function clearTimeout($id) {
        unset(self::$timeoutCallback[$id]);
    }

    public static function sleep($time, $unit = self::TIME_SECOND) {
        if ($unit === self::TIME_NANOSECOND) {
            time_nanosleep(0, $time);
        } elseif ($unit === self::TIME_MICROSECOND) {
            usleep($time);
        } elseif ($unit === self::TIME_MILLISECOND) {
            usleep($time * 1000);
        } else {
            $until = microtime(true) + $time;
            sleep($time);
            if (microtime(true) < $until) {
                $newsec = round($until - microtime(true));
                self::sleep($newsec);
            }
        }
    }

}


echo 'set timeout at  ' . microtime(true) . PHP_EOL;
Time::setTimeout(function() {
    echo 'alarm in  ' . microtime(true) . PHP_EOL;
}, 2);

Time::sleep(10);
echo 'sleep end ' . microtime(true) . PHP_EOL;
