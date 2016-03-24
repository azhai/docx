<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Cache;

use Docx\Event\Listener;


/**
 * 缓存客户端.
 *
 * @author Ryan Liu <azhai@126.com>
 */
abstract class BaseCache extends Listener
{
    protected $agent = null;
    
    /**
     * 返回被缓存对象
     *
     * @return \Docx\Cache\Agent
     */
    public function getAgent($timeout = 0)
    {
        if (!$this->agent) {
            $this->agent = new Agent($timeout);
            $this->agent->initSignals($this);
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
