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
    public $route_key = '';
    public $settings = []; // 配置

    /**
     * 私有构造函数，防止在类外创建对象
     */
    public function __construct(array $settings = [])
    {
        if (version_compare(PHP_VERSION, '7.0.0') < 0) {
            Common::throwWarnings();
        }
        if (isset($settings['timezone'])) {
            date_default_timezone_set($settings['timezone']);
        }
        if (Common::isCLI()) {
            set_time_limit(0);
            ini_set('display_errors', 1);
            if (isset($settings['memory_limit'])) {
                ini_set('memory_limit', $settings['memory_limit']);
            }
        }
        $importer = \Docx\Importer::getInstance();
        $this->installRef($importer, ['import', 'introduce', 'addClass']);
        $root = Router::getCurrent(); //根路由
        $this->installRef($root, ['dispatch', 'expose']);
        $this->route_key = isset($settings['route_key']) ? $settings['route_key'] : '';
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
     * 执行对应handlers
     */
    public function execute($path, $method)
    {
        $route = $this->dispatch($path);
        if (!$route) {
            return die();
        }
        $backend = null;
        foreach ($route['handlers'] as $handler) {
            if (empty($handler)) {
                continue;
            }
            if (is_string($handler) && class_exists($handler, true)) {
                $handler = new $handler($this, $backend);
            }
            $backend = $handler;
        }
        if (!($handler instanceof \Closure)) {
            $handler->globals['method'] = $method;
            $handler->globals['path'] = $path;
            $handler->globals['url'] = $route['url'];
            $handler->globals['rule'] = $route['rule'];
            $handler->globals['args'] = $route['args'];
        }
        $output = '';
        if (is_callable($handler)) {
            try {
                $output = Common::execFunctionArray($handler, $route['args']);
            } catch (\Exception $error) {
                if (method_exists($handler, 'except')) {
                    $output = $handler->except($error);
                }
            }
        }
        return die(strval($output));
    }

    /**
     * 运行CGI/CLI程序
     */
    public function run()
    {
        if (Common::isCLI()) {
            $argv = $this->request->getArgv();
            $path = '/' . implode('/', array_slice($argv, 1));
            $method = 'exec';
        } else {
            $path = $this->request->getPath($this->route_key);
            $method = $this->request->getMethod();
            if ($method === 'post') {
                $method = $this->request->getString('_method', 'post');
            }
        }
        return $this->execute($path, $method);
    }
}
