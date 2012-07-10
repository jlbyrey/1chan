<?php include(dirname(__FILE__) .'/viewAll.php'); ?>
<div class="b-rss-link">
	<img class="b-rss-link_icon" src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/rss.png" width="16" height="16" alt="" />
	<a class="b-rss-link_anchor" href="<?php echo $this -> getParameter('rss_link'); ?>">Фид категории «<?php echo $this -> getParameter('category_title'); ?>»</a>
</div>