<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx;

/**
 * 常用方法.
 *
 * @author Ryan Liu <azhai@126.com>
 */
final class Common
{
    /**
     * 脚本是否命令行运行.
     */
    public static function isCLI()
    {
        return strtolower(php_sapi_name()) === 'cli';
    }
    
    /**
     * 是否Windows系统，不含Cygwin.
     */
    public static function isWinNT()
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }

    /**
     * 将警告作为异常抛出，PHP7的默认行为.
     */
    public static function throwWarnings()
    {
        //只拦截警告，并以异常形式抛出
        set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcxt = []) {
            if (0 === error_reporting()) {
                return false; // error was suppressed with the @-operator
            }
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        }, E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING);
    }

    /**
     * 开始的字符串相同.
     *
     * @param string $haystack 可能包含子串的字符串
     * @param string $needle   要查找的子串
     *
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    /**
     * 结束的字符串相同.
     *
     * @param string $haystack 可能包含子串的字符串
     * @param string $needle   要查找的子串
     *
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        $ndlen = strlen($needle);
        return $ndlen === 0 || (strlen($haystack) >= $ndlen &&
            substr_compare($haystack, $needle, -$ndlen) === 0);
    }

    /**
     * 将内容字符串中的变量替换掉.
     *
     * @param string $content 内容字符串
     * @param array  $context 变量数组
     * @param string $prefix  变量前置符号
     * @param string $subfix  变量后置符号
     *
     * @return string 当前内容
     */
    public static function replaceWith($content, array $context = [],
                                       $prefix = '', $subfix = '')
    {
        if (empty($context)) {
            return $content;
        }
        if (empty($prefix) && empty($subfix)) {
            $replacers = &$context;
        } else {
            $replacers = [];
            foreach ($context as $key => &$value) {
                $replacers[$prefix.$key.$subfix] = $value;
            }
        }
        $content = strtr($content, $replacers);
        return $content;
    }

    /**
     * 将内容转为另一种编码
     *
     * @param string $word     原始字符串
     * @param string $encoding 目标编码
     *
     * @return string 转换后的字符串
     */
    public static function convertString($word, $encoding = 'UTF-8')
    {
        $encoding = strtoupper($encoding);
        if (function_exists('mb_detect_encoding')) {
            return mb_detect_encoding($word, $encoding, true) ?
                $word : mb_convert_encoding($word, $encoding, 'UTF-8, GBK');
        } elseif (function_exists('iconv')) {
            $from_encoding = $encoding === 'UTF-8' ? 'GBK' : 'UTF-8';
            return iconv($from_encoding, $encoding.'//IGNORE', $word);
        }
    }

    /**
     * 调用函数/闭包/可invoke的对象
     * 不用call_user_func_array()，因为它有两个限制：
     * 一是性能较低，只有反射的一半多一点；
     * 二是$args中如果有引用参数，那么它们必须以引用方式传入。
     *
     * @param string /Closure/object $func 函数名/闭包/含__invoke方法的对象
     * @param array                  $args 参数数组，长度限制5个元素及以下
     *
     * @return mixed 执行结果，没有找到可执行函数时返回null
     */
    public static function execFunctionArray($func, array $args = [])
    {
        switch (count($args)) {
            case 0:
                return $func();
            case 1:
                return $func($args[0]);
            case 2:
                return $func($args[0], $args[1]);
            case 3:
                return $func($args[0], $args[1], $args[2]);
            case 4:
                return $func($args[0], $args[1], $args[2], $args[3]);
            case 5:
                return $func($args[0], $args[1], $args[2], $args[3], $args[4]);
            default:
                if (is_object($func)) {
                    $ref = new \ReflectionMethod($func, '__invoke');
                    return $ref->invokeArgs($func, $args);
                } elseif (is_callable($func)) {
                    $ref = new \ReflectionFunction($func);
                    return $ref->invokeArgs($args);
                }
        }
    }

    /**
     * 调用类/对象方法.
     *
     * @param object /class $clsobj 对象/类
     * @param string        $method 方法名
     * @param array         $args   参数数组，长度限制5个元素及以下
     *
     * @return mixed 执行结果，没有找到可执行方法时返回null
     */
    public static function execMethodArray($clsobj, $method, array $args = [])
    {
        if (is_object($clsobj)) {
            switch (count($args)) {
                case 0:
                    return $clsobj->{$method}();
                case 1:
                    return $clsobj->{$method}($args[0]);
                case 2:
                    return $clsobj->{$method}($args[0], $args[1]);
                case 3:
                    return $clsobj->{$method}($args[0], $args[1], $args[2]);
                case 4:
                    return $clsobj->{$method}($args[0], $args[1], $args[2], $args[3]);
                case 5:
                    return $clsobj->{$method}($args[0], $args[1], $args[2], $args[3], $args[4]);
            }
        }
        if (method_exists($clsobj, $method)) {
            $ref = new \ReflectionMethod($clsobj, $method);
            if ($ref->isPublic() && !$ref->isAbstract()) {
                if ($ref->isStatic()) {
                    return $ref->invokeArgs(null, $args);
                } else {
                    return $ref->invokeArgs($clsobj, $args);
                }
            }
        }
    }

    /**
     * 创建对象
     *
     * @param string $class 类名
     * @param array  $args  参数数组
     *
     * @return mixed 执行结果，没有找到类时返回null
     */
    public static function execConstructArray($class, array $args = [])
    {
        if (method_exists($class, 'getInstance')) { //调用静态方法getInstance
            $ref = new \ReflectionMethod($class, 'getInstance');
            return $ref->invokeArgs(null, $args);
        }
        switch (count($args)) {
            case 0:
                return new $class();
            case 1:
                return new $class($args[0]);
            case 2:
                return new $class($args[0], $args[1]);
            case 3:
                return new $class($args[0], $args[1], $args[2]);
            case 4:
                return new $class($args[0], $args[1], $args[2], $args[3]);
            case 5:
                return new $class($args[0], $args[1], $args[2], $args[3], $args[4]);
            default:
                if (class_exists($class)) {
                    $ref = new \ReflectionClass($class);
                    return $ref->newInstanceArgs($args);
                }
        }
    }
}
