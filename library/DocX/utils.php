<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */

defined('APP_ROOT') or die('Illeigal access');     //禁止非法访问

/**
 * 自动加载库文件
 *
 * @param string $class
 *            要使用的类名
 * @return bool
 */
function autoload_class($class)
{
    if (substr($class, 0, 5) === 'DOCX_') {
        $path = 'DocX/' . substr($class, 5) . '.php';
    } else {
        $path = $class . '.php';
    }
    require_once DOCX_ROOT . '/library/' . $path;
    return class_exists($class, false);
}

/**
 * 开始的字符串相同
 *
 * @param string $haystack
 *            可能包含子串的字符串
 * @param string $needle
 *            要查找的子串
 * @return bool
 */
function starts_with($haystack, $needle)
{
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

/**
 * 结束的字符串相同
 *
 * @param string $haystack
 *            可能包含子串的字符串
 * @param string $needle
 *            要查找的子串
 * @return bool
 */
function ends_with($haystack, $needle)
{
    $ndlen = strlen($needle);
    return $ndlen === 0 || (strlen($haystack) >= $ndlen && 
            substr_compare($haystack, $needle, -$ndlen) === 0);
}

/**
 * 将内容转为另一种编码
 *
 * @param string $word
 *            原始字符串
 * @param string $encoding
 *            目标编码
 * @return string 转换后的字符串
 */
function convert($word, $encoding = 'UTF-8')
{
    $encoding = strtoupper($encoding);
    if (function_exists('mb_detect_encoding')) {
        return mb_detect_encoding($word, $encoding, true) ?
                $word : mb_convert_encoding($word, $encoding, 'UTF-8, GBK');
    } else if (function_exists('iconv')) {
        $from_encoding = $encoding ==='UTF-8' ? 'GBK' : 'UTF-8';
        return iconv($from_encoding, $encoding . '//IGNORE', $word);
    }
}

/**
 * 页面跳转，GET方式
 * @param string $to_url 要跳转网址
 * @param bool $permanent 是否永久跳转(HTTP 301)
 * @return 进入新页面
 */
function http_redirect($to_url = '', $permanent = false)
{
    $status_code = $permanent ? 301 : 302;
    @header('Location: ' . $to_url, true, $status_code);
    return die(); //阻止运行后面的代码
}

/**
 * 找出两个路径或URL开头相同的部分
 */
function compare_pathes($curr, $last)
{
    if ($curr === $last) {
        return array($curr, 0, 0);
    } else if (starts_with($curr, $last)) {
        $forward = substr_count(substr($curr, strlen($last)), '/');
        return array($last, 0, $forward);
    } else if (starts_with($last, $curr)) {
        $backward = substr_count(substr($last, strlen($curr)), '/');
        return array($curr, $backward, 0);
    }
    $last_items = explode('/', $last);
    $curr_items = explode('/', $curr);
    $count = count($last_items);
    foreach ($curr_items as $i => $item) {
        if ($i >= $count || $item !== $last_items[$i]) {
            $same = implode('/', array_slice($curr_items, 0, $i));
            return array($same, $count - $i, count($curr_items) - $i);
        }
    }
    return array('', -1, -1);
}

/**
 * 去掉文件开头排序用的数字
 */
function clean_ord($name, $ext = '.md', $sep = '_')
{
    $name = basename($name, $ext);
    $pieces = explode($sep, $name, 2);
    if (count($pieces) === 2 && is_numeric($pieces[0])) {
        return $pieces[1];
    }
    return $name;
}

/**
 * 改写成适合的网址
 */
function slugify($name) {
    return strtolower(str_replace(' ', '-', $name));
}

/**
 * 随机显示一条语录
 */
function rand_greeting($greetings) {
    static $index = 0;
    if ($index === 0) {
        shuffle($greetings);
        $index = count($greetings);
    }
    return $greetings[--$index];
}

/**
 * 中文格式化日期
 */
function zh_date($format, $timestamp = false) {
    $result = date($format, $timestamp);
    if (strpos($format, '星期w') !== false) {
        $weekdays = array('星期0'=>'星期日', '星期1'=>'星期一', '星期2'=>'星期二',
            '星期3'=>'星期三', '星期4'=>'星期四', '星期5'=>'星期五', '星期6'=>'星期六');
        $result = str_replace(array_keys($weekdays), array_values($weekdays), $result);
    }
    return $result;
}
