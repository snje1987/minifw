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
            'error' => -1,
            'returl' => '',
            'msg' => '[' . __DIR__ . '/Functions.php:16]test msg',
                ), $ret);

        $ret = $controler->json_call(
                'test msg'
                , $class . '::static_noexist'
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals(array(
            'error' => -1,
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
            'error' => -1,
            'returl' => '',
            'msg' => '[' . __DIR__ . '/Functions.php:24]test msg',
                ), $ret);

        $ret = $controler->json_call(
                'test msg'
                , array($obj, 'func_noexist')
                , FW\Controler::JSON_CALL_RETURN);
        $this->assertEquals(array(
            'error' => -1,
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
        ),
        array(
            'msg' => 'testmsg',
            'returl' => 'testurl',
            'msg1' => 'testmsg1',
        ),
        array(
            'error' => 1,
            'msg' => 'testmsg',
            'returl' => 'testurl',
            'msg1' => 'testmsg1',
        ),
    );
    public static $expect = array(
        array(
            'error' => -1,
            'msg' => '操作失败',
            'returl' => '',
        ),
        array(
            'error' => 0,
            'returl' => '',
            'msg' => '',
        ),
        array(
            'error' => 0,
            'returl' => '',
            'msg' => '',
        ),
        array(
            'error' => 0,
            'returl' => 'testurl',
            'msg' => '',
        ),
        array(
            'error' => 0,
            'returl' => '',
            'msg' => 'testmsg',
        ),
        array(
            'error' => 0,
            'returl' => 'testurl',
            'msg' => 'testmsg',
        ),
        array(
            'error' => 0,
            'returl' => 'testurl',
            'msg' => 'testmsg',
            'msg1' => 'testmsg1',
        ),
        array(
            'error' => 1,
            'returl' => 'testurl',
            'msg' => 'testmsg',
            'msg1' => 'testmsg1',
        ),
    );

}
