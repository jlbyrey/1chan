		<ul class="sideNav">
                    	<li><h3>Общие:</h3></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/chatAdd"<?php if ($this -> getParameter('submenu') == "chat_add"): ?> class="active"<?php endif; ?>>Добавить чат</a></li>
                    	<li><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/chats"<?php if ($this -> getParameter('submenu') == "chats"): ?> class="active"<?php endif; ?>>Список чатов</a></li>
		 </ul>
