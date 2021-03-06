<?php
/**
 * This file is part of Docx.
 * 
 * Copyright (c) 2014 MIT License.
 */

namespace Docx\Event;

use \SplSubject;
use \SplObserver;
use Docx\Common;


/**
 * 监听器.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Listener implements SplObserver
{
    protected $slots = [];

    /**
     * 安装槽并注册方法
     */
    public function addSlot(& $slot, $names)
    {
        if (!is_array($names)) {
            $names = array_slice(func_get_args(), 1);
        }
        foreach ($names as $name) {
            $this->slots[$name] = & $slot;
        }
        return $this;
    }

    /**
     * 触发响应.
     *
     * @param SplSubject $signal 信号
     */
    public function update(SplSubject $signal)
    {
        $name = $signal->getName();
        if (isset($this->slots[$name])) {
            $slot = $this->slots[$name];
            return Common::execMethodArray($slot, $name, $signal->args);
        }
    }
}
