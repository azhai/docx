<?php
/*
 * Project DocX (http://git.oschina.net/azhai/docx)
 * @copyright 2014 MIT License.
 * @author Ryan Liu <azhai@126.com>
 */


/**
 * 功能页面
 */
class DOCX_View
{
    const URL_PRE = '{{DOCX_URL_PRE}}';
    const URL_EXT = '{{DOCX_URL_EXT}}';
    const URL_ASSETS = '{{DOCX_URL_ASSETS}}';
    const HTML_HIDE = '{{DOCX_HTML_HIDDEN}}';
    
    protected $app = null;
    protected $metadata = array();
    protected $tpl_file = '';
    protected $content = false;
    protected $replacers = array();
    protected $templater = null;

    public function __construct($app, array& $metadata, $tpl_file = '', $edit_mode = false)
    {
        $this->app = $app;
        $this->metadata = $metadata;
        $this->tpl_file = $this->getTplFile($tpl_file, $edit_mode);
    }

    public static function isPost()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
    }
    
    public function getTplFile($tpl_file, $edit_mode = false)
    {
        if (empty($tpl_file)) {
            if (isset($this->metadata['layout'])) {
                $tpl_file = $this->metadata['layout'];
            }
            if (empty($tpl_file)) {
                $tpl_file = $this->app->getOption('layout');
            }
            $tpl_file = ($edit_mode ? 'edit/' : '') . $tpl_file . '.php';
        }
        return is_readable($this->app->theme_dir . '/' . $tpl_file) ? $tpl_file : 'base.php';
    }

    public function isEditMode()
    {
        return starts_with($this->tpl_file, 'edit/');
    }
    
    public function ensureAssets($public_dir = false)
    {
        if ($public_dir === false) {
            $public_dir = $this->app->public_dir;
        }
        $target_dir = $public_dir . '/assets';
        if (! file_exists($target_dir)) { //复制资源文件
            $cmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'xcopy' : 'cp -r';
            if (realpath($target_dir) !== $this->app->assets_dir) {
                @shell_exec($cmd . ' ' . $this->app->assets_dir . ' ' . $target_dir);
            }
        }
    }

    public function getTemplater()
    {
        if (is_null($this->templater)) {
            $docs = $this->app->getDocsDir();
            $this->templater = new DOCX_Templater($this->app->theme_dir, $this->app->cache_dir);
            $this->templater->globals = array(
                'home_url' => $this->app->getConstant('HOME_PAGE_URL'),
                'admin_urlpre' => $this->app->getConstant('ADMIN_URLPRE'),
                'url_join' => $this->app->getURLJoin(),
                'html_hide' => self::HTML_HIDE,
                'options' => $this->app->getOption(false),
                'docs' => & $docs->files,
            );
        }
        return $this->templater;
    }

    public function renderPage()
    {
        if ($this->content !== false) {
            return;
        }
        $markdoc = DOCX_Markdoc::getInstance($this->metadata['fname']);
        if (self::isPost()) {
            $markdoc->update($_POST['metatext'], trim($_POST['markdown']));
        }
        $first_page_url = '';
        if ($this->metadata['url'] === $this->app->getConstant('HOME_PAGE_URL')) {
            $first_page_url = $this->app->getToppestPage();
        }
        $templater = $this->getTemplater();
        $templater->globals['urlpre'] = self::URL_PRE;
        $templater->globals['urlext'] = self::URL_EXT;
        $templater->globals['assets_url'] = self::URL_ASSETS;
        $this->content = $templater->render($this->tpl_file, array(
            'view' => & $this, 'page' => $markdoc->getPageData(),
            'curr_url' => $this->metadata['url'], 'first_page_url' => $first_page_url,
        ), true);
    }

    public function output()
    {
        $this->renderPage();
        $urlext = $this->app->getOption('urlext_php');
        $this->replacers[self::URL_EXT] = $urlext;
        $this->replacers[self::HTML_HIDE] = '{}';
        $rel_prefix = $this->app->getRelPrefix();
        $assets_dir = $this->app->getOption('assets_dir');
        $this->replacers[self::URL_PRE] = $this->app->getAbsPrefix();
        $this->replacers[self::URL_ASSETS] = $rel_prefix . '/' . $assets_dir;
        $content = DOCX_Templater::replaceWith($this->content, $this->replacers);
        @header('Content-Type: text/html; charset=utf8');
        return die($content);
    }
    
    public function getStaticContent()
    {
        $this->renderPage();
        $urlext = $this->app->getOption('urlext_html');
        $this->replacers[self::URL_EXT] = $urlext;
        $this->replacers[self::HTML_HIDE] = '{display: none}';
        $curr_url = $this->metadata['url'] . $urlext;
        $rel_prefix = $this->app->getRelPrefix(ltrim($curr_url, '/'));
        $this->replacers[self::URL_PRE] = $rel_prefix;
        $this->replacers[self::URL_ASSETS] = $rel_prefix . '/assets';
        return DOCX_Templater::replaceWith($this->content, $this->replacers);
    }

    public function staticize($html_file = false)
    {
        $public_dir = $this->app->public_dir;
        $this->ensureAssets($public_dir);
        if ($html_file === false) {
            $html_file = $public_dir . $this->metadata['url'] . $urlext;
        }
        @mkdir(dirname($html_file), 0755, true);
        $content = $this->getStaticContent();
        return file_put_contents($html_file, $content, LOCK_EX);
    }
}
