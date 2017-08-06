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

namespace Org\Snje\MinifwTest\JsonCall;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

/**
 * Description of TableTest
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class TableTest extends Ts\TestCommon {

    public function test_sync_call() {
        $obj = Table::get();
        $db = $obj->get_db();
        $controler = new FW\Controler();
        $count = count(self::$input);
        for ($i = 0; $i < $count; $i++) {
            $ret = $controler->sync_call(
                    $db
                    , self::$input[$i]
                    , [$obj, 'func']
                    , FW\Controler::JSON_CALL_RETURN);
            $this->assertEquals(self::$expect[$i], $ret);
        }

        $ret = $controler->sync_call(
                $db
                , 'test msg'
                , [$obj, 'func_except']
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals([
            'succeed' => false,
            'returl' => '',
            'msg' => '[' . __DIR__ . '/Table.php:37]test msg',
                ], $ret);

        $ret = $controler->sync_call(
                $db
                , 'test msg'
                , [$obj, 'func_noexist']
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals([
            'succeed' => false,
            'returl' => '',
            'msg' => '操作失败',
                ], $ret);
    }

    public static $input = [
        false,
        true,
        [],
        [
            'returl' => 'testurl',
        ],
        [
            'msg' => 'testmsg',
        ],
        [
            'msg' => 'testmsg',
            'returl' => 'testurl',
        ]
    ];
    public static $expect = [
        [
            'succeed' => false,
            'msg' => '操作失败',
            'returl' => '',
        ],
        [
            'succeed' => true,
            'returl' => '',
        ],
        [
            'succeed' => true,
            'returl' => '',
        ],
        [
            'succeed' => true,
            'returl' => 'testurl',
        ],
        [
            'succeed' => true,
            'returl' => '',
            'msg' => 'testmsg',
        ],
        [
            'succeed' => true,
            'returl' => 'testurl',
            'msg' => 'testmsg',
        ],
    ];

}
