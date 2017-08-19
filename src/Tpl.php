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

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

class Tpl {

    public static $theme_path;
    public static $res_path;
    public static $compiled_path;
    protected static $_varis = [];
    public static $always_compile;

    public static function assign($name, $value) {
        self::$_varis[$name] = $value;
    }

    public static function get($name) {
        if (isset(self::$_varis[$name])) {
            return self::$_varis[$name];
        }
        return null;
    }

    public static function append($name, $value) {
        if (isset(self::$_varis[$name])) {
            self::$_varis[$name] .= $value;
        } else {
            self::$_varis[$name] = $value;
        }
    }

    public static function prepend($name, $value) {
        if (isset(self::$_varis[$name])) {
            self::$_varis[$name] = $value . self::$_varis[$name];
        } else {
            self::$_varis[$name] = $value;
        }
    }

    public static function exist($tpl, $theme, $is_block = false) {
        $path = '';
        if ($is_block) {
            $path = WEB_ROOT . self::$theme_path . '/' . $theme . '/block' . $tpl . '.html';
        } else {
            $path = WEB_ROOT . self::$theme_path . '/' . $theme . '/page' . $tpl . '.html';
        }
        return file_exists($path);
    }

    public static function display($tpl, $args, $theme, $return = false) {
        $tpl_src = WEB_ROOT . self::$theme_path . '/' . $theme . '/page' . $tpl . '.html';
        $tpl_dest = WEB_ROOT . self::$compiled_path . '/' . $theme . '/page' . $tpl . '.php';
        ob_start();
        try {
            self::_compile($tpl_src, $tpl_dest, $theme);
            extract(self::$_varis);
            include($tpl_dest);
            if ($return) {
                return ob_get_clean();
            } else {
                ob_end_flush();
                return;
            }
        } catch (\Exception $ex) {
            ob_end_clean();
            throw $ex;
        }
    }

    protected static function _inc($tpl, $args, $theme) {
        $tpl_src = WEB_ROOT . self::$theme_path . '/' . $theme . '/block' . $tpl . '.html';
        $tpl_dest = WEB_ROOT . self::$compiled_path . '/' . $theme . '/block' . $tpl . '.php';
        if (self::_compile($tpl_src, $tpl_dest, $theme)) {
            extract(self::$_varis);
            include($tpl_dest);
        }
    }

