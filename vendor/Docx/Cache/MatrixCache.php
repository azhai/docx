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
class MatrixCache extends BaseCache
{
    protected $filename = '';   //完整文件路径
    protected $dir = '';
    protected $ext = '';
    protected $delimiter = '';  //列分隔符
    protected $at_least = 0;    //最少列数

    public function __construct($dir = false, $ext = '.csv',
                                $delimiter = "\t", $at_least = 0)
    {
        if (empty($dir)) {
            $this->dir = sys_get_temp_dir();
        } else {
            $this->dir = rtrim($dir, DIRECTORY_SEPARATOR);
            @mkdir($this->dir, 0755, true);
        }
        $this->ext = '.'.ltrim(strtolower($this->ext), '.');
        $this->delimiter = $delimiter ?: "\t";
        $this->at_least = intval($at_least);
    }

    /**
     * 准备文件、加载扩展、连接服务
     *
     * @param string $name 键名
     *
     * @return bool 是否成功
     */
    public function prepare($name)
    {
        $this->filename = $this->dir.DIRECTORY_SEPARATOR
                                    .$name.$this->ext;
        if (!is_readable($this->filename)) {
            touch($this->filename);
        }

        return true;
    }

    /**
     * 读操作.
     *
     * @param string $name 键名
     *
     * @return mixed 对应值
     */
    public function read($name)
    {
        $this->prepare($name);
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
     * @param string $name    键名
     * @param mixed  $value   对应值
     * @param int    $timeout 缓存时间
     *
     * @return bool 是否成功
     */
    public function write($name, $value, $timeout = 0)
    {
        $this->prepare($name);
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
     * @param string $name 键名
     *
     * @return bool 是否成功
     */
    public function remove($name)
    {
        $this->prepare($name);
        if (file_exists($this->filename)) {
            return unlink($this->filename);
        }
    }
}
