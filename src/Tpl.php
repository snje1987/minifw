<?php

/*
 * Copyright (C) 2013 Yang Ming <yangming0116@gmail.com>
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

/**
 * @filename Tpl.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @copyright Copyright (C) 2013 杨明
 * @datetime 2013-3-26 10:46:01
 * @version 1.0
 * @Description 定义模板系统
 */

namespace Org\Snje\Minifw;
use Org\Snje\Minifw as Minifw;

/**
 * 基本的模板操作
 */
class Tpl{

    protected static $theme_path;
    protected static $res_path;
    protected static $compiled_path;

    /**
     * 显示指定的页面模板
     *
     * @param string $tpl 页面模板
     * @param string $args 页面参数
     * @param string $theme 模板的主题
     */
    public static function display($tpl, $args, $theme = ''){
        $theme = ($theme == '' ? Minifw\Config::get('main','theme') : $theme);

        self::$theme_path = Minifw\Config::get('path', 'theme');
        self::$res_path = Minifw\Config::get('path', 'theme_res');
        self::$compiled_path = Minifw\Config::get('path', 'compiled');

        $tpl_src = WEB_ROOT . self::$theme_path . '/' . $theme . '/page' . $tpl . '.html';
        $tpl_dest = WEB_ROOT . self::$compiled_path . '/' . $theme . '/page' . $tpl . '.php';

        if(self::_compile($tpl_src, $tpl_dest, $theme)){
            ob_start();
            try{
                include($tpl_dest);
            }
            catch(\Exception $ex){
                ob_end_clean();
                if(DEBUG){
                    throw $ex;
                }
                return false;
            }
            ob_end_flush();
            return true;
        }
        return false;
    }

    /**
     * 包含指定模块
     *
     * @param string $tpl 模块名称
     * @param string $args 页面参数
     * @param string $theme 模板的主题
     */
    protected static function _inc($tpl, $args, $theme){
        $tpl_src = WEB_ROOT . self::$theme_path . '/' . $theme . '/block' . $tpl . '.html';
        $tpl_dest = WEB_ROOT . self::$compiled_path . '/' . $theme . '/block' . $tpl . '.php';
        if(self::_compile($tpl_src, $tpl_dest, $theme)){
            include($tpl_dest);
        }
    }

    /**
     * 编译指定的模板
     *
     * @param string $src 源文件
     * @param string $dest 目标文件
     * @param string $theme 模板的主题
     */
    protected static function _compile($src, $dest, $theme){
        if(!file_exists($src)){
            return false;
        }

        //global $config;
        $srctime = filemtime($src);
        $desttime = 0;
        if(file_exists($dest)){
            $desttime = filemtime($dest);
        }
        if($desttime == 0 || $desttime <= $srctime){
            $str = file_get_contents($src);

            $str = preg_replace('/\s{2,}/i', ' ', $str);

            /* 处理模板中的处理逻辑语句——开始 */
            $str = preg_replace(
                    '/\<{inc (\S*?)\s*}\>/',
                    '<?php ' . __NAMESPACE__ . '\Tpl::_inc("/$1",[],"' . $theme . '"); ?>', $str);

            $str = preg_replace(
                    '/\<{inc (\S*?) (\S*?)\s*}\>/',
                    '<?php ' . __NAMESPACE__ . '\Tpl::_inc("/$1",$2,"' . $theme . '"); ?>', $str);

            $str = preg_replace(
                    '/\<{inc (\S*?) (\S*?) (\S*?)\s*}\>/',
                    '<?php ' . __NAMESPACE__ . '\Tpl::_inc("/$1",$2,"$3"); ?>', $str);

            $str = preg_replace('/\<{ (.*?)}\>/', '<?php echo ($1); ?>', $str);
            $str = preg_replace('/\<{if (.*?)}\>/', '<?php if($1){ ?>', $str);
            $str = preg_replace('/\<{elseif (.*?)}\>/', '<?php }elseif($1){ ?>', $str);
            $str = preg_replace('/\<{else}\>/', '<?php }else{ ?>', $str);
            $str = preg_replace('/\<{\/if}\>/', '<?php } ?>', $str);

            $str = preg_replace('/\<{for (\S*?) (\S*?) (\S*?)\s*?}\>/',
                    '<?php for($1=$2; $1 <= $3; $1++){ ?>', $str);

            $str = preg_replace('/\<{\/for}\>/', '<?php } ?>', $str);

            $str = preg_replace('/\<{foreach (\S*?) (\S*?)}\>/',
                    '<?php foreach($1 as $2){ ?>', $str);

            $str = preg_replace('/\<{foreach (\S*?) (\S*?) (\S*?)\s*?}\>/',
                    '<?php foreach($1 as $2 => $3){ ?>', $str);

            $str = preg_replace('/\<{\/foreach}\>/', '<?php } ?>', $str);
            $str = preg_replace('/\<{(\S.*?)}\>/', '<?php $1; ?>', $str);
            $str = preg_replace('/\<{\*((.|\r|\n)*?)\*}\>/', '', $str);
            /* 处理模板中的处理逻辑语句——完成 */

            //处理相对路径："/xxxx/yyyy"
            $str = preg_replace('/\<link (.*?)href="\/([^"]*)"(.*?) \/\>/i',
                    '<link $1 href="' . self::$res_path . '/' . $theme . '/$2" $3 />', $str);
            $str = preg_replace('/\<script (.*?)src="\/([^"]*)"(.*?)\>/i',
                    '<script $1 src="' . self::$res_path . '/' . $theme . '/$2" $3>', $str);
            $str = preg_replace('/\<img (.*?)src="\/([^"]*)"(.*?) \/\>/i',
                    '<img $1 src="' . self::$res_path . '/' . $theme . '/$2" $3 />', $str);

            //处理绝对路径："|xxx/yyy"
            $str = preg_replace('/\<link (.*?)href="\|([^"]*)"(.*?) \/\>/i',
                    '<link $1 href="/www/$2" $3 />', $str);
            $str = preg_replace('/\<script (.*?)src="\|([^"]*)"(.*?)\>/i',
                    '<script $1 src="/www/$2" $3>', $str);
            $str = preg_replace('/\<img (.*?)src="\|([^"]*)"(.*?) \/\>/i',
                    '<img $1 src="/www/$2" $3 />', $str);
            /* 处理绝对路径——完成 */

            //处理原始路径："\xxx/yyy"
            $str = preg_replace('/\<link (.*?)href="\\\([^"]*)"(.*?) \/\>/i',
                    '<link $1 href="/$2" $3 />', $str);
            $str = preg_replace('/\<script (.*?)src="\\\([^"]*)"(.*?)\>/i',
                    '<script $1 src="/$2" $3>', $str);
            $str = preg_replace('/\<img (.*?)src="\\\([^"]*)"(.*?) \/\>/i',
                    '<img $1 src="/$2" $3 />', $str);
            /* 处理原始路径——完成 */

            /* 删除模板中多余的空行和空格——开始 */
            $str = preg_replace('/^\s*(.*?)\s*$/im', '$1', $str);
            $str = preg_replace('/^\/\/(.*?)$/im', '', $str);
            $str = preg_replace('/\r|\n/i', '', $str);
            $str = preg_replace('/\>\s*(.*?)\s*\</im', '>$1<', $str);
            $str = preg_replace('/\s{2,}/i', ' ', $str);
            /* 删除模板中多余的空行和空格——完成 */

            Minifw\File::mkdir(dirname($dest));
            if(!file_put_contents($dest, $str)){
                return fasle;
            }
        }
        return true;
    }

}
