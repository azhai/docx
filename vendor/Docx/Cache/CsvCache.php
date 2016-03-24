<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Cache;


/**
 * CSV文件缓存.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class CsvCache extends BaseCache
{
    protected $filename = '';   //完整文件路径
    protected $extname = '';
    protected $delimiter = '';  //列分隔符
    protected $at_least = 0;    //最少列数

    public function __construct($filename, $delimiter = "\t", $at_least = 0)
    {
        $this->filename = $filename;
        $this->delimiter = $delimiter ?: "\t";
        $this->at_least = intval($at_least);
    }

    /**
     * 不存在时创建文件，并确定扩展名
     *
     * @return string 扩展名
     */
    public static function detectFile($filename)
    {
        $parts = pathinfo($filename);
        @mkdir($parts['dirname'], 0755, true);
        if (!is_readable($filename)) {
            touch($filename);
        }
        $extname = '.' . strtolower($parts['extension']);
        return $extname;
    }

    /**
     * 准备文件、加载扩展、连接服务
     *
     * @return bool 是否成功
     */
    public function prepare()
    {
        if (empty($this->extname)) {
            $this->extname = self::detectFile($this->filename);
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
        $bytes = filesize($this->filename);
        if ($bytes === false || $bytes === 0) {
            return;
        }
        $data = [];
        $fh = fopen($this->filename, 'rb');
        if ($fh !== false) {
            do {
                $line = fgetcsv($fh, 0, $this->delimiter);
                if (is_null($line) || $line === false) {
                    break; //无效的文件指针返回NULL，碰到文件结束时返回FALSE
                }
                if (is_null($line[0])) {
                    $line = []; //空行将被返回为一个包含有单个 null 字段的数组
                } elseif ($this->at_least > 0) {
                    if (count($line) < $this->at_least) {
                        continue; //列数不足
                    }
                }
                $data[] = $line;
            } while (1);
            fclose($fh);
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
        $fh = fopen($this->filename, 'wb');
        if ($fh === false || !is_array($value)) {
            return false;
        }
        foreach ($value as $row) {
            fputcsv($fh, $row, $this->delimiter);
        }
        fclose($fh);

        return true;
    }

    /**
     * 删除操作.
     *
     * @return bool 是否成功
     */
    public function remove()
    {
        $this->prepare();
        if (file_exists($this->filename)) {
            return unlink($this->filename);
        }
    }
}
