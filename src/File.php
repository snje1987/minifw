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
class File {

    public static $encoding;

    /**
     * 保存字符串到文件，会对字符串内容进行压缩
     *
     * @param string $str 要保存的字符串
     * @param string $group 分组
     * @param string $ext 扩展名
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     * @return string 保存的相对路径
     */
    public static function save_str($str, $group, $ext, $fsencoding = '') {
        $str = cText::strip_html($str);
        return self::save($str, $group, $ext, $fsencoding);
    }

    /**
     * 保存的二进制数据到文件
     *
     * @param string $data 要保存的二进制数据
     * @param string $group 分组
     * @param string $ext 扩展名
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     * @return string 保存的相对路径
     */
    public static function save($data, $group, $ext, $fsencoding = '') {
        $dirmap = cConfig::get('save');

        if (!isset($dirmap[$group])) {
            throw new Minifw\Exception('分组错误');
        }

        $base_dir = $dirmap[$group];

        $name = self::mkname(WEB_ROOT . '/' . $base_dir, '.' . $ext, $fsencoding);

        if ($name == '') {
            throw new Minifw\Exception('同一时间上传的文件过多');
        }
        $dest = WEB_ROOT . '/' . $base_dir . '/' . $name;
        $dest = self::conv_to($dest, $fsencoding);
        self::mkdir(dirname($dest));
        if (file_put_contents($dest, $data) !== false) {
            return '/' . $base_dir . '/' . $name;
        } else {
            throw new Minifw\Exception('文件写入出错');
        }
    }

