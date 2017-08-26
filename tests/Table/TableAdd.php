<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableAdd extends FW\Table {

    const TBNAME = 'table_with_one';

    protected function _prase($post, $type) {

    }

    const STATUS = [
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => 'Table To Create',
    ];
    const FIELD = [
        'id' => ['type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID'],
        'intfield' => ['type' => 'int(11)', 'comment' => 'A int field'],
        'charfield' => ['type' => 'varchar(200)', 'comment' => 'A varchar field'],
        'textfield' => ['type' => 'text', 'comment' => 'A text field'],
        'addfield' => ['type' => 'text', 'comment' => 'A add field'],
        'intfield_def' => ['type' => 'int(11)', 'default' => '0', 'comment' => 'A int field'],
        'charfield_def' => ['type' => 'varchar(200)', 'default' => '', 'comment' => 'A varchar field'],
    ];
    const INDEX = [
        'PRIMARY' => ['fields' => ['id']],
        'intfield' => ['fields' => ['intfield', 'charfield']],
        'uniqueindex' => ['unique' => true, 'fields' => ['intfield']],
        'addfield' => ['fields' => ['charfield']],
    ];

}
