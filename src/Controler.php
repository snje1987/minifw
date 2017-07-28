<?php

/*
 * Copyright (C) 2017 Yang Ming <yangming0116@163.com>
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

use Org\Snje\Minifw\Exception;

/**
 * Base Controler
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class Controler {

    const DEFAULT_FUNCTION = '';

    public static $cache_time;
    protected $config;

    public function __construct() {
        $this->config = Config::get();
    }

    /**
     * Call controler function according to the given name.
     * @param type $func function name
     * @param type $args args.
     */
    public function dispatch($function, $args) {

        $class = new \ReflectionClass(static::class);
        if ($function == '') {
            $function = $class->getConstant('DEFAULT_FUNCTION');
        }
        $function = str_replace('.', '', $function);
        if ($function == '') {
            throw new Exception('No function specify.');
        }
        $function = 'c_' . $function;
        if (!$class->hasMethod($function)) {
            throw new Exception('Function not exists.');
        }

        $func = $class->getMethod($function);
        $obj = $class->newInstance();
        $func->setAccessible(true);
        $func->invoke($obj, $args);
    }

    public function show_404() {
        header("HTTP/1.1 404 Not Found");
        header("status: 404 not found");
        die(readfile(WEB_ROOT . $this->config->get_config('main', 'err_404')));
    }

    public function redirect($url) {
        if (!headers_sent()) {
            header('Location:' . $url);
        } else {
            echo '<script type="text/javascript">window.location="' . $url . '";</script>';
        }
        die(0);
    }

    public function show_301($url) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);
        die(0);
    }

    public function readfile_with_304($file, $fsencoding) {
        $full = File::conv_to($file, $fsencoding);
        $mtime = \filemtime($full);
        $expire = gmdate('D, d M Y H:i:s', time() + self::$cache_time) . ' GMT';
        header('Expires: ' . $expire);
        header('Pragma: cache');
        header('Cache-Control: max-age=' . self::$cache_time);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
        header('Etag: ' . $mtime);
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $mtime) {
            header('HTTP/1.1 304 Not Modified');
        } else {
            File::readfile($full);
        }
        die(0);
    }

    public function referer($default = null) {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $url = strval($_SERVER['HTTP_REFERER']);
        } else {
            $url = $default;
        }
        return $url;
    }

}

Controler::$cache_time = Config::get()->get_config('main', 'cache', 3600);
