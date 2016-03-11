<?php

use Docx\Common;
use Docx\Cache;
use Docx\Web\Handler;
use Docx\Web\Response;
use Docx\Utility\FileSystem;
use Docx\Utility\Markdoc;
use Docx\Utility\Repository;

defined('DS') or define('DS', DIRECTORY_SEPARATOR);


/**
 * 显示页面.
 */
class ViewHandler extends Handler
{
    protected $page_type = 'view';
    protected $organiz = [];
    
    public function locate($archives_dir, $url)
    {
        $node = $this->organiz;
        $pieces = explode('/', $url);
        foreach ($pieces as $slug) {
            if (!isset($node['nodes'][$slug])) {
                return;
            }
            $node = $node['nodes'][$slug];
        }
        return $archives_dir . DS . $node['path'];
    }
    
    public function scanDocs($archives_dir)
    {
        $settings = $this->app->settings;
        $fs = new FileSystem('.md');
        $cache = new Cache\CacheBox();
        $cache_dir = APP_ROOT . DS . $settings['cache_dir'];
        $yaml_cache = new Cache\FileCache($cache_dir, $settings['cache_ext']);
        $cache->attach($yaml_cache);
        $this->organiz = $fs->getOrganiz($archives_dir, $cache);
        $this->globals['organiz'] = $this->organiz;
    }
    
    public function readDoc($nodepath)
    {
        return new Markdoc($nodepath);
    }
    
    public function parseDoc(Markdoc& $doc)
    {
        $layout = $doc->getMetaData('layout');
        if (empty($layout)) {
            $layout = $this->app->settings['layout'];
        }
        $this->context['page'] = $doc->getPageData();
        $this->globals['layout'] = $layout;
    }
    
    public function getCurrURL()
    {
        if ($this->globals['args']) {
            $curr_url = trim($this->globals['args'][0], '/');
        }
        if (empty($curr_url)) {
            $curr_url = 'index';
            $this->page_type = 'home';
        }
        return $curr_url;
    }
    
    public function parseURL($curr_url = '')
    {
        if (empty($curr_url)) {
            $curr_url = $this->getCurrURL();
        }
        $this->globals['curr_url'] = $curr_url;
        $this->globals['page_type'] = $this->page_type;
        
        $settings = $this->app->settings;
        $assets_url = $settings['public_dir'] . '/' . $settings['assets_dir'];
        if ($this->app->route_key) {
            $this->globals['urlpre'] = sprintf('?%s=/', $this->app->route_key);
            $this->globals['assets_url'] = $assets_url;
        } else if ($this->page_type === 'home') {
            $this->globals['urlpre'] = 'index.php/';
            $this->globals['assets_url'] = $assets_url;
        } else {
            $depth = substr_count(trim($curr_url, '/'), '/');
            $depth += ($this->page_type === 'edit' ? 2 : 1);
            $this->globals['urlpre'] = str_repeat('../', $depth);
            $this->globals['assets_url'] = '../' . $this->globals['urlpre'] . $assets_url;
        }
        return $curr_url;
    }
    
    public function prepare()
    {
        $settings = $this->app->settings;
        $this->globals['options'] = $settings;
        $this->globals['theme_dir'] = APP_ROOT . DS . $settings['theme_dir'];
        $this->globals['urlext'] = '/';
    }

    public function getAction()
    {
        $settings = $this->app->settings;
        $public_dir = APP_ROOT . DS . $settings['public_dir'];
        $archives_dir = $public_dir . DS . $settings['archives_dir'];
        $this->scanDocs($archives_dir);
        $curr_url = $this->parseURL();
        $nodepath = $this->locate($archives_dir, $curr_url);
        $doc = $this->readDoc($nodepath);
        $this->parseDoc($doc);
        $this->template = $this->globals['theme_dir'] . DS . $this->globals['layout'] . '.php';
    }
}


/**
 * 编辑页面.
 */
class EditHandler extends ViewHandler
{
    protected $page_type = 'edit';
    
    public function updateDoc(Markdoc& $doc)
    {
        $request = $this->app->request;
        $metatext = $request->getPost('metatext');
        $metatext = htmlspecialchars_decode($metatext, ENT_QUOTES);
        $markdown = $request->getPost('markdown');
        $markdown = htmlspecialchars_decode(trim($markdown), ENT_QUOTES);
        $doc->update($metatext, $markdown);
    }
    
    public function staticize($curr_url)
    {
        $public_dir = APP_ROOT . DS . $this->app->settings['public_dir'];
        $html_file = $public_dir . DS . $curr_url . '.html';
        @mkdir(dirname($html_file), 0755, true);
        file_put_contents($html_file, strval($this), LOCK_EX);
    }

