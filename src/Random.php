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

    public static function gen_int($min, $max) {
        if (function_exists('random_int')) {
            return random_int($min, $max);
        }
        return mt_rand($min, $max);
    }

    public static function gen_byte($len, $bin = false) {
        $byte = null;
        if (function_exists('random_bytes')) {
            $byte = random_bytes($length);
        }
        if (function_exists('mcrypt_create_iv')) {
            $byte = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $byte = openssl_random_pseudo_bytes($length);
        }
        if ($bin) {
            return $byte;
        } else {
            return bin2hex($byte);
        }
    }

    public static function gen_key($len) {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::CHARS[self::gen_int(0, self::$char_len - 1)];
        }
        return $key;
    }

    public static function gen_str($len) {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::ALPHAS[self::gen_int(0, self::$alpha_len - 1)];
        }
        return $key;
    }

    public static function gen_num($len) {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::DIGITS[self::gen_int(0, self::$digit_len - 1)];
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
