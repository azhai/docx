<?php $this->extendTpl('base.php'); ?>

<?php $this->blockStart('content'); ?>
<?php $this->includeTpl('cover.php', array(
    'urlpre' => $urlpre, 'urlext' => $urlext, 'assets_url' => $assets_url, 
    'first_page_url' => $first_page_url, 'page' => $page
)); ?>
<?php $this->blockEnd(); ?>