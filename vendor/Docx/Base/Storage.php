<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Base;

use ArrayAccess;
use Countable;


/**
 * 储存类.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Storage implements Countable, ArrayAccess
{
    protected $data = [];
    protected $insensitive = false; //区分大小写

    /**
     * 构造函数
     */
    public function __construct($data = null, $insensitive = false)
    {
        $this->insensitive = $insensitive;
        $this->update($data);
    }

    public function update($data)
    {
        if ($data instanceof self) {
            $data = $data->data;
        } else {
            $data = empty($data) ? [] : (array)$data;
        }
        if ($this->insensitive) {//将对象的key都改为小写
            $data = array_change_key_case($data);
        }
        $this->data = array_merge($this->data, $data);
    }

    public function count()
    {
        return count($this->data);
    }

    public function offsetExists($offset)
    {
        if ($this->insensitive) {
            $offset = strtolower($offset);
        }
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        if ($this->insensitive) {
            $offset = strtolower($offset);
        }
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if ($this->insensitive) {
            $offset = strtolower($offset);
        }
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if ($this->insensitive) {
            $offset = strtolower($offset);
        }
        unset($this->data[$offset]);
    }

    /**
     * 读取配置项
     */
    public function getItem($name, $default = null)
    {
        $item = @$this->offsetGet($name);
        return is_null($item) ? $default : $item;
    }

    /**
     * 读取数组类配置项
     */
    public function getArray($name, array $default = [])
    {
        $data = $this->getItem($name);
        return is_array($data) ? $data : $default;
    }

    /**
     * 读取配置区
     */
    public function getSection($name)
    {
        $data = $this->getArray($name, []);
        return new self($data);
    }

    /**
     * 读取配置区，并缓存起来
     */
    public function getSectionOnce($name)
    {
        $data = $this->getItem($name);
        if (!($data instanceof self)) {
            $data = new self($data);
            $this->offsetSet($name, $data);
        }
        return $data;
    }
}
