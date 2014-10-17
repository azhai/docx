<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */


/**
 * HTML混合模板
 */
class DOCX_Templater
{
    public $caches = array();
    public $globals = array();
    protected $source_dir = '';
    protected $cache_dir = '';
    protected $extend_files = array();
    protected $template_blocks = array();
    protected $current_block = '';
    protected $current_file = '';
    protected $current_cache = null;

    /**
     * 构造函数，设置缓存和默认模板目录
     * @param object/null $cache 模板缓存
     */
    public function __construct($source_dir, $cache_dir = '')
    {
        if ($source_dir) {
            $this->setSourceDir($source_dir);
        }
        $this->cache_dir = rtrim($cache_dir, ' /\\');
    }

    /**
     * 将内容字符串中的变量替换掉
     * @param string $content 内容字符串
     * @param array $context 变量数组
     * @return string 当前内容
     */
    public static function replaceWith($content, array $context = array())
    {
        if (! empty($context)) {
            $keys = array_map(create_function('$x', 'return "\${$x}";'), array_keys($context));
            $holders = array_map(create_function('$x', 'return "%$x\$s";'), range(1, count($context)));
            $content = vsprintf(str_replace($keys, $holders, $content), $context);
        }
        return $content;
    }

    /**
     * 设置模板目录
     * @param string $source_dir 模板目录
     */
    public function setSourceDir($source_dir)
    {
        $this->source_dir = rtrim($source_dir, ' /\\');
        if (! file_exists($this->source_dir)) {
            @mkdir($this->source_dir, 0777, true);
        }
    }
    
    public function addCache($cache_name, & $content)
    {
        if (! isset($this->caches[$cache_name])) {
            $cache = new DOCX_Cache($this->cache_dir . DIRECTORY_SEPARATOR . $cache_name);
            $this->caches[$cache_name] = & $cache;
        } else {
            $cache = & $this->caches[$cache_name];
        }
        $cache->connect($content)->load();
        return $cache;
    }

    /**
     * 更新全局变量，全局变量可作为编译器变量
     * @param array $globals 全局变量数组
     */
    public function updateGlobals(array $globals)
    {
        $this->globals = array_merge($this->globals, $globals);
    }

    /**
     * 获得模板文件绝对路径，也可能是被编译之后的输出文件
     * @param string $template_file 模板文件，相对路径
     * @return string 模板文件，绝对路径
     */
    public function prepareFile($template_file)
    {
        return $this->source_dir . '/' . $template_file;
    }

    /**
     * 输出内容
     * @param string $template_file 模板文件，相对路径
     * @param array $context 模板变量数组
     * @param bool $return 直接输出/返回字符串
     */
    public function render($template_file, array $context = array(), $return = false)
    {
        $this->current_file = $template_file;
        if ($return) {
            ob_start();
        }
        extract($this->globals);
        extract($context);
        include $this->prepareFile($this->current_file); // 入口模板
        if (! empty($this->extend_files)) {
            $layout_file = array_pop($this->extend_files);
            foreach ($this->extend_files as $file) { // 中间继承模板
                include $this->prepareFile($file);
            }
            extract($this->template_blocks);
            include $this->prepareFile($layout_file); // 布局模板
        }
        if ($return) {
            $result = ob_get_clean();
            return $result ? trim($result) : '';
        }
    }

    /**
     * 标示上级模板，需要全部标示在开头，无法象Twig一样继承
     * @param type $template_file
     */
    public function extendTpl($template_file)
    {
        array_push($this->extend_files, $template_file);
    }

    /**
     * 包含其他文件的内容
     * NOTE:
     * 必须自己传递context，如果想共享render中的context，请在模板中
     * 使用 include $this->getTemplateFile($template_file); 代替 $this->includeTpl($template_file);
     *
     * @param string $template_file 被包含文件，相对路径
     * @param array $context 局部变量数组
     * @param bool $cached 是否缓存结果
     */
    public function includeTpl($template_file, array $context = array(), $cached = false)
    {
        extract($this->globals);
        extract($context);
        include $this->prepareFile($template_file);
            
        /*if ($cached && $this->cache_dir) {
            $cache_name = basename($template_file, '.php') . '.inc.html';
            $this->addCache($cache_name, $include_html);
        }*/
    }

    /**
     * 标示区块开始
     * @param string $block_name 区块名称
     * @param bool $cached 是否缓存区块
     */
    public function blockStart($block_name = 'content', $cached = false)
    {
        $this->current_block = $block_name;
        ob_start();
        /*if ($cached && $this->cache_dir) {
            $ext_name = '.' . $this->current_block . '.html';
            $cache_name = basename($this->current_file, '.php') . $ext_name;
            $this->current_cache = $this->addCache($cache_name, $block_html);
        }*/
    }

    /**
     * 标示区块结束
     */
    public function blockEnd()
    {
        $block_html = trim(ob_get_clean());
        $this->template_blocks[$this->current_block] = $block_html;
        /*
        if (! isset($this->template_blocks[$this->current_block])) {
            $block_html = trim(ob_get_clean());
            $this->template_blocks[$this->current_block] = $block_html;
            if ($this->current_cache) { //缓存区块动作实际上是在这里进行
                $this->current_cache->save();
            }
        }
        */
    }
}
