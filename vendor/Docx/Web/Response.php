<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Web;


/**
 * 内容输出，带一个简单的模板引擎.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Response
{
    protected $frame_files = [];
    protected $stack = [];
    public $blocks = [];
    public $globals = [];
    public $mime_type = null;
    public $charset = '';

    /**
     * 构造函数.
     */
    public function __construct($entry_file = '', array $globals = [])
    {
        $this->addFrameFile($entry_file);
        $this->addGlobals($globals);
    }

    /**
     * 发送HTTP错误.
     */
    public static function abort($code = 500)
    {
        return http_response_code($code);
    }

    /**
     * 发送Header.
     */
    public static function header($name, $value, $replace = true, $code = 200)
    {
        if (!headers_sent()) {
            $line = empty($name) ? '' : strval($name).': ';
            $line .= is_array($value) ? implode(' ', $value) : strval($value);
            header($line, $replace, $code);
        }
    }

    /**
     * 页面跳转，GET方式.
     *
     * @param string $to_url    要跳转网址
     * @param bool   $permanent 是否永久跳转(HTTP 301)
     *
     * @return 进入新页面
     */
    public static function redirect($to_url = '', $permanent = false)
    {
        $status_code = $permanent ? 301 : 302;
        self::header('Location', $to_url, true, $status_code);
        return die(); //阻止运行后面的代码
    }

    /**
     * 添加全局变量.
     *
     * @param array $data 变量数组
     * @return \Docx\Web\Response
     */
    public function addGlobals(array $globals)
    {
        $this->globals = array_merge($this->globals, $globals);
        return $this;
    }

    /**
     * 添加模板文件.
     *
     * @param string $frame_file 模板文件
     * @return \Docx\Web\Response
     */
    public function addFrameFile($frame_file)
    {
        if ($frame_file && is_readable($frame_file)) {
            $this->frame_files[] = $frame_file;
        }
        return $this;
    }

    /**
     * 设置布局文件.
     *
     * @param string $layout_file 布局文件
     * @return \Docx\Web\Response
     */
    public function extendTpl($layout_file)
    {
        if ($layout_file && is_readable($layout_file)) {
            array_unshift($this->frame_files, $layout_file);
        }
        return $this;
    }

    /**
     * 包含模板文件.
     *
     * @param string $frame_file 模板文件
     */
    public function includeTpl($frame_file)
    {
        if ($frame_file && is_readable($frame_file)) {
            extract($this->globals);
            include $frame_file;
        }
    }

    /**
     * 标示区块开始.
     *
     * @param string $name 区块名称
     */
    public function blockStart($name = 'content')
    {
        array_push($this->stack, $name);
        ob_start();
    }

    /**
     * 标示区块结束
     */
    public function blockEnd()
    {
        $block_html = trim(ob_get_clean());
        if ($name = array_pop($this->stack)) {
            $this->blocks[$name] = $block_html;
        }
    }

    /**
     * 返回区块内容.
     *
     * @param string $name 区块名称
     */
    public function block($name = 'content')
    {
        if (isset($this->blocks[$name])) {
            return $this->blocks[$name];
        }
    }

    /**
     * 设置文档类型和字符集
     *
     * @param string $type 文档类型
     * @param string $charset 字符集
     * @return \Docx\Web\Response
     */
    public function setContentType($type, $charset = 'utf-8')
    {
        $this->mime_type = new MimeType($type);
        $this->charset = strval($charset);
        $line = $this->mime_type . '; charset=' . $this->charset;
        self::header('Content-Type', $line);
        return $this;
    }

    /**
     * 获取输出内容.
     *
     * @param array $context 模板变量数组
     * @return string
     */
    public function render(array $context = [])
    {
        extract($this->globals);
        extract($context);
        ob_start();
        while ($frame_file = array_pop($this->frame_files)) {
            include $frame_file;
        }
        return trim(ob_get_clean());
    }
}
