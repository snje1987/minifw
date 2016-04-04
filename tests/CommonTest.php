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

namespace Test;

use Org\Snje\Minifw as FW;

/**
 * Description of CommonTest
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class CommonTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        if (!defined("DEBUG")) {
            define('DEBUG', 1);
        }
    }

    public function test_json_call_static() {
        $ret = self::funcTest(__NAMESPACE__ . '\TestFunction::static_func', false);
        $this->assertEquals($ret, [
            'ret' => false,
            'output' => [
                'succeed' => false,
                'msg' => '操作失败',
                'returl' => '',
            ],
        ]);

        $ret = self::funcTest(__NAMESPACE__ . '\TestFunction::static_func', true);
        $this->assertEquals($ret, [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => '',
            ],
        ]);

        $ret = self::funcTest(__NAMESPACE__ . '\TestFunction::static_func', []);
        $this->assertEquals($ret, [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => '',
            ],
        ]);

        $ret = self::funcTest(__NAMESPACE__ . '\TestFunction::static_func', [
                    'returl' => 'testurl',
        ]);
        $this->assertEquals($ret, [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => 'testurl',
            ],
        ]);

        $ret = self::funcTest(__NAMESPACE__ . '\TestFunction::static_func', [
                    'msg' => 'testmsg',
        ]);
        $this->assertEquals($ret, [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => '',
                'msg' => 'testmsg',
            ],
        ]);

        $ret = self::funcTest(__NAMESPACE__ . '\TestFunction::static_func', [
                    'msg' => 'testmsg',
                    'returl' => 'testurl',
        ]);
        $this->assertEquals($ret, [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => 'testurl',
                'msg' => 'testmsg',
            ],
        ]);

        $ret = self::funcTest(__NAMESPACE__ . '\TestFunction::static_except', '测试消息');
        $this->assertEquals($ret, [
            'ret' => false,
            'output' => [
                'succeed' => false,
                'returl' => '',
                'msg' => '测试消息',
            ],
        ]);
    }

    public function test_json_call_func() {
        $obj = new TestFunction();
        $ret = self::funcTest([$obj, 'func'], false);
        $this->assertEquals($ret, [
            'ret' => false,
            'output' => [
                'succeed' => false,
                'msg' => '操作失败',
                'returl' => '',
            ],
        ]);

        $ret = self::funcTest([$obj, 'func'], true);
        $this->assertEquals($ret, [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => '',
            ],
        ]);

        $ret = self::funcTest([$obj, 'func'], []);
        $this->assertEquals($ret, [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => '',
            ],
        ]);

        $ret = self::funcTest([$obj, 'func'], [
                    'returl' => 'testurl',
        ]);
        $this->assertEquals($ret, [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => 'testurl',
            ],
        ]);

        $ret = self::funcTest([$obj, 'func'], [
                    'msg' => 'testmsg',
        ]);
        $this->assertEquals($ret, [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => '',
                'msg' => 'testmsg',
            ],
        ]);

        $ret = self::funcTest([$obj, 'func'], [
                    'msg' => 'testmsg',
                    'returl' => 'testurl',
        ]);
        $this->assertEquals($ret, [
            'ret' => true,
            'output' => [
                'succeed' => true,
                'returl' => 'testurl',
                'msg' => 'testmsg',
            ],
        ]);

        $ret = self::funcTest([$obj, 'func_except'], '测试消息');
        $this->assertEquals($ret, [
            'ret' => false,
            'output' => [
                'succeed' => false,
                'returl' => '',
                'msg' => '测试消息',
            ],
        ]);
    }

    public static function funcTest($func, $args) {
        ob_start();
        $ret = FW\Common::json_call($args, $func, false);
        $output = ob_get_clean();
        return [
            'ret' => $ret,
            'output' => \Zend\Json\Json::decode($output, \Zend\Json\Json::TYPE_ARRAY),
        ];
    }

}
