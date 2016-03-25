<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Log;


/**
 * 日志记录器.
 */
abstract class BaseLogger
{
    protected $logging = null;
    
    /**
     * 返回日志对象
     *
     * @return \Docx\Log\Logging
     */
    public function getLogging($name = 'access', $level = 'DEBUG')
    {
        if (!$this->logging) {
            $this->logging = new Logging($name, $level);
            $this->logging->addEvent('append', $this);
        }
        return $this->logging;
    }
    
    abstract public function append($name, $content, array $extra);
}
