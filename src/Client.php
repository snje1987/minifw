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
     * @param string $cookie Cookie
     * @param string $referer Referer
     * @return array 接收到的数据
     */
    public static function post($url, $data, $cookie = '', $referer = '') {
        $result = [];
        $result['url'] = $url;
        $result['cookie_send'] = $cookie;

        $o = "";
        foreach ($data as $k => $v) {
            $o .= $k . '=' . urlencode($v) . '&';
        }
        $data = substr($o, 0, -1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); //不自动跳转
        curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:20.0) Gecko/20100101 Firefox/20.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($cookie != '') {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if ($referer != '') {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }

        $content = curl_exec($ch);
        curl_close($ch);

        if (preg_match_all('/Set-Cookie:(.*);/iU', $content, $matches)) {
            $cookie = implode(';', $matches[1]);
        } else {
            $cookie = '';
        }

        $location = '';
        if (preg_match('/Location:(.*)\r\n/iU', $content, $matches)) {
            $location = trim($matches[1]);
        } else {
            $location = '';
        }

        $header = '';
        $pos = strpos($content, "\r\n\r\n");
        if ($pos !== false) {
            $header = substr($content, 0, $pos);
            $content = substr($content, $pos + 4);
        }

        $result['cookie'] = substr($cookie, 1);
        $result['location'] = $location;
        $result['header'] = $header;
        $result['content'] = $content;

        return $result;
    }

    /**
     * 用GET方法发送数据到指定的URL，并接收数据
     *
     * @param string $url 发送到的url
     * @param array $data 要发送的数据
     * @param string $cookie Cookie
     * @param string $referer Referer
     * @return array 接收到的数据
     */
    public static function get($url, $data, $cookie = '', $referer = '') {
        if (!empty($data)) {
            $o = "";
            foreach ($data as $k => $v) {
                $o .= $k . '=' . urlencode($v) . '&';
            }
            $data = substr($o, 0, -1);
            $url .= '?' . $data;
        }

        $result = [];
        $result['url'] = $url;
        $result['cookie_send'] = $cookie;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); //不自动跳转
        curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:20.0) Gecko/20100101 Firefox/20.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($cookie != '') {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if ($referer != '') {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }

        $content = curl_exec($ch);
        curl_close($ch);

        if (preg_match_all('/Set-Cookie:(.*);/iU', $content, $matches)) {
            $cookie = implode(';', $matches[1]);
        } else {
            $cookie = '';
        }

        $location = '';
        if (preg_match('/Location:(.*)\r\n/iU', $content, $matches)) {
            $location = trim($matches[1]);
        } else {
            $location = '';
        }

        $header = '';
        $pos = strpos($content, "\r\n\r\n");
        if ($pos !== false) {
            $header = substr($content, 0, $pos);
            $content = substr($content, $pos + 4);
        }

        $result['cookie'] = substr($cookie, 1);
        $result['location'] = $location;
        $result['header'] = $header;
        $result['content'] = $content;

        return $result;
    }

}
