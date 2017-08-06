<?php

namespace Org\Snje\Minifw\Table;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Table;
use Org\Snje\Minifw\Exception;

class Article extends Table {

    const TBNAME = DBPREFIX . 'article';

    public function del($args) {
        $id = intval($args[0]);
        $old = $this->get_by_id($id);
        if ($old['path'] != '') {
            FW\File::delete($old['path']);
        }
        if ($old['image'] != '') {
            FW\File::delete_img($old['image']);
        }

        $attachment_obj = Attachment::get();
        $adata = $attachment_obj->get_attachment('article', $id);
        foreach ($adata as $v) {
            $attachment_obj->del($v['id']);
        }

        return parent::del($id);
    }

    public function update_click($id) {
        $id = intval($id);
        $sql = 'update `' . static::TBNAME . '` set `click` = `click` + 1 where `id` = "' . $id . '"';
        $this->db->query($sql);
    }

    ////////////////////////////////////////////////////////////////////

    protected function __construct() {
        parent::__construct();
    }

    protected function _prase($post, $type) {
        $value = [];
        $value['time'] = strtotime(strval($post['time']));
        if ($value['time'] <= 864000) {
            $value['time'] = time();
        }
        $value['click'] = intval($post['click']);
        $value['title'] = trim(strval($post['title']));
        $value['kws'] = trim(strval($post['kws']));
        $value['desc'] = trim(strval($post['desc']));
        if ($value['title'] == '') {
            throw new Exception('标题不能为空');
        }
        $value['group_id'] = Group::pickup($post['group_id']);
        $value['sort_order'] = intval($post['sort_order']);
        if ($value['group_id'] == 0) {
            throw new Exception('必须选择分组');
        }
        $content = '';
        if ($type == 2) {
            $content = strval($post['content']);
        }
        if ($value['desc'] == '') {
            $value['desc'] = FW\Text::sub_text($content, 255);
        }
        $path = FW\File::save_str($content, 'article', 'html');
        if ($path != '') {
            $value['path'] = $path;
        } else {
            throw new Exception('保存失败');
        }
        if (isset($_FILES['image'])) {
            $path1 = FW\File::upload_file($_FILES['image'], 'article');
            if ($path1 != '') {
                $value['image'] = $path1;
            }
        }
        if ($type == 1 && !isset($value['image'])) {
            throw new Exception('未选择图片');
        }
        if ($type == 2) {
            $old = $this->get_by_id($post['id']);
            if ($value['path'] != '' && $old['path'] != '') {
                FW\File::delete($old['path']);
            }
            if (isset($value['image']) && $value['image'] != '' && $old['image'] != '') {
                FW\File::delete_img($old['image']);
            }
        }
        return $value;
    }

    const STATUS = [
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => '文章',
    ];
    const FIELD = [
        'id' => ['type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID',],
        'group_id' => ['type' => 'int(10) unsigned', 'comment' => '分组ID',],
        'sort_order' => ['type' => 'int(11)', 'default' => '0', 'comment' => '排序',],
        'title' => ['type' => 'varchar(255)', 'comment' => '标题',],
        'path' => ['type' => 'varchar(255)', 'comment' => '保存路径',],
        'image' => ['type' => 'varchar(255)', 'comment' => '图片路径',],
        'time' => ['type' => 'int(11)', 'comment' => '添加时间',],
        'kws' => ['type' => 'varchar(255)', 'default' => '', 'comment' => '关键词',],
        'desc' => ['type' => 'varchar(255)', 'default' => '', 'comment' => '描述',],
        'click' => ['type' => 'int(11)', 'default' => '0', 'comment' => '点击次数',],
    ];
    const INDEX = [
        'PRIMARY' => ['fields' => ['id']],
        'group_id' => ['fields' => ['group_id']],
        'sort_order' => ['fields' => ['sort_order']],
    ];

}
