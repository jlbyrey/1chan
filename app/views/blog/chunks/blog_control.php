			<div class="b-blog-panel g-clearfix">
					<ul>
						<li class="b-blog-panel_b-add-entry<?php if ($this -> getParameter('section') == 'add'): ?> b-blog-panel_m-active<?php endif; ?>">
							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/add-entry.png" width="16" height="16" alt="" />
							<a href="/news/add/">Добавить запись</a>
						</li>
						<li class="b-blog-panel_b-favorites<?php if ($this -> getParameter('section') == 'favorite'): ?> b-blog-panel_m-active<?php endif; ?>">
							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/favorites.png" width="16" height="16" alt="" />
							<a href="/news/fav/">Избранные</a>
						</li>
						<li class="b-blog-panel_b-approved<?php if ($this -> getParameter('section') == 'rated'): ?> b-blog-panel_m-active<?php endif; ?>">
							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/tick.png" width="16" height="16" alt="" />
							<a href="/news/">Одобренные</a>
						</li>
						<li class="b-blog-panel_b-all<?php if ($this -> getParameter('section') == 'all'): ?> b-blog-panel_m-active<?php endif; ?>">
							<a href="/news/all/">Все</a>
						</li>
					</ul>
					<div class="b-blog-panel_b-searchmenu">
						<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/search.png" width="16" height="16" alt="" /> <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/search/">Поиск записей</a> |
						<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/cat/">Категории</a> |
						<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/last_comments/">Последние комментарии</a>
					</div>
					<?php
						switch($this -> getParameter('section')):
					 	case ('entry'):
					 ?>

					<div class="b-blog-panel_b-submenu">
						<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/new.png" width="16" height="16" alt="" />
						<a href="javascript://" class="g-disabled" id="new_comments_link">К непрочитанным комментариям</a>
					</div>
					<?php break; ?>

					<?php
						case ('all'):
						case ('favorite'):
						case ('rated'):
						case ('category'):
						case ('hidden'):
					 ?>

					<div class="b-blog-panel_b-submenu">
						<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/sort.png" width="16" height="16" alt="Сортировать" />
						<span>По дате:</span>
						<?php if($this -> getParameter('sortby') == "created_at"): ?>
							<span>создания</span>
						<?php else: ?>
							<a href="/news/sort/created_at/" class="g-dynamic">создания</a>
						<?php endif; ?>

						<span>|</span>
						<?php if($this -> getParameter('sortby') == "updated_at"): ?>
							<span>обновления</span>
						<?php else: ?>
							<a href="/news/sort/updated_at/" class="g-dynamic">обновления</a>
						<?php endif; ?>

					</div>
					<?php break; ?>
					<?php endswitch; ?>
				</div>
