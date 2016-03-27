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

use Zend\Json\Json as Json;

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
     * @param string $call 调用的方法
     */
    public static function json_call($post, $call){
        try{
            $ret = call_user_func($call, $post);
            if(is_array($ret)){
                $res = [
                    'succeed' => true,
                    'returl' => urldecode($post['returl']),
                ];
                if(isset($ret['returl'])){
                    $res['returl'] = $ret['returl'];
                }
                if(isset($ret['msg'])){
                    $res['msg'] = $ret['msg'];
                }
                die(Json::encode($res));
            }
            elseif($ret == true){
                die(Json::encode([
                    'succeed' => true,
                    'returl' => urldecode($post['returl']),
                ]));
            }
            else{
                die(Json::encode(['succeed' => false, 'msg' => '操作失败']));
            }
        }catch(Minifw\Exception $e){
            die(Json::encode(['succeed' => false, 'msg' => $e->getMessage()]));
        }catch(\Exception $e){
            if(DEBUG){
                throw $e;
            }else{
                die(Json::encode(['succeed' => false, 'msg' => '操作失败']));
            }
        }
    }
}