    public function getAction()
    {
        $settings = $this->app->settings;
        $public_dir = APP_ROOT . DS . $settings['public_dir'];
        $archives_dir = $public_dir . DS . $settings['archives_dir'];
        $this->scanDocs($archives_dir);
        $curr_url = $this->parseURL();
        $nodepath = $this->locate($archives_dir, $curr_url);
        $doc = $this->readDoc($nodepath);
        $this->parseDoc($doc);
        $this->globals['layout'] = 'edit' . DS . $this->globals['layout'];
        $this->template = $this->globals['theme_dir'] . DS . $this->globals['layout'] . '.php';
    }
    
    public function postAction()
    {
        $settings = $this->app->settings;
        $public_dir = APP_ROOT . DS . $settings['public_dir'];
        $archives_dir = $public_dir . DS . $settings['archives_dir'];
        $this->scanDocs($archives_dir);
        $curr_url = $this->parseURL();
        $nodepath = $this->locate($archives_dir, $curr_url);
        $doc = $this->readDoc($nodepath);
        $this->updateDoc($doc);
        $this->parseDoc($doc);
        $this->template = $this->globals['theme_dir'] . DS . $this->globals['layout'] . '.php';
        $this->staticize($curr_url);
    }
}


/**
 * 生成静态页.
 */
class HtmlHandler extends ViewHandler
{
    protected $page_type = 'html';
    
    public function parseURL($curr_url = '')
    {
        if (empty($curr_url)) {
            $curr_url = $this->getCurrURL();
        }
        $this->globals['curr_url'] = $curr_url;
        $this->globals['page_type'] = $this->page_type;
        
        $settings = $this->app->settings;
        $depth = substr_count(trim($curr_url, '/'), '/');
        $this->globals['urlpre'] = str_repeat('../', $depth);
        $this->globals['assets_url'] = $this->globals['urlpre'] . $settings['assets_dir'];
        return $curr_url;
    }
    
    public function prepare()
    {
        parent::prepare();
        $this->globals['urlext'] = '.html';
    }

    public function finish()
    {
        if ($this->app->route_key) {
            $home_url = '';
        } else {
            $home_url = '../../' . $this->globals['urlpre'];
        }
        $public_dir = $this->app->settings['public_dir'];
        return Response::redirect($home_url . $public_dir . '/');
    }

    public function getAction()
    {
        $settings = $this->app->settings;
        $public_dir = APP_ROOT . DS . $settings['public_dir'];
        $archives_dir = $public_dir . DS . $settings['archives_dir'];
        FileSystem::removeEmptyDirs($archives_dir, 1);
        $this->scanDocs($archives_dir);
        $ignores = [
            $public_dir . DS . '.git',
            $public_dir . DS . $this->app->settings['archives_dir'],
            $public_dir . DS . $this->app->settings['assets_dir'],
        ];
        FileSystem::removeAllFiles($public_dir, $ignores);
        
        $handler = $this;
        $staticize = function($node, $curr_url, $children = [])
                        use($archives_dir, $public_dir, $handler)
        {
            if ($node['is_file'] === 0) {
                return;
            }
            $nodepath = $archives_dir . DS . $node['path'];
            $doc = $handler->readDoc($nodepath);
            $handler->parseDoc($doc);
            $handler->parseURL($curr_url);
            $handler->template = $handler->globals['theme_dir'] . DS . $handler->globals['layout'] . '.php';
            $html_file = $public_dir . DS . $curr_url . '.html';
            @mkdir(dirname($html_file), 0755, true);
            file_put_contents($html_file, strval($handler), LOCK_EX);
        };
        FileSystem::traverse($this->organiz['nodes'], $staticize);
    }
}


/**
 * Git发布.
 */
class RepoHandler extends Handler
{
    protected $repo = null;
    
    public function prepare()
    {
        $settings = $this->app->settings;
        $public_dir = APP_ROOT . DS . $settings['public_dir'];
        if (!is_dir($public_dir . DS . '.git')) {
            $remote = $settings['repo_url'];
            if (isset($settings['repo_user']) && $settings['repo_pass']) {
                $remote = Repository::buildRemotePath($remote,
                            $settings['repo_user'], $settings['repo_pass']);
            }
            $this->repo = Repository::create($public_dir, $remote);
        } else {
            $this->repo = Repository::open($public_dir);
        }
    }

    public function finish()
    {
        if ($this->app->route_key) {
            $home_url = sprintf('?%s=/', $this->app->route_key);
        } else {
            $home_url = '../../' . $this->globals['urlpre'];
        }
        return Response::redirect($home_url . 'index/');
    }
    
    public function getAction()
    {
        $request = $this->app->request;
        $comment = $request->getPost('comment', 'Nothing');
        $settings = $this->app->settings;
        $branch = $settings['repo_branch'];
        $this->repo->checkout('-b', $branch);
        $this->repo->pull();
        $this->repo->add();
        $this->repo->commitMutely($comment);
        $this->repo->push('origin', $branch, '--tags');
    }
}
