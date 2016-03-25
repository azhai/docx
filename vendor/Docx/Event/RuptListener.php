<?php
/**
 * This file is part of Docx.
 * 
 * Copyright (c) 2014 MIT License.
 */

namespace Docx\Event;

use \SplSubject;
use Docx\Event\EventInterrupt;


/**
 * 短路监听器.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class RuptListener extends Listener
{
    /**
     * 触发响应，成功时抛出中断.
     *
     * @param SplSubject $signal 信号
     */
    public function update(SplSubject $signal)
    {
        $signal->result = parent::update($signal);
        if ($signal->result) {
            throw new EventInterrupt();
        }
    }
}
