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

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

/**
 * Define some basic route functions.
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class Router {

    /**
     * Get route info from url.
     * @param string $url The url.
     * @return array Route info.
     */
    public static function path_info($url) {
        $url = strval($url);
        $index = strpos($url, '?');
        if ($index !== false) {
            $url = substr($url, 0, $index);
        }

        $matches = [];
        if (preg_match('/^(\/[_a-z0-9\/]*)?\/([_a-z\.0-9]*)(-(.*))?$/', $url, $matches) == 0) {
            throw new Exception('URL不正确.');
        }

        $dir = $matches[1];
        $fname = $matches[2];
        $args = [];
        if (isset($matches[4])) {
            $args = explode('-', $matches[4]);
        } else {
            $matches[4] = '';
        }

        return [$dir, $fname, $args, $matches[4]];
    }

    /**
     * 默认的路由函数.
     * @param string $url Url
     * @param string $namespace 处理器所属的名空间.
     * @param string $default_controler 默认的处理器
     */
    public function default_route($url, $namespace = '', $default_controler = '') {
        list($classname, $funcname, $args, $nouse) = self::path_info($url);
        $classname = str_replace('/', '\\', $classname);
        if ($classname == '') {
            $classname = '\\' . $default_controler;
        }
        if ($classname == '') {
            throw new Exception('未指定Controler.');
        }
        $classname = $namespace . ucwords($classname, '\\');
        if (!class_exists($classname)) {
            throw new Exception('Controler ' . $classname . '不存在.');
        }
        $controler = new $classname();
        if (!$controler instanceof Controler) {
            throw new Exception($classname . '不是一个Controler对象.');
        }
        $controler->dispatch($funcname, $args);
        return;
    }

}
