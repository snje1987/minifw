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

/**
 * @filename Client.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @copyright Copyright (C) 2013 杨明
 * @datetime 2013-3-26 15:43:11
 * @version 1.0
 * @Description 一些网络的操作，包括get和post
 */

namespace Org\Snje\Minifw;

/**
 * 网络的基本操作
 */
class Client {

    /**
     * 用POST方法发送数据到指定的URL，并接收数据
     *
     * @param string $url 发送到的url
     * @param array $data 要发送的数据
     * @return 接收到的数据
     */
    public static function post($url, $data) {
        $o = "";
        foreach ($data as $k => $v) {
            $o.= "$k=" . urlencode($v) . "&";
        }
        $data = substr($o, 0, -1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        //为了支持cookie
        //curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        return $result;
    }

    /**
     * 用GET方法发送数据到指定的URL，并接收数据
     *
     * @param string $url 发送到的url
     * @param array $data 要发送的数据
     * @return 接收到的数据
     */
    public static function get($url, $data) {
        $o = "";
        foreach ($data as $k => $v) {
            $o.= "$k=" . urlencode($v) . "&";
        }
        $data = substr($o, 0, -1);
        $url .= '?' . $data;
        //die($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $result = curl_exec($ch);
        return $result;
    }

}
