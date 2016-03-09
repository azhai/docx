<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx;

use Docx\Common;
use Docx\Web\Router;


/**
 * 应用类.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Application
{
    use \Docx\Base\Behavior;

    protected static $properties = [ // 快捷属性
        'request' => '\\Docx\\Web\\Request',
        'response' => '\\Docx\\Web\\Response',
        'session' => '\\Docx\\Web\\SessionHandler',
    ];
    protected $shortcuts = []; // 快捷方式
    public $settings = []; // 配置
    public $errors = [];

    /**
     * 私有构造函数，防止在类外创建对象
     */
    public function __construct(array $settings = [])
    {
        if (version_compare(PHP_VERSION, '7.0.0') < 0) {
            Common::throwWarnings();
        }
        date_default_timezone_set($this->settings['timezone']);
        if (Common::isCLI()) {
            ini_set('display_errors', 1);
            ini_set ('memory_limit', $this->settings['memory_limit']);
            set_time_limit(0);
        }
        $importer = \Docx\Importer::getInstance();
        $this->installRef($importer, ['import', 'introduce', 'addClass']);
        $root = Router::getCurrent(); //根路由
        $this->installRef($root, ['dispatch', 'expose']);
        $this->settings = $settings;
    }

    public function __get($name)
    {
        $name = strtolower($name);
        $prop = $this->prop($name);
        if (!$prop && array_key_exists($name, self::$properties)) {
            $class = self::$properties[$name];
            $prop = Common::execConstructArray($class);
            $this->setProp($name, $prop);
        }
        return $prop;
    }

    /**
     * 使用已定义的插件
     */
    public function __call($name, $args)
    {
        $name = strtolower($name); //PHP的方法名内部都是小写？
        if (isset($this->shortcuts[$name])) {
            $shortcut = $this->shortcuts[$name];
            if (is_array($shortcut)) {
                @list($plugin, $name) = $shortcut;
            } else {
                $plugin = &$this->shortcuts[$name];
            }
            return Common::execMethodArray($plugin, $name, $args);
        }
    }

    /**
     * 设置语言.
     */
    public static function setLanguage($locale_dir, $language = 'zh_CN', $domian = 'messages')
    {
        putenv('LANG=' . $language);
        setlocale(LC_ALL, $language);
        if (function_exists('bindtextdomain')) {
            bindtextdomain($domian, $locale_dir);
            textdomain($domian);
        }
    }

    /**
     * 安装插件，并注册插件的一些方法
     */
    public function install($plugin, array $methods)
    {
        foreach ($methods as $alias => $method) {
            //省略别名时，使用同名方法。PHP的方法名内部都是小写？
            $alias = strtolower(is_numeric($alias) ? $method : $alias);
            $this->shortcuts[$alias] = [$plugin, $method];
        }
        return $this;
    }

    /**
     * 安装插件引用，并注册插件的一些方法
     */
    public function installRef(& $plugin, array $methods)
    {
        foreach ($methods as $method) {
            $this->shortcuts[strtolower($method)] = & $plugin;
        }
        return $this;
    }

    /**
     * 设置网址对应的handlers
     */
    public function route($path, $handler)
    {
        $router = Router::getCurrent(); //当前路由
        $args = func_get_args();
        return Common::execMethodArray($router, 'route', $args);
    }

    /**
     * 获取当前网址对应handlers
     */
    public function run($path = false)
    {
        if ($path === false) {
            if (Common::isCLI()) {
                $argv = $this->request->getArgv();
                $path = '/' . implode('/', array_slice($argv, 1));
            } else {
                $path = $this->request->getPath();
            }
        }
        $route = $this->dispatch($path);
        if (!$route) {
            return die();
        }
        $this->setProp('route', $route);
        $method = $this->request->getMethod();
        if ($method === 'post') {
            $method = $this->request->getString('_method', 'post');
        }
        
        $output = '';
        foreach ($route['handlers'] as $handler) {
            if (empty($handler)) {
                continue;
            }
            if (is_string($handler) && class_exists($handler, true)) {
                $handler = new $handler($this, $method);
            }
            if (is_callable($handler)) {
                try {
                    $output = Common::execFunctionArray($handler, $route['args']);
                } catch (\Exception $e) {
                    $method = 'fail';
                    $this->errors[] = $e;
                }
            }
        }
        return die(strval($output));
    }
}
