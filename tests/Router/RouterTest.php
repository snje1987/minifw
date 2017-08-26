<?php

namespace Org\Snje\MinifwTest\Router;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

class RouterTest extends Ts\TestCommon {

    /**
     * @covers Org\Snje\Minifw\Text::strip_html
     */
    public function test_multi_layer_info() {
        $hash = array(
            '/' => array('', '', array(), ''),
            '/index' => array('', 'index', array(), ''),
            '/www/index' => array('/www', 'index', array(), ''),
            '/www/index-' => array('/www', 'index', array(''), ''),
            '/www/qqq/index-12' => array('/www/qqq', 'index', array('12'), '12'),
            '/www/qqq/index-1-2-3' => array('/www/qqq', 'index', array('1', '2', '3'), '1-2-3'),
            '//www/qqq/index-1-2' => array('//www/qqq', 'index', array('1', '2'), '1-2'),
        );
        foreach ($hash as $k => $v) {
            $this->assertEquals($v, FW\Router::multi_layer_info($k));
        }
        $err = array('', 'www/3eee', '/www\\/qqq/index-1-2');
        foreach ($err as $v) {
            try {
                FW\Router::multi_layer_info($v);
                $this->assertTrue(false);
            } catch (\Org\Snje\Minifw\Exception $ex) {
                $this->assertTrue(true);
            }
        }
    }

    /**
     * @covers Org\Snje\Minifw\Text::strip_tags
     */
    public function test_single_layer_info() {
        $hash = array(
            '/' => array('', '', ''),
            '/index' => array('', 'index', ''),
            '/www/index' => array('/www', 'index', ''),
            '/www/index-' => array('/www', 'index', '-'),
            '/www/qqq/index-12' => array('/www', 'qqq', '/index-12'),
            '//www/qqq/index-1-2' => array('/', 'www', '/qqq/index-1-2'),
            '/www\\/qqq/index-1-2' => array('', 'www', '\\/qqq/index-1-2'),
        );
        foreach ($hash as $k => $v) {
            $this->assertEquals($v, FW\Router::single_layer_info($k));
        }
        $err = array('', 'www/3eee');
        foreach ($err as $v) {
            try {
                FW\Router::single_layer_info($v);
                $this->assertTrue(false);
            } catch (\Org\Snje\Minifw\Exception $ex) {
                $this->assertTrue(true);
            }
        }
    }

}
