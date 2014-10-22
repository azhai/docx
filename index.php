<?php
defined('APP_ROOT') or define('APP_ROOT', dirname(__FILE__));
defined('MINIFY_LIBRARY') or define('MINIFY_LIBRARY', true);

$settings = array(
    'title' => 'docx文档生成工具',
    "tagline" => "最简单的方式构建你的项目文档",
    "reading" => "开始阅读文档",
    'cover_image' => 'img/cover.png',
    'author' => 'Ryan Liu',
    'cache_ext' => '.json',
    'blog_sorting' => array(),
    #需要安装wkhtmltopdf、fontconfig、一款中文字体如文泉驿
    #'wkhtmltopdf' => 'wkhtmltopdf',
    'repo' => 'azhai/docx',
    'links' => array(
        'Coding仓库' => 'https://coding.net/u/azhai/p/docx/git',
        "Todaymade出品" => 'http://todaymade.com',
        "Xin Meng汉化" => 'http://blog.emx2.co.uk'
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

$litelib = APP_ROOT . '/library/docx_lite.php';
if (! is_readable($litelib)) {
    $wildcard = APP_ROOT . '/library/DocX/*.php';
    foreach (glob($wildcard) as $filename) {
        require_once $filename;
    }
    if (MINIFY_LIBRARY) {
        require_once APP_ROOT . '/library/Compressor.php';
        $compressor = new Compressor();
        $compressor->compress($litelib, $wildcard);
    }
} else {
    require_once $litelib; // 使用压缩后的文件
}
require_once APP_ROOT . '/library/Parsedown.php';

$app = new DOCX_App($settings);
$app->run();
?>