<?php $this->extendTpl($theme_dir . '/base.php'); ?>

<?php $this->blockStart('content'); ?>
<div class="container-fluid fluid-height wrapper">
    <div class="row columns content">

        <div class="left-column article-tree col-sm-3 hidden-print">
            <!-- For Mobile -->
            <div class="responsive-collapse">
                <button type="button" class="btn btn-sidebar" id="menu-spinner-button"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
            </div>
            <div id="sub-nav-collapse" class="sub-nav-collapse">
                <!-- Navigation -->
                <ul class="nav nav-list">
                <?php $this->includeTpl($theme_dir . '/inc/navi.php'); ?>
                </ul>
                <div class="well well-sidebar">
                    <?php if ($options['links']): ?>
                    <!-- Links -->
                    <?php foreach($options['links'] as $link_name => $link_url): ?>
                    <a href="<?=$link_url?>" target="_blank"><?=$link_name?></a><br>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="right-column content-area col-sm-9">
            <div class="content-page">
                <article>
                    <div class="page-header sub-header clearfix">
                        <h1><?php 
                            echo $page['title'] . "\n";
                            $view_doc_url = $urlpre . '/' . $curr_url . '/';
                            echo '<a id="editThis" href="' . $view_doc_url . '" class="closeEditor btn btn-warning">关闭</a>';
                        ?></h1>
                        <span style="float: left; font-size: 10px; color: gray;">
                        <?php foreach($page['tags'] as $i => $tag):
                                            echo ($i > 0) ? ', ' : '标签：'; ?>
                        <!--a href="<?=$urlpre .'tag/'. \Docx\Common::slugify($tag);?>"--><?=$tag;?><!--/a-->
                        <?php endforeach; ?>
                        </span> 
                        <span style="float: right; font-size: 10px; color: gray;"> 
                        <!--a href="<?=$urlpre .'author/'. \Docx\Common::slugify($page['author']);?>"--><?=$page['author']?><!--/a--> 写于 <?php echo \Docx\Common::zhdate($options['date_format'], $page['date']); ?> 
                        </span>
                    </div>
                    
                    <form method="POST">
                    <div class="navbar navbar-inverse navbar-default navbar-fixed-bottom" role="navigation">
                        <div class="navbar-inner"> <a href="javascript:;" class="save_editor btn btn-primary navbar-btn pull-right">保存到文件</a> </div>
                    </div>
                    <textarea id="metatext_editor" name="metatext" style="display:none" 
                            rows="<?php echo substr_count($page['metatext'], "\n") + 1; ?>" cols="80"><?=$page['metatext']?></textarea>
                    <textarea id="markdown_editor" name="markdown"><?=$page['markdown']?></textarea>
                    <div id="htmldoc_editor" name="markdown" class="pen hinted" placeholder="im a placeholder"><?=$page['htmldoc']?></div>
                    </form>
                    
                    <div class="clearfix"></div>
                </article>
            </div>
        </div>

    </div>
</div>
<?php $this->blockEnd(); ?>

<?php $this->blockStart('scripts'); ?>
<link rel="stylesheet" href="<?=$assets_url?>/css/pen.css">
<!-- hightlight.js -->
<script src="<?=$assets_url?>/js/pen.js"></script>
<script src="<?=$assets_url?>/js/markdown.js"></script>
<script src="<?=$assets_url?>/js/demarcate.min.js"></script>
<script type="text/javascript">
$(function(){
    var marked = $('#htmldoc_editor');
    var pen = new Pen({editor: marked[0], class: "hinted"});
    marked.addClass('hinted');
    pen.rebuild();
    $('.save_editor').click(function(){
        var marktext = demarcate.demarcate(marked);
        $('#markdown_editor').val(marktext);
        document.forms[0].submit();
    });
});
</script>
<?php $this->blockEnd(); ?>

