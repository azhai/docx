<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Base;

use Docx\Event\Sender;
use Docx\Event\Listener;

/**
 * 行为类.
 *
 * @author Ryan Liu <azhai@126.com>
 */
trait Behavior
{
    protected $event_sender = [];   //事件集合

    /**
     * 获取属性.
     *
     * @param string $name 属性名
     *
     * @return mixed 属性值
     */
    public function prop($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    /**
     * 设置属性.
     *
     * @param string $name  属性名
     * @param mixed  $value 属性值
     *
     * @return object
     */
    public function setProp($name, $value)
    {
        $this->$name = $value;

        return $this;
    }

    /**
     * 获取事件.
     *
     * @param string $name 事件名
     *
     * @return mixed 事件对象
     */
    public function event($name)
    {
        if (isset($this->event_sender[$name])) {
            return $this->event_sender[$name];
        }
    }

    /**
     * 添加事件响应.
     *
     * @param string $name     事件名
     * @param type   $callback
     *
     * @return object
     */
    public function addEvent($name, $callback)
    {
        if (!$this->event($name)) {
            $this->event_sender[$name] = new Sender();
        }
        $listener = new Listener($callback);
        $this->event_sender[$name]->attach($listener);

        return $this;
    }
}
