<?php

$line = 0;
while (true) {
    echo "Line $line" . PHP_EOL;
    echo "Time " . time();
    echo Consle::up(1, 1);
    $line++;
    sleep(1);
}

class Consle
{
    const BLACK = 30;
    const RED = 31;
    const GREEN = 32;
    const YELLOW = 33;
    const BLUE = 34;
    const PURPLE = 35;
    const CYAN = 36;
    const WHITE = 37;
    const C_DEFAULT = 39;

    const H_BLACK = 90;
    const H_RED = 91;
    const H_GREEN = 92;
    const H_YELLOW = 93;
    const H_BLUE = 94;
    const H_PURPLE = 95;
    const H_CYAN = 96;
    const H_WHITE = 97;

    const B_BLACK = 40;
    const B_RED = 41;
    const B_GREEN = 42;
    const B_YELLOW = 43;
    const B_BLUE = 44;
    const B_PURPLE = 45;
    const B_CYAN = 46;
    const B_WHITE = 47;
    const B_DEFAULT = 49;
    const HB_BLACK = 100;
    const HB_RED = 101;
    const HB_GREEN = 102;
    const HB_YELLOW = 103;
    const HB_BLUE = 104;
    const HB_PURPLE = 105;
    const HB_CYAN = 106;
    const HB_WHITE = 107;

    const RESET = 0;
    const BLOD = 1;
    const BLUR = 2;
    const ITALIC = 3;
    const UNDERLINE = 4;
    const BLINK = 5;
    const REVERT = 7;
    const HIDDEN = 8;
    const THROUGH = 9;

    const C_UP = 'A';
    const C_DOWN = 'B';
    const C_RIGHT = 'C';
    const C_LEFT = 'D';
    const C_DOWN_S = 'E';
    const C_UP_S = 'F';
    const C_COLS = 'G';

    const C_POS = 'H';
    const C_GPOS = '6n';
    const C_SC = 's';
    const C_RC = 'u';
    const C_H_CURSOR = '?25l';
    const C_S_CURSOR = '?25h';
    const C_R_SCREEN = '?47l';
    const C_S_SCREEN = '?47h';
    const C_CACHE = '?1049h';
    const C_DCACHE = '?1049l';
    const C_CLEAR = 'J';
    const C_EL = 'K';

    /**
     * @param mixed $txt
     * @param string|array $color   字符串为控制码，2个元素的数组为256色， 4个元素数组为RGB色，数组时最后一个元素为是否是背景色
     *
     * @return void
     */
    public function print($txt, string|array $color):void
    {
        if (is_string($color)) {
            echo self::m($color) . $txt . self::m(self::RESET);
        }
        if (count($color) === 2) {
            echo self::color256(...$color) . $txt . self::m(self::RESET);
        }
        if (count($color) === 4) {
            echo self::rgb(...$color) . $txt . self::m(self::RESET);
        }
    }

    public static function m($code)
    {
        return "\033[{$code}m";
    }

    public static function c($code, $n = '')
    {
        return "\033[{$n}{$code}";
    }

    public static function cHide()
    {
        echo self::c(self::C_H_CURSOR);
    }

    public static function cShow()
    {
        echo self::c(self::C_S_CURSOR);
    }

    /**
     * @param int $t    0 从光标到屏尾，1从光标到屏首，2擦除整个屏幕，3删除保存的行
     *
     * @return void
     */
    public static function clear($t = 2):void
    {
        echo self::c(self::C_CLEAR, $t);
    }

    /**
     * @param int $pos   从光标到行尾，1从光标到行首，2擦除整个行
     *
     * @return void
     */
    public static function el($pos = 2):void
    {
        echo self::c(self::C_EL, $pos);
    }

    /**
     * 光标到指定行列
     * @param int $x
     * @param int $y
     *
     * @return void
     */
    public static function pos(int $x, int $y): void
    {
        echo self::c(self::C_POS, "$x;$y");
    }

    /**
     * 光标右移动列
     *
     * @param int $cols
     *
     * @return void
     */
    public static function right(int $cols): void
    {
        echo self::c(self::C_RIGHT, $cols);
    }

    /**
     * 光标左移动列
     * @param int $cols
     *
     * @return void
     */
    public static function left(int $cols): void
    {
        echo self::c(self::C_LEFT, $cols);
    }
    /**
     * 光标下移动行
     *
     * @param int $line
     * @param bool $start
     *
     * @return void
     */
    public static function down(int $line, bool $start = false): void
    {
        if ($start) {
            echo self::c(self::C_DOWN_S, $line);
        } else {
            echo self::c(self::C_DOWN, $line);
        }
    }
    /**
     * 光标上移动行
     *
     * @param int $line
     * @param bool $start
     *
     * @return void
     */
    public static function up(int $line = 1, bool $start = false): void
    {
        if ($start) {
            echo self::c(self::C_UP_S, $line);
        } else if ($line > 1) {
            echo self::c(self::C_UP, $line);
        } else {
            echo "\033M";
        }
    }
    public static function gPos()
    {
        echo "\0337";
    }
    public static function rPos()
    {
        echo "\0338";
    }

    protected static function color256($color, $bg)
    {
        if ($color > 255 || $color < 0) {
            throw new TypeError("color only 0 - 255");
        }
        return "\033[{$bg};5;{$color}m";
    }
    protected static function rgb($r, $g, $b, $bg)
    {
        $code = $bg ? 48 : 38;
        return "\033[{$bg};2;{$r};{$g};${b}m";
    }
}
