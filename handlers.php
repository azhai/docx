<?php

use Docx\Common;
use Docx\Cache;
use Docx\Web\Response;
use Docx\Utility\FileSystem;
use Docx\Utility\Markdoc;

defined('DS') or define('DS', DIRECTORY_SEPARATOR);


/**
 * 基本控制器.
 */
class Handler
{
    use \Docx\Base\Behavior;

    protected $app = null;
    protected $method = 'get';
    protected $template = '';
    protected $context = [];
    protected $globals = [];

    public function __construct(& $app, $method = 'get')
    {
        $this->app = $app;
        $this->method = strtolower($method);
    }

    public function __invoke()
    {
        $this->prepare();
        $action = $this->method . 'Action';
        $args = func_get_args();
        Common::execMethodArray($this, $action, $args);
        $this->finish();
        return $this;
    }

    public function __toString()
    {
        $response = new Response($this->template);
        $response->globals = array_merge($response->globals, $this->globals);
        return $response->render($this->context);
    }
}


/**
 * 显示页面.
 */
class Viewhandler extends Handler
{
    protected $docs = null;
    protected $cache = null;
    protected $organiz = [];
    protected $page_type = 'view';
    
    public function locate($source_dir, $url)
    {
        $node = $this->organiz;
        $pieces = explode('/', $url);
        foreach ($pieces as $slug) {
            if (!isset($node['nodes'][$slug])) {
                return;
            }
            $node = $node['nodes'][$slug];
        }
        return $source_dir . DS . $node['path'];
    }
    
    /**
     *
     */
    public function prepare($curr_url)
    {
        $settings = $this->app->settings;
        $this->globals['options'] = $settings;
        $this->globals['curr_url'] = $curr_url;
        $this->globals['urlpre'] = $settings['urlpre'];
        $depth = substr_count(trim($curr_url, '/'), '/');
        //offset = doc:1 edit:2 html:0 home:-1
        $this->globals['page_type'] = $this->page_type;
        if ($this->page_type === 'html') {
            $this->globals['urlext'] = '.html';
            $this->globals['relate_url'] = str_repeat('../', $depth);
            $this->globals['assets_url'] = $this->globals['relate_url'] . $settings['assets_dir'];
        } else {
            $this->globals['urlext'] = '/';
            $this->globals['assets_url'] = $settings['public_dir'] . '/' . $settings['assets_dir'];
            if ($this->page_type === 'home') {
                $this->globals['relate_url'] = 'index.php/';
            } else {
                $depth += ($this->page_type === 'edit' ? 2 : 1);
                $this->globals['relate_url'] = str_repeat('../', $depth);
                $this->globals['assets_url'] = '../' . $this->globals['relate_url'] . $this->globals['assets_url'];
            }
        }
        $this->globals['theme_dir'] = APP_ROOT . DS . $settings['theme_dir'];
    }
    
    public function scanDocs($source_dir)
    {
        $settings = $this->app->settings;
        $this->docs = new FileSystem('.md');
        $this->cache = new Cache\CacheBox();
        $cache_dir = APP_ROOT . DS . $settings['cache_dir'];
        $yaml_cache = new Cache\FileCache($cache_dir, $settings['cache_ext']);
        $this->cache->attach($yaml_cache);
        $this->organiz = $this->docs->getOrganiz($source_dir, $this->cache);
        $this->globals['organiz'] = $this->organiz;
    }
    
    public function parseDoc($nodepath)
    {
        $doc = new Markdoc($nodepath);
        $layout = $doc->getMetaData('layout');
        if (empty($layout)) {
            $layout = $this->app->settings['layout'];
        }
        $this->context['page'] = $doc->getPageData();
        $this->template = $this->globals['theme_dir'] . DS . $layout . '.php';
    }

    public function finish()
    {
    }

    public function __invoke()
    {
        $curr_url = trim(func_get_arg(0), '/');
        if (empty($curr_url)) {
            $curr_url = 'index';
            $this->page_type = 'home';
        }
        $this->prepare($curr_url);
        $public_dir = APP_ROOT . DS . $this->app->settings['public_dir'];
        $source_dir = $public_dir . DS . $this->app->settings['source_dir'];
        $this->scanDocs($source_dir);
        $nodepath = $this->locate($source_dir, $curr_url);
        $this->parseDoc($nodepath);
        $this->finish();
        return $this;
    }
}


/**
 * 编辑页面.
 */
class EditHandler extends Viewhandler
{
    protected $page_type = 'edit';
    
