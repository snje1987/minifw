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

namespace Org\Snje\MinifwTest\Table;

use Org\Snje\Minifw as FW;

/**
 * Description of TableCreate
 *
 * @author Yang Ming <yangming0116@163.com>
 */
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
