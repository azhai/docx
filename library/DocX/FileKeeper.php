<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */


defined('CACHE_DIR_MODE') or define('CACHE_DIR_MODE', 0755);    //缓存目录权限
defined('CACHE_FILE_MODE') or define('CACHE_FILE_MODE', 0666);  //缓存文件权限


class DOCX_FileKeeper
{
    public $csv_delimiter = ",";
    protected $extname = false;
    
    protected function prepare($filepath, $create = false)
    {
        assert(is_string($filepath));
        if ($create && ! is_file($filepath)) { // 尝试创建目录和文件以及赋予权限
            try {
                @mkdir(dirname($filepath), CACHE_DIR_MODE, true);
                @touch($filepath);
                @chmod($filepath, CACHE_FILE_MODE);
            } catch (\Exception $e) {
                return false;
            }
        }
        if ($this->extname === false) {
            $this->extname = pathinfo($filepath, PATHINFO_EXTENSION);
        }
        return $filepath;
    }
    
    public function read($filepath, $default = null)
    {
        $data = null;
        if ($filepath = $this->prepare($filepath, true)) {
            $read = '_read' . $this->extname;
            if (method_exists($this, $read)) {
                $data = $this->$read($filepath);
            } else {
                $data = $this->_read($filepath);
            }
        }
        return (is_null($data) || $data === false) ? $default : $data;
    }
    
    public function write($filepath, & $data, $ttl = 0)
    {
        if ($filepath = $this->prepare($filepath, true)) {
            $write = '_write' . $this->extname;
            if (method_exists($this, $write)) {
                return $this->$write($filepath, $data);
            } else {
                return $this->_write($filepath, $data);
            }
        }
    }
    
    public function delete($filepath)
    {
        if ($filepath = $this->prepare($filepath, false)) {
            unlink($filepath);
            DOCX_Directory::unlinkEmpty(dirname($filepath));
        }
    }
    
    protected function _read($filepath)
    {
        if ($data = file_get_contents($filepath)) {
            $unformat = '_unformat' . $this->extname;
            if (method_exists($this, $unformat)) {
                return $this->$unformat($data);
            } else {
                return $data;
            }
        }
    }
    
    protected function _write($filepath, & $data, $ttl = 0)
    {
        $format = '_format' . $this->extname;
        if (method_exists($this, $format)) {
            $content = $this->$format($data);
        } else {
            $content = $data;
        }
        return file_put_contents($filepath, $content, LOCK_EX);
    }
    
    protected function _readPHP($filepath)
    {
        if (filesize($filepath) > 0) {
            return (include $filepath);
        }
    }
    
    protected function _formatPHP(& $data)
    {
        return "<?php \nreturn " . var_export($data, true) . ";\n";
    }
    
    protected function _readCSV($filepath)
    {
        $data = array();
        if (($fh = fopen($filepath, 'rb')) !== false) {
            while (($row = fgetcsv($fh, 0, $this->csv_delimiter)) !== false) {
                $data[] = $row;
            }
            fclose($fh);
        }
        return $data;
    }
    
    protected function _writeCSV($filepath, & $data)
    {
        $size = 0;
        if (($fh = fopen($filepath, 'wb')) !== false) {
            foreach ($data as $row) {
                $size += fputcsv($fh, $row, $this->csv_delimiter);
            }
            fclose($fh);
        }
        return $size;
    }
    
    protected function _formatJSON(& $data)
    {
        return json_encode($data);
    }
    
    protected function _unformatJSON(& $data)
    {
        return json_decode($data, true);
    }
    
    protected function _formatYAML(& $data)
    {
        if (extension_loaded('yaml')) {
            return yaml_emit($data, YAML_UTF8_ENCODING, YAML_LN_BREAK);
        } else if (class_exists('Yaml\\Dumper', true)) {
            $dumper = new Yaml\Dumper();
            return $dumper->dump($data);
        }
    }
    
    protected function _unformatYAML(& $data)
    {
        if (extension_loaded('yaml')) {
            return yaml_parse($data);
        } else if (class_exists('Yaml\\Parser', true)) {
            $parser = new Yaml\Parser();
            return $parser->parse($data);
        }
    }
}
