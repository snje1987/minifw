<?php

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

class Random {

    public static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
    public static $alphas = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    public static $digits = '0123456789';
    protected static $char_len;
    protected static $alpha_len;
    protected static $digit_len;

    public static function gen_key($len) {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::$chars[mt_rand(0, self::$char_len - 1)];
        }
        return $key;
    }

    public static function gen_str($len) {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::$alphas[mt_rand(0, self::$alpha_len - 1)];
        }
        return $key;
    }

    public static function gen_num($len) {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::$digits[mt_rand(0, self::$digit_len - 1)];
        }
        return $key;
    }

    public static function init() {
        self::$char_len = strlen(self::$chars);
        self::$alpha_len = strlen(self::$alphas);
        self::$digit_len = strlen(self::$digits);
    }

}

Random::init();
