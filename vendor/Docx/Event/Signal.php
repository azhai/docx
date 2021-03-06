<?php
/**
 * This file is part of Docx.
 * 
 * Copyright (c) 2014 MIT License.
 */

namespace Docx\Event;

use \SplSubject;
use \SplObserver;


/**
 * 信号.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Signal implements SplSubject
{
    protected $listeners = [];  //监听器列表
    protected $name = '';       //动作名
    public $args = [];          //参数表
    public $result = null;      //结果

    /**
     * 添加监听器.
     *
     * @param SplObserver $listener 监听器对象
     */
    public function attach(SplObserver $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * 移除监听器.
     *
     * @param SplObserver $listener 监听器对象
     */
    public function detach(SplObserver $listener)
    {
        $key = array_search($listener, $this->listeners, true);
        if ($key !== false) {
            $this->detachKey($key);
        }
    }

    /**
     * 通过索引移除监听器.
     *
     * @param int $key 监听器索引
     */
    public function detachKey($key)
    {
        unset($this->listeners[$key]);
    }
    
    public function getName()
    {
        return $this->name;
    }

    /**
     * 通知所有监听器.
     */
    public function notify()
    {
        foreach ($this->listeners as $key => &$listener) {
            try {
                $listener->update($this);
            } catch (EventInterrupt $e) {
                break;
            }
        }
    }
    
    public function emit($name, array $args = [])
    {
        $this->name = $name;
        $this->args = $args;
        $this->notify();
        return $this->result;
    }
}
