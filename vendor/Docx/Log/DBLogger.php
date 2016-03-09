<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Log;

use PDO;
use Docx\Event\Listener;

/**
 * 数据库日志.
 *
 * @author Ryan Liu <azhai@126.com>
 *
 * -- Table Structure:
 * CREATE TABLE `t_log` (
 *   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 *   `moment` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 *   `ipaddr` varchar(15) DEFAULT NULL,
 *   `level` varchar(9) NOT NULL DEFAULT '',
 *   `name` varchar(20) NOT NULL DEFAULT '',
 *   `content` text,
 *   PRIMARY KEY (`id`),
 *   KEY `name` (`name`),
 *   KEY `moment` (`moment`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */
class DBLogger extends Listener
{
    const ZONE_INTERVAL = 28800; //8小时
    protected $pdo = null;
    protected $last_hour = 0;

    /**
     * 构造函数，设置文件位置和过滤级别.
     *
     * @param string $directory 日志目录
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __destruct()
    {
        unset($this->pdo);
    }

    public function getTable($moment)
    {
        $table = 't_log_'.date('Ymd', $moment);
        if ($moment - $this->last_hour >= self::ZONE_INTERVAL) {
            $this->last_hour = $moment - $moment % self::ZONE_INTERVAL;
            //Create table if not exists
            $tpl = 'CREATE TABLE IF NOT EXISTS %s LIKE %s';
            $sql = sprintf($tpl, $table, 't_log');
            $this->pdo->exec($sql);
        }

        return $table;
    }

    public function insertRow($table, array &$row)
    {
        $fields = implode(',', array_keys($row));
        $values = implode("','", array_values($row));
        $tpl = "INSERT INTO %s(%s) VALUES('%s')";
        $sql = sprintf($tpl, $table, $fields, $values);
        $this->pdo->exec($sql);
    }

    public function reply(array &$message, $sender = null)
    {
        @list($content, $extra) = $message;
        $table = $this->getTable($extra['moment']);
        $extra['moment'] = date('Y-m-d H:i:s', $extra['moment']);
        $extra['content'] = $content;
        $this->insertRow($table, $extra);
    }
}
