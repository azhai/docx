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
    public $name = '';
    public $shape = '';      //数据类型

    public function __construct(& $redis, $name, $shape = 'string')
    {
        $this->redis = $redis;
        $this->name = $name;
        $this->shape = $shape;
    }
    
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * 准备文件、加载扩展、连接服务
     *
     * @return bool 是否成功
     */
    public function prepare()
    {
        try {
            $this->redis->ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 读操作.
     *
     * @return mixed 对应值
     */
    public function read()
    {
        $this->prepare();
        if ($this->shape === self::SHAPE_OBJECT) {
            $data = $this->redis->hGetAll($this->name);
        } elseif ($this->shape === self::SHAPE_ARRAY) {
            $data = $this->redis->lRange($this->name, 0, -1);
        } else {
            $data = $this->redis->get($this->name);
        }
        return $data;
    }

    /**
     * 写操作.
     *
     * @param mixed  $value   对应值
     * @param int    $timeout 缓存时间
     *
     * @return bool 是否成功
     */
    public function write($value, $timeout = 0)
    {
        $this->prepare();
        if ($this->shape === self::SHAPE_OBJECT) {
            foreach ($value as $key => $part) {
                $this->redis->hSet($this->name, $key, $part);
            }
        } elseif ($this->shape === self::SHAPE_ARRAY) {
            foreach ($value as $part) {
                $this->redis->lPush($this->name, $part);
            }
        } else {
            $this->redis->set($this->name, $value);
        }
        if ($timeout > 0) {
            $this->redis->expire($this->name, $timeout);
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
    public function remove()
    {
        $this->prepare();
        $effects = $this->redis->del($this->name);
        return $effects > 0;
    }

    public function incre($step = 1)
    {
        $this->prepare();
        $step = abs($step);
        if ($step === 1) {
            return $this->redis->incre($this->name);
        } else {
            return $this->redis->increBy($this->name, $step);
        }
    }

    public function decre($step = 1)
    {
        $this->prepare();
        $step = abs($step);
        if ($step === 1) {
            return $this->redis->decre($this->name);
        } else {
            return $this->redis->decreBy($this->name, $step);
        }
    }
}
