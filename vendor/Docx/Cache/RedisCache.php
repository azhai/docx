<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Cache;

use Docx\Common;

/**
 * Redis文件缓存.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class RedisCache extends BaseCache
{
    const SHAPE_INT = 'int';
    const SHAPE_FLOAT = 'float';
    const SHAPE_STRING = 'string';
    const SHAPE_ARRAY = 'array';
    const SHAPE_OBJECT = 'object';
    const SHAPE_DATETIME = 'datetime';

    protected $redis = null;
    protected $shape = '';      //数据类型

    public function __construct($redis, $shape = 'string')
    {
        $this->redis = $redis;
        $this->shape = $shape;
    }

    /**
     * 准备文件、加载扩展、连接服务
     *
     * @param string $name 键名
     *
     * @return bool 是否成功
     */
    public function prepare($name)
    {
        try {
            $this->redis->ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
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
        $result = null;
        list($action, $method) = $message;
        if ($action === self::OP_CUSTOM) {
            $args = array_slice($message, 2);
            $result = Common::execMethodArray($this, $method, $args);
        } else {
            $result = parent::reply($message, $sender);
        }

        return $result;
    }

    /**
     * 读操作.
     *
     * @param string $name 键名
     *
     * @return mixed 对应值
     */
    public function read($name)
    {
        $this->prepare($name);
        if ($this->shape === self::SHAPE_OBJECT) {
            $data = $this->redis->hGetAll($name);
        } elseif ($this->shape === self::SHAPE_ARRAY) {
            $data = $this->redis->lRange($name, 0, -1);
        } else {
            $data = $this->redis->get($name);
        }

        return $data;
    }

    /**
     * 写操作.
     *
     * @param string $name    键名
     * @param mixed  $value   对应值
     * @param int    $timeout 缓存时间
     *
     * @return bool 是否成功
     */
    public function write($name, $value, $timeout = 0)
    {
        $this->prepare($name);
        if ($this->shape === self::SHAPE_OBJECT) {
            foreach ($value as $key => $part) {
                $this->redis->hSet($name, $key, $part);
            }
        } elseif ($this->shape === self::SHAPE_ARRAY) {
            foreach ($value as $part) {
                $this->redis->lPush($name, $part);
            }
        } else {
            $this->redis->set($name, $value);
        }
        if ($timeout > 0) {
            $this->redis->expire($name, $timeout);
        }

        return true;
    }

    /**
     * 删除操作.
     *
     * @param string $name 键名
     *
     * @return bool 是否成功
     */
    public function remove($name)
    {
        $this->prepare($name);
        $effects = $this->redis->del($name);

        return $effects > 0;
    }

    public function incre($name, $step = 1)
    {
        $this->prepare($name);
        $step = abs($step);
        if ($step === 1) {
            return $this->redis->incre($key);
        } else {
            return $this->redis->increBy($key, $step);
        }
    }

    public function decre($name, $step = 1)
    {
        $this->prepare($name);
        $step = abs($step);
        if ($step === 1) {
            return $this->redis->decre($key);
        } else {
            return $this->redis->decreBy($key, $step);
        }
    }
}
