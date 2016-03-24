<?php
/**
 * This file is part of Docx.
 * 
 * Copyright (c) 2014 MIT License.
 */

namespace Docx\Event;

use SplSubject;
use SplObserver;

/**
 * 信号.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Signal implements SplSubject
{
    const FIRST_SUCCESS = 1;
    const ONE_BY_ONE = 2;
    
    protected $listeners = [];  //监听器列表
    public $sender = null;  //发送者
    public $mode = 0;  //类型
    public $name = '';  //动作名
    public $args = [];  //参数表
    
    /**
     * 构造函数.
     *
     * @param SplObserver $listener 监听器对象
     */
    public function __construct($name, & $sender)
    {
        $this->name = $name;
        $this->sender = $sender;
        $this->setMode(self::ONE_BY_ONE);
    }
    
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

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

    /**
     * 通知所有监听器.
     */
    public function notify()
    {
        $result = null;
        foreach ($this->listeners as & $listener) {
            try {
                $result = $listener->update($this);
                if ($this->mode === self::FIRST_SUCCESS
                                && !is_null($result)) {
                    break;
                }
            } catch (EventInterrupt $e) {
                break;
            } catch (EventIgnore $e) {
                continue;
            }
        }
        return $result;
    }
}
