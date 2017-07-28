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

namespace Org\Snje\Minifw\Traits;

use Org\Snje\Minifw as FW;

/**
 * This class can have only one instance
 */
trait OneInstance {

    /**
     * @var static the instance
     */
    protected static $_instance = [];

    /**
     * get instance,create if not exists
     * @param mixed $args args pass to construct
     * @return static the instance
     */
    public static function get($args = []) {
        if (!isset(self::$_instance[static::class])) {
            self::$_instance[static::class] = new static($args);
        }
        return self::$_instance[static::class];
    }

    /**
     * delete the old instance and get a new one
     * @param mixed $args args pass to construct
     * @return static the instance
     */
    public static function get_new($args = []) {
        if (isset(self::$_instance[static::class])) {
            unset(self::$_instance[static::class]);
        }
        return self::get($args);
    }

}
