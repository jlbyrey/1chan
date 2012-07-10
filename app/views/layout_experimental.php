<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="description" content="Первый канал интернетов" />
		<meta name="keywords" content="крокодил, залупа, сыр" />

		<title><?php echo $this -> getParameter('title'); ?> | 1chan.ru</title>

		<link rel="icon"       type="image/x-icon" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/favicon.ico" />
		<link rel="stylesheet" type="text/css"     href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/css/production.css?14" media="all" />
		<link rel="stylesheet" type="text/css"     href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/css/jquery_style/jquery-ui.css" media="all" />

		<script type="text/javascript" src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/js/jquery.js"></script>
		<script type="text/javascript" src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/js/realplexor.js"></script>
		<script type="text/javascript" src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/js/production_ex.js?58"></script>
	</head>

	<body id="<?php echo(Session::getInstance() -> getKey()); ?>">
	<div class="b-notifiers js-notifiers"></div>

	<div class="b-mod-toolbar g-hidden">
	    <a href="#" id="mod_category" title="Категория"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/settings2.png" width="16" height="16" alt="" /></a>
		<a href="#" id="mod_pinned"   title="Прикреплена"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/pinned.png" width="16" height="16" alt="" /></a>
		<a href="#" id="mod_rated"    title="Одобрена"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/tick.png" width="16" height="16" alt="" /></a>
		<a href="#" id="mod_rateable" title="Оцениваема"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/rate_on.png" width="16" height="16" alt="" /></a>
		<a href="#" id="mod_closed"   title="Закрыта"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/block.png" width="16" height="16" alt="" /></a>
		<a href="#" id="mod_remove"   title="Удалить"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/remove.gif" width="16" height="16" alt="" /></a>
	</div>
	<?php if ($message = ControlModel::isGlobalMessage()): ?>
		<div class="b-global-message-panel">
			<div class="l-wrap">
				<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/warning.png" width="16" height="16" alt="" /> <?php echo($message); ?>
			</div>
		</div>
	<?php endif; ?>
		<div class="l-wrap">
			<?php /*<div style="
				position: fixed;
				background: white;
				border: 1px solid #aaa;
				width: 100px;
				height: 100px;
				border-radius: 8px;
				box-shadow: 0px 0px 1px #888;
				margin-left: 700px;
				bottom: 0;
				z-index: 999;
				font-size: 11px;
				padding: 5px;
				text-align: center;
			"><div class="js-poo-target" style="text-align: center; padding-top: 3px; padding-right: 0px; padding-bottom: 3px; padding-left: 0px;"><img src="/img/poo.png" width="64" height="65"></div><a href="javascript://" class="g-dynamic js-poo-toggle">Включить каку</a></div> */ ?>
			<div id="ajax_loader" class="b-ajax-loader">Загрузка...</div>
			<div id="ajax_loader_error" class="b-ajax-loader-error">Произошла ошибка!</div>

			<div class="b-top-panel">
				<ul>
					<li>
						<a href="/live/" class="b-top-panel_b-online-link<?php if(!TemplateHelper::isLiveUpdated()): ?> m-disactive<?php endif; ?>">Онлайн ссылки</a>
					</li>
					<li>|</li>
					<li>
						<a href="/chat/">Анонимные чаты</a>
					</li>
					<li>|</li>
					<li>
						<a href="/press/" class="g-disabled">Цифровые издания</a>
					</li>
					<li>|</li>
					<li>
						<a href="/radio/" class="g-disabled">Радио и подкасты</a>
					</li>

					<li class="b-top-panel_m-right">
						<a href="http://1chan.ru/chat/support/">Обратная связь</a>
					</li>
				</ul>
			</div>

			<div class="b-header-block m-mascot-<?php echo($this -> getParameter('board_id', 'news')); ?>">
				<div class="b-header-block_b-logotype">
					<a href="http://1chan.ru/">
						<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/img/logo.png" width="250" height="80" alt="1chan.ru" />
					</a>
				</div>
				<div class="b-header-block_b-stats" id="stats_block">
					<p>
						Вижу <strong id="stats_online"><?php echo($this -> getParameter('global_online', 1)); ?></strong>! Всего сегодня было <strong id="stats_hosts"><?php echo($this -> getParameter('global_unique', 1)); ?></strong>.<br />
						За день постов — <strong id="stats_posts"><?php echo($this -> getParameter('global_posts', 0)); ?></strong>, скорость ~<strong id="stats_speed"><?php echo($this -> getParameter('global_speed', 0)); ?></strong> п/ч.
						<span class="g-hidden" id="stats_unique_posters"><?php echo($this -> getParameter('global_unique_posters', 1)); ?></span>
					</p>
				</div>
			</div>

			<?php if($this -> getParameter('right_panel')): ?>
            		<div class="l-right-panel-wrap">
			    <div class="b-links-panel js-links-panel">
			         <div class="b-links-panel_b-title">
			            <h2>Онлайн ссылки</h2>
			        </div>
			        <div class="b-links-panel_b-links">
			            <div id="placeholder_link_panel">
			            <?php $links = $this -> getParameter('online_links', array()); ?>
			            <?php if (!empty($links)): ?>
				            <?php foreach($links as $link): ?>
				            <div class="b-live-entry">
					            <a target="_blank" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/live/redirect/<?php echo($link['id']) ?>?to=<?php echo($link['link']); ?>" class="b-live-entry_b-description"><?php echo($link['description']); ?></a> &larr; <a href="<?php echo($link['category']['url']); ?>" class="b-live-entry_b-board"><?php echo($link['category']['title']); ?></a>
				            </div>
				            <?php endforeach; ?>

			            <?php else: ?>
			                <em>Нет активных ссылок</em>
			            <?php endif; ?>
			            </div>
			            <textarea id="template_link_panel" style="display:none">
					        <div class="b-live-entry">
						        <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/live/redirect/<%=id%>?to=<%=link%>" class="b-live-entry_b-description"><%=description%></a> &larr; <a href="<%=category['url']%>" class="b-live-entry_b-board"><%=category['title']%></a>
					        </div>
				        </textarea>
				    </div>
				    <div class="b-links-panel_b-footer">
				        <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/live/">К подробному списку &rarr;</a>
				    </div>
				    <div class="b-links-panel_b-hide">
				    	<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/live/linksPanel/?status=off" class="b-links-panel_b-title_b-close g-dynamic js-close-right-panel g-hidden">Скрыть панель</a>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="l-left-panel-wrap">
			    <div class="b-menu-panel">
			         <div class="b-menu-panel_b-title">
			            <h2>Первый канал</h2>
			        </div>
			        <div class="b-menu-panel_b-links">
			            <ul>
			            	<li<?php if($this -> getParameter('board_id') == 'news'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/">Одобренные</a> | <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/all/">Все</a></li>
			            	<li class="b-menu-panel_b-footer">
				            <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/share/">сообщить новость</a>
				        </li>
				    </ul>
				    </div>
				</div>

			    <div class="b-menu-panel">
			         <div class="b-menu-panel_b-title">
			            <h2>Общение</h2>
			        </div>
			          <div class="b-menu-panel_b-links">
			            <ul>
			            	<li<?php if($this -> getParameter('board_id') == 'd'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/d/">/d/ - Дискуссии об имиджбордах</a></li>
			            	<li<?php if($this -> getParameter('board_id') == 'b'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/b/">/b/ - Бред</a></li>
			            	<li<?php if($this -> getParameter('board_id') == 'to'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/to/">/to/ - Тохо</a></li>
			            	<li<?php if($this -> getParameter('board_id') == 'a'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/a/">/a/ - Аниме</a></li>
			            	<li<?php if($this -> getParameter('board_id') == 's'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/s/">/s/ - Софт</a></li>
			            	<li<?php if($this -> getParameter('board_id') == 'vg'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/vg/">/vg/ - Видеоигры</a></li>
			            	<li<?php if($this -> getParameter('board_id') == 'pr'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/pr/">/pr/ - Программинг</a></li>
			            	<li<?php if($this -> getParameter('board_id') == 'mu'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/mu/">/mu/ - Музыка</a></li>
			            	<li<?php if($this -> getParameter('board_id') == 'tv'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/tv/">/tv/ - ТВ и Кино</a></li>
			            	<li<?php if($this -> getParameter('board_id') == 'int'): ?> class="m-active"<?php endif; ?>><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/int/">/int/ - International</a></li>
			            	<li<?php if($this -> getParameter('board_id') == 'fav'): ?> class="m-active"<?php endif; ?>><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/favorites-false.png" width="16" height="16" alt="" /> <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/fav/">Избранные треды</a></li>
				        <li class="b-menu-panel_b-footer">
				            <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/service/last_board_posts/">&larr; последние посты</a>
				        </li>
				    </ul>
                                    <div class="b-menu-panel_b-conference" style="padding-top: 10px; font-size: 10px; color: #bbb;">Jabber-Конференции:<br> <u>news@conference.1chan.ru</u><br /><u>press@conference.1chan.ru</u></div>
				    </div>
				</div>
			</div>

			<div class="l-content-wrap">
				<?php echo $content; ?>

			</div>
			<div class="l-footer-wrap m-mascot-<?php echo($this -> getParameter('board_id', 'news')); ?>">
				<div class="b-underlinks">
					<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/rss.png" width="16" height="16" alt="" />
					<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/rss.xml">Одобренные</a> |
					<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/rss2.png" width="16" height="16" alt="" />
					<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/all/rss.xml">Все</a> |
					<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/jabber.png" width="16" height="16" alt="" />
					<a href="xmpp:bot@1chan.ru?roster;name=1chan.ru;">Jabber-бот</a>
				</div>
				<?php $_footer_links = Blog_BlogLinksModel::GetLinks(); ?>
				<div class="b-footer-imgboards">
					<h2>Имиджборды:</h2>
					<ul>
					<?php foreach($_footer_links['imgboards'] as $link): ?>

						<li>
						<?php if(@$link['offline']): ?>

							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/offline.png" width="16" height="16" alt="Сайт недоступен" />
							<a class="g-strike" href="<?php echo($link['href']); ?>"><?php echo($link['title']); ?></a>
						<?php else: ?>

							<img src="<?php echo(TemplateHelper::getIcon($link['href'])); ?>" width="16" height="16" alt="" />
							<a href="<?php echo($link['href']); ?>"><?php echo($link['title']); ?></a>
						<?php endif; ?>

						</li>
					<?php endforeach; ?>

					</ul>

				</div>
				<div class="b-footer-services">
					<h2>Другие ссылки:</h2>
					<ul>
					<?php foreach($_footer_links['services'] as $link): ?>

						<li>
						<?php if(@$link['offline']): ?>

							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/offline.png" width="16" height="16" alt="Сайт недоступен" />
							<a class="g-strike" href="<?php echo($link['href']); ?>"><?php echo($link['title']); ?></a>
						<?php else: ?>

							<img src="<?php echo(TemplateHelper::getIcon($link['href'])); ?>" width="16" height="16" alt="" />
							<a href="<?php echo($link['href']); ?>"><?php echo($link['title']); ?></a>
						<?php endif; ?>

						</li>
					<?php endforeach; ?>

					</ul>
				</div>
				<div class="b-footer-copyrights">
					<span>При копировании материалов ни в коем случае не давать ссылку на <a href="http://1chan.ru/">1chan.ru</a></span>
				</div>
			</div>
		</div>
	</body>
</html>
