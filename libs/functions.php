<?php

    $tree = array();
    $base = dirname(dirname(__FILE__));
    $options = get_options(isset($argv[2]) ? $argv[2] : '');
    $docs_path = $base . '/' . $options['docs_path'];
    $multilanguage = !empty($options['languages']) ? TRUE : FALSE;
    $metakeys = array('layout','date','created','title','slug','author','category','tags','comments');

    //  Options
    function get_options($config_file) {
        global $base;
        $options = array(
            'title' => "Documentation",
            'tagline' => false,
            'image' => false,
            'theme' => 'red',
            'docs_path' => 'docs',
            'date_modified' => true,
            'date_format' => 'D, Y-m-d',
            'author' => '',
            'float' => true,
            'repo' => false,
            'toggle_code' => false,
            'twitter' => array(),
            'links' => array(),
            'colors' => false,
            'clean_urls' => true,
            'google_analytics' => false,
            'piwik_analytics' => false,
            'piwik_analytics_id' => 1,
            'ignore' => array(),
            'languages' => array(),
            'file_editor' => false,
            'template' => 'default',
            'greetings' => array()
        );
        // Load User Config
        $config_file = (($config_file === '') ? 'docs/config.json' : $config_file);
        if (substr($config_file, 0, 1) !== '/') $config_file = $base . '/' . $config_file;
        if (file_exists($config_file)) {
            $config = json_decode(file_get_contents($config_file), true);
            $options = array_merge($options, $config);
        }
        if (!isset($options['ignore']['files'])) $options['ignore']['files'] = array();
        if (!isset($options['ignore']['folders'])) $options['ignore']['folders'] = array();

        if ($options['theme'] !== 'custom') {
            $themes = array("blue","navy","green","red");
            if (!in_array($options['theme'], $themes)) {
                echo "<strong>Daux.io Config Error:</strong><br>The theme you set is not not a valid option. Please use one of the following options: " . join(array_keys($themes), ', ') . ' or <a href="http://daux.io">learn more</a> about how to customize the colors.';
                exit;
            }
        } else {
            if (empty($options['colors'])) {
                echo '<strong>Daux.io Config Error:</strong><br>You are trying to use a custom theme, but did not setup your color options in the config. <a href="http://daux.io">Learn more</a> about how to customize the colors.';
                exit;
            }
        }
        if (!ini_get('date.timezone')) date_default_timezone_set('GMT');
        return $options;
    }

    //  Build Directory Tree
    function build_tree() {
        global $tree, $options, $docs_path , $multilanguage, $output_language;
        if (!$multilanguage) $tree = directory_tree($docs_path, $options['ignore']);
        else
            foreach ($options['languages'] as $languageKey => $language) {
                $output_language = $languageKey;
                $tree[$languageKey] = directory_tree($docs_path . '/' . $languageKey, $options['ignore']);
            }
    }

    //  Recursively add files & directories to Tree
    function directory_tree($dir, $ignore) {
        global $base_doc, $multilanguage, $output_language;
        $tree = array();
        $Item = array_diff(scandir($dir), array(".", "..", "index.md"));
        foreach ($Item as $key => $value) {
            if (is_dir($dir . '/' . $value)) {
                if (!in_array($value, $ignore['folders']))
                    $tree[$value] = directory_tree($dir . '/' . $value, $ignore);
            } else if (!in_array($value, $ignore['files'])) {
                    if (substr($value, -3) === ".md") {
                        $tree[$value] = $value;
                        if ($multilanguage)
                            $base_doc[$output_language] = isset($base_doc[$output_language]) ? $base_doc[$output_language] : $dir . '/' . $value;
                        else $base_doc = isset($base_doc) ? $base_doc : $dir . '/' . $value;
                    }
                }
        }
        return $tree;
    }

    //  Build Navigation
    function get_navigation($url) {
        global $tree, $multilanguage, $output_language, $output_path;
        $dir = isset($output_path) ? $output_path : '';
        $return = "<ul class=\"nav nav-list\">";
        $return .= $multilanguage ? build_navigation($tree[$output_language], (($dir !== '') ? $dir . '/' : '') . $output_language, $url) : build_navigation($tree, $dir, $url);
        $return .= "</ul>";
        return $return;
    }

    function build_navigation($tree, $current_dir, $url) {
        global $mode, $base_path, $docs_path, $options;
        $return = "";
        if ($mode === 'Static') $t = relative_path($current_dir . "/.", $url) . '/';
        else {
            $t = "http://" . $base_path . '/';
            if (!$options['clean_urls']) $t .= 'index.php?';
            $rel = clean_url($current_dir, 'Live');
            $t .= ($rel === '') ? '' : $rel . '/';
        }
        foreach ($tree as $key => $node)
            if (is_array($node)) {
                $return .= "<li";
                if (!(strpos($url, $key) === FALSE)) $return .= " class=\"open\"";
                $return .= ">";
                $return .= "<a href=\"#\" class=\"aj-nav folder\">";
                $return .= clean_url($key, "Title");
                $return .= "</a>";
                $return .= "<ul class=\"nav nav-list\">";
                $dir = ($current_dir === '') ? $key : $current_dir . '/' . $key;
                $return .= build_navigation($node, $dir, $url);
                $return .= "</ul>";
                $return .= "</li>";
            }
            else {
                $return .= "<li";
                if ($url === $current_dir . '/' . $node) $return .= " class=\"active\"";
                $return .= ">";
                $link = $t . clean_url($node, $mode);
                $return .= "<a href=\"" . $link . "\">" . clean_url($node, "Title");
                $return .= "</a></li>";
            }
        return $return;
    }

    //  Generate Documentation from Markdown file
    function generate_page($file) {
        global $base, $base_doc, $base_path, $docs_path, $options, $mode, $relative_base;
        $template = $options['template'];
        $filename = substr(strrchr($file, "/"), 1);
        if ($filename === 'index.md') $homepage = TRUE;
        else $homepage = FALSE;
        if (!$file) {
            $page['path'] = '';
            $page['markdown'] = '';
            $page['title'] = 'Oh No';
            $page['content'] = "<h3>抱歉，找不到页面~!</h3>";
            $options['file_editor'] = false;
        } else {
            $page['path'] = str_replace($docs_path . '/', "", $file);
            $page['title'] = clean_url($file, 'Title');
            $page['date'] = $page['modified'] = filemtime($file);
            $page['author'] = $options['author'];
            $page['tags'] = array();
            $page = parse_markdown($file, $page, 'markdown');
        }
        $relative_base = ($mode === 'Static') ? relative_path("", $file) : "http://" . $base_path . '/';
        ob_start();
        include($base . "/template/" . $template . ".tpl");
        $return = ob_get_contents();
        @ob_end_clean();
        return $return;
    }

    //  File to URL
    function clean_url($url, $mode = 'Static') {
        global $docs_path, $output_path, $options;
        switch ($mode) {
            case 'Live':
                $url = str_replace(array(".md", ".html", ".php"), "", $url);
            case 'Static':
                $url = str_replace(".md", ".html", $url);
                $remove = array($docs_path . '/');
                if (isset($output_path)) $remove[] = $output_path . '/';
                $url = str_replace($remove, "", $url);
                $url = explode('/', $url);
                foreach ($url as &$a) {
                    $a = explode('_', $a);
                    if (isset($a[0]) && is_numeric($a[0])) unset($a[0]);
                    $a = implode('_', $a);
                }
                $url = implode('/', $url);
                return $url;
            case 'Title':
            case 'Filename':
                $t = substr_count($url, '/');
                if ($t > 0) $url = substr(strrchr($url, "/"), 1);
                $url = explode('_', $url);
                if (isset($url[0]) && is_numeric($url[0])) unset($url[0]);
                if ($mode === 'Filename') $url = implode('_', $url);
                else $url = implode(' ', $url);
                $url = str_replace(array(".md", ".html"), "", $url);
                return $url;

        }
    }

    //  Get Path based on Server. For Use in template file.
    function get_url($url) {
        global $mode, $options, $relative_base;
        $t = clean_url($url, $mode);
        if ($t === 'index') {
            if ($mode === 'Static') return $relative_base . 'index.html';
            else return $relative_base;
        }
        if ($mode === 'Live' && !$options['clean_urls']) $t = 'index.php?' . $t;
        return $t;
    }

    //  Relative Path From Path2 to Path1
    function relative_path($path1, $path2) {
        global $output_path, $docs_path, $base;
        $remove = array($docs_path . '/');
        if (isset($output_path)) $remove[] = $output_path . '/';
        $remove[] = $base . '/';
        $path1 = str_replace($remove, "", $path1);
        $path2 = str_replace($remove, "", $path2);
        $nesting = substr_count($path2, "/");
        if ($nesting == 0) return clean_url($path1);
        $return = "";
        $t = 0;
        while ($t < $nesting) {
            $return .= "../";
            $t += 1;
        }
        $return .= clean_url($path1);
        return $return;
    }
    
    //改写成适合的网址
    function slugify($name) {
        return strtolower(str_replace(' ', '-', $name));
    }
    
    //随机显示一条语录
    function rand_greeting() {
        global $options;
        static $index = 0;
        if ($index === 0) {
            shuffle($options['greetings']);
            $index = count($options['greetings']);
        }
        return $options['greetings'][--$index];
    }
    
    //中文格式化日期
    function zh_date($format, $timestamp = false) {
        $result = date($format, $timestamp);
        if (strpos($format, '星期w') !== false) {
            $weekdays = array('星期0'=>'星期日', '星期1'=>'星期一', '星期2'=>'星期二', 
                '星期3'=>'星期三', '星期4'=>'星期四', '星期5'=>'星期五', '星期6'=>'星期六');
            $result = str_replace(array_keys($weekdays), array_values($weekdays), $result);
        }
        return $result;
    }
    
    //解析MetaData
    function parse_metadata($line) {
        global $metakeys;
        $pieces = explode(':', $line);
        if (count($pieces) === 2) {
            $pieces[0] = strtolower(trim($pieces[0]));
            if (in_array($pieces[0], $metakeys, true)) {
                $pieces[1] = trim($pieces[1]);
                return $pieces;
            }
        }
    }
    
    //解析markdown文档
    function parse_markdown($file, $result = null, $mdkey = 'origin') {
        if (is_null($result)) {
            $result = array();
        }
        $result[$mdkey] = file_get_contents($file); //全部内容用于编辑
        //将MetaData和Content分开
        $fh = fopen($file, 'rb');
        $line = trim(fgets($fh) ?: '');
        $line = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $line);
        while ($meta = parse_metadata($line)) {
            $result[$meta[0]] = $meta[1];
            $line = trim(fgets($fh) ?: '');
        }
        $content = $line . fread($fh, filesize($file));
        fclose($fh);
        if (isset($result['tags']) && is_string($result['tags'])) {
            $result['tags'] = array_map('trim', explode(',', $result['tags']));
        }
        if (isset($result['date']) && !is_numeric($result['date'])) {
            $result['date'] = strtotime($result['date']);
        }
        //使用外部解析器解析内容
        $parser_file_pd = dirname( __FILE__) . "/Parsedown.php";
        $parser_file_mde = dirname( __FILE__) . "/markdown_extended.php";
        if (is_readable($parser_file_pd)) {
            require_once $parser_file_pd;
            $parser = new Parsedown();
            $result['content'] = $parser->parse($content);
        } else if (is_readable($parser_file_mde)) {
            require_once $parser_file_mde;
            $result['content'] = MarkDownExtended($content);
        }
        return $result;
    }


?>
