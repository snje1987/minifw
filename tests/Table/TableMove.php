<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableMove extends FW\Table {

    const TBNAME = 'table_with_all';

    protected function _prase($post, $type) {

    }

    const STATUS = [
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => 'Table To Create',
    ];
    const FIELD = [
        'charfield_def' => ['type' => 'varchar(200)', 'default' => '', 'comment' => 'A varchar field'],
        'intfield_def' => ['type' => 'int(11)', 'default' => '0', 'comment' => 'A int field'],
        'textfield' => ['type' => 'text', 'comment' => 'A text field'],
        'charfield' => ['type' => 'varchar(200)', 'comment' => 'A varchar field'],
        'intfield' => ['type' => 'int(11)', 'comment' => 'A int field'],
        'id' => ['type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID'],
    ];
    const INDEX = [
        'PRIMARY' => ['fields' => ['id']],
        'charfield' => ['fields' => ['charfield']],
        'intfield' => ['fields' => ['intfield', 'charfield']],
        'uniqueindex' => ['unique' => true, 'fields' => ['intfield']]
    ];

}
