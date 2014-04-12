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

        <!-- Docs -->
        <div class="container-fluid fluid-height wrapper">
            <div class="navbar navbar-fixed-top hidden-print">
                <div class="container-fluid">
                    <h1>目录</h1>
                    <p class="navbar-text pull-right">
                    </p>
                </div>
            </div>

            <div class="row columns content">
                <div class="left-column article-tree col-sm-3 hidden-print">
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
                    </div>
                </div>
                
            </div>
        </div>


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
