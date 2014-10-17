<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */


/**
 * 缓存
 */
class DOCX_Cache
{
    protected $data = null;
    protected $path = '';
    protected $reader = null;
    protected $writer = null;
    protected $closed = false;
    protected $signature = '';
    
    /**
     * 构造函数，设置存储点和读写器
     * @param string $path 缓存位置
     * @param object $reader
     * @param object $writer
     */
    public function __construct($path, & $reader = null, & $writer = null)
    {
        $this->setPath($path);
        $this->reader = $reader;
        $this->writer = $writer;
        register_shutdown_function(function($self) {
            $self->__destruct();
        }, $this);
    }
    
    /**
     * 析构函数
     */
    public function __destruct()
    {
        if ($this->closed === false) {
            $this->close();
        }
        $this->closed = true;
    }
    
    public function close()
    {
        if (empty($this->signature) || $this->signature !== $this->sign()) {
            $this->save();
        }
        unset($this->data);
    }
    
    public function offsetGet($offset)
    {
        if(isset($this->data[$offset])) {
            return $this->data[$offset];
        }
    }
    
    public function offsetSet($offset, & $value)
    {
        $this->data[$offset] = $value;
        return $this;
    }
    
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
    
    public function connect(& $target, $key = null)
    {
        if (empty($key)) {
            $this->data = & $target;
        } else {
            if (! is_array($this->data)) {
                $this->data = array();
            }
            $this->data[$key] = & $target;
        }
        return $this;
    }
    
    protected function getReader()
    {
        if (is_null($this->reader)) {
            $this->reader = new DOCX_FileKeeper();
        }
        return $this->reader;
    }
    
    protected function getWriter()
    {
        if (is_null($this->writer)) {
            $reader = $this->getReader();
            $this->writer = & $reader;
        }
        return $this->writer;
    }
    
    public function sign()
    {
        return serialize($this->data);
    }
    
    public function load()
    {
        if ($reader = $this->getReader()) {
            $data = $reader->read($this->path);
            if (! is_null($data)) {
                $this->data = $data;
                $this->signature = $this->sign();
            }
        }
        return $this->data;
    }
    
    public function save()
    {
        if ($writer = $this->getWriter()) {
            $writer->write($this->path, $this->data);
        }
        return $this->data;
    }
}
