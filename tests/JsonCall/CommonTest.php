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

    /**
     * @covers Org\Snje\Minifw\Common::json_call
     */
    public function test_json_call_static() {
        $obj = new Functions();
        $class = get_class($obj);
        $count = count(self::$input);
        for ($i = 0; $i < $count; $i++) {
            $ret = self::func_test($class . '::static_func', self::$input[$i]);
            $this->assertEquals(self::$expect[$i], $ret);
        }

        $ret = self::func_test($class . '::static_except', '测试消息');
        $this->assertEquals([
            'ret' => false,
            'output' => [
                'succeed' => false,
                'returl' => '',
                'msg' => '测试消息',
            ],
                ], $ret);

        //不存在的方法
        $ret = self::func_test($class . '::static_noexist', '测试消息');
        $this->assertEquals([
            'ret' => false,
            'output' => [
                'succeed' => false,
                'returl' => '',
                'msg' => '操作失败',
            ],
                ], $ret);
    }

    /**
     * @covers Org\Snje\Minifw\Common::json_call
     */
    public function test_json_call_func() {
        $obj = new Functions();
        $count = count(self::$input);
        for ($i = 0; $i < $count; $i++) {
            $ret = self::func_test([$obj, 'func'], self::$input[$i]);
            $this->assertEquals(self::$expect[$i], $ret);
        }

        $ret = self::func_test([$obj, 'func_except'], '测试消息');
        $this->assertEquals([
            'ret' => false,
            'output' => [
                'succeed' => false,
                'returl' => '',
                'msg' => '测试消息',
            ],
                ], $ret);

        //不存在的方法
        $ret = self::func_test([$obj, 'func_noexist'], '测试消息');
        $this->assertEquals([
            'ret' => false,
            'output' => [
                'succeed' => false,
                'returl' => '',
                'msg' => '操作失败',
            ],
                ], $ret);
    }

    public static function func_test($func, $args) {
        ob_start();
        $ret = FW\Common::json_call($args, $func, false);
        $output = ob_get_clean();
        return [
            'ret' => $ret,
            'output' => \json_decode($output, true),
        ];
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
            'ret' => false,
            'output' => [
                'succeed' => false,
                'msg' => '操作失败',
                'returl' => '',
            ],
        ],
        [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => '',
            ],
        ],
        [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => '',
            ],
        ],
        [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => 'testurl',
            ],
        ],
        [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => '',
                'msg' => 'testmsg',
            ],
        ],
        [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => 'testurl',
                'msg' => 'testmsg',
            ],
        ],
    ];

}
