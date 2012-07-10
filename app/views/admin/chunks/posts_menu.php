					<ul class="sideNav">
                    	<li><h3>Посты:</h3></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postAdd"<?php if ($this -> getParameter('submenu') == "post_add"): ?> class="active"<?php endif; ?>>Добавить пост</a></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/posts"<?php if ($this -> getParameter('submenu') == "post_list"): ?> class="active"<?php endif; ?>>Список постов</a></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postSearch"<?php if ($this -> getParameter('submenu') == "post_search"): ?> class="active"<?php endif; ?>>Фильтр постов</a></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postComments"<?php if ($this -> getParameter('submenu') == "post_comments"): ?> class="active"<?php endif; ?>>Комментарии</a></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postCategory"<?php if ($this -> getParameter('submenu') == "post_category"): ?> class="active"<?php endif; ?>>Категории</a></li>

                    	<li><h3>Онлайн:</h3></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/onlineChannel"<?php if ($this -> getParameter('submenu') == "online_channel"): ?> class="active"<?php endif; ?>>Каналы</a></li>

                    	<li><h3>Общее:</h3></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/blogSettings"<?php if ($this -> getParameter('submenu') == "settings"): ?> class="active"<?php endif; ?>>Настройки</a></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/blogWordfilter"<?php if ($this -> getParameter('submenu') == "wordfilter"): ?> class="active"<?php endif; ?>>Вордфильтр</a></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/blogModerators"<?php if ($this -> getParameter('submenu') == "moderators"): ?> class="active"<?php endif; ?>>Модераторы</a></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/blogLinks"<?php if ($this -> getParameter('submenu') == "links"): ?> class="active"<?php endif; ?>>Ссылки</a></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/blogLog"<?php if ($this -> getParameter('submenu') == "log"): ?> class="active"<?php endif; ?>>Лог управления</a></li>
                    </ul>