<?php
/**
 * This file is part of Docx.
 * 
 * Copyright (c) 2014 MIT License.
 */

namespace Docx\Event;


/**
 * 信号.
 *
 * @author Ryan Liu <azhai@126.com>
 */
trait Sender
{
    protected $signals = [];
    
    public function getSignal($name, $create = false)
    {
        if (isset($this->signals[$name])) {
            return $this->signals[$name];
        } else if ($create) {
            $this->signals[$name] = new Signal($name, $this);
            return $this->signals[$name];
        }
    }
    
    public function addEvent($name, $listener)
    {
        $signal = $this->getSignal($name, true);
        $signal->attach($listener);
        return $this;
    }
    
    public function emit($name)
    {
        $signal = $this->getSignal($name, false);
        if ($signal) {
            $signal->args = array_slice(func_get_args(), 1);
            return $signal->notify();
        }
    }
}
