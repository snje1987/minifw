<?php

/*
 * Copyright (C) 2016 Yang Ming <yangming0116@163.com>
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
 * @filename Test.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@163.com>
 * @datetime 2016-2-27  23:28:47
 * @Description
 */

namespace Org\Snje\MinifwTest\JsonCall;

class Functions {

    public function __construct() {

    }

    public static function static_func($args) {
        return $args;
    }

    public static function static_except($args) {
        throw new \Org\Snje\Minifw\Exception($args);
    }

    public function func($args) {
        return $args;
    }

    public function func_except($args) {
        throw new \Org\Snje\Minifw\Exception($args);
    }

}
