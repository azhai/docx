<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Cache;


/**
 * 缓存客户端.
 *
 * @author Ryan Liu <azhai@126.com>
 */
abstract class BaseCache
{
    protected $agent = null;
    
    /**
     * 返回被缓存对象
     *
     * @return object
     */
    public function getAgent($timeout = 0)
    {
        if (!$this->agent) {
            $this->agent = new Agent($timeout);
            $this->agent->addCache($this);
        }
        return $this->agent;
    }

    public function prepare()
    {
    }
    
    abstract public function read();
    abstract public function write($value, $timeout = 0);
    abstract public function remove();
}
