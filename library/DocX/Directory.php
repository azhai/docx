<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */

defined('CACHE_UNLINK_EMPTY_FREQ') or define('CACHE_UNLINK_EMPTY_FREQ', 0.2); //删除空目录的概率


class DOCX_Directory
{
    const ATTR_FILE_MTIME = 'mtime';
    const ATTR_FILE_MD5 = 'md5';
    const ATTR_FILE_SHA = 'sha';
    const ATTR_FILE_SIZE = 'size';
    public $dir = '';
    public $extname = false;
    public $files = array();
    public $order_prefixes = array();
    public $order_attr = '';
    public $order_desc = false;
    protected $attr_cmp = '';
    protected $changed = false;
    
    public function __construct($path = '.', $extname = false, $attr_cmp = self::ATTR_FILE_MTIME)
    {
        $path = realpath($path);
        if (is_dir($path)) {
            $this->dir = $path;
            $this->extname = $extname;
        }
        $this->attr_cmp = $attr_cmp;
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
    
    public static function getAttrFunc($attr_cmp)
    {
        switch ($attr_cmp) {
            case self::ATTR_FILE_MD5:
                return 'md5_file';
            case self::ATTR_FILE_SHA:
                return 'sha1_file';
            case self::ATTR_FILE_SIZE:
                return 'filesize';
            case self::ATTR_FILE_MTIME:
            default:
                return 'filemtime';
        }
    }
    
    /**
     * 文件排序
     */
    public static function sortFiles(array& $files, $attr_cmp, $order_desc = false)
    {
        if (in_array($attr_cmp, array(self::ATTR_FILE_SIZE, self::ATTR_FILE_MTIME))) {
            $func_body = 'return $a["' . $attr_cmp . '"] - $b["' . $attr_cmp . '"];';
        } else {
            $func_body = 'return strcasecmp($a["' . $attr_cmp . '"], $b["' . $attr_cmp . '"]);';
        }
        $params = $order_desc ? '$b, $a' : '$a, $b';
        uasort($files, create_function($params, $func_body));
    }
    
    public function sortSubDir($subdir)
    {
        if (empty($this->order_prefixes) || empty($this->order_attr)) {
            return;
        }
        $subname = ltrim($subdir, '.');
        foreach ($this->order_prefixes as $prefix) {
            if (starts_with($subname, $prefix)) {
                self::sortFiles($this->files[$subdir], $this->order_attr, $this->order_desc);
            }
        }
    }
    
    public function setSorting(array $order_prefixes, $order_attr, $order_desc = false)
    {
        if ($order_prefixes && $order_attr === $this->attr_cmp) {
            $this->order_prefixes = $order_prefixes;
            $this->order_attr = $order_attr;
            $this->order_desc = $order_desc;
        }
        return $this;
    }
    
    public function addCache($cache_file)
    {
        $cache = new DOCX_Cache($cache_file);
        $cache->connect($this->files)->load($this->changed);
        return $this;
    }
    
    /**
     * 递归地扫描文件、子目录
     */
    public function scan(array& $files, $path, $prefix = '.')
    {
        $filenames = scandir($path);
        if (version_compare(PHP_VERSION, '5.4.0') < 0) {
            sort($filenames); //按文件名/目录名排序
        }
        foreach ($filenames as $basename) {
            if (substr($basename, 0, 1) === '.') {
                continue; //忽略目录如 . .. .git
            }
            $child = $path . DIRECTORY_SEPARATOR . $basename;
            $extname = '.' . pathinfo($basename, PATHINFO_EXTENSION);
            $urlname = self::eraseOrd($basename, '_', $this->extname ? $this->extname : $extname);
            if (is_dir($child)) {
                $pathname = $prefix . '/' . $urlname;
                $files[$pathname] = array();
                $this->scan($files, $child, $pathname);
            } else if ($this->extname === false || $this->extname === $extname) {
                $attr_func = self::getAttrFunc($this->attr_cmp);
                $files[$prefix][$urlname] = array(
                    'fname' => $child, $this->attr_cmp => $attr_func($child),
                );
            }
        }
        return $this;
    }
    
    public function getFiles()
    {
        if (empty($this->files)) {
            $this->scan($this->files, $this->dir);
            if ($this->order_prefixes && $this->order_attr) {
                foreach ($this->files as $subdir) {
                    $this->sortSubDir($subdir);
                }
            }
        }
        return $this->files;
    }
    
    /**
     * 比较和当前目录的区别
     */
    public function getDiffs()
    {
        $curr_files = array();
        $this->scan($curr_files, $this->dir);
        $result = array(
            'deldirs' => array(), 'delfiles' => array(), 
            'addfiles' => array(), 'modfiles' => array()
        );
        $result['deldirs'] = array_diff(array_keys($this->files), array_keys($curr_files));
        if (! empty($result['deldirs'])) {
            foreach ($result['deldirs'] as $dir) {
                unset($this->files[$dir]);
            }
            $this->changed = true;
        }
        foreach ($curr_files as $dir => & $files) {
            $old_files = isset($this->files[$dir]) ? $this->files[$dir] : array();
            if ($delfiles = array_diff(array_keys($old_files), array_keys($files))) {
                $result['delfiles'][$dir] = $delfiles;
                if (! empty($delfiles)) {
                    foreach ($delfiles as $file) {
                        unset($this->files[$dir][$file]);
                    }
                    $this->changed = true;
                }
            }
            $addfiles = array();
            $modfiles = array();
            foreach ($files as $file => & $metas) {
                if (! isset($old_files[$file])) {
                    $addfiles[] = $file;
                    $this->files[$dir][$file] = & $metas;
                } else if ($metas[$this->attr_cmp] !== $old_files[$file][$this->attr_cmp]) {
                    $modfiles[] = $file;
                    $this->files[$dir][$file] = & $metas;
                }
            }
            if (! empty($addfiles)) {
                $result['addfiles'][$dir] = $addfiles;
                $this->changed = true;
            }
            if (! empty($modfiles)) {
                $result['modfiles'][$dir] = $modfiles;
                $this->changed = true;
            }
            $this->sortSubDir($dir);
        }
        return $result;
    }
}
