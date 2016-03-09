<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx;

/**
 * 类加载器.
 *
 * @author Ryan Liu <azhai@126.com>
 *
 * USAGE:
 * defined('APP_ROOT') or define('APP_ROOT', dirname(__DIR__));
 * defined('VENDOR_DIR') or define('VENDOR_DIR', APP_ROOT . '/vendor');
 * require_once APP_ROOT . '/src/Importer.php';
 * $importer = \Docx\Importer::getInstance();
 * $importer->import('NotORM', VENDOR_DIR . '/notorm');
 * //OR
 * $importer->addClass(VENDOR_DIR . '/notorm/NotORM.php',
 *         'NotORM', 'NotORM_Result', 'NotORM_Row', 'NotORM_Literal', 'NotORM_Structure');
 */
final class Importer
{
    private static $instance = null; //实例
    private $classes = []; // 已注册的class/interface/trait对应的文件
    private $prefixes = []; // 已注册的namespace对用的起始目录
    //分隔符
    private $sepeators = '';
    private $sepeator_array = [];

    /**
     * 私有构造函数，防止在类外创建对象
     */
    private function __construct($sepeators = '\\')
    {
        $this->sepeators = $sepeators;
        $this->sepeator_array = str_split($sepeators);
        // 加载基本的命令空间
        $this->prefixes[__NAMESPACE__] = __DIR__;
    }

    /**
     * Importer单例.
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->register();
        }

        return self::$instance;
    }

    /**
     * 检查指定class/interface/trait是否已存在.
     *
     * @param string $class    要检查的完整class/interface/trait名称
     * @param bool   $autoload 如果当前不存在，是否尝试PHP的自动加载功能
     *
     * @return bool
     */
    public static function exists($class, $autoload = true)
    {
        return class_exists($class, $autoload)
                || interface_exists($class, $autoload)
                || trait_exists($class, $autoload);
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     * @param bool   $once
     *
     * @return bool True if the file exists, false if not.
     */
    public static function requireFile($file, $once = false)
    {
        if (empty($file) || !file_exists($file)) {
            return false;
        }
        if ($once) {
            require_once $file;
        } else {
            require $file;
        }

        return true;
    }

    /**
     * 将对象的autoload方法注册到PHP系统
     * 在这之后往对象中添加的class和namespace也起作用.
     *
     * @return bool
     */
    public function register()
    {
        return spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * 自动加载方法，用于spl_autoload_register注册.
     *
     * @param string $class 要寻找的完整class/interface/trait名称
     *
     * @return bool
     */
    public function autoload($class)
    {
        $class = trim($class, $this->sepeators);
        if (isset($this->classes[$class])) { // 在已知类中查找
            if (self::requireFile($this->classes[$class])) {
                return self::exists($class, false);
            }
        }
        $ns_check = $this->matchPrefix($class); // 在已知域名中查找
        return $ns_check === true;
    }

    /**
     * Namespace/class自动加载时，寻找匹配文件的方式.
     *
     * @param string $class 要寻找的完整class/interface/trait名称
     *
     * @return bool
     */
    public function matchPrefix($class)
    {
        $sub_ns = '';
        foreach ($this->prefixes as $ns => $path) {
            if (substr($class, 0, strlen($ns)) === $ns) {
                if (empty($ns)) {
                    $sub_ns = $class;
                } else {
                    $sub_ns = substr($class, strlen($ns) + 1);
                }
                $tok = strtok($sub_ns, $this->sepeators);
                break;
            }
        }
        if (empty($sub_ns)) {
            return false;
        }
        // 先试试一步到位，用于符合PSR-0标准的库
        $fname = $path.DIRECTORY_SEPARATOR;
        $fname .= str_replace($this->sepeator_array, DIRECTORY_SEPARATOR, $sub_ns);
        if (self::requireFile($fname.'.php')) {
            if (self::exists($class, false)) {
                return true;
            }
        }
        // 尝试循序渐进地检查目标对应的路径
        while ($tok) {
            $path .= DIRECTORY_SEPARATOR.$tok;
            // 先检查文件，再检查目录，次序不可颠倒
            if (self::requireFile($path.'.php')) { // 找到文件了
                if (self::exists($class, false)) {
                    return true;
                }
            }
            if (!file_exists($path)) { // 目录不对，不要再找了
                return false;
            }
            $tok = strtok($this->sepeators);
        }
    }

    /**
     * 当自动加载class,class2,class3,...时，将filename文件包含进来.
     *
     * @param string $filename 这些class/interface/trait所在的文件或入口文件
     * @param string $class    完整class/interface/trait名称
     * @param ... 其他class/interface/trait名称
     *
     * @return this
     */
    public function addClass($filename, $class)
    {
        $classes = func_get_args();
        $filename = array_shift($classes);
        if (is_readable($filename)) {
            foreach ($classes as $class) {
                $this->classes[trim($class, '\\')] = $filename;
            }
        }
        krsort($this->classes);

        return $this;
    }

    /**
     * 当自动加载的namespace/class以某个词ns开头时，尝试在dir目录寻找匹配文件.
     *
     * @param string $ns  包前缀
     * @param string $dir 包所在目录
     *
     * @return this
     */
    public function introduce($ns, $dir)
    {
        $ns = trim($ns, '\\');
        $dir = rtrim($dir, '\\/');
        $this->prefixes[$ns] = $dir;
        krsort($this->prefixes); //贪婪匹配需要倒序排列
        return $this;
    }

    /**
     * 同上，但目录不含前缀，这是标准做法.
     *
     * @param string $ns  包前缀
     * @param string $dir 包所在目录
     *
     * @return this
     */
    public function import($ns, $dir)
    {
        $sub_dir = str_replace($this->sepeator_array, DIRECTORY_SEPARATOR, $ns);
        $dir .= DIRECTORY_SEPARATOR.$sub_dir;

        return $this->introduce($ns, $dir);
    }
}

\Docx\Importer::getInstance();
