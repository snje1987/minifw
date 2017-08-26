<?php

namespace Org\Snje\Minifw;

class TableUtils {

    public static function display_all_diff($ns = '', $path = '') {
        $diff = self::get_all_diff($ns, $path);

        header("Content-Type:text/plain;charset=utf-8");
        $otable = '';
        $trans = [];
        foreach ($diff as $v) {
            if ($otable == '' || $otable != $v['table']) {
                echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
                $otable = $v['table'];
                echo $otable . "\n\n";
            }
            echo $v['diff'] . "\n";
            $trans[] = $v['trans'];
        }
        echo "\n\n================================================================\n\n";
        echo implode("\n", $trans);
    }

    public static function get_all_diff($ns = '', $path = '') {
        if ($path == '' || !is_dir($path)) {
            return;
        }
        $diff = [];
        try {
            $dir = opendir($path);
            while (false !== ($file = readdir($dir))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $ndiff = [];
                if (is_dir($path . '/' . $file)) {
                    $ndiff = self::get_all_diff($ns . '\\' . $file, $path . '/' . $file);
                } else {
                    if (substr($file, -4, 4) !== '.php') {
                        continue;
                    }
                    $classname = $ns . '\\' . substr($file, 0, strlen($file) - 4);
                    if (class_exists($classname)) {
                        $obj = $classname::get();
                        if ($obj instanceof Table) {
                            $ndiff = $obj->table_diff();
                        }
                    }
                }
                if (empty($ndiff)) {
                    continue;
                }
                $diff = array_merge($diff, $ndiff);
            }
            closedir($dir);
            return $diff;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

}
