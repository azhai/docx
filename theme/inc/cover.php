<div class="homepage-hero well container-fluid">
    <div class="container">
        <div class="row">
            <div class="text-center col-sm-12">
                <?php if ($options['tagline']): ?>
                <h2><?php echo $options['tagline'];?></h2>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <?php if ($options['cover_image']): ?>
                <img class="homepage-image img-responsive" src="<?php echo $assets_url . '/' . $options['cover_image'];?>" alt="<?php echo $options['title'];?>">
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="hero-buttons container-fluid">
    <div class="container">
        <div class="row">
            <div class="text-center col-sm-12">
                <?php if ($options['repo']): ?>
                <a href="https://github.com/<?php echo $options['repo']; ?>" class="btn btn-secondary btn-hero"> 前往GitHub </a>
                <?php endif; ?>
                <a href="<?php echo $urlpre . $admin_urlpre . $urlext;?>" class="btn btn-secondary btn-hero html-hidden"> 管理静态页 </a>
                <a href="<?php echo $urlpre . $first_page_url . $urlext;?>" class="btn btn-primary btn-hero"> <?php echo $options['reading']; ?> </a>
            </div>
        </div>
    </div>
</div>

<div class="homepage-content container-fluid">
    <div class="container">
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1"> <?php echo $page['htmldoc'];?> </div>
        </div>
    </div>
</div>

<div class="homepage-footer well container-fluid">
    <div class="container">
        <div class="row">
            <div class="col-sm-5 col-sm-offset-1">
                <?php if ($options['links']): ?>
                <!-- Links -->
                <ul class="footer-nav">
                    <?php foreach($options['links'] as $link_name => $link_url): ?>
                    <a href="<?php echo $link_url; ?>" target="_blank"><?php echo $link_name; ?></a><br>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>