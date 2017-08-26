<?php

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

class TableDel extends FW\Table {

    const TBNAME = 'table_with_all';

    protected function _prase($post, $type) {

    }

    const STATUS = [
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'comment' => 'Table To Create',
    ];
    const FIELD = [
        'intfield' => ['type' => 'int(11)', 'comment' => 'A int field'],
    ];
    const INDEX = [
    ];

}
