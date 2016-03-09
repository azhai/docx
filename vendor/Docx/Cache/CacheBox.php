<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Cache;

use Docx\Event\Sender;

/**
 * 被缓存数据.
 *
 * @author Ryan Liu <azhai@126.com>
 *
 * //EXAMPLE:
 * $cache = new CacheBox();
 * $cache->attach(new FileCache(sys_get_temp_dir(), '.json'));
 */
class CacheBox extends Sender
{
    use \Docx\Base\Behavior;

    protected $timeout = -1; //失效时间

    public function __construct(array $data = [], $timeout = 0)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        $this->setTimeout($timeout);
    }

    /**
     * 设置超时时间.
     *
     * @param int $timeout <=0表示无限期
     *
     * @return \Docx\Cache\CacheBox
     */
    public function setTimeout($timeout)
    {
        $timeout = intval($timeout);
        if ($timeout > 0) {
            $this->timeout = $timeout;
        } else {
            $this->timeout = -1;
        }

        return $this;
    }

    /**
     * 获取缓存.
     *
     * @param string $name 属性名
     * @param mixed $default 默认值
     *
     * @return mixed 属性值
     */
    public function get($name, $default = null)
    {
        $result = $this->emitOnce(BaseCache::OP_READ, $name);
        if (is_null($result)) {
            $result = $default;
        }
        $this->setProp($name, $result);
        return $result;
    }

    /**
     * 设置缓存.
     *
     * @param string $name  属性名
     * @param mixed  $value 属性值
     *
     * @return object
     */
    public function put($name, $value)
    {
        if (is_null($value)) {
            $this->emit(BaseCache::OP_REMOVE, $name);
        } else {
            $this->emit(BaseCache::OP_WRITE, $name, $value, $this->timeout);
        }

        return $this->setProp($name, $value);
    }
}
