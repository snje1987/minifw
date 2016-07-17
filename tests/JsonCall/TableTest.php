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

    /**
     * @covers Org\Snje\Minifw\Table::sync_call
     */
    public function test_sync_call() {
        $obj = Table::get();

        $count = count(self::$input);
        for ($i = 0; $i < $count; $i++) {
            $ret = self::sync_call_test($obj, 'func', self::$input[$i]);
            $this->assertEquals(self::$expect[$i], $ret);
        }

        $ret = self::sync_call_test($obj, 'func_except', '测试消息');
        $this->assertEquals([
            'succeed' => false,
            'returl' => '',
            'msg' => '测试消息',
                ], $ret);

        $ret = self::sync_call_test($obj, 'func_noexist', '测试消息');
        $this->assertEquals([
            'succeed' => false,
            'returl' => '',
            'msg' => '操作失败',
                ], $ret);
    }

    /**
     * @covers Org\Snje\Minifw\Table::json_call
     */
    public function test_json_call() {
        $obj = Table::get();

        $count = count(self::$input);
        for ($i = 0; $i < $count; $i++) {
            $ret = self::json_call_test($obj, 'func', self::$input[$i]);
            $this->assertEquals(self::$expect[$i], $ret);
        }

        $ret = self::json_call_test($obj, 'func_except', '测试消息');
        $this->assertEquals([
            'succeed' => false,
            'returl' => '',
            'msg' => '测试消息',
                ], $ret);

        $ret = self::json_call_test($obj, 'func_noexist', '测试消息');
        $this->assertEquals([
            'succeed' => false,
            'returl' => '',
            'msg' => '操作失败',
                ], $ret);
    }

    public static function json_call_test($obj, $func, $args) {
        ob_start();
        $obj->json_call($args, $func, false);
        $output = ob_get_clean();
        return \json_decode($output, true);
    }

    public static function sync_call_test($obj, $func, $args) {
        ob_start();
        $obj->sync_call($args, $func, false);
        $output = ob_get_clean();
        return \json_decode($output, true);
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
