<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Log;

use Docx\Common;
use Docx\Event\Sender;
use Docx\Web\Request;

/**
 * 日志.
 *
 * @author Ryan Liu <azhai@126.com>
 *
 * //EXAMPLE:
 * $logging = new Logging('test');
 * $logging->attach(new FileLogger('./logs'));
 */
class Logging extends Sender
{
    protected $name = '';
    protected $threshold = 0;

    /**
     * 构造函数，设置过滤级别.
     *
     * @param string $threshold 过滤级别（低于本级别的不记录）
     */
    public function __construct($name = 'default', $level = 'DEBUG')
    {
        $this->name = $name;
        $log_level = new LogLevel($level);
        $this->threshold = $log_level->getValue();
    }

    /**
     * 比较两个过滤级别的重要程度.
     *
     * @param int $level 消息级别
     *
     * @return bool 消息级别持平或更重要
     */
    public function allowLevel($level)
    {
        $log_level = new LogLevel($level);

        return $log_level->getValue() <= $this->threshold;
    }

    public static function getClientIP()
    {
        return Request::getClientIP();
    }

    public static function format($message, array $context = [])
    {
        $content = is_null($message) ? '' : (string) $message;

        return Common::replaceWith($content, $context, '{', '}');
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        $level = strtoupper($level);
        if ($this->allowLevel($level)) {
            $content = self::format($message, $context);
            $extra = [
                'moment' => time(),
                'ipaddr' => self::getClientIP(),
                'level' => $level,
                'name' => $this->name,
            ];
            $this->emit($content, $extra);
        }
    }

    /**
     * System is unusable.
     */
    public function emergency($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Action must be taken immediately.
     */
    public function alert($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Critical conditions.
     */
    public function critical($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public function error($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     */
    public function warning($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Normal but significant events.
     */
    public function notice($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Interesting events.
     */
    public function info($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Detailed debug information.
     */
    public function debug($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }
}
