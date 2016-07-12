<?php

/*
 * Copyright (C) 2016 Yang Ming <yangming0116@163.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Org\Snje\MinifwTest\Data\Table\Diff;

use Org\Snje\Minifw as FW;

/**
 * Description of TableAdd
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class TableAdd extends FW\Table {

    const TBNAME = 'table_with_one';

    protected function _prase($post, $type) {

    }

    const COMMENT = 'Table To Create';
    const FIELDS = [
        'id' => ['type' => 'int(10) unsigned', 'extra' => 'auto_increment', 'comment' => 'ID'],
        'intfield' => ['type' => 'int(11)', 'comment' => 'A int field'],
        'charfield' => ['type' => 'varchar(200)', 'comment' => 'A varchar field'],
        'textfield' => ['type' => 'text', 'comment' => 'A text field'],
        'addfield' => ['type' => 'text', 'comment' => 'A add field'],
        'intfield_def' => ['type' => 'int(11)', 'default' => '0', 'comment' => 'A int field'],
        'charfield_def' => ['type' => 'varchar(200)', 'default' => '', 'comment' => 'A varchar field'],
    ];
    const INDEXS = [
        'PRIMARY' => ['fields' => ['id']],
        'intfield' => ['fields' => ['intfield', 'charfield']],
        'uniqueindex' => ['unique' => true, 'fields' => ['intfield']],
        'addfield' => ['fields' => ['charfield']],
    ];

}
