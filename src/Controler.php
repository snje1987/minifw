<?php

namespace Org\Snje\Minifw;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

class Controler {

    const DEFAULT_FUNCTION = '';
    const JSON_CALL_DIE = 0;
    const JSON_CALL_RETURN = 1;
    const JSON_CALL_REDIRECT = 2;
    const JSON_ERROR_OK = 0;
    const JSON_ERROR_UNKNOWN = -1;

    public static $cache_time;

    /**
     *
     * @var FW\Config
     */
    protected $config;
    protected $theme;

    public static function send_download_header($filename) {
        header("Accept-Ranges: bytes");
        $ua = isset($_SERVER["HTTP_USER_AGENT"]) ? strval($_SERVER["HTTP_USER_AGENT"]) : '';
        if (strpos($ua, "MSIE") || (strpos($ua, 'rv:11.0') && strpos($ua, 'Trident'))) {
            $encoded_filename = urlencode($filename);
            $encoded_filename = str_replace("+", "%20", $encoded_filename);
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } elseif (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
    }

    public function __construct() {
        $this->config = Config::get();
        $this->theme = null;
    }

    /**
     * Call controler function according to the given name.
     * @param type $func function name
     * @param type $args args.
     */
    public function dispatch($function, $args) {
        $class = new \ReflectionClass(static::class);
        if ($function == '') {
            $function = $class->getConstant('DEFAULT_FUNCTION');
        }
        $function = str_replace('.', '', $function);
        if ($function == '') {
            throw new Exception('No function specify.');
        }
        $function = 'c_' . $function;
        if (!$class->hasMethod($function)) {
            throw new Exception('Function not exists.');
        }

        $func = $class->getMethod($function);
        $func->setAccessible(true);
        $func->invoke($this, $args);
    }

    public function show_msg($content, $title = '', $link = '') {
        if (Tpl::exist('/msg', $this->theme)) {
            Tpl::assign('content', $content);
            Tpl::assign('title', $title);
            Tpl::assign('link', $link);
            Tpl::display('/msg', $this, $this->theme);
        } else {
            echo <<<TEXT
<h1>{$title}</h1>
<p>{$content}</p>
<p><a href="{$link}">返回</a></p>
TEXT;
        }
    }

    public function show_404() {
        header("HTTP/1.1 404 Not Found");
        header("status: 404 not found");
        if (Tpl::exist('/404', $this->theme)) {
            Tpl::display('/404', $this, $this->theme);
        }
    }

    public function redirect($url) {
        if (!headers_sent()) {
            header('Location:' . $url);
        } else {
            echo '<script type="text/javascript">window.location="' . $url . '";</script>';
        }
    }

    public function show_301($url) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);
    }

    public function readfile_with_304($file, $fsencoding = '') {
        $full = File::conv_to($file, $fsencoding);
        $mtime = \filemtime($full);
        $expire = gmdate('D, d M Y H:i:s', time() + self::$cache_time) . ' GMT';
        header('Expires: ' . $expire);
        header('Pragma: cache');
        header('Cache-Control: max-age=' . self::$cache_time);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
        header('Etag: ' . $mtime);
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $mtime) {
            header('HTTP/1.1 304 Not Modified');
        } else {
            File::readfile($full);
        }
    }

    public function download_file($path, $filename, $fsencoding = '') {
        $full = File::conv_to($path, $fsencoding);
        if (!file_exists($full)) {
            $this->show_404();
        }
        self::send_download_header($filename);
        File::readfile($full);
    }

    public function referer($default = null) {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $url = strval($_SERVER['HTTP_REFERER']);
        } else {
            $url = $default;
        }
        return $url;
    }

    public function cur_url() {
        $url = 'http';
        if ($_SERVER['HTTPS'] == 'on') {
            $url .= 's';
        }
        $url .= '://' . $_SERVER['SERVER_NAME'];
        if ($_SERVER['SERVER_PORT'] != '80') {
            $url .= ':' . $_SERVER['SERVER_PORT'];
        }
        return $url . $_SERVER['REQUEST_URI'];
    }

    public function json_call($post, $call, $mode = self::JSON_CALL_DIE) {
        $ret = [
            'error' => self::JSON_ERROR_UNKNOWN,
            'returl' => '',
        ];
        try {
            $value = false;
            if (is_callable($call)) {
                $value = call_user_func($call, $post);
            }
            if (is_array($value)) {
                $ret = $value;
                if (!isset($ret['error'])) {
                    $ret['error'] = self::JSON_ERROR_OK;
                }
                if (!isset($ret['returl'])) {
                    if (is_array($post) && isset($post['returl'])) {
                        $ret['returl'] = urldecode(strval($post['returl']));
                    } else {
                        $ret['returl'] = '';
                    }
                }
            } elseif ($value === true) {
                $ret['error'] = self::JSON_ERROR_OK;
                if (is_array($post) && isset($post['returl'])) {
                    $ret['returl'] = urldecode(strval($post['returl']));
                }
            } else {
                $ret['msg'] = '操作失败';
            }
        } catch (Exception $e) {
            if (DEBUG === 1) {
                $ret['msg'] = '[' . $e->getFile() . ':' . $e->getLine() . ']' . $e->getMessage();
            } else {
                $ret['msg'] = $e->getMessage();
            }
        } catch (\Exception $e) {
            if (DEBUG === 1) {
                $ret['msg'] = '[' . $e->getFile() . ':' . $e->getLine() . ']' . $e->getMessage();
            } else {
                $ret['msg'] = '操作失败';
            }
        }
        if ($mode == self::JSON_CALL_REDIRECT) {
            // @codeCoverageIgnoreStart
            if ($ret['returl'] != '') {
                $this->redirect($ret['returl']);
            } else {
                $this->redirect($this->referer('/'));
            }
            die(0);
            // @codeCoverageIgnoreEnd
        } elseif ($mode == self::JSON_CALL_DIE) {
            // @codeCoverageIgnoreStart
            die(\json_encode($ret, JSON_UNESCAPED_UNICODE));
            // @codeCoverageIgnoreEnd
        } else {
            return $ret;
        }
    }

    /**
     *
     * @param FW\DB $db
     * @param array $post
     * @param callback $call
     * @param int $mode
     */
    public function sync_call($db, $post, $call, $mode = self::JSON_CALL_DIE) {
        $db->begin();
        $ret = $this->json_call($post, $call, self::JSON_CALL_RETURN);
        if ($ret['error'] === self::JSON_ERROR_OK) {
            $db->commit();
        } else {
            $db->rollback();
        }
        if ($mode == self::JSON_CALL_REDIRECT) {
            // @codeCoverageIgnoreStart
            if ($ret['returl'] != '') {
                $this->redirect($ret['returl']);
            } else {
                $this->redirect($this->referer('/'));
            }
            die(0);
            // @codeCoverageIgnoreEnd
        } elseif ($mode == self::JSON_CALL_DIE) {
            // @codeCoverageIgnoreStart
            die(\json_encode($ret, JSON_UNESCAPED_UNICODE));
            // @codeCoverageIgnoreEnd
        } else {
            return $ret;
        }
    }

}

Controler::$cache_time = Config::get()->get_config('main', 'cache', 3600);
