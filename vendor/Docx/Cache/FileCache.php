<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Cache;

/**
 * 文件缓存.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class FileCache extends BaseCache
{
    protected $filename = '';   //完整文件路径
    protected $dir = '';
    protected $ext = '';

    public function __construct($dir = false, $ext = '.php')
    {
        if (empty($dir)) {
            $this->dir = sys_get_temp_dir();
        } else {
            $this->dir = rtrim($dir, DIRECTORY_SEPARATOR);
            @mkdir($this->dir, 0755, true);
        }
        $this->ext = '.' . ltrim(strtolower($ext), '.');
    }

    public static function dumpYaml($data)
    {
        if (extension_loaded('yaml')) {
            return yaml_emit($data, YAML_UTF8_ENCODING, YAML_LN_BREAK);
        } else if (class_exists('\\Symfony\\Component\\Yaml\\Yaml')) {
            return \Symfony\Component\Yaml\Yaml::dump($data);
        }
    }

    public static function parseYaml($data)
    {
        if (extension_loaded('yaml')) {
            return yaml_parse($data);
        } else if (class_exists('\\Symfony\\Component\\Yaml\\Yaml')) {
            return \Symfony\Component\Yaml\Yaml::parse($data);
        }
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
        if ($this->ext === '.yml' || $this->ext === '.yaml') {
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
        if ($this->ext === '.php') {
            return include $this->filename;
        } else {
            $cipher = file_get_contents($this->filename);
            return $this->decode($cipher);
        }
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
        $data = $this->encode($value, $name);
        $bytes = file_put_contents($this->filename, $data, LOCK_EX);

        return $bytes && $bytes > 0;
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

    /**
     * 解码
     *
     * @param string $cipher 密文
     *
     * @return mixed 原始值
     */
    protected function decode($cipher)
    {
        switch ($this->ext) {
            case '.txt':
            case '.htm':
            case '.html':
                $result = $cipher;
                break;
            case '.json':
                $result = json_decode($cipher, true);
                break;
            case '.yml':
            case '.yaml':
                $result = self::parseYaml($cipher);
                break;
        }

        return $result;
    }

    /**
     * 编码
     *
     * @param mixed $data 原始值
     *
     * @return string 密文
     */
    protected function encode($data)
    {
        switch ($this->ext) {
            case '.txt':
            case '.htm':
            case '.html':
                $result = $data;
                break;
            case '.php':
                $cipher = var_export($data, true);
                $result = "<?php \nreturn ".$cipher.";\n";
                break;
            case '.json':
                $result = json_encode($data);
                break;
            case '.yml':
            case '.yaml':
                $result = self::dumpYaml($data);
                break;
        }

        return $result;
    }
}
