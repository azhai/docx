<!DOCTYPE html>
<!--[if lt IE 7]>       <html class="no-js ie6 oldie" lang="zh"> <![endif]-->
<!--[if IE 7]>          <html class="no-js ie7 oldie" lang="zh"> <![endif]-->
<!--[if IE 8]>          <html class="no-js ie8 oldie" lang="zh"> <![endif]-->
<!--[if gt IE 8]><!-->  <html class="no-js" lang="zh"> <!--<![endif]-->
<head>
    <title><?php echo $options['title']; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="<?php echo $options['tagline'];?>" />
    <meta name="author" content="<?php echo $options['title']; ?>">
    <?php if ($options['colors']) { ?>
    <link rel="icon" href="<?php echo $relative_base; ?>img/favicon.png" type="image/x-icon">
    <?php } else { ?>
    <link rel="icon" href="<?php echo $relative_base; ?>img/favicon-<?php echo $options['theme'];?>.png" type="image/x-icon">
    <?php } ?>
    <!-- Mobile -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font -->
    <link href='http://fonts.googleapis.com/css?family=Roboto+Slab:400,700,300,100' rel='stylesheet' type='text/css'>

    <!-- LESS -->
    <?php if ($options['colors']) { ?>
        <style type="text/less">
            @import "<?php echo $relative_base; ?>less/import/daux-base.less";
            <?php foreach($options['colors'] as $k => $v) { ?>
            @<?php echo $k;?>: <?php echo $v;?>;
            <?php } ?>
        </style>
        <script src="<?php echo $relative_base; ?>js/less.min.js"></script>
    <?php } else { ?>
        <link rel="stylesheet" href="<?php echo $relative_base; ?>css/daux-<?php echo $options['theme'];?>.min.css">
    <?php } ?>
