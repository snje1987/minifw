<?php

namespace Org\Snje\MinifwTest\JsonCall;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

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
        $this->assertEquals(array(
            'succeed' => false,
            'returl' => '',
            'msg' => '[' . __DIR__ . '/Functions.php:41]test msg',
                ), $ret);

        $ret = $controler->json_call(
                'test msg'
                , $class . '::static_noexist'
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals(array(
            'succeed' => false,
            'returl' => '',
            'msg' => '操作失败',
                ), $ret);
    }

    public function test_json_call_func() {
        $obj = new Functions();
        $controler = new FW\Controler();
        $count = count(self::$input);
        for ($i = 0; $i < $count; $i++) {
            $ret = $controler->json_call(
                    self::$input[$i]
                    , array($obj, 'func')
                    , FW\Controler::JSON_CALL_RETURN);
            $this->assertEquals(self::$expect[$i], $ret);
        }

        $ret = $controler->json_call(
                'test msg'
                , array($obj, 'func_except')
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals(array(
            'succeed' => false,
            'returl' => '',
            'msg' => '[' . __DIR__ . '/Functions.php:49]test msg',
                ), $ret);

        $ret = $controler->json_call(
                'test msg'
                , array($obj, 'func_noexist')
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals(array(
            'succeed' => false,
            'returl' => '',
            'msg' => '操作失败',
                ), $ret);
    }

    public static $input = array(
        false,
        true,
        array(),
        array(
            'returl' => 'testurl',
        ),
        array(
            'msg' => 'testmsg',
        ),
        array(
            'msg' => 'testmsg',
            'returl' => 'testurl',
        )
    );
    public static $expect = array(
        array(
            'succeed' => false,
            'msg' => '操作失败',
            'returl' => '',
        ),
        array(
            'succeed' => true,
            'returl' => '',
        ),
        array(
            'succeed' => true,
            'returl' => '',
        ),
        array(
            'succeed' => true,
            'returl' => 'testurl',
        ),
        array(
            'succeed' => true,
            'returl' => '',
            'msg' => 'testmsg',
        ),
        array(
            'succeed' => true,
            'returl' => 'testurl',
            'msg' => 'testmsg',
        ),
    );

}
