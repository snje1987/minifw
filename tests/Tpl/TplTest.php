<?php

namespace Org\Snje\MinifwTest\Tpl;

use Org\Snje\Minifw as FW;
use Org\Snje\MinifwTest as Ts;

class TplTest extends Ts\TestCommon {

    public function test_compile_string() {
        $hash = [
            '<{inc header}>' => '<?php ' . FW\Tpl::class . '::_inc(\'/header\',[],\'default\');' . "\n",
            '<{inc /header}>' => '<?php ' . FW\Tpl::class . '::_inc(\'/header\',[],\'default\');' . "\n",
            '<{inc header $args}>' => '<?php ' . FW\Tpl::class . '::_inc(\'/header\',$args,\'default\');' . "\n",
            '<{inc /header $args}>' => '<?php ' . FW\Tpl::class . '::_inc(\'/header\',$args,\'default\');' . "\n",
            '<{inc header $args theme}>' => '<?php ' . FW\Tpl::class . '::_inc(\'/header\',$args,\'theme\');' . "\n",
            '<{inc /header $args theme}>' => '<?php ' . FW\Tpl::class . '::_inc(\'/header\',$args,\'theme\');' . "\n",
            '<{=$a+$b}>' => '<?=($a+$b);' . "\n",
            '<{|$a}>' => '<?=(isset($a)?($a):\'\');' . "\n",
            '<{|$a|$b}>' => '<?=(isset($a)?($a):($b));' . "\n",
            '<{|$a|$b|$c}>' => '<?=(isset($a)?($a):(isset($b)?($b):($c)));' . "\n",
            '<{if $a == $b}>' => '<?php if($a == $b){' . "\n",
            '<{elseif $a == $b}>' => '<?php }elseif($a == $b){' . "\n",
            '<{else}>' => '<?php }else{' . "\n",
            '<{/if}>' => '<?php }' . "\n",
            '<{for $i 1 10}>' => '<?php for($i=1;$i<=10;$i++){' . "\n",
            '<{/for}>' => '<?php }' . "\n",
            '<{foreach $data $v}>' => '<?php foreach($data as $v){' . "\n",
            '<{foreach $data $k $v}>' => '<?php foreach($data as $k=>$v){' . "\n",
            '<{/foreach}>' => '<?php }' . "\n",
            '<{$a=$b}>' => '<?php $a=$b;' . "\n",
            '<{*werwerwer*}>' => '',
            '<{*123123' . "\n" . 'qeqeqwe*}>' => '',
            '<link class="a" href="/a/b.css" attr="b" />' => '<link class="a" href="/www/theme/default/a/b.css" attr="b" />',
            '<script class="a" src="/a/b.js" attr="b">' => '<script class="a" src="/www/theme/default/a/b.js" attr="b">',
            '<img class="a" src="/a/b.jpg" attr="b" />' => '<img class="a" src="/www/theme/default/a/b.jpg" attr="b" />',
            '<link class="a" href="|a/b.css" attr="b" />' => '<link class="a" href="/www/a/b.css" attr="b" />',
            '<script class="a" src="|a/b.js" attr="b">' => '<script class="a" src="/www/a/b.js" attr="b">',
            '<img class="a" src="|a/b/jpg" attr="b" />' => '<img class="a" src="/www/a/b/jpg" attr="b" />',
            '<link class="a" href="\\a/b.css" attr="b" />' => '<link class="a" href="/a/b.css" attr="b" />',
            '<script class="a" src="\\a/b.js" attr="b">' => '<script class="a" src="/a/b.js" attr="b">',
            '<img class="a" src="\\a/b.jpg" attr="b" />' => '<img class="a" src="/a/b.jpg" attr="b" />',
        ];

        $class = new \ReflectionClass(FW\Tpl::class);
        $function = $class->getMethod('_compile_string');
        $function->setAccessible(true);

        foreach ($hash as $k => $v) {
            $this->assertEquals($v, $function->invoke(null, $k, 'default'));
        }
    }

}
