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
    const HOME_PAGE_URL = '/index';
    const ADMIN_URLPRE = '/admin';
    const URLEXT = '{DOCX_URL_EXT}';
    const HTML_HIDE = '{DOCX_HTML_HIDDEN}';
    
    protected $parsers = array();
    protected $templater = null;
    protected $documents = null;
    protected $document_dir = '';
    protected $protected_dir = '';
    protected $cache_dir = '';
    protected $curr_metas = array();
    protected $curr_url = '';
    protected $toppest_url = '';
    protected $edit_mode = false;
    
    protected $options = array(
        'title' => "我的文档",              #站名
        'tagline' => false,                 #封面宣言
        'reading' => "开始阅读文档",          #封面阅读按钮上的文字
        'cover_image' => '',                #封面图片
        'author' => '',                     #默认作者
        'url_prefix' => '',                 #首页网址
        'document_dir' => 'documents',      #原始文档目录
        'public_dir' => 'public',           #静态输出目录
        'theme_dir' => 'theme',             #主题模板目录
        'assets_dir' => 'assets',            #资源目录
        'cache_dir' => 'cache',
        'cache_ext' => '.json',
        'urlext_php' => '',                #动态网页扩展名
        'urlext_html' => '.html',           #静态网页扩展名
        'timezone' => 'Asia/Shanghai',
        'file_sort_latest' => array(),      #文件按更新时间排列，用于博客
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
        $this->document_dir = APP_ROOT  . '/' . trim($this->options['document_dir'], ' /');
        $this->public_dir = APP_ROOT  . '/' . trim($this->options['public_dir'], ' /');
        $this->cache_dir = APP_ROOT  . '/' . trim($this->options['cache_dir'], ' /');
    }

    public static function isPost()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
    }

    public static function isHome($slug)
    {
        return $slug === 'home';
    }
    
    protected static function getRawCurrURL()
    {
        $raw_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return is_null($raw_url) ? '' : $raw_url;
    }

    public static function getURLPre($url, $tpl_file = 'post.php')
    {
        $offset = ($tpl_file === 'edit.php') ? 1 : 0;
        /*if ($tpl_file !== 'index.php') {
            $curr_url = self::getRawCurrURL();
            $offset += (ends_with($curr_url, '/') ? 1 : 0);
        }*/
        $depth = substr_count(trim($url, '/'), '/') + intval($offset);
        return ($depth > 0) ? rtrim(str_repeat('../', $depth), '/') : '.';
    }

    protected function getCurrURL()
    {
        if (empty($this->curr_url)) {
            $curr_url = substr(self::getRawCurrURL(), strlen($this->options['url_prefix']));
            $curr_url = rtrim(str_replace('/index.php', '', $curr_url), ' /');
            if ($curr_url === self::ADMIN_URLPRE) {
                $this->edit_mode = true;
            } else if (starts_with($curr_url, self::ADMIN_URLPRE . '/')) {
                $this->edit_mode = true;
                //substr()陷阱，当string的长度等于start，将返回FALSE而不是''
                $curr_url = substr($curr_url, strlen(self::ADMIN_URLPRE));
            }
            if (empty($curr_url) || $curr_url === '/') {
                $this->curr_url = self::HOME_PAGE_URL;
            } else {
                $this->curr_url = urldecode(rtrim($curr_url, '/')); //汉字逆向转码
            }
        }
        return $this->curr_url;
    }
    
    public function isEditMode()
    {
        return $this->edit_mode;
    }

    public function getDocuments()
    {
        if (is_null($this->documents)) {
            $cache_file = $this->cache_dir . '/docs' . $this->options['cache_ext'];
            $this->documents = new DOCX_Directory($this->document_dir, '.md');
            $this->documents->addCache($cache_file)->getFiles();
        }
        return $this->documents;
    }

    public function ensureAssets()
    {
        $assets_dir = $this->public_dir . '/' . $this->options['assets_dir'];
        if (! file_exists($assets_dir)) { //复制资源文件
            $cmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'xcopy' : 'cp -r';
            $source_dir = APP_ROOT . '/' . $this->options['assets_dir'];
            @shell_exec($cmd . ' ' . $source_dir . ' ' . $this->public_dir . '/');
        }
    }

    public function getTemplater()
    {
        if (is_null($this->templater)) {
            $this->ensureAssets();
            $theme_dir = APP_ROOT . '/' . trim($this->options['theme_dir'], ' /');
            $this->templater = new DOCX_Templater($theme_dir, $this->cache_dir);
            $this->templater->globals = array(
                'urlext' => self::URLEXT,
                'html_hide' => self::HTML_HIDE,
                'home_url' => self::HOME_PAGE_URL,
                'admin_urlpre' => self::ADMIN_URLPRE,
                'options' => & $this->options,
            );
        }
        return $this->templater;
    }

    public function getParser($url)
    {
        if (! isset($this->parsers[$url])) {
            $this->parsers[$url] = new DOCX_Parser();
        }
        return $this->parsers[$url];
    }
    
    public function getMetadata($find_url, $metakey = false)
    {
        if ($find_url === self::ADMIN_URLPRE) {
            $find_url = self::HOME_PAGE_URL;
        }
        $dir = '.' . rtrim(dirname($find_url), '/');
        $file = basename($find_url);
        $docs = $this->getDocuments();
        if (isset($docs->files[$dir]) && isset($docs->files[$dir][$file])) {
            $metadata = $docs->files[$dir][$file];
            if ($metakey === false) {
                return $metadata; //metadata数组
            } else if (isset($metadata[$metakey])) {
                return $metadata[$metakey]; //metadata其中一个元素
            }
        }
    }
    
    public function genMetas($find_url = false)
    {
        if ($find_url === false || $find_url === self::ADMIN_URLPRE) {
            $find_url = self::HOME_PAGE_URL;
        }
        $result = array();
        $docs = $this->getDocuments();
        foreach ($docs->files as $dir => & $files) {
            foreach ($files as $file => & $metas) {
                $url = ltrim($dir, '.') . '/' . $file;
                $parser = $this->getParser($url);
                $metadata = $parser->parseMetaData($metas['fname']);
                $metas['url'] = $url;
                $metas['html'] = $this->public_dir . $url . '.html';
                $metas['slug'] = $file;
                $metas = array_merge($metas, $metadata);
                if ($find_url === $url) {
                    $result = $metas;
                }
            }
        }
        return $result;
    }
    
    public function run()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : false;
        if ($action === 'cleancache') {
            DOCX_Directory::removeAll($this->cache_dir, array('.'));
        } else if ($action === 'staticize') {
            $this->curr_metas = $this->genMetas($this->getCurrURL());
            $this->genPages();
        } else if ($action === 'genpdf') {
            $pdf = $this->genPDF();
            $pdf->send('docx.pdf', true);
        }
        
        if (! $this->curr_metas) {
            $this->curr_metas = $this->genMetas($this->getCurrURL());
            //$this->curr_metas = $this->getMetadata($this->getCurrURL(), false);
        }
        $edit_mode = $this->isEditMode();
        if (self::isHome($this->curr_metas['slug'])) {
            $tpl_file = $edit_mode ? 'admin.php' : 'index.php';
            $content = $this->showPage($tpl_file, ! $edit_mode);
        } else if (! $edit_mode) {
            $content = $this->showPage('post.php', true);
        } else {
            if (self::isPost()) {
                $doc_file = $this->curr_metas['fname'];
                $doc = $_POST['metatext'] . "\n\n" . trim($_POST['markdown']);
                file_put_contents($doc_file, $doc, LOCK_EX);
                $content = $this->renderPage($this->curr_metas, 'post.php');
                $this->staticize($this->curr_metas['html'], $content);
            } else {
                $content = $this->renderPage($this->curr_metas, 'edit.php');
            }
            $content = $this->replaceURL($content);
        }
        @header('Content-Type: text/html; charset=utf8');
        return die($content);
    }
    
    public function replaceURL($content)
    {
        return str_replace(self::URLEXT, $this->options['urlext_php'], $content);
    }
    
    public function staticize($html_file, $content)
    {
        if (! $content) {
            return;
        }
        @mkdir(dirname($html_file), 0755, true);
        $urlext_html = $this->options['urlext_html'];
        $html_hide = '{display: none}';
        $content = str_replace(array(self::URLEXT, self::HTML_HIDE), array($urlext_html, $html_hide), $content);
        return file_put_contents($html_file, $content, LOCK_EX);
    }

    public function showPage($tpl_file = 'post.php', $staticize = false)
    {
        if (! $this->curr_metas) {
            $this->curr_metas = $this->getMetadata($this->getCurrURL(), false);
        }
        $content = $this->renderPage($this->curr_metas, $tpl_file);
        if ($staticize) {
            $html_file = $this->public_dir . rtrim($this->getCurrURL(), '/') . '.html';
            $this->staticize($html_file, $content);
        }
        return $this->replaceURL($content);
    }
        
    public function renderPage(array& $metas, $tpl_file = 'post.php')
    {
        $parser = $this->getParser($metas['url']);
        $page = $parser->parseAll($metas['fname']);
        $first_page_url = '';
        if ($tpl_file === 'index.php' || $tpl_file === 'admin.php') {
            $first_page_url = $this->getToppestPage();
        }
        $docs = $this->getDocuments();
        $templater = $this->getTemplater();
        $templater->globals['docs'] = & $docs->files;
        $urlpre = self::getURLPre($metas['url'], $tpl_file);
        $content = $templater->render($tpl_file, array(
            'page' => $page, 'curr_url' => $metas['url'],
            'urlpre' => $urlpre, 'first_page_url' => $first_page_url,
            'assets_url' => $urlpre . '/' . $this->options['assets_dir'],
        ), true);
        return $content;
    }

    public function getToppestPage()
    {
        if (! empty($this->toppest_url)) {
            return $this->toppest_url;
        }
        $docs = $this->getDocuments();
        foreach ($docs->files as $dir => & $files) {
            foreach ($files as $file => & $metas) {
                if (! self::isHome($metas['slug'])) { //不是home的第一个页面
                    return $metas['url'];
                }
            }
        }
    }
    
    public function genPages()
    {
        DOCX_Directory::removeAll($this->public_dir, array('.', 'index.php'));
        $docs = $this->getDocuments();
        foreach ($docs->files as $dir => & $files) {
            foreach ($files as $file => & $metas) {
                $tpl_file = self::isHome($metas['slug']) ? 'index.php' : 'post.php';
                if ($content = $this->renderPage($metas, $tpl_file)) {
                    $this->staticize($metas['html'], $content);
                }
            }
        }
    }
    
    public function genPDF($staticize = false)
    {
        require_once APP_ROOT . '/library/WkHtmlToPdf.php';
        $assets_dir = $this->public_dir . '/' . $this->options['assets_dir'];
        $pdf = new WkHtmlToPdf(array(
            'binPath' => $this->options['wkhtmltopdf'],
            'encoding' => 'UTF-8',
            'user-style-sheet' => $assets_dir . '/css/style.min.css',
            'run-script' => array(
                $assets_dir . '/js/jquery.min.js',
                $assets_dir . '/js/highlight.min.js',
                $assets_dir . '/js/pdfscript.js',
            ),
        ));
        $docs = $this->getDocuments();
        foreach ($docs->files as $dir => & $files) {
            foreach ($files as $file => & $metas) {
                if (self::isHome($metas['slug'])) {
                    //$content = $this->renderPage($metas, 'index.php', true);
                    //$pdf->addCover($content);
                } else {
                    $content = $this->renderPage($metas, 'pdf.php', true);
                    if ($content = $this->replaceURL($content)) {
                        $pdf->addPage($content);
                    }
                }
            }
        }
        return $pdf;
    }
}
