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
 * @filename Mysqli.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @copyright Copyright (C) 2013 杨明
 * @datetime 2013-4-8 13:35:37
 * @version 1.0
 * @Description 定义一些数据表的公共操作
 */

namespace Org\Snje\Minifw;
use Org\Snje\Minifw as Minifw;
use Zend\Json\Json as Json;

/**
 * 定义数据表的基本操作
 */
abstract class Table{

    /**
     * @var string 数据表的名称
     */
    public $_tbname = '';

    /**
     * @var Minifw\DB 数据库的实例
     */
    protected $_db;

    /**
     * 启用事务处理，执行指定的方法
     *
     * @param mixed $post 方法的参数
     * @param string $call 调用的方法
     */
    public function sync_call($post, $call){
        $this->_db->begin();
        try{
            $ret = $this->$call($post);
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
                $this->_db->commit();
                die(Json::encode($res));
            }
            elseif($ret == true){
                $this->_db->commit();
                die(Json::encode([
                    'succeed' => true,
                    'returl' => urldecode($post['returl']),
                ]));
            }
            else{
                $this->_db->rollback();
                die(Json::encode(['succeed' => false, 'msg' => '操作失败']));
            }
        }catch(Minifw\Exception $e){
            $this->_db->rollback();
            die(Json::encode(['succeed' => false, 'msg' => $e->getMessage()]));
        }catch(\Exception $e){
            $this->_db->rollback();
            if(DEBUG){
                throw $e;
            }else{
                die(Json::encode(['succeed' => false, 'msg' => '操作失败']));
            }
        }
    }

    /**
     * 使用Ajax方式调用指定方法
     *
     * @param mixed $post 方法的参数
     * @param string $call 调用的方法
     */
    public function json_call($post, $call){
        try{
            $ret = $this->$call($post);
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

    /**
     * 根据条件计算数据的条数
     *
     * @param array $condition 计算的条件
     * @return int 数据的数量
     */
    public function count($condition = []){
        return $this->_db->count($this->_tbname, $condition);
    }

    /**
     * 在表中插入一条数据
     *
     * @param array $post 要插入的数据
     * @return bool 成功返回true，失败返回false
     */
    public function add($post){
        $data = $this->_prase($post, 1);
        return $this->_db->insert($this->_tbname, $data);
    }

    /**
     * 修改表中的一条数据
     *
     * @param array $post 修改的条件和数值
     * @return bool 成功返回true，否则返回false
     */
    public function edit($post){
        $data = $this->_prase($post, 2);
        $condition = [];
        $condition['id'] = intval($post['id']);
        return $this->_db->update($this->_tbname, $data, $condition);
    }

    /**
     * 根据id修改表中指定字段的值
     *
     * @param int $id 要修改的数据的id
     * @param string $field 要修改的字段
     * @param mixed $value 要修改成的值
     * @return bool 成功返回true，否则返回false
     */
    public function set_field($id, $field, $value){
        $condition = [];
        $condition['id'] = intval($id);
        $data = [];
        $data[strval($field)] = $value;
        return $this->_db->update($this->_tbname, $data, $condition);
    }

    /**
     * 删除指定id的数据
     *
     * @param int $id 要删除数据的id
     * @return bool 成功返回ture，否则返回fasle
     */
    public function del($id){
        $condition = [
            'id' => intval($id)
        ];
        return $this->_db->delete($this->_tbname, $condition);
    }

    /**
     * 根据id获取指定的数据
     *
     * @param int $id 要获取的数据的id
     * @return array 具有指定id的数据
     */
    public function get_by_id($id){
        $condition = [];
        $condition['id'] = intval($id);
        return $this->_db->one_query($this->_tbname, $condition);
    }

    /**
     * 根据条件获取一条数据
     *
     * @param array $condition 查询的条件
     * @param array $field 查询的字段
     * @return array 要查询的数据
     */
    public function get_one($condition, $field = []){
        return $this->_db->one_query($this->_tbname, $condition, $field);
    }

    /**
     * 根据指定字段的值获取一条数据
     *
     * @param string $field 指定的字段
     * @param string $value 指定的值
     * @return array 查询的结果
     */
    public function get_by_field($field, $value){
        $field = strval($field);
        $value = strval($value);
        $condition = [];
        $condition[$field] = $value;
        return $this->_db->one_query($this->_tbname, $condition);
    }

    /**
     * 根据指定字段的值获取符合条件的数据
     *
     * @param string $field 指定的字段
     * @param string $value 指定的值
     * @return array 查询的结果
     */
    public function gets_by_field($field, $value){
        $field = strval($field);
        $value = strval($value);
        $condition = [];
        $condition[$field] = $value;
        return $this->_db->limit_query($this->_tbname, $condition);
    }

    /**
     * 根据条件获取符合条件的数据
     *
     * @param array $condition 查询的条件
     * @param array $field 查询的字段
     * @return array 查询的结果
     */
    public function gets_by_condition($condition = [], $field = []){
        return $this->_db->limit_query($this->_tbname, $condition, $field);
    }

    /**
     * 根据sql语句获取符合条件的的数据
     *
     * @param string $sql sql语句
     * @return array 查询的结果
     */
    public function gets_by_query($sql){
        return $this->_db->get_query($sql);
    }

    /**
     * 执行sql语句
     *
     * @param string $sql sql语句
     * @return mixed 查询的结果
     */
    public function query($sql){
        return $this->_db->query($sql);
    }

    /**
     * 开启事务
     */
    public function begin(){
        $this->_db->begin();
    }

    /**
     * 提交事务
     */
    public function commit(){
        $this->_db->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback(){
        $this->_db->rollback();
    }

    public function drop(){
        $this->_db->query('drop table if exists `' . $this->_tbname . '`');
    }

    abstract public function create($args = []);

    /*     * ******************************************************* */

    /**
     * 私有构造函数
     */
    protected function __construct($args = []){

    }

    /**
     * 虚函数，用于处理用户的输入，生成执行插入和修改的数据值
     */
    abstract protected function _prase($post, $type);
}