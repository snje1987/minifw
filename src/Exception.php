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
 * @filename Exception.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @copyright Copyright (C) 2013 杨明
 * @datetime 2013-3-29 11:41:39
 * @version 1.0
 * @Description 异常类
 */

namespace Org\Snje\Minifw;

/**
 * 自定义的异常类，只在本程序内抛出和捕获
 */
class Exception extends \Exception {

    /**
     *
     * @param mixed $message 错误消息，如果是数组或对象，则会使用print_r转换
     * @param int $code 错误码
     * @param \Exception $previous 触发者
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null) {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        parent::__construct($message, $code, $previous);
    }

}
