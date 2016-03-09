<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Log;

use Docx\Event\Listener;

defined('LOG_WRITE_FILE_FREQ') or define('LOG_WRITE_FILE_FREQ', 1); //写文件的概率

/**
 * 文件日志.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class FileLogger extends Listener
{
    protected $filepath = '';
    protected $records = [];

    /**
     * 构造函数，设置文件位置和过滤级别.
     *
     * @param string $directory 日志目录
     */
    public function __construct($directory = false)
    {
        if ($directory === false) {
            $directory = realpath('./logs');
        }
        @mkdir($directory, 0777, true);
        $this->filepath = $directory . DIRECTORY_SEPARATOR . '%s.log';
    }

    public function __destruct()
    {
        $this->writeFiles();
        unset($this->records);
    }

    public function writeFiles()
    {
        foreach ($this->records as $filename => &$records) {
            $file = sprintf($this->filepath, $filename);
            $appends = implode('', $records);
            $bytes = file_put_contents($file, $appends, FILE_APPEND | LOCK_EX);
            if ($bytes !== false) { //写入成功，清除已写记录
                $records = [];
            }
        }
    }

    public function reply(array &$message, $sender = null)
    {
        @list($content, $extra) = $message;
        $filename = $extra['name'].'_'.date('Ymd', $extra['moment']);
        $extra['moment'] = date('Y-m-d H:i:s', $extra['moment']);
        $record = implode("\t", $extra)."\t".$content;
        if (!isset($this->records[$filename])) {
            $this->records[$filename] = [];
        }
        array_push($this->records[$filename], $record.PHP_EOL);
        if (LOG_WRITE_FILE_FREQ >= 1
                || LOG_WRITE_FILE_FREQ >= mt_rand(1, 10000) / 10000) {
            $this->writeFiles();
        }
    }
}
