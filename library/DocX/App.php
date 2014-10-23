<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */


/**
 * 应用程序
 */
class DOCX_App
{
    const URL_TYPE_QUERY = 0;       //参数
    const URL_TYPE_TAIL = 1;        //地址
    const URL_TYPE_REWRITE = 2;     //重写
    const URL_TYPE_AUTO = 9;        //根据url_prefix判断
    const HOME_PAGE_URL = '/index';
    const ADMIN_URLPRE = '/admin';

    public $document_dir = '';
    public $public_dir = '';
    public $cache_dir = '';
    public $theme_dir = '';
    public $assets_dir = '';
    
    protected $abs_prefix = false;
    protected $url_type = self::URL_TYPE_AUTO;
    protected $offset = 0;
    protected $index = 'index.php';
    protected $route = 'r';
    protected $docs_dir = null;
    protected $toppest_url = '';
    protected $options = array(
        'url_prefix' => '/index.php',       #首页网址
        'url_type' => self::URL_TYPE_AUTO,  #网址类型
        'title' => "我的文档",              #站名
        'tagline' => false,                 #封面宣言
        'reading' => "开始阅读文档",        #封面阅读按钮上的文字
        'cover_image' => '',                #封面图片
        'author' => '',                     #默认作者
        'layout' => 'post',                 #默认模板布局
        'document_dir' => 'documents',      #原始文档目录
        'public_dir' => 'public',           #静态输出目录
        'theme_dir' => 'theme',             #主题模板目录
        'assets_dir' => 'theme/assets',     #资源目录
        'cache_dir' => 'cache',
        'cache_ext' => '.json',
        'urlext_php' => '/',                #动态网页扩展名
        'urlext_html' => '.html',           #静态网页扩展名
        'timezone' => 'Asia/Shanghai',
        'blog_sorting' => array(),          #文件按更新时间排列，用于博客
        'date_format' => 'Y年n月j日 星期w',
        'repo' => false,                    #github仓库url
        'links' => array(),                 #友情链接
        'google_analytics' => false,
        'ignore' => array('folders' => array('.git', )),
        #需要安装wkhtmltopdf、fontconfig、一款中文字体如文泉驿
        'wkhtmltopdf' => null,              #pdf工具路径
        'greetings' => array(),             #供随机展示的语录
    );

