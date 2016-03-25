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
    
    public function __get($name)
    {
        if (!isset($this->signals[$name])) {
            $this->signals[$name] = new Signal();
        }
        return $this->signals[$name];
    }
    
    public function __set($name, $signal)
    {
        $this->signals[$name] = $signal;
    }
    
    public function __call($name, $args)
    {
        if (isset($this->signals[$name])) {
            $signal = $this->signals[$name];
            return $signal->emit($name, $args);
        }
    }
    
    public function addEvent($names, & $slot, $class = 'Listener')
    {
        if ($class === 'Listener' || $class === 'RuptListener') {
            $class = __NAMESPACE__ . '\\' . $class;
        }
        $listener = new $class();
        $listener->addSlot($slot, $names);
        if (!is_array($names)) {
            $this->$names->attach($listener);
        } else {
            foreach ($names as $name) {
                $this->$name->attach($listener);
            }
        }
        return $this;
    }
}
