<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Web;

use Docx\Common;
@session_start();

/**
 * 输入参数过滤器
 * 注意$_SERVER中只有少量元素出现在INPUT_SERVER中.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Request
{
    const PHP_INDEX_FILE = 'index.php';

    /**
     * getValue()或getInput()的简写形式
     * 如：getInt($key, $default)等价于getValue('REQUEST', $key, $default, 'int').
     */
    public function __call($name, $arguments)
    {
        if (!Common::startsWith($name, 'get')) {
            return;
        }
        $name = strtolower(substr($name, 3));
        @list($key, $default, $orig_input) = $arguments;
        switch ($name) {
            case 'server':
            case 'session':
                $result = self::getValue($name, $key, $default);
                break;
            case 'cookie':
            case 'env':
            case 'post':
                $result = self::getInput($name, $key, $default);
                break;
            default:
                $input = self::detectInput($orig_input);
                if ($input === INPUT_REQUEST || $input === INPUT_SESSION) {
                    $result = self::getValue($orig_input, $key, $default, $name);
                } else {
                    $result = self::getInput($orig_input, $key, $default, $name);
                }
                break;
        }
        return $result;
    }

    /**
     * 判别输入的常量表示.
     * 可用INPUT_GET|INPUT_POST|INPUT_COOKIE|INPUT_SERVER|INPUT_ENV
     */
    protected static function detectInput($input = 'REQUEST')
    {
        $input = strtoupper($input ?: 'REQUEST');
        return constant('INPUT_' . $input);
    }

    /**
     * 判别类型的准确的常量表示.
     * 当$types是关联数组时，按键名对应类型过滤，否则，全部使用单一类型过滤
     */
    protected static function detectType($types = 'string')
    {
        if (!is_array($types)) {
            if ($types === 'raw') {
                return filter_id('unsafe_raw');
            } else {
                return filter_id($types ?: 'string');
            }
        }
        foreach ($types as $key => $type) {
            if (is_array($type)) {
                $types[$key]['filter'] = self::detectType($type['filter']);
            } else {
                $types[$key] = self::detectType($type);
            }
        }
        return $types;
    }

    public static function setValue($key, $value, $input = 'REQUEST')
    {
        $input = self::detectInput($input);
        switch ($input) {
            case INPUT_REQUEST:
                $_REQUEST[$key] = $value;
                break;
            case INPUT_COOKIE:
                $_COOKIE[$key] = $value;
                break;
            case INPUT_SESSION:
                $_SESSION[$key] = $value;
                break;
            case INPUT_ENV:
                $_ENV[$key] = $value;
                break;
        }
    }

    public static function getValue($input, $key, $default = null, $type = 'string')
    {
        $input = self::detectInput($input);
        if (is_null($input)) {
            return;
        }
        if ($input === INPUT_REQUEST) {
            $input = $_REQUEST;
        } elseif ($input === INPUT_SESSION) {
            $input = $_SESSION;
        } elseif ($input === INPUT_SERVER) {
            $input = $_SERVER;
        }
        $type = self::detectType($type);
        if (is_array($type) || is_array($default)) {
            $key_type = [$key => ['filter' => $type, 'flags' => FILTER_FORCE_ARRAY]];
            $values = filter_var_array($input, $key_type, true);
            return isset($values[$key]) ? $values[$key] : $default;
        } else {
            $value = isset($input[$key]) ? $input[$key] : $default;
            return filter_var($value, $type);
        }
    }

    public static function getInput($input, $key, $default = null, $type = 'string')
    {
        $input = self::detectInput($input);
        if (is_null($input)) {
            return;
        }
        $type = self::detectType($type);
        if (is_array($type) || is_array($default)) {
            $key_type = [$key => ['filter' => $type, 'flags' => FILTER_FORCE_ARRAY]];
            $value = filter_input_array($input, $key_type, true);
        } else {
            $value = filter_input($input, $key, $type);
        }
        return is_null($value) ? $default : $value;
    }

    /**
     * 获取REQUEST中单个键的值，并抛出它.
     */
    public static function pop($key, $default = null, $type = 'string')
    {
        $value = self::getValue('REQUEST', $key, $default, $type);
        if (isset($_REQUEST[$key])) {
            unset($_REQUEST[$key]);
        }
        return $value;
    }

    /**
     * 获取全部的的值
     */
    public static function all($types = 'string', $input = 'REQUEST')
    {
        $input = self::detectInput($input);
        if (is_null($input)) {
            return;
        }
        $types = self::detectType($types);
        if ($input === INPUT_REQUEST) {
            return filter_var_array($_REQUEST, $types);
        } elseif ($input === INPUT_SESSION) {
            return filter_var_array($_SESSION, $types);
        } elseif ($input === INPUT_SERVER) {
            //$_SERVER中只有少量元素出现在INPUT_SERVER中.
            return filter_var_array($_SERVER, $types);
        } else {
            return filter_input_array($input, $types, true);
        }
    }

    /**
     * 获取当前Path Info
     *
     * @return string
     */
    public static function getPath($route_key = null)
    {
        if ($route_key) {
            return self::pop($route_key, '/');
        }
        $url = self::getInput('SERVER', 'REQUEST_URI');
        $url = parse_url($url, PHP_URL_PATH);
        $name = self::getInput('SERVER', 'SCRIPT_NAME');
        if (empty($url) || $url === $name) {
            return '';
        }
        
        $path = rtrim($url, '/ ') . '/';
        $head = substr($name, 0, - strlen(self::PHP_INDEX_FILE));
        if ($path . self::PHP_INDEX_FILE === $name) {
            $path = '/';
        } else if (Common::startsWith($path, $name)) {
            $path = substr($path, strlen($name));
        } else if (Common::startsWith($path, $head)) {
            $path = substr($path, strlen($head) - 1);
        }
        return $path;
    }

    /**
     * 获取当前Method.
     *
     * @return string
     */
    public static function getMethod()
    {
        $method = self::getInput('SERVER', 'REQUEST_METHOD', 'GET');
        return strtolower($method);
    }

    /**
     * 获取真实HTTP客户端IP，按次序尝试.
     *
     * @return string
     */
    public static function getClientIP()
    {
        $keys = [
            'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR',
        ];
        foreach ($keys as $key) {
            $ipaddr = self::getValue('SERVER', $key, '');
            if ($ipaddr && strlen($ipaddr) >= 7) {
                break;
            }
        }
        return $ipaddr;
    }

    /**
     * 获取命令行中的参数.
     *
     * @return array 参数列表，array[0]为当前文件名
     */
    public static function getArgv()
    {
        if (Common::isCLI()) {
            return self::getValue('SERVER', 'argv', [], 'string');
        }
    }

}