    public function parseDoc($nodepath)
    {
        $doc = new Markdoc($nodepath);
        if ($this->method === 'post') {
            $metatext = $this->app->request->getPost('metatext');
            $markdown = $this->app->request->getPost('markdown');
            $doc->update($metatext, trim($markdown));
        }
        $layout = $doc->getMetaData('layout');
        if (empty($layout)) {
            $layout = $this->app->settings['layout'];
        }
        $this->context['page'] = $doc->getPageData();
        if (empty($layout)) {
            $layout = $this->app->settings['layout'];
        }
        if ($this->method === 'get') {
            $layout = 'edit' . DS . $layout;
        }
        $this->template = $this->globals['theme_dir'] . DS . $layout . '.php';
    }

    public function finish()
    {
        if ($this->method === 'post') {
            $public_dir = APP_ROOT . DS . $this->app->settings['public_dir'];
            $curr_url = $this->globals['curr_url'];
            $html_file = $public_dir . DS . $curr_url . '.html';
            @mkdir(dirname($html_file), 0755, true);
            file_put_contents($html_file, strval($this), LOCK_EX);
        }
    }
}


/**
 * 生成静态页.
 */
class HtmlHandler extends Viewhandler
{
    protected $page_type = 'html';
    
    public function __invoke()
    {
        $public_dir = APP_ROOT . DS . $this->app->settings['public_dir'];
        $ignores = [
            $public_dir . DS . '.git',
            $public_dir . DS . $this->app->settings['source_dir'],
            $public_dir . DS . $this->app->settings['assets_dir'],
        ];
        FileSystem::removeAllFiles($public_dir, $ignores);
        $source_dir = $public_dir . DS . $this->app->settings['source_dir'];
        FileSystem::removeEmptyDirs($source_dir, 1);
        $this->scanDocs($source_dir);
        
        $handler = $this;
        $staticize = function($node, $curr_url, $children = [])
                        use($source_dir, $public_dir, $handler)
        {
            if ($node['is_file'] === 0) {
                return;
            }
            $handler->prepare($curr_url);
            $nodepath = $source_dir . DS . $node['path'];
            $handler->parseDoc($nodepath);
            $html_file = $public_dir . DS . $curr_url . '.html';
            @mkdir(dirname($html_file), 0755, true);
            file_put_contents($html_file, strval($handler), LOCK_EX);
        };
        FileSystem::traverse($this->organiz['nodes'], $staticize);
        $this->finish();
        return $this;
    }

    public function finish()
    {
        $home_url = $this->app->settings['urlpre'];
        if (Common::endsWith($home_url, '/index.php')) {
            $home_url = substr($home_url, 0, - strlen('/index.php'));
        }
        $dir = $this->app->settings['public_dir'];
        return Response::redirect($home_url . '/' . $dir . '/index.html');
    }
}


/**
 * Git发布.
 */
class RepoHandler extends Viewhandler
{
    public function __invoke()
    {
        $comment = $this->app->request->getPost('comment', 'Nothing');
        $public_dir = APP_ROOT . DS . $this->app->settings['public_dir'];
        $branch = $this->app->settings['publish_branch'];
        
        $is_exists = is_dir($public_dir . DS . '.git');
        $repo = \TQ\Git\Repository\Repository::open($public_dir, 'git', true);
        $repo->add();
        $repo->commit($comment);
        if (!$is_exists) {
            $remote = $this->app->settings['publish_repo'];
            $repo->getGit()->remote($public_dir, 'add origin ' . $remote);
        }
        exit;
        
        /*
        $client = new Gitter\Client();
        if (!is_dir($public_dir . DS . '.git')) {
            $repo = $client->createRepository($public_dir);
            $remote = $this->app->settings['publish_repo'];
            $client->run($repo, 'remote add origin ' . $remote);
        } else {
            $repo = $client->getRepository($public_dir);
        }
        $repo->add();
        exit;
        try {
            $repo->commit($comment);
        } catch (\Exception $e) {
            echo strval($e);
        }
        */
        
        /*
        if (Common::isWinNT()) {
            \Git::windows_mode();
        }
        if (!is_dir($public_dir . DS . '.git')) {
            $remote = $this->app->settings['publish_repo'];
            $repo = \Git::create($public_dir);
            $repo->run('remote add origin ' . $remote);
        } else {
            $repo = \Git::open($public_dir);
        }
        //$this->addFiles($repo);
        $repo->add();
        var_dump($repo->status());
        exit;
        try {
            $repo->commit($comment);
        } catch (\Exception $e) {
            echo strval($e);
        }
        exit;
        $repo->push('origin', $branch);
        */
        $this->finish();
        return $this;
    }

    public function finish()
    {
        $home_url = $this->app->settings['urlpre'];
        if (Common::endsWith($home_url, '/index.php')) {
            $home_url = substr($home_url, 0, - strlen('/index.php'));
        }
        $dir = $this->app->settings['public_dir'];
        return Response::redirect($home_url . '/' . $dir . '/index.html');
    }
}
