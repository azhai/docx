<?php $this->extendTpl('base.php'); ?>

<?php $this->blockStart('content'); ?>
<div class="container-fluid fluid-height wrapper">
    <div class="row columns content">
    
        <div class="left-column article-tree col-sm-3 hidden-print"> 
            <!-- For Mobile -->
            <div class="responsive-collapse">
                <button type="button" class="btn btn-sidebar" id="menu-spinner-button"> 
                <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> 
                </button>
            </div>
            <div id="sub-nav-collapse" class="sub-nav-collapse"> 
                <!-- Navigation -->
                <ul class="nav nav-list">
                    <?php $this->includeTpl('inc/navi.php', array(
                        'urlpre' => $urlpre, 'urlext' => $urlext, 'curr_url' => $curr_url
                    )); ?>
                </ul>
                <div class="well well-sidebar">
                    <?php if ($options['links']): ?>
                    <!-- Links -->
                    <?php foreach($options['links'] as $link_name => $link_url): ?>
                    <a href="<?php echo $link_url; ?>" target="_blank"><?php echo $link_name; ?></a><br>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <a href="<?php echo $linkage->buildQuery('action=cleancache'); ?>">重新缓存</a><br>
                    <a href="<?php echo $linkage->buildQuery('action=staticize'); ?>">生成静态页</a><br>
                    <?php if ($options['wkhtmltopdf']): ?>
                    <a href="<?php echo $linkage->buildQuery('action=genpdf'); ?>">生成PDF</a><br>
                    <?php endif; ?>
                    <?php if ($options['publish_branch']): ?>
                    <a href="<?php echo $linkage->buildQuery('action=publish'); ?>">发布HTML页面</a><br>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="right-column content-area col-sm-9">
            <div class="content-page">
            <?php $this->includeTpl('inc/cover.php', array(
                'urlpre' => $urlpre, 'urlext' => $urlext, 'assets_url' => $assets_url, 
                'first_page_url' => $first_page_url, 'page' => $page
            )); ?>
            </div>
        </div>
        
    </div>
</div>
<?php $this->blockEnd(); ?>
