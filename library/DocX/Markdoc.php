<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */


/**
 * MarkDown文档
 */
class DOCX_Markdoc
{
    public static $meta_keys = array(
        'layout', 'date', 'created', 'title', 'slug',
        'author', 'category', 'tags', 'comments'
    );
    protected static $instances = array();
    protected $metadata = array();
    protected $filename = '';
    protected $htmldoc = '';
    protected $markdown = '';
    protected $metatext = '';
    protected $headsize = -1;
    
    /**
     * @param string $filename 文件路径
     */
    protected function __construct($filename)
    {
        $this->filename = $filename;
    }
    
    public static function getInstance($filename)
    {
        if (! isset(self::$instances[$filename])) {
            self::$instances[$filename] = new self($filename);
        }
        return self::$instances[$filename];
    }

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
        if (! isset($metadata['title'])) {
            $metadata['title'] = clean_ord($this->filename);
        }
        if (! isset($metadata['author'])) {
            $metadata['author'] = '';
        }
        if (isset($metadata['tags']) && is_string($metadata['tags'])) {
            $metadata['tags'] = array_map('trim', explode(',', $metadata['tags']));
        }
        if (! isset($metadata['date'])) {
            $metadata['date'] = filemtime($this->filename);
        } else if (!is_numeric($metadata['date'])) {
            $metadata['date'] = strtotime($metadata['date']);
        }
        return $this->metadata = & $metadata;
    }

    /**
     * 解析META
     * @param bool $readall 读完正文
     */
    public function parseMetaData($readall = false)
    {
        //将MetaData和Content分开
        if (! is_readable($this->filename)) {
            $this->metadata = array('title' => 'Oh No');
            $this->htmldoc = '<h3>抱歉，找不到页面~!</h3>';
            $this->headsize = 0; //读完了markdown
            return $this->metadata;
        }
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
        return $this->normalizeMetaData($metadata);
    }
    
    /**
     * @param string/false $metakey 要获取的元素名
     * @return mixed
     */
    public function getMetaData($metakey = false)
    {
        if ($this->headsize < 0) {
            $this->parseMetaData(false);
        }
        if ($metakey === false) {
            return $this->metadata; //metadata数组
        } else if (isset($this->metadata[$metakey])) {
            return $this->metadata[$metakey]; //metadata其中一个元素
        }
    }

    /**
     * 
     */
    public function getPageData()
    {
        $this->getMetaData(false);
        if ($this->headsize > 0 || ! $this->markdown) { //markdown没有读完
            $content = file_get_contents($this->filename);
            $this->markdown = substr($content, $this->headsize);
            $this->headsize = 0; //读完了markdown
        }
        if (! $this->htmldoc && $this->markdown) { //使用外部解析器解析内容
            $this->htmldoc = Parsedown::instance()->text($this->markdown);
        }
        $textdata = array(
            'metatext' => $this->metatext,
            'markdown' => $this->markdown,
            'htmldoc' => $this->htmldoc,
        );
        return array_merge($this->metadata, $textdata);
    }
    
    public function update($metatext, $markdown)
    {
        $content = $metatext . "\n\n" . $markdown;
        file_put_contents($this->filename, $content, LOCK_EX);
        $this->parseMetaData(true);
        return $this;
    }
}
