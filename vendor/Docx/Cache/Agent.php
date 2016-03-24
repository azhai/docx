<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Cache;

use Docx\Event\Signal;


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
    
    const OP_READ = 'read';
    const OP_WRITE = 'write';
    const OP_REMOVE = 'remove';

    protected $timeout = -1; //失效时间
    protected $data = null; //数据

    public function __construct($timeout = 0)
    {
        $this->setTimeout($timeout);
    }
    
    /**
     * 初始化信号
     */
    public function initSignals(& $cache)
    {
        $this->addEvent(self::OP_READ, $cache);
        $this->getSignal(self::OP_READ)->setMode(Signal::FIRST_SUCCESS);
        $this->addEvent(self::OP_WRITE, $cache);
        $this->addEvent(self::OP_REMOVE, $cache);
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
        $this->data = $this->emit(self::OP_READ);
        return is_null($this->data) ? $default : $this->data;
    }

    /**
     * 设置缓存.
     *
     * @param mixed  $value 属性值
     *
     * @return mixed 属性值
     */
    public function put($data = null)
    {
        $data = is_null($data) ? $this->data : $data;
        $this->emit(self::OP_WRITE, $data, $this->timeout);
        return $data;
    }

    /**
     * 清除缓存.
     *
     * @return bool
     */
    public function remove()
    {
        $this->data = null;
        return $this->emit(self::OP_REMOVE);
    }
}
