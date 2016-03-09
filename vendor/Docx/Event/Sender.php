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
 * 发送者.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Sender implements SplSubject
{
    protected $listeners = [];  //监听器列表
    public $message = [];       //最后一次调用的参数表

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
     * 通知所有监听器，但不收集结果.
     */
    public function notify()
    {
        foreach ($this->listeners as &$listener) {
            try {
                $listener->update($this);
            } catch (EventFailed $e) {
                continue;
            }
        }
    }

    /**
     * 通知所有监听器.
     *
     * @return array 结果集
     */
    public function emit()
    {
        $this->message = func_get_args();
        $answers = [];
        foreach ($this->listeners as $i => &$listener) {
            try {
                $answers[$i] = $listener->reply($this->message, $this);
            } catch (EventFailed $e) {
                continue;
            }
        }

        return $answers;
    }

    /**
     * 通知监听器，直到有一个执行成功
     *
     * @return mixed 单个结果
     */
    public function emitOnce()
    {
        $this->message = func_get_args();
        $answer = null;

        foreach ($this->listeners as &$listener) {
            try {
                $answer = $listener->reply($this->message, $this);
            } catch (EventFailed $e) {
                continue;
            }
            break; //只执行一次
        }

        return $answer;
    }
}
