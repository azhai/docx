<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Base;

use Docx\Common;


/**
 * 枚举类型.
 *
 * @author Ryan Liu <azhai@126.com>
 */
abstract class EnumType
{
    const __prefix = '';
    const __default = null;

    protected $name = '__default';
    protected $value = null;

    public function __construct($initial_value = '__default', $strict = false)
    {
        $this->value = self::getDefault();
        if (is_int($initial_value) && is_numeric($initial_value)) { //提供值
            $this->initByValue($initial_value, $strict);
        } elseif (is_string($initial_value)) { //提供名称
            $this->initByName($initial_value, $strict);
        }
    }

    public static function getPrefix()
    {
        return static::__prefix;
    }

    public static function getDefault()
    {
        return static::__default;
    }

    public function initByValue($value, $strict = false)
    {
        $consts = $this->getConstList(true);
        $key = array_search($value, $consts, $strict);
        if ($key !== false) {
            $this->name = $key;
            $this->value = $value;
        }
    }

    public function initByName($name = '__default', $strict = false)
    {
        if ($strict === false) {
            $name = strtoupper($name);
            $prefix = self::getPrefix();
            if ($prefix && $name !== '__default') {
                if (!Common::startsWith($name, $prefix)) { //补上前缀
                    $name = $prefix . $name;
                }
            }
        }
        $class = get_class($this);
        $value = constant($class . '::' . $name);
        if (!is_null($value)) {
            $this->name = $name;
            $this->value = $value;
        }
    }

    public function __toString()
    {
        return strval($this->getValue());
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getConstList($include_default = false)
    {
        $result = [];
        if ($include_default) {
            $result['__default'] = self::getDefault();
        }
        $names = $this->getConstants();
        if ($names && is_array($names)) {
            $class = get_class($this);
            foreach ($names as $name) {
                $result[$name] = constant($class.'::'.$name);
            }
        }

        return $result;
    }

    abstract public function getConstants();
}
