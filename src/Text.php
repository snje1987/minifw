<?php

/*
 * Copyright (C) 2013 Yang Ming <yangming0116@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Org\Snje\Minifw;

class Text {

    /**
     * 压缩html数据
     *
     * @param string $str 要压缩的数据
     * @return string 压缩后的数据
     */
    public static function strip_html($str) {
        $str = preg_replace('/^\s*(.*?)\s*$/im', '$1', $str);
        $str = preg_replace('/^\/\/(.*?)$/im', '', $str);
        $str = preg_replace('/\r|\n/i', ' ', $str);
        $str = preg_replace('/\>\s*(.*?)\s*\</im', '>$1<', $str);
        $str = preg_replace('/\s{2,}/i', ' ', $str);
        return $str;
    }

    /**
     * 清除所有的html标记
     *
     * @param string $str 要处理的数据
     * @return string 处理后的数据
     */
    public static function strip_tags($str) {
        return preg_replace('/\<(\/?[a-zA-Z0-9]+)(\s+[^>]*)?\/?\>/i', '', $str);
    }

    /**
     * 判断字符串中是否具有html标记
     *
     * @param string $str 要判断的字符串
     * @return bool 具有标记返回true，否则返回fasle
     */
    public static function is_rich($str) {
        return preg_match('/\<(\/?[a-zA-Z0-9]+)(\s+[^>]*)?\/?\>/i', $str);
    }

    /**
     * 清除标记后截取指定长度的字符串
     *
     * @param string $str 要截取的字符串
     * @param int $len 要截取的长度
     * @return string 截取的结果
     */
    public static function sub_text($str, $len) {
        $encoding = Config::get()->get_config('main', 'encoding');
        $str = self::strip_tags($str);
        $str = preg_replace('/(\s|&nbsp;)+/i', ' ', $str);
        return mb_substr($str, 0, $len, $encoding);
    }

    /**
     * 截取指定长度的具有基本格式的字符串
     *
     * @param string $str 要截取的字符串
     * @param int $len 要截取的长度
     * @return string 截取的结果
     */
    public static function sub_rich($str, $len) {
        $encoding = Config::get()->get_config('main', 'encoding');
        if (self::is_rich($str)) {
            $str = self::strip_html($str);
            $str = preg_replace('/\r/i', '', preg_replace('/\n/i', '', $str));
            $str = preg_replace('/\<br[^>]*\>/i', "\n", preg_replace('/\<p[^>]*\>/i', "\n", $str));
            $str = self::strip_tags($str);
        }
        $str = preg_replace('/^\s*\n/im', '', preg_replace('/(\t| |　|&nbsp;)+/i', ' ', $str));
        $str = mb_substr($str, 0, $len, $encoding);
        $str = preg_replace('/^([^\r\n]*)\r?\n?$/im', "<p>$1</p>", $str);
        return $str;
    }

    /**
     * 计算字符串长度
     *
     * @param string $str 字符串
     * @return int 长度
     */
    public static function str_len($str) {
        $encoding = Config::get()->get_config('main', 'encoding');
        return mb_strlen($str, $encoding);
    }

    public static function is_email($str) {
        if (!filter_var($str, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }

    public static function is_phone($str) {
        if (!preg_match("/^(13[0-9]{9})|(15[0-9]{9})|(18[0-9]{9})$/", $str)) {
            return false;
        }
        return true;
    }

    public static function is_tel($str) {
        if (!preg_match("/^\d{3,4}-\d{7,8}(-\d{1,6})?$/", $str)) {
            return false;
        }
        return true;
    }

    public static function is_num($str) {
        if (!preg_match("/^-?\d+(\.\d+)?$/", $str)) {
            return false;
        }
        return true;
    }

    public static function is_expr($str) {
        if (!preg_match("/^[-(]*\d+(\.\d+)?[)]*$/", $str)) {
            return false;
        }
        return true;
    }

    public static function is_positive($str) {
        if (!preg_match("/^\d+(\.\d+)?$/", $str)) {
            return false;
        }
        return true;
    }

}
