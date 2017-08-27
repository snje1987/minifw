<?php

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;

class File {

    /**
     * 程序内部使用的编码
     *
     * @var string
     */
    public static $encoding;

    /**
     * 保存错误信息
     * @var string
     */
    private static $last_error = '';

    const MIME_HASH = [
        'css' => 'text/css',
        'html' => 'text/html',
        'js' => 'text/javascript',
    ];

    public static function format_path() {
        $args = func_get_args();
        $args = array_reverse($args);
        $cur_path = [];
        foreach ($args as $arg) {
            if ($arg == '') {
                continue;
            }
            $arg = str_replace('\\', '/', $arg);
            $arg = rtrim($arg, '/');
            $path_array = explode('/', $arg);
            $cur_path = array_merge($path_array, $cur_path);
            if ($cur_path[0] == '') {
                break;
            }
        }
        $parsed = [];
        foreach ($cur_path as $v) {
            if ($v == '.') {
                continue;
            }
            if ($v == '..') {
                if (count($parsed) < 1) {
                    $parsed = [];
                    break;
                } else {
                    unset($parsed[count($parsed) - 1]);
                }
            } else {
                $parsed[] = $v;
            }
        }
        if (count($parsed) == 1 && $parsed[0] == '') {
            return '/';
        }
        return implode('/', $parsed);
    }

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
        $str = Text::strip_html($str);
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
        $dirmap = Config::get()->get_config('save');

        if (!isset($dirmap[$group])) {
            return self::error('分组错误');
        }

        $base_dir = $dirmap[$group];

        $name = self::mkname(WEB_ROOT . $base_dir, '.' . $ext, $fsencoding);

        if ($name == '') {
            return self::error('同一时间上传的文件过多');
        }
        $dest = WEB_ROOT . $base_dir . '/' . $name;
        $dest = self::conv_to($dest, $fsencoding);
        self::mkdir(dirname($dest));
        if (file_put_contents($dest, $data) !== false) {
            return $base_dir . '/' . $name;
        } else {
            return self::error('文件写入出错');
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
            return self::error($error);
        }

        $dirmap = Config::get()->get_config('upload');

        if (!isset($dirmap[$group])) {
            return self::error('文件分组错误');
        }

        $allow = $dirmap[$group]['allow'];
        $base_dir = $dirmap[$group]['path'];
        $maxsize = isset($dirmap[$group]['maxsize']) ? intval($dirmap[$group]['maxsize']) : 0;
        $filesize = intval($file['size']);
        if ($maxsize > 0 && $filesize > $maxsize) {
            return self::error('文件大小超过限制');
        }

        $pinfo = pathinfo($file['name']);
        $ext = strtolower($pinfo['extension']);

