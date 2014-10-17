<html lang="zh">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page['title']; ?></title>
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style rel="stylesheet">
article pre { color: #eaeaea; }
</style>
</head>
<body>

<div class="navbar navbar-fixed-top hidden-print">
    <div class="container-fluid">
        <p class="brand navbar-brand pull-left"> <?php echo $options['title']; ?> </p>
        <p class="navbar-text pull-right"> <?php echo rand_greeting($options['greetings']); ?> </p>
    </div>
</div>

<div class="container-fluid fluid-height wrapper">
<div class="row columns content">
<div class="right-column content-area col-sm-9">
<div class="content-page">

    <article>
        <div class="page-header sub-header clearfix">
            <h1>
                <?php echo $page['title'];?>
            </h1>
            <span style="float: left; font-size: 10px; color: gray;">
            <?php foreach($page['tags'] as $i => $tag):
                                echo ($i > 0) ? ', ' : '标签：'; ?>
            <!--a href="<?php echo $url_prefix .'tag/'. slugify($tag);?>"--><?php echo $tag;?><!--/a-->
            <?php endforeach; ?>
            </span> 
            <span style="float: right; font-size: 10px; color: gray;"> 
            <!--a href="<?php echo $url_prefix .'author/'. slugify($page['author']);?>"-->
            <?php echo $page['author']; ?><!--/a--> 写于 <?php echo zh_date($options['date_format'], $page['date']); ?> 
            </span>
        </div>
        
        <?php echo $page['dochtml']; ?>
    </article>
    
</div>
</div>
</div>
</div>

</body>
</html>