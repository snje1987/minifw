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

namespace Org\Snje\Minifw;

/**
 * Description of Common
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class Common {

    /**
     * 使用Ajax方式调用指定方法
     *
     * @param mixed $post 方法的参数
     * @param mixed $call 调用的方法
     */
    public static function json_call($post, $call, $die = true) {
        $ret = [
            'succeed' => false,
            'returl' => '',
        ];
        try {
            $value = false;
            if (is_callable($call)) {
                $value = call_user_func($call, $post);
            }
            if (is_array($value)) {
                $ret['succeed'] = true;
                if (isset($value['returl'])) {
                    $ret['returl'] = $value['returl'];
                } elseif (isset($_POST['returl'])) {
                    $ret['returl'] = urldecode(strval($_POST['returl']));
                }
                if (isset($value['msg'])) {
                    $ret['msg'] = $value['msg'];
                }
            } elseif ($value == true) {
                $ret['succeed'] = true;
                if (isset($_POST['returl'])) {
                    $ret['returl'] = urldecode(strval($_POST['returl']));
                }
            } else {
                $ret['msg'] = '操作失败';
            }
        } catch (Minifw\Exception $e) {
            $ret['msg'] = $e->getMessage();
        } catch (\Exception $e) {
            if (DEBUG) {
                $ret['msg'] = $e->getMessage();
            } else {
                $ret['msg'] = '操作失败';
            }
        }
        if ($die) {
            die(\json_encode($ret, JSON_UNESCAPED_UNICODE));
        } else {
            echo \json_encode($ret, JSON_UNESCAPED_UNICODE);
            return $ret['succeed'];
        }
    }

}
