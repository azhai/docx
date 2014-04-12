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
                            <?php echo $options['reading']; ?>
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
