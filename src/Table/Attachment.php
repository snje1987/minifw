<?php

namespace Org\Snje\Minifw\Table;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Table;
use Org\Snje\Minifw\Exception;

class Attachment extends Table {

    public static $tbname = DBPREFIX . 'attachment';

    public function getlist($args) {
        $type = strval($args[0]);
        $rid = intval($args[1]);

        $condition = array();
        $condition['type'] = $type;
        $condition['rid'] = $rid;

        $data = $this->gets_by_condition($condition, array());

        $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
        $file_list = array();
        foreach ($data as $v) {
            $file = array();
            $file['id'] = $v['id'];
            $file['is_die'] = false;
            $file['has_file'] = false;
            $file['dir_path'] = '';
            $file_ext = strtolower(pathinfo($v['path'], PATHINFO_EXTENSION));
            $file['is_photo'] = in_array($file_ext, $ext_arr);
            $file['filetype'] = $file_ext;
            $file['filename'] = $v['name']; //文件名，包含扩展名
            $file['fileUrl'] = $v['path'];
            if (file_exists(WEB_ROOT . $v['path'])) {
                $file['filesize'] = filesize(WEB_ROOT . $v['path']);
                $file['datetime'] = date('Y-m-d H:i:s', filemtime(WEB_ROOT . $v['path'])); //文件最后修改时间
            }
            $file_list[] = $file;
        }

        $result = array();
        $result['moveup_dir_path'] = '';
        $result['current_dir_path'] = '';
        $result['current_url'] = '';
        $result['total_count'] = count($file_list);
        $result['file_list'] = $file_list;
        return $result;
    }

    public function del($args) {
        $id = intval($args[0]);
        $old = $this->get_by_id($id);
        if ($old['path'] != '') {
            FW\File::delete($old['path']);
        }
        return parent::del($id);
    }

    public function edit($post) {
        throw new Exception('非法操作');
    }

    public function get_attachment($type, $rid) {
        $condition = array();
        $condition['type'] = strval($type);
        $condition['rid'] = intval($rid);
        $condition['order'] = '`id` desc';
        return $this->gets_by_condition($condition);
    }

    ////////////////////////////////////////////////////////////////////

    protected function __construct() {
        parent::__construct();
    }

    protected function _prase($post, $type) {

        if ($type == 2) {
            throw new Exception('非法操作');
        }

        $value = array();
        $value['rid'] = intval($post['rid']);
        if ($value['rid'] == 0) {
            throw new Exception('数据错误');
        }
        $value['type'] = strval($post['type']);
        if ($value['type'] == '') {
            throw new Exception('数据错误');
        }
        $value['name'] = trim(strval($_FILES['file']['name']));
        if ($value['name'] == '') {
            throw new Exception('名称不能为空');
        }
        $value['path'] = FW\File::upload_file($_FILES['file'], $value['type']);
        if ($value['path'] == '') {
            throw new Exception('上传失败');
        }
        return $value;
    }

    public static $status = array(
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => '附件',
    );
    public static $field = array(
        'id' => array('type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID',),
        'name' => array('type' => 'varchar(255)', 'comment' => '名称',),
        'type' => array('type' => 'varchar(255)', 'comment' => '类型',),
        'rid' => array('type' => 'int(10) unsigned', 'comment' => '关联ID',),
        'path' => array('type' => 'varchar(255)', 'comment' => '附件路径',),
    );
    public static $index = array(
        'PRIMARY' => array('fields' => array('id')),
        'type' => array('fields' => array('type')),
        'rid' => array('fields' => array('rid')),
    );

}
