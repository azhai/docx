<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Utility;

use Docx\Common;
use Docx\Utility\Markdoc;

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('CACHE_UNLINK_EMPTY_FREQ') or define('CACHE_UNLINK_EMPTY_FREQ', 0.2); //删除空目录的概率


/**
 * 文件系统扫描
 *
 * @author Ryan Liu <azhai@126.com>
 */
class FileSystem
{
    protected $extname = '';
    
    public function __construct($extname = '')
    {
        $this->extname = $extname;
    }
    
    /**
     * 删除空子目录
     * @param string $dir 要删除的目录
     * @param float $freq 执行的机会概率
     */
    public static function removeEmptyDirs($dir, $freq = CACHE_UNLINK_EMPTY_FREQ)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            return;
        }
        if ($freq < 1 && $freq < mt_rand(1, 10000) / 10000) {
            return;
        }
        $is_empty = true;
        $nodes = glob($dir . DS . '*');
        foreach ($nodes as $node) {
            if (is_dir($node)) {
                //避免短路操作
                $node_is_empty = self::removeEmptyDirs($node, 1);
                $is_empty = $is_empty && $node_is_empty;
            } else {
                $is_empty = false;
            }
        }
        if ($is_empty) {
            @rmdir($dir);
        }
        return $is_empty;
    }
    
    /**
     * 循环删除目录及其下的文件
     * @param string $dir 要删除的目录或文件
     * @param array $ignores 忽略的目录或文件
     */
    public static function removeAllFiles($path, array $ignores = [])
    {
        if (!file_exists($path)) {
            return;
        }
        if (is_file($path)) {
            return @unlink($path);
        }
        $is_success = true;
        $nodes = glob($path . DS . '*');
        foreach ($nodes as $node) {
            if (in_array($node, $ignores, true)) {
                $node_is_removed = false;
            } else if (is_dir($node)) {
                //避免短路操作
                $node_is_removed = self::removeAllFiles($node);
            } else {
                $node_is_removed = @unlink($node);
            }
            $is_success = $is_success && $node_is_removed;
        }
        if ($is_success) {
            @rmdir($path);
        }
        return $is_success;
    }
    
    /**
     * 遍历所有节点
     */
    public static function traverse(array& $nodes, $func, $prefix = '')
    {
        $result = [];
        foreach ($nodes as $slug => $node) {
            $url = $prefix . '/' . $slug;
            if (isset($node['nodes'])) {
                $children = self::traverse($node['nodes'], $func, $url);
            }
            $result[] = Common::execFunctionArray($func, [$node, $url, $children]);
        }
        return $result;
    }
    
    /**
     * 文件名排序
     */
    public static function sortNames(array& $files, $attr, $is_desc = false, $is_numeric = false)
    {
        if ($is_numeric) {
            $func_body = 'return $a["' . $attr . '"] - $b["' . $attr . '"];';
        } else {
            $func_body = 'return strcasecmp($a["' . $attr . '"], $b["' . $attr . '"]);';
        }
        $params = $is_desc ? '$b, $a' : '$a, $b';
        uasort($files, create_function($params, $func_body));
    }
    
    /**
     * 清除名称前面表示次序的数字
     */
    public static function slugifyName($filename, $sep = '_')
    {
        $pieces = explode($sep, $filename, 2);
        if (count($pieces) === 2 && is_numeric($pieces[0])) {
            $filename = $pieces[1];
        }
        $slugname = Common::slugify($filename);
        return array($slugname, $filename);
    }
    
    /**
     * 更新文件信息
     */
    public static function adjustFile(array& $result, $filepath)
    {
        $changed = false;
        $mtime = filemtime($filepath);
        if (!isset($result['mtime']) || $result['mtime'] !== $mtime) {
            $changed = true;
            $result['mtime'] = $mtime;
            $result['sha1'] = sha1_file($filepath);
            $doc = new Markdoc($filepath);
            if ($title = $doc->getMetaData('title')) {
                $result['title'] = $title;
            }
        }
        return $changed;
    }
    
    /**
     * 更新目录信息
     */
    public static function adjustDir(array& $result, $mtime)
    {
        $changed = false;
        if (!isset($result['mtime']) || $result['mtime'] !== $mtime) {
            $changed = true;
            $result['mtime'] = $mtime;
            $result['sha1'] = sha1(json_encode($result['nodes']));
        }
        return $changed;
    }
    
    public function initNode(array& $nodes, $nodename, $nodepath, $is_file = false)
    {
        if ($is_file && $this->extname) {
            $filename = basename($nodename, $this->extname);
            assert($filename !== $nodename); //保证扩展名相符
        } else {
            $filename = $nodename;
        }
        list($slugname, $filename) = self::slugifyName($filename);
        $slugname = strtolower($slugname);
        if (!isset($nodes[$slugname])) {
            $nodes[$slugname] = [
                'path' => $nodepath,
                'title' => $filename,
                'is_file' => $is_file ? 1 : 0,
            ];
        }
        return $slugname;
    }
    
    /**
     * 扫描目录下的子目录和文件
     */
    public function discover(array& $result, $rootdir, $dirpath = '')
    {
        if (!isset($result['nodes'])) {
            $result['nodes'] = [];
        }
        $dir_fullpath = $dirpath ? $rootdir . DS . $dirpath : $rootdir;
        $max_mtime = filemtime($dir_fullpath . DS . '.');
        $nodes = scandir($dir_fullpath);
        foreach ($nodes as $nodename) {
            if (substr($nodename, 0, 1) === '.') {
                continue; //忽略目录如 . .. .git
            }
            $nodepath = $dirpath ? $dirpath . DS . $nodename : $nodename;
            $fullpath = $rootdir . DS . $nodepath;
            if (is_file($fullpath)) {
                $slugname = $this->initNode($result['nodes'], $nodename, $nodepath, true);
                self::adjustFile($result['nodes'][$slugname], $fullpath);
                $mtime = $result['nodes'][$slugname]['mtime'];
                if ($mtime > $max_mtime) {
                    $max_mtime = $mtime;
                }
            } else {
                $slugname = $this->initNode($result['nodes'], $nodename, $nodepath, false);
                $this->discover($result['nodes'][$slugname], $rootdir, $nodepath);
            }
        }
        $changed = self::adjustDir($result, $max_mtime);
        return $changed;
    }
    
    /**
     * 返回目录的组织结构
     */
    public function getOrganiz($dir, $cache = null)
    {
        $rootdir = realpath($dir);
        if (!is_dir($rootdir)) {
            return;
        }
        $result = $cache ? $cache->get('docs', []) : [];
        $changed = $this->discover($result, $rootdir);
        if ($changed) {
            if (isset($result['nodes']['index'])) { //首页放在最前
                $result['nodes']['index']['is_file'] = -1;
            }
            self::sortNames($result['nodes'], 'is_file', false, true);
            if ($cache) {
                $cache->put('docs', $result);
            }
        }
        return $result;
    }
}
