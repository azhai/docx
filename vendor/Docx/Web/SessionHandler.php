<?php

/**
 * This file is part of Docx.
 * 
 * Copyright (c) 2014 MIT License
 * Code From:
 *      http://www.sitepoint.com/saving-php-sessions-in-redis/
 */

namespace Docx\Web;

use Docx\Cache\RedisCache;
use \SessionHandlerInterface;

/**
 * Redis会话保存管理器
 * Notice:
 *  传统的文件会话保存管理器，在会话开始的时候会给会话数据文件加锁。
 * 常用方法： setExpire(), share(), update()
 */
class SessionHandler implements SessionHandlerInterface
{

    const PREFIX = 'PHPSESSID:';

    protected static $instance = null; //单例
    protected $cache = null; //存储容器
    protected $timeout = 0;  //失效时间

    public function __construct(RedisCache& $cache, $timeout = 1800)
    {
        $this->cache = $cache;
        $this->timeout = intval($timeout);
        if (version_compare(PHP_VERSION, '6.0.0') < 0) {
            session_set_save_handler($this); //PHP7无法使用
        }
        @session_start();
    }
    
    public static function getInstance($timeout = 1800)
    {
        if (!self::$instance && class_exists('\\Redis')) {
            $cache = new RedisCache(new \Redis(), 'object');
            if ($cache->prepare('ping')) {
                self::$instance = new self($cache, $timeout);
            }
        }
        return self::$instance;
    }

    public function getSessID($session_id)
    {
        return self::PREFIX . $session_id;
    }

    public function open($save_path, $name)
    {
        // No action necessary because connection is injected
        // in constructor and arguments are not applicable.
    }

    public function close()
    {
    }

    public function read($session_id)
    {
        return $this->cache->read($this->getSessID($session_id));
    }

    public function write($session_id, $session_data)
    {
        $this->cache->write($this->getSessID($session_id), $session_data);
    }

    public function destroy($session_id)
    {
        return $this->cache->remove($this->getSessID($session_id));
    }

    public function gc($max_lifetime)
    {
        // no action necessary because using EXPIRE
    }

}
