<?php

/*
 * Copyright (C) 2014 Yang Ming <yangming0116@gmail.com>
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
 * @filename File.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@gmail.com>
 * @datetime 2014-10-18  23:00:54
 * @version 1.0
 * @Description
 */

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as Minifw;

/**
 * 定义常用的文件操作
 */
class File{

    /**
     * 保存字符串到文件，会对字符串内容进行压缩
     *
     * @param string $str 要保存的字符串
     * @param string $group 分组
     * @param string $ext 扩展名
     * @return string 保存的相对路径
     */
    public static function save_str($str, $group, $ext){
        $str = cText::strip_html($str);
        return self::save($str, $group, $ext);
    }

    /**
     * 保存的二进制数据到文件
     *
     * @param string $data 要保存的二进制数据
     * @param string $group 分组
     * @param string $ext 扩展名
     * @return string 保存的相对路径
     */
    public static function save($data, $group, $ext){
        $dirmap = cConfig::get('save');

        if(!isset($dirmap[$group])){
            throw new Minifw\Exception('分组错误');
        }

        $base_dir = $dirmap[$group];

        $name = self::mkname(WEB_ROOT . '/' . $base_dir, '.' . $ext);

        if($name == ''){
            throw new Minifw\Exception('同一时间上传的文件过多');
        }
        $dest = WEB_ROOT . '/' . $base_dir . '/' . $name;
        self::mkdir(dirname($dest));
        if(file_put_contents($dest, $data) !== false){
            return '/' . $base_dir . '/' . $name;
        }else{
            throw new Minifw\Exception('文件写入出错');
        }
    }

    /**
     * 保存通过表单提交的文件
     *
     * @param array $file 要上传的文件
     * @param string $group 文件分组
     * @return string 保存的相对路径
     */
    public static function upload_file($file, $group){
        if(empty($file)){
            return '';
        }
        if($file['error'] != 0){
            $error = '';
            switch($file['error']){
                case '1':
                    $error = '超过服务器允许的大小。';
                    break;
                case '2':
                    $error = '超过表单允许的大小。';
                    break;
                case '3':
                    $error = '文件只有部分被上传。';
                    break;
                case '4':
                    $error = '请选择文件。';
                    break;
                case '6':
                    $error = '找不到临时目录。';
                    break;
                case '7':
                    $error = '写文件到硬盘出错。';
                    break;
                case '8':
                    $error = '文件传输被扩展终止。';
                    break;
                case '999':
                default:
                    $error = '未知错误。';
            }
            throw new Minifw\Exception($error);
        }

        $dirmap = cConfig::get('upload');

        if(!isset($dirmap[$group])){
            throw new Minifw\Exception('文件分组错误');
        }

        $allow = $dirmap[$group]['allow'];
        $base_dir = $dirmap[$group]['path'];

        $pinfo = pathinfo($file['name']);
        $ext = strtolower($pinfo['extension']);

        if(!in_array($ext, $allow)){
            throw new Minifw\Exception('不允许的文件类型');
        }
        $name = self::mkname(WEB_ROOT . '/' . $base_dir, '.' . $ext);
        if($name == ''){
            throw new Minifw\Exception('同一时间上传的文件过多');
        }
        $dest = WEB_ROOT . '/' . $base_dir . '/' . $name;
        self::mkdir(dirname($dest));
        if(move_uploaded_file($file['tmp_name'], $dest)){
            return '/' . $base_dir . '/' . $name;
        }else{
            throw new Minifw\Exception('文件移动出错');
        }
    }

    /**
     * 建立文件夹，如果父目录不存在也会同时建立
     * @param string $path 目录的绝对路径
     */
    public static function mkdir($path){
        $sub = dirname($path);
        if(!file_exists($sub)){
            self::mkdir($sub);
        }
        if(!file_exists($path)){
            mkdir($path);
        }
    }

    /**
     * 复制文件到指定路径，目录不存在也会同时建立
     *
     * @param string $src 要复制的文件的绝对路径
     * @param string $dest 复制到的位置的绝对路径
     */
    public static function copy($src,$dest){
        self::mkdir(dirname($dest));
        copy($src,$dest);
    }

    /**
     * 在指定的目录中依据当前时间生成唯一的文件名
     *
     * @param string $dir 目录的绝对路径
     * @param string $tail 文件的扩展名
     * @return string 生成的文件名的相对路径，失败的返回空
     */
    public static function mkname($dir, $tail){
        $name = '';
        $count = 0;
        while($name == '' && $count < 1000000){
            $time = date('YmdHis');
            $year = date('Y');
            $month = date('m');
            $day = date('d');
            $rand = rand(100000, 999999);
            $name = $year . '/' . $month . '/' . $day . '/' . $time . $rand . $tail;
            if(file_exists($dir . '/' . $name)){
                $name = '';
            }
            $count++;
        }
        return $name;
    }

    /**
     * 删除指定的文件或目录，如果删除后父目录为空也会删除父目录
     *
     * @param string $path 要删除的文件的相对路径
     */
    public static function delete($path){
        if($path == ''){
            return;
        }
        $full = WEB_ROOT . $path;
        $parent = dirname($path);
        if(file_exists($full)){
            if(is_dir($full)){
                @rmdir($full);
            }
            else{
                @unlink($full);
            }
            if(self::dir_empty($parent)){
                self::delete($parent);
            }
        }
    }

    /**
     * 删除指定的文件，同时也会删除以指定文件名为前缀的所有文件，如果删除后父目录为空也会删除父目录
     *
     * @param string $path 要删除的文件的相对路径
     */
    public static function delete_img($path){
        $pinfo = pathinfo($path);
        $dir = $pinfo['dirname'].'/';
        $name = $pinfo['filename'];
        if($dh = opendir(WEB_ROOT.$dir)){
            while(($file = readdir($dh))!== false){
                //echo $file;
                if($file == '.' || $file == '..'){
                    continue;
                }
                if(preg_match('/^'.$name.'_?.*$/i',$file)){
                    self::delete($dir.$file);
                }
            }
            closedir($dh);
        }
    }

    /**
     * 检测指定的目录是否为空
     *
     * @param string $path 要检测的目录
     * @return bool 为空返回true，否则返回false
     */
    public static function dir_empty($path){
        $full = WEB_ROOT . $path;
        if(is_dir($full)){
            $dir = opendir($full);
            while (false !== ($file = readdir($dir))) {
                if($file == '.' || $file == '..'){
                    continue;
                }
                else{
                    closedir($dir);
                    return false;
                }
             }
             closedir($dir);
             return true;
        }
        else{
            return false;
        }
    }

    /**
     * 列出指定目录的内容
     *
     * @param string $path 要检测的目录
     * @return array 目录中的文件列表
     */
    public static function ls($path){
        if(substr($path, -1) !== '/'){
            $path .= '/';
        }
        $res = [];
        if (is_dir($path)) {
            if ($dh = opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if($file === '.' || $file === '..'){
                        continue;
                    }
                    $res[] = [
                        'name' => $file,
                        'dir' => is_dir($path . $file),
                    ];
                }
                closedir($dh);
            }
        }
        return $res;
    }
}
