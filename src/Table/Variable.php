<?php

namespace Org\Snje\Minifw\Table;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Table;
use Org\Snje\Minifw\Exception;

class Variable extends Table {

    public static $tbname = DBPREFIX . 'variable';
    public static $vari_types = array(
        'system' => array(
            'site_name' => array('show' => '网站名称', 'type' => 'text',),
            'site_url' => array('show' => '网站网址', 'type' => 'text',),
            'site_tail' => array('show' => '名称后缀', 'type' => 'text',),
            'site_kws' => array('show' => '关键词', 'type' => 'text',),
            'site_desc' => array('show' => '描述', 'type' => 'text',),
            'tongji' => array('show' => '流量统计代码', 'type' => 'script',),
            'footer_info' => array('show' => '底部信息', 'id' => 1, 'type' => 'richtext',),
        ),
        'contract' => array(
            'tel' => array('show' => '联系电话', 'type' => 'text',),
        ),
    );

    public static function value($arr, $key) {
        if (isset($arr[$key])) {
            return $arr[$key];
        } else {
            return '';
        }
    }

    public function get_varis($type) {
        $condition = array();
        $condition['type'] = strval($type);
        $condition['order'] = '`sort_order` desc, `id` desc';
        $datas = $this->db->limit_query(static::$tbname, $condition);
        $res = array();
        foreach ($datas as $one) {
            $res[$one['name']] = $one['value'];
        }
        return $res;
    }

    public function set_varis($post) {
        $type = strval($post['type']);

        $vars = array();
        if (array_key_exists($type, static::$vari_types)) {
            $vars = static::$vari_types[$type];
        }

        foreach ($vars as $v) {
            if (isset($post[$v['name']])) {
                $condition = array();
                $condition['type'] = $type;
                $condition['name'] = $v['name'];
                $count = $this->db->count(static::$tbname, $condition);
                $value = array();
                if ($count == 0) {
                    $value = $condition;
                }
                if ($v['type'] == 'text') {
                    $value['value'] = strval($post[$v['name']]);
                } else if ($v['type'] == 'int') {
                    $value['value'] = intval($post[$v['name']]);
                } else {
                    $value['value'] = array('rich', \Org\Snje\Minifw\Text::strip_html(strval($post[$v['name']])));
                }

                if ($count != 0) {
                    $this->db->update(static::$tbname, $value, $condition);
                } else {
                    $this->db->insert(static::$tbname, $value);
                }
            }
        }
        return true;
    }

    protected function __construct() {
        parent::__construct();
    }

    protected function _prase($post, $type) {
        $value = array();
        $value['name'] = strval($post['name']);
        $value['value'] = strval($post['value']);
        $value['type'] = strval($post['type']);
        $value['sort_order'] = intval($post['sort_order']);
        return $value;
    }

    public static $status = array(
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => '变量',
    );
    public static $field = array(
        'id' => array('type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID',),
        'name' => array('type' => 'varchar(50)', 'comment' => '名称',),
        'value' => array('type' => 'text', 'comment' => '值',),
        'type' => array('type' => 'varchar(40)', 'comment' => '类型',),
        'sort_order' => array('type' => 'int(11)', 'default' => '0', 'comment' => '排序',),
    );
    public static $index = array(
        'PRIMARY' => array('fields' => array('id')),
        'name' => array('unique' => true, 'fields' => array('name', 'type')),
    );

}
