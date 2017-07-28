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
 * @filename Server.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @copyright Copyright (C) 2013 杨明
 * @datetime 2013-3-26 15:42:14
 * @version 1.0
 * @Description 一些有关url的操作
 */

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;

/**
 * 定义一些常用的服务端功能
 */
class Server {

    public static $cache_time;

    /**
     * 重定向页面到指定位置，会根据是否发送的header来决定使用不同的方法
     *
     * @param string $url 目标地址
     */
    public static function redirect($url) {
        if (!headers_sent()) {
            header('Location:' . $url);
        } else {
            echo '<script type="text/javascript">window.location="' . $url . '";</script>';
        }
        die(0);
    }

    /**
     * 返回当前页面的地址
     */
    public static function cur_page_url() {
        $pageURL = 'http';

        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    /**
     * 显示404页面
     */
    public static function show_404() {
        header("HTTP/1.1 404 Not Found");
        header("status: 404 not found");
        die(readfile(WEB_ROOT . FW\Config::get()->get_config('main', 'err_404')));
    }

    /**
     * 使用301重定向页面到指定位置
     *
     * @param string $url 目标地址
     */
    public static function show_301($url) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);
        die(0);
    }

    /**
     * 根据文件的更新情况显示304页面
     *
     * @param type $mtime 文件更新时间
     */
    public static function show_304($mtime) {
        $expire = gmdate('D, d M Y H:i:s', time() + self::$cache_time) . ' GMT';
        header('Expires: ' . $expire);
        header('Pragma: cache');
        header('Cache-Control: max-age=' . self::$cache_time);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
        header('Etag: ' . $mtime);
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $mtime) {
            header('HTTP/1.1 304 Not Modified');
            die(0);
        }
    }

    /**
     * 生成到前一个页面的链接
     *
     * @param string $default 如果referer不存在则返回该值
     * @return string 生成的链接
     */
    public static function referer($default = '') {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $url = $_SERVER['HTTP_REFERER'];
        } else {
            $url = $default;
        }
        return $url;
    }

}

Server::$cache_time = Config::get()->get_config('main', 'cache', 3600);
