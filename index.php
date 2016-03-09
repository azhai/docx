<?php
if (version_compare(PHP_VERSION, '5.4.0') < 0) {
    die('PHP最低要求5.4版本');
}
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
defined('APP_ROOT') or define('APP_ROOT', __DIR__);
defined('VENDOR_DIR') or define('VENDOR_DIR', APP_ROOT . '/vendor');

$settings = array(
    'urlpre' => '/docx/index.php',          #首页URL
    'source_dir' => 'source',               #原始文档目录
    'public_dir' => 'public',               #静态输出目录
    'assets_dir' => 'assets',               #资源目录
    'title' => 'docx文档生成工具',
    'tagline' => '最简单的方式构建你的项目文档',
    'reading' => '开始阅读文档',
    'cover_image' => 'img/cover.png',
    'author' => 'Ryan Liu',
    'timezone' => 'Asia/Shanghai',
    'date_format' => 'Y年n月j日 星期w',
    'memory_limit' => '256M',
    'layout' => 'post',                     #默认模板布局
    #相对路径，自动加上APP_ROOT前缀
    'theme_dir' => 'theme',                 #主题模板目录
    'cache_dir' => 'temp',                  #缓存目录
    'cache_ext' => '.yml',
    'blog_sorting' => array('/PHP'),
    #（可写）HTML仓库分支名称
    'publish_branch' => 'coding-pages',
    #（可写）HTML仓库完整url
    'publish_repo' => 'https://azhai:x1378742@git.coding.net/azhai/azhai.git',
    #github仓库url
    'github_repo' => 'azhai/docx',
    'google_analytics' => false,
    'links' => array(
        'Coding仓库' => 'https://coding.net/u/azhai/p/docx/git',
        'OSChina仓库' => 'http://git.oschina.net/azhai/docx',
        'Todaymade出品Daux.io' => 'http://todaymade.com',
        'Xin Meng翻译文档' => 'http://blog.emx2.co.uk',
    ),
    'greetings' => array (
        '在合适的时候使用PHP – Rasmus Lerdorf',
        '使用多表存储提高规模伸缩性 – Matt Mullenweg',
        '千万不要相信用户 – Dave Child',
        '多使用PHP缓存 – Ben Balbo',
        '使用IDE, Templates和Snippets加速PHP开发 – Chad Kieffer',
        '利用好PHP的过滤函数 – Joey Sochacki',
        '使用PHP框架 – Josh Sharp',
        '不要使用PHP框架 – Rasmus Lerdorf',
        '使用批处理 – Jack D. Herrington',
        '及时启用错误报告 – David Cummings',
    ),
);


$package = VENDOR_DIR . '/docx.lite.php';
if (is_readable($package)) { // 使用压缩后的文件
    require_once $package;
} else {
    require_once VENDOR_DIR . '/Docx/Importer.php';
}

$app = new \Docx\Application($settings);
$app->import('TQ', VENDOR_DIR . '/PHP-Stream-Wrapper-for-Git-1.0.1/src');
//$app->introduce('Symfony\\Component\\Process', VENDOR_DIR . '/process-3.0.3');
//$app->import('Gitter', VENDOR_DIR . '/gitter/lib');
//$app->addClass(VENDOR_DIR . '/Git.php', 'Git');
$app->addClass(VENDOR_DIR . '/Parsedown.php', 'Parsedown');


require_once APP_ROOT . '/handlers.php';
$app->route('/<path>', 'Viewhandler');
$app->route('/admin/<path>', 'EditHandler');
$app->route('/admin/staticize/', 'HtmlHandler');
$app->route('/admin/publish/', 'RepoHandler');
$app->run();
?>