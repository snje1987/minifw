<?php

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

class Resource {

    protected $map;
    protected $map_path;

    public function __construct($map_path = null) {
        if ($map_path === null) {
            $this->map_path = WEB_ROOT . Config::get()->get_config('main', 'resource_map');
        } else {
            $this->map_path = $map_path;
        }
        $this->load_map();
    }

    public function load_map() {
        if (file_exists($this->map_path)) {
            $this->map = require $this->map_path;
        }
    }

    public function compile_all() {
        foreach ($this->map as $cfg) {
            if ($cfg['type'] === 'file') {
                if (!$this->compile($cfg['to'])) {
                    return false;
                }
            } elseif ($cfg['type'] === 'dir') {
                if (!$this->compile_dir($cfg['from'], $cfg['to'])) {
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
        foreach ($this->map as $cfg) {
            if ($cfg['type'] === 'file') {
                if ($cfg['to'] === $dest) {
                    if (!is_array($cfg['from'])) {
                        $cfg['from'] = array(
                            $cfg['from'],
                        );
                    }
                    return $cfg;
                }
            } elseif ($cfg['type'] === 'dir') {
                $len = strlen($cfg['to']);
                if (strncmp($cfg['to'], $dest, $len) !== 0) {
                    continue;
                }
                if (isset($cfg['tail'])) {
                    if (!is_array($cfg['tail'])) {
                        $cfg['tail'] = array(
                            $cfg['tail'],
                        );
                    }
                    $match = false;
                    foreach ($cfg['tail'] as $v) {
                        if (substr($dest, -1 * strlen($v)) === $v) {
                            $match = true;
                            break;
                        }
                    }
                    if (!$match) {
                        continue;
                    }
                }
                $cfg['from'] = array(
                    $cfg['from'] . substr($dest, $len),
                );
                return $cfg;
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
        foreach ($cfg['from'] as $file) {
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
        foreach ($cfg['from'] as $file) {
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
        foreach ($cfg['from'] as $file) {
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
        foreach ($cfg['from'] as $file) {
            $full = WEB_ROOT . $file;
            if (\file_exists($full)) {
                $content = \file_get_contents($full);
                File::put_content($dest, $content, '', FILE_APPEND);
            }
        }
        return true;
    }

}
