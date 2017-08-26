<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableChange extends FW\Table {

    public static $tbname = 'table_with_all';

    protected function _prase($post, $type) {

    }

    public static $status = array(
        'engine' => 'MyISAM',
        'charset' => 'GBK',
        'comment' => 'Table To Change',
    );
    public static $field = array(
        'id' => array('type' => 'int(10) unsigned', 'comment' => 'ID'),
        'intfield' => array('type' => 'int(10) unsigned', 'comment' => 'A int field'),
        'charfield' => array('type' => 'varchar(100)', 'default' => '#', 'comment' => 'A varchar field'),
        'textfield' => array('type' => 'text', 'comment' => 'A text field change'),
        'intfield_def' => array('type' => 'int(11)', 'comment' => 'A int field'),
        'charfield_def' => array('type' => 'int(11)', 'default' => '0', 'comment' => 'A varchar field'),
    );
    public static $index = array(
        'PRIMARY' => array('fields' => array('intfield')),
        'intfield' => array('fields' => array('charfield')),
        'charfield' => array('fields' => array('intfield', 'charfield')),
        'uniqueindex' => array('fields' => array('intfield'))
    );

}
