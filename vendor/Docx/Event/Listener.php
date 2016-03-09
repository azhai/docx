<?php
/**
 * This file is part of Docx.
 * 
 * Copyright (c) 2014 MIT License.
 */

namespace Docx\Event;

use SplSubject;
use SplObserver;
use Docx\Common;

/**
 * 监听者.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Listener implements SplObserver
{
    public $callback = null;    //实际执行的响应函数

    public function __construct($callback = null)
    {
        $this->callback = $callback;
    }

    /**
     * 无答复的触发响应.
     *
     * @param SplSubject $sender 发送者
     */
    public function update(SplSubject $sender)
    {
        $this->reply($sender->message, $sender);
    }

    /**
     * 有答复的执行响应.
     *
     * @param array $message 事件参数
     * @param mixed $sender  发送者
     *
     * @return mixed
     */
    public function reply(array &$message, $sender = null)
    {
        if ($this->callback) {
            return Common::execFunctionArray($this->callback, $message);
        }
    }
}
