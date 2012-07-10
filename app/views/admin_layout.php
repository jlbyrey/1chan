<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>1chan | Администраторская</title>
<link href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin_style/css/transdmin.css" rel="stylesheet" type="text/css" media="screen" />
<!--[if IE 6]><link rel="stylesheet" type="text/css" media="screen" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin_style/css/ie6.css" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin_style/css/ie7.css" /><![endif]-->
<script type="text/javascript" src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin_style/js/jquery.js"></script>
<script type="text/javascript" src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin_style/js/jNice.js"></script>
</head>

<body>
	<div id="wrapper">
		<?php include(dirname(__FILE__) .'/admin/chunks/main_menu.php'); ?>

        <div id="containerHolder">
			<div id="container">
        		<div id="sidebar">
                	<?php include(dirname(__FILE__) .'/admin/chunks/'. $this -> getParameter('menu') .'_menu.php'); ?>
                </div>

				<?php echo $content; ?>

                <div class="clear"></div>
            </div>
        </div>
    </div>
</body>
</html>
