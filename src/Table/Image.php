<?php

namespace Org\Snje\Minifw\Table;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Table;
use Org\Snje\Minifw\Exception;

class Image extends Table {

    public static $tbname = DBPREFIX . 'image';

    public function get_image($name) {
        $group_obj = Group::get();
        $hash = $group_obj->hash_all('image', 'name', 'id');
        if (!isset($hash[$name])) {
            return array();
        }
        $condition = array();
        $condition['group_id'] = $group_obj->make_sub_condition($hash[$name]);
        $condition['order'] = '`sort_order` desc, `id` desc';
        return $this->gets_by_condition($condition, array('id', 'link', 'image'));
    }

    public function del($args) {
        $id = intval($args[0]);
        $old = $this->get_by_id($id);
        if ($old['image'] != '') {
            FW\File::delete_img($old['image']);
        }
        return parent::del($id);
    }

    protected function __construct() {
        parent::__construct();
    }

    protected function _prase($post, $type) {
        $value = array();
        $value['link'] = trim(strval($post['link']));
        if ($value['link'] == '') {
            $value['link'] = 'javascript:void(0);';
        }
        $value['group_id'] = Group::pickup($post['group_id']);
        $value['sort_order'] = intval($post['sort_order']);
        if ($value['group_id'] == 0) {
            throw new Exception('必须选择分组');
        }
        if (isset($_FILES['image'])) {
            $path1 = FW\File::upload_file($_FILES['image'], 'article');
            if ($path1 != '') {
                $value['image'] = $path1;
            }
        }
        if ($type == 2) {
            $old = $this->get_by_id($post['id']);
            if ((isset($value['image']) && $value['image'] != '') && $old['image'] != '') {
                FW\File::delete_img($old['image']);
            }
        }
        return $value;
    }

    public static $status = array(
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => '网站图片',
    );
    public static $field = array(
        'id' => array('type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID',),
        'group_id' => array('type' => 'int(10) unsigned', 'comment' => '分组ID',),
        'sort_order' => array('type' => 'int(11)', 'default' => '0', 'comment' => '排序',),
        'link' => array('type' => 'varchar(255)', 'comment' => '链接地址',),
        'image' => array('type' => 'varchar(255)', 'comment' => '图片路径',),
    );
    public static $index = array(
        'PRIMARY' => array('fields' => array('id')),
        'group_id' => array('fields' => array('group_id')),
        'sort_order' => array('fields' => array('sort_order')),
    );

}
