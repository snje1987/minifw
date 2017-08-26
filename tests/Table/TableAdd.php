<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableAdd extends FW\Table {

    public static $tbname = 'table_with_one';

    protected function _prase($post, $type) {

    }

    public static $status = array(
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => 'Table To Create',
    );
    public static $field = array(
        'id' => array('type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID'),
        'intfield' => array('type' => 'int(11)', 'comment' => 'A int field'),
        'charfield' => array('type' => 'varchar(200)', 'comment' => 'A varchar field'),
        'textfield' => array('type' => 'text', 'comment' => 'A text field'),
        'addfield' => array('type' => 'text', 'comment' => 'A add field'),
        'intfield_def' => array('type' => 'int(11)', 'default' => '0', 'comment' => 'A int field'),
        'charfield_def' => array('type' => 'varchar(200)', 'default' => '', 'comment' => 'A varchar field'),
    );
    public static $index = array(
        'PRIMARY' => array('fields' => array('id')),
        'intfield' => array('fields' => array('intfield', 'charfield')),
        'uniqueindex' => array('unique' => true, 'fields' => array('intfield')),
        'addfield' => array('fields' => array('charfield')),
    );

}
