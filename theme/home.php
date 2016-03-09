<?php $this->extendTpl($theme_dir . '/base.php'); ?>
<?php $this->blockStart('content'); ?>

<div class="container-fluid fluid-height wrapper">
    <div class="row columns content">
    
        <div class="left-column article-tree col-sm-3 hidden-print">
            <?php $this->includeTpl($theme_dir . '/inc/navi.php'); ?>
        </div>
        
        <div class="right-column content-area col-sm-9">
        
            <div class="homepage-hero well container-fluid">
                <div class="row">
                    <div class="text-center col-sm-12">
                        <?php if ($options['tagline']): ?>
                        <h2>
                            <?=$options['tagline'];?>
                        </h2>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <?php if ($options['cover_image']): ?>
                        <img class="homepage-image img-responsive" 
                                src="<?=$assets_url . '/' . $options['cover_image'];?>"
                                alt="<?=$options['title'];?>">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="hero-buttons container-fluid">
                <div class="row">
                    <div class="text-center col-sm-12">
                        <?php if ($options['github_repo']): ?>
                        <a href="https://github.com/<?=$options['github_repo']?>"
                                class="btn btn-secondary btn-hero"> 前往GitHub </a>
                        <?php endif; ?>
                        <a href="<?=$urlpre . '/index/'?>" class="btn btn-primary btn-hero">
                        <?=$options['reading']?>
                        </a> </div>
                </div>
            </div>
            
            <div class="homepage-content container-fluid">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <?=$page['htmldoc']?>
                    </div>
                </div>
            </div>
            
            <div class="homepage-footer well container-fluid">
                <div class="row">
                    <div class="col-sm-5 col-sm-offset-1">
                        <?php if ($options['links']): ?>
                        <!-- Links -->
                        <ul class="footer-nav">
                            <?php foreach($options['links'] as $link_name => $link_url): ?>
                            <a href="<?=$link_url?>" target="_blank">
                            <?=$link_name?>
                            </a><br />
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
</div>
<?php $this->blockEnd(); ?>
