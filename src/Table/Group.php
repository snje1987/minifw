<?php

namespace Org\Snje\Minifw\Table;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Table;
use Org\Snje\Minifw\Exception;

class Group extends Table {

    const TBNAME = DBPREFIX . 'group';

    protected $cache = [];

    public static function pickup($arr) {
        $arr2 = array_reverse($arr);
        foreach ($arr2 as $val) {
            if ($val != '' && $val != 0) {
                return $val;
            }
        }
        return '';
    }

    public static function get_parent_id($data) {
        return intval(preg_replace('/^(.*@)?(\d*)@$/', '$2', $data['group_id']));
    }

    public function add($post) {
        $value = [];
        $value['type'] = strval($post['type']);
        $value['name'] = strval($post['name']);
        $value['attr'] = strval($post['attr']);
        $value['sort_order'] = intval($post['sort_order']);
        $parent = $this->get_by_id(intval($post['group_id']));
        $value['group_id'] = $parent['group_id'] . $parent['id'] . '@';
        return $this->db->insert(static::TBNAME, $value);
    }

    public function edit($post) {
        $id = intval($post['id']);
        $condition = [];
        $condition['id'] = $id;
        $value = [];
        $value['name'] = strval($post['name']);
        $value['attr'] = strval($post['attr']);
        $value['sort_order'] = intval($post['sort_order']);
        return $this->db->update(static::TBNAME, $value, $condition);
    }

    public function del($args) {
        $id = intval($args[0]);
        $condition = [];
        $condition['group_id'] = ['have', '@' . $id . '@'];
        $data = $this->gets_by_condition($condition);
        if (!empty($data)) {
            throw new Exception('子项目不为空');
        }
        $condition = [
            'id' => intval($id)
        ];
        return $this->db->delete(static::TBNAME, $condition);
    }

    public function get_siblings($id, $type, $order = '`sort_order` desc,`id` desc', $fields = []) {
        $condition = [];
        $id = intval($id);
        if ($type != '') {
            $condition['type'] = strval($type);
        }
        if ($id == 0) {
            return [];
        } else {
            $data = $this->get_by_id($id);
            if (empty($data)) {
                return [];
            }
            $condition['group_id'] = $data['group_id'];
        }
        $condition['order'] = $order;
        return $this->db->limit_query(static::TBNAME, $condition, $fields);
    }

    public function get_children($id, $type, $order = '`sort_order` desc,`id` desc', $fields = []) {
        $condition = [];
        $id = intval($id);
        if ($type != '') {
            $condition['type'] = strval($type);
        }
        if ($id == 0) {
            $condition['group_id'] = '@';
        } else {
            $data = $this->get_by_id($id);
            if (empty($data)) {
                return [];
            }
            $condition['group_id'] = $data['group_id'] . $data['id'] . '@';
        }
        $condition['order'] = $order;
        return $this->db->limit_query(static::TBNAME, $condition, $fields);
    }

    public function hash_all($type, $key = 'id', $val = 'name') {
        $condition = [];
        if ($type != '') {
            $condition['type'] = strval($type);
        }
        $condition['order'] = '`sort_order` desc,`id` desc';
        $data = $this->db->limit_query(static::TBNAME, $condition);
        $hash = [];
        foreach ($data as $v) {
            $hash[$v[$key]] = $v[$val];
        }
        return $hash;
    }

    public function name_path($data, $sep = '/') {
        if (!is_array($data)) {
            $id = intval($data);
            $data = $this->get_by_id($id);
        }
        $arr = explode('@', $data['group_id'] . $data['id'] . '@');
        $str = '';
        $first = true;
        if (!isset($this->cache[$data['type']])) {
            $this->cache[$data['type']] = $this->hash_all($data['type']);
        }
        foreach ($arr as $one) {
            if ($one == '') {
                continue;
            }
            if ($first == true) {
                $first = false;
                $str .= $this->cache[$data['type']][$one];
            } else {
                $str .= $sep . $this->cache[$data['type']][$one];
            }
        }
        return $str;
    }

    public function array_path($id, $all = false) {
        $id = intval($id);
        $data = $this->get_by_id($id);
        $arr = explode('@', $data['group_id'] . $data['id'] . '@');
        $res = [];
        if (!$all && !isset($this->cache[$data['type']])) {
            $this->cache[$data['type']] = $this->hash_all($data['type']);
        }
        foreach ($arr as $one) {
            if ($one == '') {
                continue;
            }
            if ($all) {
                $res[$one] = $this->get_by_id($one);
            } else {
                $res[$one] = $this->cache[$data['type']][$one];
            }
        }
        return $res;
    }

    public function make_sub_condition($id) {
        if ($id == 0) {
            return false;
        }
        $group_data = $this->get_by_id($id);
        if (!empty($group_data)) {
            $condition2 = array();
            $condition2['id'][] = array('=', $id);
            $condition2['group_id'][] = array('or', array('begin', $group_data['group_id'] . $group_data['id'] . '@'));
        } else {
            return false;
        }
        return array('in', static::TBNAME, 'id', $condition2);
    }

    protected function __construct() {
        parent::__construct();
    }

    protected function _prase($post, $type) {
        throw new Exception('非法操作');
    }

    const STATUS = [
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => '无限级分组',
    ];
    const FIELD = [
        'id' => ['type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID',],
        'type' => ['type' => 'varchar(40)', 'comment' => '类型',],
        'group_id' => ['type' => 'varchar(255)', 'default' => '@', 'comment' => '父分组路径',],
        'sort_order' => ['type' => 'int(11)', 'default' => '0', 'comment' => '排序',],
        'name' => ['type' => 'varchar(255)', 'comment' => '名称',],
        'attr' => ['type' => 'varchar(40)', 'default' => '', 'comment' => '属性',],
    ];
    const INDEX = [
        'PRIMARY' => ['fields' => ['id']],
        'group_id' => ['fields' => ['group_id']],
        'sort_order' => ['fields' => ['sort_order']],
    ];

}
