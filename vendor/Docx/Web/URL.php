<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Web;

use Docx\Common;


/**
 * 网址
 *
 * @author Ryan Liu <azhai@126.com>
 */
class URL
{
    const PHP_INDEX_FILE = 'index.php';
    
    protected $route_key = null;
    protected $path = false;
    public $rule = '';
    public $args = [];

    /**
     * 构造函数.
     */
    public function __construct($route_key = null)
    {
        $this->route_key = $route_key;
    }
    
    public function getRouteKey()
    {
        return $this->route_key;
    }

    /**
     * 获取当前Path Info
     *
     * @return string
     */
    public function parseURI()
    {
        $path = Request::getURIPath();
        $name = Request::getInput('SERVER', 'SCRIPT_NAME');
        if (empty($path) || $path === $name) {
            return '';
        }
        $head = substr($name, 0, - strlen(self::PHP_INDEX_FILE));
        if ($path === $head) {
            $path = '';
        } else if (Common::startsWith($path, $name)) {
            $path = substr($path, strlen($name));
        } else if (Common::startsWith($path, $head)) {
            $path = substr($path, strlen($head) - 1);
        }
        return trim($path);
    }

    /**
     * 获取当前Path Info
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->path === false) {
            if ($this->route_key) {
                $this->path = Request::pop($this->route_key, '/');
            } else {
                $this->path = $this->parseURI();
            }
        }
        return $this->path;
    }
    
    public function getDepth()
    {
        if ($this->route_key) {
            return -1;
        } else {
            $count = substr_count($this->getPath(), '/');
            return $count > 0 ? $count - 1 : 0;
        }
    }

    /**
     * 获取网址前缀
     */
    public function getPrefix()
    {
        if ($this->route_key) {
            return sprintf('?%s=/', $this->route_key);
        } else if ($depth = $this->getDepth()) {
            return str_repeat('../', $depth);
        } else {
            return self::PHP_INDEX_FILE . '/';
        }
    }

    /**
     * 获取首页地址
     */
    public function getHome()
    {
        if ($this->route_key) {
            $home_url = sprintf('?%s=/', $this->route_key);
        } else {
            $home_url = str_repeat('../', $this->getDepth() + 3);
        }
        return $home_url;
    }
    
    /*public function urlFor()
    {
        $args = func_get_args();
        if ($args) {
            $args = array_merge($this->args, array_reverse($args));
            $replaces = $args;
            $uri = preg_replace($this->rule, $replaces, $this->path);
            $uri = $this->prefix . $uri;
        } else {
            $uri = $this->url;
        }
        return ltrim($this->url, '/');
    }*/
}
