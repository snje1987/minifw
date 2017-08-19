<?php

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

class Secoder {

    const EXPIRE = 600;
    public static $code_set = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public static function entry($key) {
        $font_size = 14;
        $length = 4;
        $bg = array(243, 251, 254);

        $image_l = $length * $font_size * 1.8 + $font_size * 2.0;
        $image_h = $font_size * 1.5;
        $image = imagecreate($image_l, $image_h);
        imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);
        $_color = imagecolorallocate($image, mt_rand(1, 120), mt_rand(1, 120), mt_rand(1, 120));
        $ttfs = Config::get()->get_config('fonts', 'secode');
        if (!is_array($ttfs) || count($ttfs) < 1) {
            throw new Exception('字体未指定');
        }
        $key = array_rand($ttfs);
        $ttf = WEB_ROOT . $ttfs[$key];

        $last_index = strlen(self::$code_set) - 1;
        for ($i = 0; $i < 10; $i++) {
            $noiseColor = imagecolorallocate($image, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
            for ($j = 0; $j < 5; $j++) {
                imagestring($image, 3, mt_rand(-10, $image_l), mt_rand(-10, $image_h), self::$code_set{mt_rand(0, $last_index)}, $noiseColor);
            }
        }

        $code = array();
        $codeNX = $font_size * 1.2;

        for ($i = 0; $i < $length; $i++) {
            $code[$i] = self::$code_set[mt_rand(0, $last_index)];
            imagettftext($image, $font_size, mt_rand(-20, 40), $codeNX, $font_size * 1.2, $_color, $ttf, $code[$i]);
            $codeNX += mt_rand($font_size * 1.5, $font_size * 2.0);
        }

        isset($_SESSION) || session_start();
        $_SESSION[$key]['code'] = join('', $code);
        $_SESSION[$key]['time'] = time();

        header('Pragma: no-cache');
        header("content-type: image/JPEG");

        imageJPEG($image);
        imagedestroy($image);
    }

    public static function test($str, $key = 'secoder_key') {
        $str = strtoupper($str);
        $time = time();
        if (!isset($_SESSION[$key])) {
            return false;
        }
        $code = $_SESSION[$key]['code'];
        $ctime = $_SESSION[$key]['time'];
        unset($_SESSION[$key]);
        if (($time - $ctime) > self::EXPIRE) {
            return false;
        }
        if ($str == $code) {
            return true;
        }
        return false;
    }

}
