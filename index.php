#!/usr/bin/env php
<?php
/*

Daux.io
==================

Description
-----------

This is a tool for auto-generating documentation based on markdown files
located in the /docs folder of the project. To see all of the available
options and to read more about how to use the generator, visit:

http://daux.io


Author
------
Justin Walsh (Todaymade): justin@todaymade.com, @justin_walsh
Garrett Moon (Todaymade): garrett@todaymade.com, @garrett_moon


Feedback & Suggestions
----

To give us feedback or to suggest an idea, please create an request on the the
GitHub issue tracker:

https://github.com/justinwalsh/daux.io/issues

*/


require_once dirname( __FILE__ ) . '/libs/functions.php';
$command_line=FALSE;
if(isset($argv)){
    require_once dirname( __FILE__ ) . '/libs/static.php';
    define("CLI",TRUE);

    if(!isset($argv[1]))
        $argv[1]= 'help';

    switch ($argv[1]) {
        case 'gen':
        case 'generate':
            echo utf8_to_locale('正在生成静态网页…') . "\n";
            generate_static((isset($argv[3])) ? $argv[3] : '');
            echo utf8_to_locale('成功！生成的网页在静态目录下。') . "\n";
            break;
        case 'pdf':
            echo utf8_to_locale('正在生成PDF……') . "\n";
            $pdf = isset($argv[2]) ? $argv[2] : 'docx.pdf';
            $pdf = generate_pdf($pdf, array_slice($argv, 3));
            echo utf8_to_locale('成功！生成的PDF文件为') . $pdf . "\n";
            break;
        default:
            echo "\n";
            echo utf8_to_locale('用法:')."\n";
            echo 'php index.php gen'."\n";
            echo '  ' . utf8_to_locale('生成静态网页')."\n";
            echo 'php index.php pdf [docx.pdf] [subdir1] [subdir2] [...]'."\n";
            echo '  ' . utf8_to_locale('生成PDF文件')."\n";
            echo "\n";
            break;
    }
    exit();
}
require_once(dirname( __FILE__)."/libs/live.php");
$base_path = str_replace("/index.php", "", $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
define("CLI", FALSE);
build_tree();
$remove = array($base_path . '/');
if (!$options['clean_urls']) $remove[] = 'index.php?';
$request = str_replace($remove, "", $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
if (isset($_POST['markdown']) && $options['file_editor'])
    file_put_contents(clean_url_to_file($request), $_POST['markdown']);
echo generate_live($request);
