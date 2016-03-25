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
    
    public function getSignal($name, $create = true)
    {
        if (isset($this->signals[$name])) {
            return $this->signals[$name];
        } else if ($create) {
            $this->signals[$name] = new Signal($name);
            return $this->signals[$name];
        }
    }
    
    public function addListener(& $listener, $name, $mode = Signal::ONE_BY_ONE)
    {
        $signal = $this->getSignal($name, true);
        $signal->setMode($mode)->attach($listener);
        return $this;
    }
    
    public function emit($name, array $args = [])
    {
        $signal = $this->getSignal($name, false);
        if ($signal) {
            $signal->sender = $this;
            $signal->args = $args;
            return $signal->notify();
        }
    }
}
