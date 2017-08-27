<?php

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

class Resource {

    protected $map;
    protected $map_path;

    public function __construct() {
        $this->map_path = WEB_ROOT . Config::get()->get_config('main', 'resource_map');
        $this->load_map();
    }

    public function load_map() {
        if (file_exists($this->map_path)) {
            $this->map = require $this->map_path;
        }
    }

    public function compile_all() {
        foreach ($this->map as $dest => $cfg) {
            if ($cfg['type'] === 'file') {
                if (!$this->compile($dest)) {
                    return false;
                }
            } elseif ($cfg['type'] === 'dir') {
                if (!$this->compile_dir($cfg['dep'], $dest)) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    public function compile_dir($src, $dest) {
        $list = File::ls(WEB_ROOT . $src);
        foreach ($list as $file) {
            if ($file['dir'] === true) {
                if (!$this->compile_dir($src . '/' . $file['name'], $dest . '/' . $file['name'])) {
                    return false;
                }
            } else {
                if (!$this->compile($dest . '/' . $file['name'])) {
                    return false;
                }
            }
        }
        return true;
    }

    public function compile($dest) {
        $cfg = $this->get_match_rule($dest);
        if ($cfg === null) {
            return true;
        }
        if (!$this->need_compile($dest, $cfg)) {
            return true;
        }
        $func = 'compile_' . $cfg['method'];
        if (method_exists($this, $func)) {
            return $this->$func($dest, $cfg);
        }
        return false;
    }

    public function get_match_rule($dest) {
        foreach ($this->map as $src => $cfg) {
            if ($cfg['type'] === 'file') {
                if ($src === $dest) {
                    return $cfg;
                }
            } elseif ($cfg['type'] === 'dir') {
                $len = strlen($src);
                if (strncmp($src, $dest, $len) === 0) {
                    $cfg['dep'] = [
                        $cfg['dep'] . substr($dest, $len),
                    ];
                    return $cfg;
                }
            }
        }
        return null;
    }

    public function need_compile($dest, $cfg) {
        if ($cfg === null) {
            return false;
        }
        if (!\file_exists(WEB_ROOT . $dest)) {
            return true;
        }
        $dtime = \filemtime(WEB_ROOT . $dest);
        foreach ($cfg['dep'] as $file) {
            $full = WEB_ROOT . $file;
            if (\file_exists($full)) {
                $stime = \filemtime($full);
                if ($stime >= $dtime) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function compile_uglify($dest, $cfg) {
        $content = '';
        foreach ($cfg['dep'] as $file) {
            $full = WEB_ROOT . $file;
            if (\file_exists($full)) {
                $content .= \file_get_contents($full);
            }
        }
        $myPacker = new \GK\JavascriptPacker($content, 'Normal', true, false);
        $content = $myPacker->pack();
        $dest = WEB_ROOT . $dest;
        File::put_content($dest, $content);
        return true;
    }

    protected function compile_cssmin($dest, $cfg) {
        $content = '';
        foreach ($cfg['dep'] as $file) {
            $full = WEB_ROOT . $file;
            if (\file_exists($full)) {
                $content .= \file_get_contents($full);
            }
        }
        $content = \CssMin::minify($content);
        $dest = WEB_ROOT . $dest;
        File::put_content($dest, $content);
        return true;
    }

    protected function compile_copy($dest, $cfg) {
        $dest = WEB_ROOT . $dest;
        File::delete($dest, true);
        foreach ($cfg['dep'] as $file) {
            $full = WEB_ROOT . $file;
            if (\file_exists($full)) {
                $content = \file_get_contents($full);
                File::put_content($dest, $content, '', FILE_APPEND);
            }
        }
        return true;
    }

}
