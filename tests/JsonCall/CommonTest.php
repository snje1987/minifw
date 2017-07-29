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
 * Description of CommonTest
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class CommonTest extends Ts\TestCommon {

    public function test_json_call_static() {
        $obj = new Functions();
        $controler = new FW\Controler();
        $class = get_class($obj);
        $count = count(self::$input);
        for ($i = 0; $i < $count; $i++) {
            $ret = $controler->json_call(
                    self::$input[$i]
                    , $class . '::static_func'
                    , FW\Controler::JSON_CALL_RETURN);
            $this->assertEquals(self::$expect[$i], $ret);
        }
        $ret = $controler->json_call(
                'test msg'
                , $class . '::static_except'
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals([
            'succeed' => false,
            'returl' => '',
            'msg' => '[' . __DIR__ . '/Functions.php:41]test msg',
                ], $ret);

        $ret = $controler->json_call(
                'test msg'
                , $class . '::static_noexist'
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals([
            'succeed' => false,
            'returl' => '',
            'msg' => 'Action failed',
                ], $ret);
    }

    public function test_json_call_func() {
        $obj = new Functions();
        $controler = new FW\Controler();
        $count = count(self::$input);
        for ($i = 0; $i < $count; $i++) {
            $ret = $controler->json_call(
                    self::$input[$i]
                    , [$obj, 'func']
                    , FW\Controler::JSON_CALL_RETURN);
            $this->assertEquals(self::$expect[$i], $ret);
        }

        $ret = $controler->json_call(
                'test msg'
                , [$obj, 'func_except']
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals([
            'succeed' => false,
            'returl' => '',
            'msg' => '[' . __DIR__ . '/Functions.php:49]test msg',
                ], $ret);

        $ret = $controler->json_call(
                'test msg'
                , [$obj, 'func_noexist']
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals([
            'succeed' => false,
            'returl' => '',
            'msg' => 'Action failed',
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
            'msg' => 'Action failed',
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
