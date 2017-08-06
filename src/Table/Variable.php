<?php

namespace Org\Snje\Minifw\Table;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Table;
use Org\Snje\Minifw\Exception;

class Variable extends Table {

    const TBNAME = DBPREFIX . 'variable';
    const VARI_TYPES = [
        'system' => [
            'site_name' => ['show' => '网站名称', 'type' => 'text',],
            'site_url' => ['show' => '网站网址', 'type' => 'text',],
            'site_tail' => ['show' => '名称后缀', 'type' => 'text',],
            'site_kws' => ['show' => '关键词', 'type' => 'text',],
            'site_desc' => ['show' => '描述', 'type' => 'text',],
            'tongji' => ['show' => '流量统计代码', 'type' => 'script',],
            'footer_info' => ['show' => '底部信息', 'id' => 1, 'type' => 'richtext',],
        ],
        'contract' => [
            'tel' => ['show' => '联系电话', 'type' => 'text',],
        ],
    ];

    public static function value($arr, $key) {
        if (isset($arr[$key])) {
            return $arr[$key];
        } else {
            return '';
        }
    }

    public function get_varis($type) {
        $condition = [];
        $condition['type'] = strval($type);
        $condition['order'] = '`sort_order` desc, `id` desc';
        $datas = $this->db->limit_query(static::TBNAME, $condition);
        $res = [];
        foreach ($datas as $one) {
            $res[$one['name']] = $one['value'];
        }
        return $res;
    }

    public function set_varis($post) {
        $type = strval($post['type']);

        $vars = [];
        if (array_key_exists($type, static::VARI_TYPES)) {
            $vars = static::VARI_TYPES[$type];
        }

        foreach ($vars as $v) {
            if (isset($post[$v['name']])) {
                $condition = [];
                $condition['type'] = $type;
                $condition['name'] = $v['name'];
                $count = $this->db->count(static::TBNAME, $condition);
                $value = [];
                if ($count == 0) {
                    $value = $condition;
                }
                if ($v['type'] == 'text') {
                    $value['value'] = strval($post[$v['name']]);
                } else if ($v['type'] == 'int') {
                    $value['value'] = intval($post[$v['name']]);
                } else {
                    $value['value'] = ['rich', \Org\Snje\Minifw\Text::strip_html(strval($post[$v['name']]))];
                }

                if ($count != 0) {
                    $this->db->update(static::TBNAME, $value, $condition);
                } else {
                    $this->db->insert(static::TBNAME, $value);
                }
            }
        }
        return true;
    }

    protected function __construct() {
        parent::__construct();
    }

    protected function _prase($post, $type) {
        $value = [];
        $value['name'] = strval($post['name']);
        $value['value'] = strval($post['value']);
        $value['type'] = strval($post['type']);
        $value['sort_order'] = intval($post['sort_order']);
        return $value;
    }

    const STATUS = [
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => '变量',
    ];
    const FIELD = [
        'id' => ['type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID',],
        'name' => ['type' => 'varchar(50)', 'comment' => '名称',],
        'value' => ['type' => 'text', 'comment' => '值',],
        'type' => ['type' => 'varchar(40)', 'comment' => '类型',],
        'sort_order' => ['type' => 'int(11)', 'default' => '0', 'comment' => '排序',],
    ];
    const INDEX = [
        'PRIMARY' => ['fields' => ['id']],
        'name' => ['unique' => true, 'fields' => ['name', 'type']],
    ];

}
