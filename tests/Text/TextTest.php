<?php

namespace Org\Snje\MinifwTest\Text;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

class TextTest extends Ts\TestCommon {

    public function test_strip_html() {
        $hash = array(
            " 123456   \n  2345 \n" => "123456 2345",
            " 123456\n2345 \n" => "123456 2345",
            " 123456\n   //not show  \n2345 \n" => "123456 2345",
            "> 12345 <" => ">12345<",
            ">\n12345 <" => ">12345<",
        );
        foreach ($hash as $k => $v) {
            $this->assertEquals($v, FW\Text::strip_html($k));
        }
    }

    public function test_strip_tag() {
        $hash = array(
            "<p>123</p>" => "123",
            "<br />123<br/>" => "123",
            "<p style=\"font-size:12px; color:red\">123</p>" => "123",
            "<p style=\"font-size:12px; color:red\" >123</p>" => "123",
            "<?ss style=\"font-size:12px; color:red\" >123</p>" => "<?ss style=\"font-size:12px; color:red\" >123",
        );
        foreach ($hash as $k => $v) {
            $this->assertEquals($v, FW\Text::strip_tags($k));
        }
    }

    public function test_str_num_cmp() {
        $hash = array(
            array('left' => '--50', 'right' => '100', 'out' => false,),
            array('left' => '-50', 'right' => '--100', 'out' => false,),
            array('left' => '-50.23', 'right' => '100.01', 'out' => -1,),
            array('left' => '50.23', 'right' => '-80.02', 'out' => 1,),
            array('left' => '100.23', 'right' => '80.56', 'out' => 1,),
            array('left' => '-100.23', 'right' => '-80.56', 'out' => -1,),
            array('left' => '80.56', 'right' => '100.23', 'out' => -1,),
            array('left' => '-80.56', 'right' => '-100.23', 'out' => 1,),
            array('left' => '83.56', 'right' => '82.21', 'out' => 1,),
            array('left' => '-83.56', 'right' => '-82.21', 'out' => -1,),
            array('left' => '83.56', 'right' => '84.01', 'out' => -1,),
            array('left' => '-83.56', 'right' => '-84.01', 'out' => 1,),
            array('left' => '83.56', 'right' => '83.21', 'out' => 1,),
            array('left' => '-83.56', 'right' => '-83.21', 'out' => -1,),
            array('left' => '83.325', 'right' => '83.32', 'out' => 1,),
            array('left' => '-83.325', 'right' => '-83.32', 'out' => -1,),
        );

        foreach ($hash as $v) {
            if ($v['out'] === false) {
                $this->assertTrue(FW\Text::str_num_cmp($v['left'], $v['right']) === false);
            } elseif ($v['out'] === 0) {
                $this->assertTrue(FW\Text::str_num_cmp($v['left'], $v['right']) === 0);
            } else {
                $this->assertTrue(FW\Text::str_num_cmp($v['left'], $v['right']) * $v['out'] > 0);
            }
        }
    }

}