        if (!in_array($ext, $allow)) {
            return self::error('不允许的文件类型');
        }
        $name = self::mkname(WEB_ROOT . $base_dir, '.' . $ext, $fsencoding);
        if ($name == '') {
            return self::error('同一时间上传的文件过多');
        }
        $dest = WEB_ROOT . $base_dir . '/' . $name;
        $dest = self::conv_to($dest, $fsencoding);
        self::mkdir(dirname($dest));
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $base_dir . '/' . $name;
        } else {
            return self::error('文件移动出错');
        }
    }

    /**
     * 获取上传文件的原文件名
     *
     * @param array $file 要上传的文件
     * @param bool $ext 是否包含扩展名
     * @return string 上传文件的文件名
     */
    public static function get_name($file, $ext = false) {
        $name = trim($file['name']);
        $name = str_replace(' ', '', $name);
        $name = str_replace('　', '', $name);
        if ($ext) {
            return $name;
        }
        $pos = strrpos($name, '.');
        if ($pos === false) {
            return $name;
        }
        return trim(substr($name, 0, $pos));
    }

    /**
     * 移动文件到指定路径，目录不存在也会同时建立
     *
     * @param string $src 要移动的文件的绝对路径
     * @param string $dest 移动到的位置的绝对路径
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function rename($src, $dest, $fsencoding = '') {
        $src = self::conv_to($src, $fsencoding);
        $dest = self::conv_to($dest, $fsencoding);
        $dest_dir = self::conv_to(dirname($dest), $fsencoding);
        self::mkdir($dest_dir);
        \rename($src, $dest);
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
        $dest_dir = self::conv_to(dirname($dest), $fsencoding);
        self::mkdir($dest_dir);
        \copy($src, $dest);
    }

    /**
     * 复制指定目录的内容到目标目录中，目标不存在也会自动建立
     *
     * @param string $src 要复制的目录的绝对路径
     * @param string $dest 复制到的目录的绝对路径
     * @param boolean $hidden 是否复制隐藏文件
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function copy_dir($src, $dest, $hidden = false, $fsencoding = '') {
        $src = self::conv_to($src, $fsencoding);
        $dest = self::conv_to($dest, $fsencoding);
        self::mkdir($dest);
        if (!\is_dir($dest) || !\is_dir($src)) {
            return;
        }
        if (substr($src, -1) !== '/') {
            $src .= '/';
        }
        if (substr($dest, -1) !== '/') {
            $dest .= '/';
        }
        if ($dh = opendir($src)) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                if ($file{0} == '.' && !$hidden) {
                    continue;
                }
                $from = $src . $file;
                $to = $dest . $file;
                if (is_dir($from)) {
                    self::copy_dir($from, $to, $hidden);
                } else {
                    self::copy($from, $to);
                }
            }
            closedir($dh);
        }
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
     * @param string $path 要删除的文件的路径
     * @param string $isfull 路径是否为完整路径
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function delete($path, $isfull = false, $fsencoding = '') {
        if ($path == '') {
            return;
        }
        if (!$isfull) {
            $path = WEB_ROOT . $path;
        }

        $path = self::conv_to($path, $fsencoding);

        $parent = dirname($path);
        if (file_exists($path)) {
            if (is_dir($path)) {
                rmdir($path);
            } else {
                @unlink($path);
            }
            if (self::dir_empty($parent)) {
                self::delete($parent, true);
            }
        }
    }

    /**
     * 清空指定的目录，不会删除目录本身
     *
     * @param string $path 要清空的目录的路径
     * @param string $isfull 路径是否为完整路径
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function clear_dir($path, $isfull = false, $fsencoding = '') {
        if ($path == '') {
            return;
        }
        if (!$isfull) {
            $path = WEB_ROOT . $path;
        }
        $path = self::conv_to($path, $fsencoding);
        if (!is_dir($path)) {
            return;
        }
        if (substr($path, -1) !== '/') {
            $path .= '/';
        }
        if ($dh = opendir($path)) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $full = $path . $file;
                if (is_dir($full)) {
                    self::clear_dir($full, true);
                    rmdir($full);
                } else {
                    @unlink($full);
                }
            }
            closedir($dh);
        }
    }

    /**
     * 删除指定的文件，同时删除后缀相同的文件
     *
     * @param string $path 要删除的文件的路径
     * @param string $isfull 路径是否为完整路径
     * @param string $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function delete_img($path, $isfull = false, $fsencoding = '') {
        if ($path == '') {
            return;
        }
        if (!$isfull) {
            $path = WEB_ROOT . $path;
        }

        $path = self::conv_to($path, $fsencoding);
        $pinfo = pathinfo($path);
        $dir = $pinfo['dirname'] . '/';
        $name = $pinfo['filename'];
        $files = array();

        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (preg_match('/^' . $name . '_?.*$/i', $file)) {
                    $files[] = $dir . $file;
                }
            }
            closedir($dh);
        }

        foreach ($files as $one) {
            self::delete($one, true);
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
    public static function ls($full, $ext = '', $hidden = false, $fsencoding = '') {
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
                    if ($file{0} == '.' && !$hidden) {
                        continue;
                    }
                    $filename = self::conv_from($file, $fsencoding);

                    if ($ext != '') {
                        if (is_file($full . '/' . $file) && substr($filename, -1 * strlen($ext)) != $ext) {
                            continue;
                        }
                    }

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
    public static function put_content($full, $data, $fsencoding = '', $flags = 0) {
        $full = self::conv_to($full, $fsencoding);
        self::mkdir(dirname($full));
        return file_put_contents($full, $data, $flags);
    }

    public static function get_mime($full, $fsencoding = '') {
        $full = self::conv_to($full, $fsencoding);
        if (file_exists($full)) {
            $pinfo = pathinfo($full);
            $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
            $mime_type = 'application/octet-stream';
            if (array_key_exists($ext, self::MIME_HASH)) {
                $mime_type = self::MIME_HASH[$ext];
            } else {
                $fi = new \finfo(FILEINFO_MIME_TYPE);
                $mime_type = $fi->file($full);
            }
            return $mime_type;
        }
        return null;
    }

    /**
     * 读取文件并输出到浏览器
     *
     * @param type $full 文件路径
     * @param type $fsencoding 文件系统的编码，如不为空则会自动进行一些编码转换
     */
    public static function readfile($full, $fsencoding = '') {
        $full = self::conv_to($full, $fsencoding);
        $mime_type = self::get_mime($full);
        if ($mime_type !== null) {
            header('Content-Type: ' . $mime_type);
            readfile($full);
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
     * 返回路径的父目录，如果不存在，则返回空
     *
     * @param string $path
     * @return string
     */
    public static function dirname($path) {
        $path = \dirname($path);
        if ($path == '.') {
            $path = '';
        }
        return $path;
    }

    /**
     * 返回路径中的文件名，不存在则原样返回
     *
     * @param string $path
     * @return string
     */
    public static function basename($path) {
        $pos = strrpos($path, '/');
        if ($pos === false) {
            return $path;
        }
        return substr($path, $pos + 1);
    }

    /**
     * 返回文件中去除扩展名的部分
     *
     * @param string $file
     * @param boolean $last 扩展名是否从最后一个'.'开始
     * @return string
     */
    public static function filename($file, $last = true) {
        $pos = false;
        if ($last) {
            $pos = strrpos($file, '.');
        } else {
            $pos = strpos($file, '.');
        }
        if ($pos === false) {
            return $file;
        }
        return substr($file, 0, $pos);
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

    /**
     * 当文件上传／保存过程中发生错误时设置错误信息
     *
     * @param string $str 错误信息
     * @return string　空字符串
     */
    public static function error($str) {
        self::$last_error = $str;
        return '';
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public static function last_error() {
        return self::$last_error;
    }

}

File::$encoding = Config::get()->get_config('main', 'encoding', '');
