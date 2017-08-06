<?php

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

class Random {

    const CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
    const ALPHAS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789';
    const DIGITS = '0123456789';

    protected static $char_len;
    protected static $alpha_len;
    protected static $digit_len;

    public static function gen_key($len) {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::CHARS[mt_rand(0, self::$char_len - 1)];
        }
        return $key;
    }

    public static function gen_str($len) {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::ALPHAS[mt_rand(0, self::$alpha_len - 1)];
        }
        return $key;
    }

    public static function gen_num($len) {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::DIGITS[mt_rand(0, self::$digit_len - 1)];
        }
        return $key;
    }

    public static function init() {
        self::$char_len = strlen(self::CHARS);
        self::$alpha_len = strlen(self::ALPHAS);
        self::$digit_len = strlen(self::DIGITS);
    }

}

Random::init();
