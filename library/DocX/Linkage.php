<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */


/**
 * 网址管理
 */
class DOCX_Linkage
{
    const URL_TYPE_QUERY = 0;       //参数
    const URL_TYPE_TAIL = 1;        //地址
    const URL_TYPE_REWRITE = 2;     //重写
    const URL_TYPE_AUTO = 9;        //根据url_prefix判断    
    protected $abs_prefix = false;
    protected $url_type = self::URL_TYPE_AUTO;
    protected $curr_url = false;
    protected $offset = 0;
    protected $index = 'index.php';
    protected $route = 'q';

    public function __construct($url_prefix, $url_type = self::URL_TYPE_AUTO)
    {
        $url_prefix = rtrim($url_prefix, '/');
        $this->url_type = intval($url_type);
        if ($this->isURLType('auto')) { //根据网址前缀判断类型
            $this->abs_prefix = $this->fixPrefix($url_prefix);
        } else {
            $this->abs_prefix = $url_prefix;
        }
    }
    
    public static function getConstant($name)
    {
        return constant(__CLASS__ . '::' . $name);
    }

    public static function getRawURL()
    {
        $raw_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return is_null($raw_url) ? '/' : urldecode($raw_url); //汉字逆向转码
    }
    
    public function isURLType($type = 'query')
    {
        $type_name = 'URL_TYPE_' . strtoupper($type);
        return $this->url_type === $this->getConstant($type_name);
    }
    
    public function fixPrefix($url_prefix)
    {
        $pattern = '!([a-zA-Z0-9_-]+\.php)!';
        if (preg_match($pattern, $url_prefix, $matches, PREG_OFFSET_CAPTURE)) {
            list($this->index, $position) = $matches[0];
            $pattern = '!^\?([a-zA-Z0-9_-]+)=!';
            $query = substr($url_prefix, $position + strlen($this->index));
            if (preg_match($pattern, $query, $matches)) {
                $this->url_type = self::URL_TYPE_QUERY;
                $this->route = $matches[1];
            } else {
                $this->url_type = self::URL_TYPE_TAIL;
            }
            return rtrim($url_prefix, '?&');
        } else {
            $this->url_type = self::URL_TYPE_REWRITE;
            return rtrim($url_prefix, '/');
        }
    }

    public function getCurrURL($rstrip = false)
    {
        if ($this->curr_url === false) {
            if ($this->isURLType('query')) {
                $this->curr_url = isset($_GET[$this->route]) ? $_GET[$this->route] : '';
                $this->offset = - 99;
            } else {
                $raw_url = self::getRawURL();
                if ($this->isURLType('rewrite')) {
                    $raw_url = str_replace('/' . $this->index, '/', $raw_url);
                    $this->offset = - 1;
                }
                $prelen = strlen($this->getAbsPrefix());
                //substr()陷阱，当string的长度等于start，将返回FALSE而不是''
                $this->curr_url = (strlen($raw_url) > $prelen) ? substr($raw_url, $prelen) : '';
            }
        }
        return $rstrip ? rtrim($this->curr_url, '/') : $this->curr_url;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getAbsPrefix()
    {
        return $this->abs_prefix;
    }

    public function getRelPrefix($curr_url = false)
    {
        if ($curr_url === false) {
            if ($this->isURLType('query')) {
                return '.';
            }
            $curr_url = $this->getCurrURL(false);
            $depth = substr_count($curr_url, '/') + $this->offset;
        } else {
            $depth = substr_count($curr_url, '/');
        }
        return ($depth > 0) ? rtrim(str_repeat('../', $depth), '/') : '.';
    }
    
    public function buildQuery($extra = '')
    {
        if (is_string($extra)) {
            parse_str($extra, $args);
            $extra = array();
        } else {
            $args = array();
        }
        $args = array_merge($_GET, $extra, $args);
        $query = '?';
        if ($this->isURLType('query') && isset($args[$this->route]) ) {
            $query = sprintf('?%s=%s&', $this->route, $args[$this->route]);
            unset($args[$this->route]);
        }
        return $query . (empty($args) ? '' : http_build_query($args) . '&');
    }
    
    public function exchangeDir($to_dir, $from_dir)
    {
        $from_url = self::getRawURL();
        if ($this->isURLType('query')) {
            $from_url = dirname($from_url);
        }
        list($same, $backward, $forward) = compare_pathes($to_dir, $from_dir);
        $to_url = null;
        if ($backward === 0) {
            $to_url = $from_url;
            if ($forward > 0) {
                $to_url .= substr($to_dir, strlen($from_dir));
            }
        } else if ($backward <= substr_count($from_url, '/')) {
            $to_url = rtrim(str_repeat('../', $backward), '/');
            $to_url .= substr($to_dir, strlen($same));
        }
        return $to_url;
    }
}
