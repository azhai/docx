<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */

defined('CACHE_UNLINK_EMPTY_FREQ') or define('CACHE_UNLINK_EMPTY_FREQ', 0.2); //删除空目录的概率


class DOCX_Directory
{
    public $dir = '';
    public $extname = false;
    public $files = array();
    
    public function __construct($path = '.', $extname = false)
    {
        $path = realpath($path);
        if (is_dir($path)) {
            $this->dir = $path;
            $this->extname = $extname;
        }
    }
    
    /**
     * 删除空子目录
     * @param string $dir 要删除的目录
     * @param float $freq 执行的机会概率
     */
    public static function removeEmpty($dir, $freq = CACHE_UNLINK_EMPTY_FREQ)
    {
        if (! file_exists($dir)) {
            return 0;
        }
        $counter = 0;
        if ($freq >= 1 || $freq >= mt_rand(1, 10000) / 10000) {
            $files = scandir($dir);
            if (is_array($files) && count($files) === 2) {
                if ($files[0] === '.' && $files[1] === '..') {
                    @rmdir($dir);
                    $counter ++;
                }
            }
        }
        return $counter;
    }
    
    /**
     * 删除目录以及其下子目录和文件
     * @param string $dir 要删除的目录
     */
    public static function removeAll($dir, array $excludes = array())
    {
        if (! file_exists($dir)) {
            return 0;
        }
        $counter = 0;
        $children = scandir($dir);
        foreach ($children as $child) {
            if ($excludes && in_array($child, $excludes)) {
                continue;
            }
            $child_path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $child;
            if (is_dir($child_path)) {
                if ($child === '.' || $child === '..') {
                    continue;
                }
                self::removeAll($child_path);
            } else {
                unlink($child_path);
                $counter ++;
            }
        }
        if (! in_array('.', $excludes)) {
            rmdir($dir);
        }
        return $counter;
    }
    
    /**
     * 清除名称前面表示次序的数字
     */
    public static function eraseOrd($name, $sep = '_', $ext = false)
    {
        $name = $ext ? basename($name, $ext) : basename($name);
        $pieces = explode($sep, $name, 2);
        if (count($pieces) === 2 && is_numeric($pieces[0])) {
            return $pieces[1];
        }
        return $name;
    }
    
    public function addCache($cache_file)
    {
        $cache = new DOCX_Cache($cache_file);
        $cache->connect($this->files)->load();
        return $this;
    }
    
    public function getFiles()
    {
        if (empty($this->files)) {
            $this->scan($this->dir);
        }
        return $this->files;
    }
    
    /**
     * 递归地扫描文件、子目录
     */
    public function scan($path, $prefix = '.')
    {
        if ($dh = opendir($path)) {
            while (($basename = readdir($dh)) !== false) {
                if (substr($basename, 0, 1) === '.') {
                    continue; //忽略目录如 . .. .git
                }
                $child = $path . DIRECTORY_SEPARATOR . $basename;
                $extname = '.' . pathinfo($basename, PATHINFO_EXTENSION);
                $urlname = self::eraseOrd($basename, '_', $this->extname ? $this->extname : $extname);
                if (is_dir($child)) {
                    $pathname = $prefix . '/' . $urlname;
                    $this->files[$pathname] = array();
                    $this->scan($child, $pathname);
                } else if ($this->extname === false || $this->extname === $extname) {
                    $this->files[$prefix][$urlname] = array(
                        'mtime' => filemtime($child), 'fname' => $child,
                    );
                }
            }
            closedir($dh);
            ksort($this->files);
        }
        return $this;
    }
    
    /**
     * 比较和当前目录的区别
     */
    public function diff()
    {
        $all_files = $this->getFiles();
        $this->files = array();
        $this->scan($this->dir);
        $result = array(
            'deldirs' => array(), 'delfiles' => array(), 
            'addfiles' => array(), 'modfiles' => array()
        );
        $result['deldirs'] = array_diff_key($all_files, $this->files);
        foreach ($this->files as $dir => $files) {
            $old_files = isset($all_files[$dir]) ? $all_files[$dir] : array();
            if ($delfiles = array_diff_key($old_files, $files)) {
                $result['delfiles'][$dir] = $delfiles;
            }
            $addfiles = array();
            $modfiles = array();
            foreach ($files as $file => $metas) {
                if (! isset($old_files[$file])) {
                    $addfiles[$file] = $metas;
                } else if ($metas['mtime'] > $old_files[$file]['mtime']) {
                    $modfiles[$file] = $metas;
                }
            }
            if (! empty($addfiles)) {
                $result['addfiles'][$dir] = & $addfiles;
            }
            if (! empty($modfiles)) {
                $result['modfiles'][$dir] = & $modfiles;
            }
        }
        return $result;
    }
}
