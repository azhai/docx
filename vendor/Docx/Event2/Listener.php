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
    
    public function getSlot($name)
    {
        if (isset($this->slots[$name])) {
            return $this->slots[$name];
        } else if (method_exists($this, $name)) {
            return $this;
        }
    }

    /**
     * 安装插件并注册方法
     */
    public function addPlugin(& $plugin, $name)
    {
        $this->slots[$name] = & $plugin;
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
        $plugin = $this->getSlot($name);
        return Common::execMethodArray($plugin, $name, $signal->args);
    }
}
