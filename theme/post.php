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
                <?php if ($options['links']): ?>
                <div class="well well-sidebar"> 
                    <!-- Links -->
                    <?php foreach($options['links'] as $name => $url): ?>
                    <a href="<?php echo $url;?>" target="_blank"><?php echo $name;?></a><br>
                    <?php endforeach; ?>
                    <a href="#" id="toggleCodeBlockBtn" onclick="toggleCodeBlocks();">外置代码框</a><br>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="right-column content-area col-sm-9">
            <div class="content-page">
                <article>
                    <div class="page-header sub-header clearfix">
                        <h1><?php 
                            echo $page['title'] . "\n";
                            $edit_doc_url = $urlpre . $admin_urlpre . $curr_url . $urlext;
                            echo '<a id="editThis" href="' . $edit_doc_url . '" class="btn html-hidden">编辑文档</a>';
                        ?></h1>
                        <span style="float: left; font-size: 10px; color: gray;">
                        <?php foreach($page['tags'] as $i => $tag):
                                            echo ($i > 0) ? ', ' : '标签：'; ?>
                        <!--a href="<?php echo $urlpre .'tag/'. slugify($tag);?>"--><?php echo $tag;?><!--/a-->
                        <?php endforeach; ?>
                        </span> 
                        <span style="float: right; font-size: 10px; color: gray;"> 
                        <!--a href="<?php echo $urlpre .'author/'. slugify($page['author']);?>"-->
                        <?php echo $page['author']; ?><!--/a--> 写于 <?php echo zh_date($options['date_format'], $page['date']); ?> 
                        </span>
                    </div>
                    
                    <?php echo $page['htmldoc']; ?>
                </article>
            </div>
        </div>
        
    </div>
</div>
<?php $this->blockEnd(); ?>

<?php $this->blockStart('scripts'); ?>
<!-- hightlight.js --> 
<script src="<?php echo $assets_url; ?>/js/highlight.min.js"></script> 
<script>hljs.initHighlightingOnLoad();</script>
<?php $this->blockEnd(); ?>