    protected static function _compile($src, $dest, $theme) {
        if (!file_exists($src)) {
            if (DEBUG === 1) {
                throw new Exception('模板不存在：' . $src);
            } else {
                throw new Exception('模板不存在');
            }
        }

        $srctime = filemtime($src);
        $desttime = 0;
        if (file_exists($dest)) {
            $desttime = filemtime($dest);
        }
        if (self::$always_compile == 1 || $desttime == 0 || $desttime <= $srctime) {
            $str = file_get_contents($src);

            $str = preg_replace(
                    '/\<{inc (\S*?)\s*}\>/', '<?php ' . __NAMESPACE__ . '\Tpl::_inc("/$1",[],"' . $theme . '"); ?>', $str);

            $str = preg_replace(
                    '/\<{inc (\S*?) (\S*?)\s*}\>/', '<?php ' . __NAMESPACE__ . '\Tpl::_inc("/$1",$2,"' . $theme . '"); ?>', $str);

            $str = preg_replace(
                    '/\<{inc (\S*?) (\S*?) (\S*?)\s*}\>/', '<?php ' . __NAMESPACE__ . '\Tpl::_inc("/$1",$2,"$3"); ?>', $str);

            $str = preg_replace('/\<{=(.*?)}\>/', '<?= ($1); ?>', $str);
            $str = preg_replace('/\<{if (.*?)}\>/', '<?php if($1){ ?>', $str);
            $str = preg_replace('/\<{elseif (.*?)}\>/', '<?php }elseif($1){ ?>', $str);
            $str = preg_replace('/\<{else}\>/', '<?php }else{ ?>', $str);
            $str = preg_replace('/\<{\/if}\>/', '<?php } ?>', $str);

            $str = preg_replace('/\<{for (\S*?) (\S*?) (\S*?)\s*?}\>/', '<?php for($1=$2; $1 <= $3; $1++){ ?>', $str);

            $str = preg_replace('/\<{\/for}\>/', '<?php } ?>', $str);

            $str = preg_replace('/\<{foreach (\S*?) (\S*?)}\>/', '<?php foreach($1 as $2){ ?>', $str);

            $str = preg_replace('/\<{foreach (\S*?) (\S*?) (\S*?)\s*?}\>/', '<?php foreach($1 as $2 => $3){ ?>', $str);

            $str = preg_replace('/\<{\/foreach}\>/', '<?php } ?>', $str);
            $str = preg_replace('/\<{(\S.*?)}\>/', '<?php $1; ?>', $str);
            $str = preg_replace('/\<{\*((.|\r|\n)*?)\*}\>/', '', $str);

            //path relate to theme："/xxxx/yyyy"
            $str = preg_replace('/\<link (.*?)href="\/([^"]*)"(.*?) \/\>/i', '<link $1 href="' . self::$res_path . '/' . self::$theme_path . '/' . $theme . '/$2" $3 />', $str);
            $str = preg_replace('/\<script (.*?)src="\/([^"]*)"(.*?)\>/i', '<script $1 src="' . self::$res_path . '/' . self::$theme_path . '/' . $theme . '/$2" $3>', $str);
            $str = preg_replace('/\<img (.*?)src="\/([^"]*)"(.*?) \/\>/i', '<img $1 src="' . self::$res_path . '/' . self::$theme_path . '/' . $theme . '/$2" $3 />', $str);

            //path relate to resource root："|xxx/yyy"
            $str = preg_replace('/\<link (.*?)href="\|([^"]*)"(.*?) \/\>/i', '<link $1 href="' . self::$res_path . '/$2" $3 />', $str);
            $str = preg_replace('/\<script (.*?)src="\|([^"]*)"(.*?)\>/i', '<script $1 src="' . self::$res_path . '/$2" $3>', $str);
            $str = preg_replace('/\<img (.*?)src="\|([^"]*)"(.*?) \/\>/i', '<img $1 src="' . self::$res_path . '/$2" $3 />', $str);

            //path keep original："\xxx/yyy"
            $str = preg_replace('/\<link (.*?)href="\\\([^"]*)"(.*?) \/\>/i', '<link $1 href="/$2" $3 />', $str);
            $str = preg_replace('/\<script (.*?)src="\\\([^"]*)"(.*?)\>/i', '<script $1 src="/$2" $3>', $str);
            $str = preg_replace('/\<img (.*?)src="\\\([^"]*)"(.*?) \/\>/i', '<img $1 src="/$2" $3 />', $str);

            //remove empty character
            $str = preg_replace('/^\s*(.*?)\s*$/im', '$1', $str);
            $str = preg_replace('/\r|\n/', '', $str);
            $str = preg_replace('/\>\s*\</', '>$1<', $str);
            $str = preg_replace('/\s*\?\>\s*\<\?php\s*/', '', $str);
            $str = preg_replace('/\>\s*(.*?)\s*\</', '>$1<', $str);
            $str = preg_replace('/\s{2,}/i', ' ', $str);
            $str = preg_replace('/\?\>$/i', '', $str);

            FW\File::mkdir(dirname($dest));
            if (!file_put_contents($dest, $str)) {
                if (DEBUG === 1) {
                    throw new Exception('写入模板失败: ' . $dest);
                } else {
                    throw new Exception('写入模板失败');
                }
            }
        }
    }

}

$config = Config::get();
Tpl::$always_compile = $config->get_config('debug', 'tpl_always_compile', 0);
Tpl::$theme_path = $config->get_config('path', 'theme');
Tpl::$res_path = $config->get_config('path', 'res');
Tpl::$compiled_path = $config->get_config('path', 'compiled');