</head>
<body>
    <?php if ($homepage) { ?>
        <!-- Homepage -->
        <div class="navbar navbar-fixed-top hidden-print">
                <div class="container">
                    <a class="brand navbar-brand pull-left" href="<?php echo get_url('index'); ?>"><?php echo $options['title']; ?></a>
                    <p class="navbar-text pull-right">
                        <?php echo rand_greeting(); ?>
                    </p>
                </div>
        </div>

        <div class="homepage-hero well container-fluid">
            <div class="container">
                <div class="row">
                    <div class="text-center col-sm-12">
                        <?php if ($options['tagline']) { ?>
                            <h2><?php echo $options['tagline'];?></h2>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <?php if ($options['image']) { ?>
                            <img class="homepage-image img-responsive" src="<?php echo $options['image'];?>" alt="<?php echo $options['title'];?>">
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="hero-buttons container-fluid">
            <div class="container">
                <div class="row">
                    <div class="text-center col-sm-12">
                        <?php if ($options['repo']) { ?>
                            <a href="https://github.com/<?php echo $options['repo']; ?>" class="btn btn-secondary btn-hero">
                                前往GitHub
                            </a>
                        <?php } ?>
                        <?php if (count($options['languages']) > 0) { ?>
                            <?php foreach ($options['languages'] as $language_key => $language_name) { ?>
                            <a href="<?php echo get_url($base_doc[$language_key]); ?>" class="btn btn-primary btn-hero">
                                <?php echo $language_name; ?>
                            </a>
                            <?php } ?>
                        <?php } else { ?>
                        <a href="<?php echo get_url($base_doc);?>" class="btn btn-primary btn-hero">
                            开始阅读文档
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="homepage-content container-fluid">
            <div class="container">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <?php echo $page['content'];?>
                    </div>
                </div>
            </div>
        </div>

        <div class="homepage-footer well container-fluid">
            <div class="container">
                <div class="row">
                    <div class="col-sm-5 col-sm-offset-1">
                        <?php if (!empty($options['links'])) { ?>
                            <ul class="footer-nav">
                                <?php foreach($options['links'] as $name => $url) { ?>
                                    <li><a href="<?php echo $url;?>" target="_blank"><?php echo $name;?></a></li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>
                    <div class="col-sm-5">
                        <div class="pull-right">
                            <?php if (!empty($options['twitter'])) { ?>
                                <?php foreach($options['twitter'] as $handle) { ?>
                                    <div class="twitter">
                                        <iframe allowtransparency="true" frameborder="0" scrolling="no" style="width:162px; height:20px;" src="https://platform.twitter.com/widgets/follow_button.html?screen_name=<?php echo $handle;?>&amp;show_count=false"></iframe>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php } else { ?>
        <!-- Docs -->
        <?php if ($options['repo']) { ?>
            <a href="https://github.com/<?php echo $options['repo']; ?>" target="_blank" id="github-ribbon" class="hidden-print"><img src="<?php echo $relative_base; ?>img/forkme_right_darkblue_121621.png" alt="Fork me on GitHub"></a>
        <?php } ?>
        <div class="container-fluid fluid-height wrapper">
            <div class="navbar navbar-fixed-top hidden-print">
                <div class="container-fluid">
                    <a class="brand navbar-brand pull-left" href="<?php echo get_url('index'); ?>"><?php echo $options['title']; ?></a>
                    <p class="navbar-text pull-right">
                        <?php echo rand_greeting(); ?>
                    </p>
                </div>
            </div>

            <div class="row columns content">
                <div class="left-column article-tree col-sm-3 hidden-print">
                    <!-- For Mobile -->
                    <div class="responsive-collapse">
                        <button type="button" class="btn btn-sidebar" id="menu-spinner-button">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>
                    <div id="sub-nav-collapse" class="sub-nav-collapse">
                        <!-- Navigation -->
                        <?php echo get_navigation($file); ?>
                        <?php if ($options['toggle_code'] 
                                    || !empty($options['links']) || !empty($options['twitter'])) { ?>
                            <div class="well well-sidebar">
                                <!-- Links -->
                                <?php foreach($options['links'] as $name => $url) { ?>
                                    <a href="<?php echo $url;?>" target="_blank"><?php echo $name;?></a><br>
                                <?php } ?>
                                <?php if ($options['toggle_code']) { ?>
                                    <a href="#" id="toggleCodeBlockBtn" onclick="toggleCodeBlocks();">外置代码框</a><br>
                                <?php } ?>
                                <!-- Twitter -->
                                <?php foreach($options['twitter'] as $handle) { ?>
                                    <div class="twitter">
                                                <hr/>
                                        <iframe allowtransparency="true" frameborder="0" scrolling="no" style="width:162px; height:20px;" src="https://platform.twitter.com/widgets/follow_button.html?screen_name=<?php echo $handle;?>&amp;show_count=false"></iframe>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="right-column content-area col-sm-9">
                    <div class="content-page">
                        <article>
                            <div class="page-header sub-header clearfix">
                                <h1><?php echo $page['title'];?>
                                    <?php if ($mode === 'Live' && $options["file_editor"]) { ?>
                                        <a href="javascript:;" id="editThis" class="btn">编辑文档</a>
                                    <?php } ?>
                                </h1>
                                <span style="float: left; font-size: 10px; color: gray;">
                                    <?php foreach($page['tags'] as $i => $tag) { 
                                            echo ($i > 0) ? ', ' : '标签：'; ?>
                                        <!--a href="<?php echo $relative_base .'tag/'. slugify($tag);?>"--><?php echo $tag;?><!--/a-->
                                    <?php } ?>
                                </span>
                                <span style="float: right; font-size: 10px; color: gray;">
                                    <!--a href="<?php echo $relative_base .'author/'. slugify($page['author']);?>"--><?php echo $page['author']; ?><!--/a--> 写于
                                    <?php echo zh_date($options['date_format'], $page['date']); ?>
                                </span>
                            </div>
                            <?php echo $page['content']; ?>
                            <?php if ($mode === 'Live' && $options["file_editor"]) { ?>
                                <div class="editor <?php if(!$options['date_modified']) { ?>paddingTop<?php } ?>">
                                    <h3>正在修改 <?php echo $page['path']; ?>&nbsp;<a href="javascript:;" class="closeEditor btn btn-warning">关闭</a></h3>
                                    <div class="navbar navbar-inverse navbar-default navbar-fixed-bottom" role="navigation">
                                        <div class="navbar-inner">
                                            <a href="javascript:;" class="save_editor btn btn-primary navbar-btn pull-right">保存到文件</a>
                                        </div>
                                    </div>
                                    <textarea id="markdown_editor"><?php echo $page['markdown'];?></textarea>
                                    <div class="clearfix"></div>
                                </div>
                            <?php } ?>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

<?php if ($options['google_analytics']) { ?>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', '<?php echo $options['google_analytics'];?>', '<?php echo (isset($_SERVER['HTTP_HOST']))?$_SERVER['HTTP_HOST']:''; ?>');
    ga('send', 'pageview');

</script>
<?php } ?>
<?php if ($options['piwik_analytics']) { ?>
<script type="text/javascript">
    var _paq = _paq || [];
    _paq.push(["trackPageView"]);
    _paq.push(["enableLinkTracking"]);

    (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://<?php echo $options['piwik_analytics'];?>/";
    _paq.push(["setTrackerUrl", u+"piwik.php"]);
    _paq.push(["setSiteId", <?php echo $options['piwik_analytics_id'];?>]);
    var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";
    g.defer=true; g.async=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);
    })();
</script>
<?php } ?>


    <!-- hightlight.js -->
    <script src="<?php echo $relative_base; ?>js/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>

    <!-- Navigation -->
    <!--script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script-->
    <script>
    if (typeof jQuery == 'undefined')
        document.write(unescape("%3Cscript src='<?php echo $relative_base; ?>js/jquery-1.11.0.min.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <?php if ($mode === 'Live' && $options["file_editor"]) { ?>
    <!-- Front end file editor -->
    <script src="<?php echo $relative_base; ?>js/editor.js"></script>
    <?php } ?>
    <script src="<?php echo $relative_base; ?>js/bootstrap.min.js"></script>
    <script src="<?php echo $relative_base; ?>js/custom.js"></script>
    <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

</body>
</html>
