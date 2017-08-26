<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableMove extends FW\Table {

    public static $tbname = 'table_with_all';

    protected function _prase($post, $type) {

    }

    public static $status = array(
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => 'Table To Create',
    );
    public static $field = array(
        'charfield_def' => array('type' => 'varchar(200)', 'default' => '', 'comment' => 'A varchar field'),
        'intfield_def' => array('type' => 'int(11)', 'default' => '0', 'comment' => 'A int field'),
        'textfield' => array('type' => 'text', 'comment' => 'A text field'),
        'charfield' => array('type' => 'varchar(200)', 'comment' => 'A varchar field'),
        'intfield' => array('type' => 'int(11)', 'comment' => 'A int field'),
        'id' => array('type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID'),
    );
    public static $index = array(
        'PRIMARY' => array('fields' => array('id')),
        'charfield' => array('fields' => array('charfield')),
        'intfield' => array('fields' => array('intfield', 'charfield')),
        'uniqueindex' => array('unique' => true, 'fields' => array('intfield'))
    );

}
