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
 * 监听器.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Listener implements SplObserver
{
    protected $slots = [];

    /**
     * 安装插件并注册方法
     */
    public function addPlugin($method, & $plugin)
    {
        $this->slots[strtolower($method)] = $plugin;
        return $this;
    }

    /**
     * 触发响应.
     *
     * @param SplSubject $signal 信号
     */
    public function update(SplSubject $signal)
    {
        $name = strtolower($signal->name);
        $args = $signal->args;
        if (isset($this->slots[$name])) {
            $plugin = $this->slots[$name];
        } else {
            $plugin = $this;
        }
        return Common::execMethodArray($plugin, $name, $args);
    }
}
