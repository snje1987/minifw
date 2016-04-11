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

namespace Org\Snje\MinifwTest\Data\Table;

use Org\Snje\Minifw as FW;

/**
 * Description of TestTable
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class Table1 extends FW\Table {

    const ALLOW = ['func', 'func_except', 'func_noexist'];

    public function func($args) {
        return $args;
    }

    public function func_except($args) {
        throw new \Org\Snje\Minifw\Exception($args);
    }

    public function func_not_allow($args) {
        return $args;
    }

    protected function _prase($post, $type) {

    }

    public function create() {

    }

}
