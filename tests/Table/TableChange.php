<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableChange extends FW\Table {

    const TBNAME = 'table_with_all';

    protected function _prase($post, $type) {

    }

    const STATUS = [
        'engine' => 'MyISAM',
        'charset' => 'GBK',
        'comment' => 'Table To Change',
    ];
    const FIELD = [
        'id' => ['type' => 'int(10) unsigned', 'comment' => 'ID'],
        'intfield' => ['type' => 'int(10) unsigned', 'comment' => 'A int field'],
        'charfield' => ['type' => 'varchar(100)', 'default' => '#', 'comment' => 'A varchar field'],
        'textfield' => ['type' => 'text', 'comment' => 'A text field change'],
        'intfield_def' => ['type' => 'int(11)', 'comment' => 'A int field'],
        'charfield_def' => ['type' => 'int(11)', 'default' => '0', 'comment' => 'A varchar field'],
    ];
    const INDEX = [
        'PRIMARY' => ['fields' => ['intfield']],
        'intfield' => ['fields' => ['charfield']],
        'charfield' => ['fields' => ['intfield', 'charfield']],
        'uniqueindex' => ['fields' => ['intfield']]
    ];

}
