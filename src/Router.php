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
            throw new Exception('Url not correct.');
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
     * Default route function.
     * @param string $url Url
     * @param string $prefix Namespace of the controler.
     * @param string $die If true, shutdown after called the controler.
     */
    public function default_route($url, $prefix = '') {
        list($classname, $funcname, $args, $nouse) = self::path_info($url);
        $classname = str_replace('/', '\\', $classname);
        $classname = $prefix . ucwords($classname, '\\');
        if (!class_exists($classname)) {
            throw new Exception('Controler ' . $classname . ' don\'t exists.');
        }
        $controler = new $classname();
        if (!$controler instanceof Controler) {
            throw new Exception($classname . ' is not a Controler');
        }
        $controler->dispatch($funcname, $args);
        return;
    }

}
