<?php
defined('APP_ROOT') or define('APP_ROOT', dirname(__FILE__));
defined('DOCX_ROOT') or define('DOCX_ROOT', dirname(APP_ROOT));
defined('MINIFY_LIBRARY') or define('MINIFY_LIBRARY', true);

$settings = array(
    'url_prefix' => '/blog/index.php?q=',
    'title' => 'docx文档生成工具',
    'tagline' => '最简单的方式构建你的项目文档',
    'reading' => '开始阅读文档',
    'cover_image' => 'img/cover.png',
    'author' => 'Ryan Liu',
    'layout' => 'post',                     #默认模板布局
    'document_dir' => APP_ROOT . '/_docs',  #原始文档目录
    'public_dir' => APP_ROOT,               #静态输出目录
    #相对路径，自动加上DOCX_ROOT前缀
    'theme_dir' => 'theme',                 #主题模板目录
    'assets_dir' => 'theme/assets',         #资源目录
    'cache_dir' => 'cache/blog',            #缓存目录
    'cache_ext' => '.json',
    'blog_sorting' => array('/PHP'),
    #需要安装wkhtmltopdf、fontconfig、一款中文字体如文泉驿
    #'wkhtmltopdf' => 'wkhtmltopdf',
    'repo' => 'azhai/docx',
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


if (MINIFY_LIBRARY) {
    $package = DOCX_ROOT . '/library/docx.lite.php';
    if (! is_readable($package)) {
        require_once DOCX_ROOT . '/library/Compressor.php';
        $wildcard = DOCX_ROOT . '/library/DocX/*.php';
        $compressor = new Compressor();
        $compressor->compress($package, $wildcard);
    }
    require_once $package; // 使用压缩后的文件
} else {
    require_once DOCX_ROOT . '/library/DocX/utils.php';
}

spl_autoload_register('autoload_class');
$app = new DOCX_App($settings);
$app->run();
?>