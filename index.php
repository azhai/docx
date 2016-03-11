<?php
if (version_compare(PHP_VERSION, '5.4.0') < 0) {
    die('PHP最低要求5.4版本');
}
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
defined('APP_ROOT') or define('APP_ROOT', __DIR__);
defined('VENDOR_DIR') or define('VENDOR_DIR', APP_ROOT . '/vendor');

$settings = array(
    'route_key' => 'r',
    'public_dir' => 'public',               #静态输出目录
    'archives_dir' => 'archives',           #原始文档目录
    'assets_dir' => 'assets',               #资源目录
    'title' => 'docx文档生成工具',
    'tagline' => '最简单的方式构建你的项目文档',
    'reading' => '开始阅读文档',
    'cover_image' => 'img/cover.png',
    'author' => 'Ryan Liu',
    'layout' => 'post',                     #默认模板布局
    'date_format' => 'Y年n月j日 星期w',
    'timezone' => 'Asia/Shanghai',
    'memory_limit' => '256M',
    #相对路径，自动加上APP_ROOT前缀
    'theme_dir' => 'theme',                 #主题模板目录
    'cache_dir' => 'temp',                  #缓存目录
    'cache_ext' => '.json',
    #pages仓库
    'repo_url' => 'https://git.coding.net/azhai/azhai.git',
    'repo_user' => 'azhai',
    'repo_pass' => 'xxxxxx',
    'repo_branch' => 'coding-pages',
    #github仓库名称
    'github_repo_name' => 'azhai/docx',
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


$package_file = VENDOR_DIR . '/docx.lite.php';
if (is_readable($package_file)) { // 使用压缩后的文件
    require_once $package_file;
} else {
    require_once VENDOR_DIR . '/Docx/Importer.php';
}

$app = new \Docx\Application($settings);


require_once APP_ROOT . '/handlers.php';
require_once APP_ROOT . '/helpers.php';
$app->route('/<path>', 'Viewhandler');
$app->route('/admin/<path>', 'EditHandler');
$app->route('/admin/staticize/', 'HtmlHandler');
$app->route('/admin/publish/', 'RepoHandler');
$app->route('/admin/compress/', function() use($app, $package_file) {
    $app->addClass(VENDOR_DIR . '/Compressor.php', 'Compressor');
    $cps = new \Compressor();
    $cps->minify($package_file,
                glob(VENDOR_DIR . '/Docx/*.php'),
                glob(VENDOR_DIR . '/Docx/Base/*.php'),
                glob(VENDOR_DIR . '/Docx/Event/*.php'),
                glob(VENDOR_DIR . '/Docx/*/*.php'));
    return "DONE.\n";
});
$app->run();
?>