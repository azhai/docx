<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Cache;

use Docx\Event\Listener;
use Docx\Event\RuptListener;


/**
 * 被缓存数据.
 *
 * @author Ryan Liu <azhai@126.com>
 *
 * //EXAMPLE:
 * $cache = new FileCache(sys_get_temp_dir(), '.json');
 * $cache_agent = $cache->getAgent();
 */
class Agent
{
    use \Docx\Event\Sender;

    protected $timeout = -1; //失效时间
    protected $data = null; //数据

    public function __construct($timeout = 0)
    {
        $this->setTimeout($timeout);
    }
    
    /**
     * 添加缓存
     */
    public function addCache(BaseCache& $cache)
    {
        $this->addEvent('read', $cache, 'RuptListener');
        $this->addEvent(['write', 'remove'], $cache);
        return $this;
    }

    /**
     * 设置超时时间.
     *
     * @param int $timeout <=0表示无限期
     *
     * @return this
     */
    public function setTimeout($timeout)
    {
        $timeout = intval($timeout);
        if ($timeout > 0) {
            $this->timeout = $timeout;
        } else {
            $this->timeout = -1;
        }

        return $this;
    }
    

    /**
     * 连接数据源.
     *
     * @param mixed $data 数据源
     *
     * @return this
     */
    public function connect(& $data)
    {
        $this->data = & $data;
        return $this;
    }

    /**
     * 获取缓存.
     *
     * @param mixed $default 默认值
     *
     * @return mixed 属性值
     */
    public function get($default = null)
    {
        $this->data = $this->read();
        return is_null($this->data) ? $default : $this->data;
    }

    /**
     * 设置缓存.
     *
     * @param mixed  $value 属性值
     *
     * @return mixed 属性值
     */
    public function put($data)
    {
        $this->data = $data;
        $this->write($data, $this->timeout);
        return $data;
    }

    /**
     * 清除缓存.
     *
     * @return bool
     */
    public function del()
    {
        $this->data = null;
        return $this->remove();
    }
}