    public function __construct($options = false)
    {
        if (is_array($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->document_dir = self::getRealPath($this->getOption('document_dir'));
        $this->public_dir = self::getRealPath($this->getOption('public_dir'));
        $this->cache_dir = self::getRealPath($this->getOption('cache_dir'));
        $this->theme_dir = self::getRealPath($this->getOption('theme_dir'));
        $this->assets_dir = self::getRealPath($this->getOption('assets_dir'));
        $this->abs_prefix = $this->getAbsPrefix();
    }

    public static function getRealPath($dir)
    {
        $path = DOCX_ROOT . DIRECTORY_SEPARATOR . trim($dir, '/');
        $realpath = realpath($path);
        if ($realpath === false) {
            @mkdir($path, 0755, true);
            $realpath = realpath($path);
        }
        return $realpath;
    }

    public static function isHome($slug)
    {
        return $slug === 'home';
    }

    public static function getRawURL()
    {
        $raw_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return is_null($raw_url) ? '/' : urldecode($raw_url); //汉字逆向转码
    }
    
    public function getConstant($name)
    {
        return constant(__CLASS__ . '::' . $name);
    }
    
    public function getOption($key = false)
    {
        if ($key === false) {
            return array_merge($this->options, $_ENV);
        } else if (isset($_ENV[$key])) {
            return $_ENV[$key];
        } else if (isset($this->options[$key])) {
            return $this->options[$key];
        }
    }

    public function getAbsPrefix()
    {
        if ($this->abs_prefix !== false) {
            return $this->abs_prefix;
        }
        $this->url_type = intval($this->getOption('url_type'));
        $this->abs_prefix = rtrim($this->getOption('url_prefix'), '/');
        $url_types = array(
            self::URL_TYPE_QUERY, self::URL_TYPE_TAIL, self::URL_TYPE_REWRITE
        );
        if (! in_array($this->url_type, $url_types)) { //根据网址前缀判断类型
            $this->url_type = $this->fixURLType();
        }
        return $this->abs_prefix;
    }
    
    public function fixURLType()
    {
        $pattern = '!([a-zA-Z0-9_-]+\.php)!';
        if (preg_match($pattern, $this->abs_prefix, $matches, PREG_OFFSET_CAPTURE)) {
            list($this->index, $position) = $matches[0];
            $pattern = '!^\?([a-zA-Z0-9_-]+)=!';
            $query = substr($this->abs_prefix, $position + strlen($this->index));
            if (preg_match($pattern, $query, $matches)) {
                $this->url_type = self::URL_TYPE_QUERY;
                $this->route = $matches[1];
            } else {
                $this->url_type = self::URL_TYPE_TAIL;
            }
            $this->abs_prefix = rtrim($this->abs_prefix, '?&');
        } else {
            $this->url_type = self::URL_TYPE_REWRITE;
            $this->abs_prefix = rtrim($this->abs_prefix, '/');
        }
        return $this->url_type;
    }

    public function getCurrURL($rstrip = false)
    {
        $raw_url = self::getRawURL();
        if ($this->url_type === self::URL_TYPE_QUERY) {
            $curr_url = '';
            if (isset($_GET[$this->route])) {
                $curr_url = '/' . trim($_GET[$this->route], '/') . '/';
            }
            $this->offset = - 99;
        } else {
            if ($this->url_type === self::URL_TYPE_REWRITE) {
                $raw_url = str_replace('/' . $this->index, '/', $raw_url);
                $this->offset = - 1;
            }
            $prelen = strlen($this->getAbsPrefix());
            //substr()陷阱，当string的长度等于start，将返回FALSE而不是''
            $curr_url = (strlen($raw_url) > $prelen) ? substr($raw_url, $prelen) : '';
        }
        return $rstrip ? rtrim($curr_url, '/') : $curr_url;
    }
    
    public function getURLJoin()
    {
        if ($this->url_type === self::URL_TYPE_QUERY && ! empty($_GET)) {
            $query = urldecode(http_build_query($_GET));
            return "?$query&";
        } else {
            return '?';
        }
    }

    public function getRelPrefix($curr_url = false)
    {
        if ($curr_url === false) {
            if ($this->url_type === self::URL_TYPE_QUERY) {
                return '.';
            }
            $curr_url = $this->getCurrURL(false);
            $depth = substr_count($curr_url, '/') + $this->offset;
        } else {
            $depth = substr_count($curr_url, '/');
        }
        return ($depth > 0) ? rtrim(str_repeat('../', $depth), '/') : '.';
    }

    public function getDocsDir()
    {
        if (is_null($this->docs_dir)) {
            $cache_file = $this->cache_dir . '/docs' . $this->getOption('cache_ext');
            $this->docs_dir = new DOCX_Directory($this->document_dir, '.md');
            if ($order_dirs = $this->getOption('blog_sorting')) {
                $this->docs_dir->setSorting($order_dirs, DOCX_Directory::ATTR_FILE_MTIME, true);
            }
            $diffs = $this->docs_dir->addCache($cache_file)->getDiffs();
            $this->updateMetas($diffs['addfiles']);
            $this->updateMetas($diffs['modfiles']);
        }
        return $this->docs_dir;
    }

    public function updateMetas($diffs)
    {
        foreach ($diffs as $dir => $files) {
            foreach ($files as $file) {
                $metadata = & $this->docs_dir->files[$dir][$file];
                $metadata['slug'] = $file;
                $metadata['url'] = ltrim($dir, '.') . '/' . $file;
                $markdoc = DOCX_Markdoc::getInstance($metadata['fname']);
                $metadata = array_merge($metadata, $markdoc->getMetaData());
            }
        }
    }

    public function getMetadata($find_url)
    {
        if ($find_url === self::ADMIN_URLPRE) {
            $find_url = self::HOME_PAGE_URL;
        }
        $dir = '.' . rtrim(dirname($find_url), '/');
        $file = basename($find_url);
        $docsdir = $this->getDocsDir();
        if (isset($docsdir->files[$dir]) && isset($docsdir->files[$dir][$file])) {
            return $docsdir->files[$dir][$file];
        }
    }

    public function dispatch()
    {
        $curr_url = $this->getCurrURL(true);
        $edit_mode = false;
        if (empty($curr_url) || in_array($curr_url, array('/', self::HOME_PAGE_URL))) {
            $curr_url = self::HOME_PAGE_URL;
        } else if ($curr_url === self::ADMIN_URLPRE) {
            $curr_url = self::HOME_PAGE_URL;
            $edit_mode = true;
        } else if (starts_with($curr_url, self::ADMIN_URLPRE . '/')) {
            //substr()陷阱，当string的长度等于start，将返回FALSE而不是''
            $curr_url = substr($curr_url, strlen(self::ADMIN_URLPRE));
            $edit_mode = true;
        }
        $metadata = $this->getMetadata($curr_url);
        if (is_null($metadata)) { //找不到页面，或者URL不正确
            die($curr_url);
        }
        $view = new DOCX_View($this, $metadata, '', $edit_mode);
        return $view;
    }

    public function run()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : false;
        if ($action === 'cleancache') {
            DOCX_Directory::removeAll($this->cache_dir, array('.'));
        } else if ($action === 'staticize') {
            $this->genPages();
            return $this->gotoStaticIfCould();
        } else if ($action === 'genpdf') {
            $pdf = $this->genPDF();
            return $pdf->send($this->getOption('title') . '.pdf', true);
        }
        $this->dispatch()->output();
    }
    
    public function gotoStaticIfCould()
    {
        $dir = dirname($this->getAbsPrefix());
        if (starts_with($this->public_dir, APP_ROOT)) {
            $dir .= substr($this->public_dir, strlen(APP_ROOT));
            $urlext = $this->getOption('urlext_html');
            return http_redirect($dir . '/index' . $urlext);
        }
    }

    public function getToppestPage()
    {
        if (! empty($this->toppest_url)) {
            return $this->toppest_url;
        }
        $docsdir = $this->getDocsDir();
        foreach ($docsdir->files as $dir => & $files) {
            foreach ($files as $file => & $metadata) {
                if (! self::isHome($metadata['slug'])) { //不是home的第一个页面
                    return $metadata['url'];
                }
            }
        }
    }

    public function genPages($target_dir = false)
    {
        if ($target_dir === false) {
            $target_dir = $this->public_dir;
        }
        $urlext = $this->getOption('urlext_html');
        $excludes = array('.', $this->index);
        if (starts_with($this->document_dir, $this->public_dir)) {
            //避免误删原始文档
            $excludes[] = trim(substr($this->document_dir, strlen($this->public_dir)), '/');
        }
        DOCX_Directory::removeAll($this->public_dir, $excludes);
        $docsdir = $this->getDocsDir();
        foreach ($docsdir->files as $dir => & $files) {
            foreach ($files as $file => & $metadata) {
                $html_file = $target_dir . $metadata['url'] . $urlext;
                $view = new DOCX_View($this, $metadata, '', false);
                $view->staticize($html_file);
            }
        }
    }

    public function genPDF()
    {
        $pdf = new WkHtmlToPdf(array(
            'binPath' => $this->getOption('wkhtmltopdf'),
            'encoding' => 'UTF-8',
            'user-style-sheet' => $this->assets_dir . '/css/style.min.css',
            'run-script' => array(
                $this->assets_dir . '/js/jquery.min.js',
                $this->assets_dir . '/js/highlight.min.js',
                $this->assets_dir . '/js/pdfscript.js',
            ),
        ));
        $docs = $this->getDocsDir();
        foreach ($docs->files as $dir => & $files) {
            foreach ($files as $file => & $metadata) {
                if (self::isHome($metadata['slug'])) {
                    /*$view = new DOCX_View($this, $metadata, 'pdf.php', false);
                    if ($content = $view->getContent()) {
                        $pdf->addCover($content);
                    }*/
                } else {
                    $view = new DOCX_View($this, $metadata, 'pdf.php', false);
                    if ($content = $view->getStaticContent()) {
                        $pdf->addPage($content);
                    }
                }
            }
        }
        return $pdf;
    }
}
