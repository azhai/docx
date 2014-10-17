<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */


/**
 * 文档内容解析器
 */
class DOCX_Parser
{
    public static $meta_keys = array(
        'layout', 'date', 'created', 'title', 'slug',
        'author', 'category', 'tags', 'comments'
    );
    public $metadata = array();
    protected $filename = '';
    protected $dochtml = '';
    protected $markdown = '';
    protected $metatext = '';
    protected $headsize = 0;

    //解析MetaData
    public static function parseMetaLine($line)
    {
        $pieces = explode(':', $line);
        if (count($pieces) === 2) {
            $pieces[0] = strtolower(trim($pieces[0]));
            if (in_array($pieces[0], self::$meta_keys, true)) {
                $pieces[1] = trim($pieces[1]);
                return $pieces;
            }
        }
    }

    public function normalizeMetaData(array& $metadata)
    {
        //规范化元素和内容
        if (!isset($metadata['title'])) {
            $metadata['title'] = clean_ord($this->filename);
        }
        if (!isset($metadata['author'])) {
            $metadata['author'] = '';
        }
        if (isset($metadata['tags']) && is_string($metadata['tags'])) {
            $metadata['tags'] = array_map('trim', explode(',', $metadata['tags']));
        }
        if (!isset($metadata['date'])) {
            $metadata['date'] = filemtime($this->filename);
        } else if (!is_numeric($metadata['date'])) {
            $metadata['date'] = strtotime($metadata['date']);
        }
        return $this->metadata = & $metadata;
    }

    /**
     * @param string $filename 文件路径
     * @param string/false $metakey 要获取的元素名
     * @return mixed
     */
    public function parseMetaData($filename, $metakey = false, $readall = false)
    {
        if ($this->filename !== $filename) {
            $this->filename = $filename;
            $this->metatext = '';
            $metadata = array();
            
            $fh = fopen($this->filename, 'rb');
            if ($fh === false) {
                return false; //打开失败
            }
            $line = fgets($fh);
            $line = $line ? trim($line) : '';
            $line = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $line);
            while(!$line || !trim($line)); //跳过开头的空行
            $open_tag_len = 0;
            while ($len = strlen($line)) {
                $this->metatext .= $line . "\n";
                if ($len >= 3 && $line === str_repeat('-', $len)) { //使用---分隔区域
                    if ($open_tag_len <= 0) {
                        $open_tag_len = $len;
                    } else if ($open_tag_len === $len) {
                        $line = '';
                        break;
                    }
                } else if ($meta = self::parseMetaLine($line)) {
                    $metadata[$meta[0]] = $meta[1];
                } else {
                    break;
                }
                $line = fgets($fh);
                $line = $line ? trim($line) : '';
            }
            
            if ($readall) {
                $this->markdown = $line . fread($fh, filesize($this->filename)); //剩余文本，内容部分
                $this->headsize = 0; //读完了markdown
            } else {
                $this->markdown = $line;
                $this->headsize = ftell($fh);
            }
            fclose($fh);
            $this->metatext = trim($this->metatext);
            $this->normalizeMetaData($metadata);
        }
        
        if ($metakey === false) {
            return $this->metadata; //metadata数组
        } else if (isset($this->metadata[$metakey])) {
            return $this->metadata[$metakey]; //metadata其中一个元素
        }
    }

    /**
     * @param string $filename 文件路径
     */
    public function parseAll($filename)
    {
        if ($this->filename !== $filename) {
            //将MetaData和Content分开
            if (! is_readable($filename)) {
                $this->metadata = array('title' => 'Oh No');
                $this->dochtml = '<h3>抱歉，找不到页面~!</h3>';
            } else {
                $this->parseMetaData($filename, false, true);
            }
        } else if ($this->headsize > 0 || ! $this->markdown) { //markdown没有读完
            $content = file_get_contents($this->filename);
            $this->markdown = substr($content, $this->headsize);
        }
        if (! $this->dochtml && $this->markdown) { //使用外部解析器解析内容
            $this->dochtml = Parsedown::instance()->text($this->markdown);
        }
        $textdata = array(
            'metatext' => $this->metatext,
            'markdown' => $this->markdown,
            'dochtml' => $this->dochtml,
        );
        return array_merge($this->metadata, $textdata);
    }
}
