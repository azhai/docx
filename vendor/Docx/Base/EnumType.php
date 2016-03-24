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

    public function __construct($initial = '__default')
    {
        $value = null;
        if ($initial && $initial !== '__default') {
            $name = static::__prefix . strtoupper($initial);
            $value = @constant(get_class($this) . '::' . $name);
        }
        if (is_null($value)) {
            $this->value = static::__default;
        } else {
            $this->name = $name;
            $this->value = $value;
        }
    }

    public function __toString()
    {
        return strval($this->getValue());
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function getName($with_prefix = false)
    {
        if ($with_prefix || '__default' === $this->name) {
            return $this->name;
        } else {
            return substr($this->name, static::__prefix);
        }
    }

    public function isDefault()
    {
        return $this->getValue() === static::__default;
    }

    abstract public function getConstants();
}
