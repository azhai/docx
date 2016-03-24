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
    protected $extname = '';

    public function __construct($filename = '', $extname = '')
    {
        $this->filename = $filename;
        $this->extname = $extname;
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
     * @return bool 是否成功
     */
    public function prepare()
    {
        if (empty($this->filename)) {
            $this->filename = tempnam(sys_get_temp_dir(), 'cache_');
        } else if (empty($this->extname)) {
            $this->extname = CsvCache::detectFile($this->filename);
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
        if ($this->extname === '.php') {
            return include $this->filename;
        } else {
            $cipher = file_get_contents($this->filename);
            return $this->decode($cipher);
        }
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
        $data = $this->encode($value);
        $bytes = file_put_contents($this->filename, $data, LOCK_EX);

        return $bytes && $bytes > 0;
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

    /**
     * 解码
     *
     * @param string $cipher 密文
     *
     * @return mixed 原始值
     */
    protected function decode($cipher)
    {
        switch ($this->extname) {
            case '':
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
        switch ($this->extname) {
            case '':
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
