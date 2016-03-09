<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Cache;

use Docx\Event\EventFailed;
use Docx\Event\Listener;

/**
 * 缓存客户端.
 *
 * @author Ryan Liu <azhai@126.com>
 */
abstract class BaseCache extends Listener
{
    const OP_READ = 0;
    const OP_WRITE = 1;
    const OP_REMOVE = 2;
    const OP_CUSTOM = 3;

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
        $result = null;
        list($action, $name) = $message;
        switch ($action) {
            case self::OP_READ:
                $result = $this->read($name);
                break;
            case self::OP_WRITE:
                $value = $message[2];
                $timeout = $message[3];
                $result = $this->write($name, $value, $timeout);
                break;
            case self::OP_REMOVE:
                $result = $this->remove($name);
                break;
            default:
                throw new EventFailed('Operation not supported!');
        }

        return $result;
    }

    abstract public function prepare($name);
    abstract public function read($name);
    abstract public function write($name, $value, $timeout = 0);
    abstract public function remove($name);
}