    /**
     * 保存通过表单提交的文件
     *
     * @param array $file 要上传的文件
     * @param string $group 文件分组
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     * @return string 保存的相对路径
     */
    public static function upload_file($file, $group, $fsencoding = '') {
        if (empty($file)) {
            return '';
        }
        if ($file['error'] != 0) {
            $error = '';
            switch ($file['error']) {
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

        if (!isset($dirmap[$group])) {
            throw new Minifw\Exception('文件分组错误');
        }

        $allow = $dirmap[$group]['allow'];
        $base_dir = $dirmap[$group]['path'];

        $pinfo = pathinfo($file['name']);
        $ext = strtolower($pinfo['extension']);

        if (!in_array($ext, $allow)) {
            throw new Minifw\Exception('不允许的文件类型');
        }
        $name = self::mkname(WEB_ROOT . '/' . $base_dir, '.' . $ext, $fsencoding);
        if ($name == '') {
            throw new Minifw\Exception('同一时间上传的文件过多');
        }
        $dest = WEB_ROOT . '/' . $base_dir . '/' . $name;
        $dest = self::conv_to($dest, $fsencoding);
        self::mkdir(dirname($dest));
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return '/' . $base_dir . '/' . $name;
        } else {
            throw new Minifw\Exception('文件移动出错');
        }
    }

    /**
     * 复制文件到指定路径，目录不存在也会同时建立
     *
     * @param string $src 要复制的文件的绝对路径
     * @param string $dest 复制到的位置的绝对路径
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function copy($src, $dest, $fsencoding = '') {
        $src = self::conv_to($src, $fsencoding);
        $dest = self::conv_to($dest, $fsencoding);
        self::mkdir($dest);
        copy($src, $dest);
    }

    /**
     * 在指定的目录中依据当前时间生成唯一的文件名
     *
     * @param string $full 目录的绝对路径
     * @param string $tail 文件的扩展名
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     * @return string 生成的文件名的相对路径，失败的返回空
     */
    public static function mkname($full, $tail, $fsencoding = '') {
        $full = self::conv_to($full, $fsencoding);
        $name = '';
        $count = 0;
        while ($name == '' && $count < 1000000) {
            $time = date('YmdHis');
            $year = date('Y');
            $month = date('m');
            $day = date('d');
            $rand = rand(100000, 999999);
            $name = $year . '/' . $month . '/' . $day . '/' . $time . $rand . $tail;
            if (file_exists($full . '/' . $name)) {
                $name = '';
            }
            $count++;
        }
        return $name;
    }

    /**
     * 删除指定的文件或目录，如果删除后父目录为空也会删除父目录
     *
     * @param string $full 要删除的文件的相对路径
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function delete($full, $fsencoding = '') {
        if ($full == '') {
            return;
        }

        $full = self::conv_to($full, $fsencoding);

        $parent = dirname($full);
        if (file_exists($full)) {
            if (is_dir($full)) {
                rmdir($full);
            } else {
                @unlink($full);
            }
            if (self::dir_empty($parent)) {
                self::delete($parent);
            }
        }
    }

    /**
     * 检测指定的目录是否为空
     *
     * @param string $full 要检测的目录
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     * @return bool 为空返回true，否则返回false
     */
    public static function dir_empty($full, $fsencoding = '') {
        $full = self::conv_to($full, $fsencoding);
        if (is_dir($full)) {
            $dir = opendir($full);
            while (false !== ($file = readdir($dir))) {
                if ($file == '.' || $file == '..') {
                    continue;
                } else {
                    closedir($dir);
                    return false;
                }
            }
            closedir($dir);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 列出指定目录的内容
     *
     * @param string $full 要检测的目录
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     * @return array 目录中的文件列表
     */
    public static function ls($full, $fsencoding = '') {
        $full = self::conv_to($full, $fsencoding);
        if (substr($full, -1) !== '/') {
            $full .= '/';
        }
        $res = [];
        if (is_dir($full)) {
            if ($dh = opendir($full)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    $filename = self::conv_from($file, $fsencoding);
                    $res[] = [
                        'name' => $filename,
                        'dir' => is_dir($full . $file),
                    ];
                }
                closedir($dh);
            }
        }
        return $res;
    }

    /**
     *
     * @param string $full 文件路径
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     * @return string
     */
    public static function get_content($full, $fsencoding = '') {
        $full = self::conv_to($full, $fsencoding);
        if (file_exists($full)) {
            return file_get_contents($full);
        }
        return '';
    }

    /**
     *
     * @param type $full 文件路径
     * @param type $data 要保存的内容
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     * @return int
     */
    public static function put_content($full, $data, $fsencoding = '') {
        $full = self::conv_to($full, $fsencoding);
        return file_put_contents($full, $data);
    }

    /**
     * 读取文件并输出到浏览器
     *
     * @param type $full 文件路径
     * @param type $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function readfile($full, $fsencoding = '') {
        $full = self::conv_to($full, $fsencoding);
        if (file_exists($full)) {
            $mtime = filemtime($full);
            Server::show_304($mtime);
            $fi = new \finfo(FILEINFO_MIME_TYPE);
            $mime_type = $fi->file($full);
            header('Content-Type: ' . $mime_type);
            readfile($full);
        } else {
            Server::show_404();
        }
    }

    /**
     * 创建目录，同时会创建所有的父目录
     *
     * @param string $full 要创建的目录路径
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function mkdir($full, $fsencoding = '') {
        $full = self::conv_to($full, $fsencoding);
        if (!file_exists($full)) {
            return \mkdir($full, 0777, true);
        }
        return true;
    }

    /**
     * 调用php文件相关函数
     *
     * @param type $func 函数名
     * @param type $full 文件路径
     * @param type $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function call($func, $full, $fsencoding = '') {
        if (function_exists($func)) {
            $full = self::conv_to($full, $fsencoding);
            return $func($full);
        }
        return false;
    }

    /**
     * 将字符串的编码进行转换
     *
     * @param string $str 要转换的字符串
     * @param string $fsencoding 转换到的编码，为空的不转换
     * @return string 转换后的字符串
     */
    public static function conv_to($str, $fsencoding) {
        if ($fsencoding != '' && $fsencoding != self::$encoding) {
            $str = iconv(self::$encoding, $fsencoding, $str);
        }
        return $str;
    }

    /**
     * 将字符串的编码进行转换
     *
     * @param string $str 要转换的字符串
     * @param string $fsencoding 原字符串的编码，为空的不转换
     * @return string 转换后的字符串
     */
    public static function conv_from($str, $fsencoding) {
        if ($fsencoding != '' && $fsencoding != self::$encoding) {
            $str = iconv($fsencoding, self::$encoding, $str);
        }
        return $str;
    }

}

File::$encoding = Config::get('main', 'encoding', 'encoding');
