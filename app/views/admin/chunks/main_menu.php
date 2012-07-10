        <ul id="mainNav">
        	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/posts"<?php if ($this -> getParameter('menu') == "posts"): ?> class="active"<?php endif; ?>>ПЕРВЫЙ КАНАЛ</a></li>
        	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/chats"<?php if ($this -> getParameter('menu') == "chats"): ?> class="active"<?php endif; ?>>ЧАТЫ</a></li>
        	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/staticPages"<?php if ($this -> getParameter('menu') == "static"): ?> class="active"<?php endif; ?>>СТАТИЧЕСКИЕ СТРАНИЦЫ</a></li>
        	<li class="logout"><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/logout">ВЫХОД</a></li>
        </ul>
