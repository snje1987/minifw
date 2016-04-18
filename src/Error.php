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
 * @filename Error.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @copyright Copyright (C) 2013 杨明
 * @datetime 2013-3-11 16:44:27
 * @version 1.0
 * @Description 进行错误处理
 */

namespace Org\Snje\Minifw;

/**
 * 用于错误处理的辅助方法
 */
class Error {

    /**
     * 用于处理普通的错误信息
     *
     * @param int $number 错误号
     * @param string $message 错误信息
     * @param string $file 出错的文件
     * @param int $line 出错的行号
     */
    public static function captureNormal($number, $message, $file, $line) {
        if (DEBUG == 1) {
            $error = ['type' => $number, 'message' => $message, 'file' => $file, 'line' => $line];
            echo '<pre>';
            print_r($error);
            echo '</pre>';
        }
    }

    /**
     * 用于处理未捕获的异常
     *
     * @param \Exception $exception 要处理的异常
     */
    public static function captureException($exception) {
        if (DEBUG == 1) {
            echo '<pre>';
            print_r($exception);
            echo '</pre>';
        } else {
            echo 'Runtime Error';
        }
    }

    /**
     * 处理程序的异常停止
     */
    public static function captureShutdown() {
        $error = error_get_last();
        if ($error) {
            if (DEBUG == 1) {
                echo '<pre>';
                print_r($error);
                echo '</pre>';
            } else {
                echo 'Runtime Error';
            }
        } else {
            return true;
        }
    }

}
