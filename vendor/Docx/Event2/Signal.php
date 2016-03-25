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
    const ONE_BY_ONE = 1;
    const FIRST_SUCCESS = 2;
    
    protected $listeners = [];  //监听器列表
    protected $name = '';  //信号名
    protected $mode = 0;  //类型
    public $sender = null;  //发送者
    public $args = [];  //参数表
    
    /**
     * 构造函数.
     *
     * @param SplObserver $listener 监听器对象
     */
    public function __construct($name, $mode = self::ONE_BY_ONE)
    {
        $this->name = $name;
        $this->setMode($mode);
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
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
     * 检测结果，决定是否要继续.
     *
     * @param mixed $result 监听结果
     * @param int $key 监听器索引
     */
    public function check($result, $key)
    {
        if ($this->mode === self::FIRST_SUCCESS && !is_null($result)) {
            throw new EventInterrupt();
        }
    }

    /**
     * 通知所有监听器.
     */
    public function notify()
    {
        $result = null;
        foreach ($this->listeners as $key => &$listener) {
            try {
                $result = $listener->update($this);
                $this->check($result, $key);
            } catch (EventInterrupt $e) {
                break;
            } catch (EventIgnore $e) {
                continue;
            }
        }
        return $result;
    }
}
