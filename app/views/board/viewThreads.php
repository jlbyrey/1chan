                <?php $ENG = ($this -> getParameter('board_id') == 'int'); ?>
				<div class="b-board-header">
					<div class="b-board-header_name">
						<h1><?php echo($this -> getParameter('title')); ?></h1>
						<div class="b-board-header_desc">
							<?php if (!$ENG): ?><label><input type="checkbox" name="subscribe" class="js-subscribe-checkbox" <?php if ($subscribed) echo('checked="checked"'); ?> /> <span>Оповещение о новых тредах</span></label><?php else: ?>
							<?php echo($this -> getParameter('description')); ?><?php endif; ?>
						</div>
					</div>
					<div class="b-board-header_options">
						 <?php if (!$ENG): ?><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/help/board/">Правила разделов</a><?php endif; ?>
					</div>
				</div>

				<div class="b-board-form">
					<div id="board_captcha" style="display:none;font-size:1.2em;text-align:center;" title="<?php if (!$ENG): ?>Обратный тест Тьюринга<?php else: ?>Please enter Captcha code<?php endif; ?>">
						<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/captcha/?key=board&<?php echo session_name()?>=<?php echo session_id()?>&rand=" /><br />
						<input type="text" value="" size="10" />					
					</div>
					<div class="b-board-form_b-error" id="board_form_error"><?php echo(implode(', ', array_values($form_errors))); ?></div>
					<iframe src="about:blank" name="board_form_iframe" style="position:absolute;left:-9999px;visibility:hidden"></iframe>
					<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($this -> getParameter('board_id')); ?>/create/" method="post" id="board_form" enctype="multipart/form-data">
					<input type="hidden" name="captcha" value="" />
					<input type="hidden" name="homeboard" value="anonymous" />
						<div class="g-clearfix">
							<div class="b-board-form_title">
								<label><?php if (!$ENG): ?>Заголовок:<?php else: ?>Title:<?php endif; ?></label>
								<input name="title" type="text" value="<?php echo @htmlspecialchars($board_form['title']); ?>"<?php if(array_key_exists('title', $form_errors)):?> class="g-input-error"<?php endif; ?> />
							</div>
							<div class="b-board-form_image">
								<label><?php if (!$ENG): ?>Изображение:<?php else: ?>Image file:<?php endif; ?></label>
								<input name="upload" max_file_size="4194304" type="file" />
							</div>
						</div>
						<div class="b-board-form_body">
							<label><?php if (!$ENG): ?>Текст сообщения:<?php else: ?>Message:<?php endif; ?></label>
							<textarea name="text" rows="4"<?php if(array_key_exists('text', $form_errors)):?> class="g-input-error"<?php endif; ?>><?php echo @htmlspecialchars($board_form['text']); ?></textarea>
						</div>
						<div class="b-board-footer">
							<div class="b-board-form_password">
								<label><span><?php if (!$ENG): ?>Пароль на удаление:<?php else: ?>Password:<?php endif; ?></span></label>
								<input type="password" name="password" value="<?php echo @htmlspecialchars($board_form['password']); ?>" />
							</div>
							<div class="b-board-form_submit">
							    <?php if (!$ENG): ?>
								<span class="b-homeboard-form" style="font-size: 0.6em">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/anonymous.png" class="b-homeboard-form_icon js-homeboard-icon" /> 
									<a href="javascript://" class="b-homeboard-form_link js-homeboard-link g-dynamic">Сменить</a>
									<div class="b-homeboard-form_select js-homeboard-select g-hidden">
									<?php foreach(HomeBoardHelper::getBoards() as $board => $data): ?>
										<a href="javascript://" name="<?php echo($board); ?>" title="<?php echo($data[1]); ?>" class="js-homeboard-select-link">
											<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<?php echo($data[0]); ?>" class="b-homeboard-form_icon" />
										</a>
									<?php endforeach; ?>

									</div>
								</span>
                                <?php endif; ?>
								<input type="submit" value="<?php if (!$ENG): ?>Создать тред<?php else: ?>Submit<?php endif; ?>" />
							</div>
						</div>
					</form>
				</div>

				<div class="b-board">
					<?php if($posts == false): ?>

                    <?php if (!$ENG): ?>
					<div class="b-static">
						<h1>В данном разделе нет постов</h1>
						<p>Либо их действительно нет, либо опять все сломалось.</p>
					</div>
					<?php else: ?>
					<div class="b-static">
						<h1>There aren't any posts here yet!</h1>
						<p>Create the thread to rock and roll.</p>
					</div>
					<?php endif; ?>

					<?php else: ?>

                     <?php if (!$ENG): ?>
					<div class="b-board-post-added-notify" id="post_notify">
						Доступны новые записи, <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($this -> getParameter('board_id')); ?>/">перезагрузите страницу</a>, чтобы увидеть их.
					</div>
					<?php else: ?>
					<div class="b-board-post-added-notify" id="post_notify">
						There are few new posts available, <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($this -> getParameter('board_id')); ?>/">reload this page</a> to see them.
					</div>
					<?php endif; ?>

					<textarea id="template_comment" style="display:none">
						<div class="b-comment m-new" id="comment_<%=board_id%>_<%=id%>">
							<div class="b-comment_b-info">
								<%=created_at%>, <span class="js-comment-id"><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($this -> getParameter('board_id')); ?>/res/<%=parent_id%>/#<%=id%>" class="js-paste-link" name="<%=id%>">№<%=id%></a></span>
								<a href="#" class="js-remove-button g-hidden">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/remove.gif" width="16" height="16" alt="<%=id%>" />
								</a>
								<?php if (!$ENG): ?>
								<% if (author[0] != "anonymous") { %>
								<a href="http://<%=author[0]%>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<%=author[1][1]%>»">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<%=author[1][0]%>" width="16" height="16" alt="" />
								</a>
								<% } %>
						        <?php else: ?>
							    <a href="javascript://" class="b-comment_b-homeboard" title="<%=country%>">
								    <img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/flags/<%=country%>.png" width="18" height="12" alt="" />
							    </a>
							    <?php endif; ?>
							</div>
							<div class="b-comment_b-body g-clearfix">
								<p>
								<% if (upload) { %>
								<a class="b-image-link" target="_blank" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<%=upload['web_full']%>" title="<%=upload['full_size'][0]%>x<%=upload['full_size'][1]%>, <%=upload['size']%>">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<%=upload['web_thumb']%>" width="<%=upload['thumb_size'][0]%>" height="<%=upload['thumb_size'][1]%>" alt="" />
								</a>
								<% } %>
								<%=text%>
								</p>
							</div>
						</div>
					</textarea>

					<div id="board_comment_captcha" style="display:none;font-size:1.2em;text-align:center;" title="<?php if (!$ENG): ?>Обратный тест Тьюринга<?php else: ?>Please enter Captcha code<?php endif; ?>">
						<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/captcha/?key=board_comment&<?php echo session_name()?>=<?php echo session_id()?>&rand=" /><br />
						<input type="text" value="" size="10" />					
					</div>

					<textarea id="template_form_comment" style="display:none">
						<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($this -> getParameter('board_id')); ?>/createPost/" method="post" id="comment_form" enctype="multipart/form-data">
						<input type="text" name="email" value="" class="g-hidden" />
						<input type="hidden" name="parent_id" value="<%=id%>" />
						<input type="hidden" name="captcha" value="" />
						<input type="hidden" name="homeboard" value="anonymous" />
						<div class="b-comment-form">
						    <?php if (!$ENG): ?>
							<strong>Ответить в тред:</strong><?php else: ?>
							<strong>Add a reply:</strong>
							<?php endif; ?>
							<span class="b-comment-form_b-helplink">
						        <?php if (!$ENG): ?>
								<a href="/help/markup/" target="_blank">Правила разметки</a><?php endif; ?>
							</span>
							<div>
								<textarea id="comment_form_text" name="text" rows="5"><%=textarea%>
								<div class="b-comment-form_b-upload g-clearfix"><?php if (!$ENG): ?>Изображение:<?php else: ?>Image file:<?php endif; ?> <label><input name="upload" max_file_size="4194304" type="file" /></label></div>
								<input type="submit" value="<?php if (!$ENG): ?>Отправить<?php else: ?>Submit<?php endif; ?>" />
								<?php if (!$ENG): ?>
								<span class="b-homeboard-form">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/anonymous.png" class="b-homeboard-form_icon js-homeboard-icon" /> 
									<a href="javascript://" class="b-homeboard-form_link js-homeboard-link g-dynamic">Сменить</a>
									<div class="b-homeboard-form_select js-homeboard-select g-hidden">
									<?php if (!$ENG): ?>
									<?php foreach(HomeBoardHelper::getBoards() as $board => $data): ?>
										<a href="javascript://" name="<?php echo($board); ?>" title="<?php echo($data[1]); ?>" class="js-homeboard-select-link">
											<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<?php echo($data[0]); ?>" class="b-homeboard-form_icon" />
										</a>
									<?php endforeach; ?>
									<?php endif; ?>

									</div>
								</span>
								<?php endif; ?>
								<em id="comment_form_error"></em>
							</div>
						</div>
						</form>
					</textarea>
					<?php foreach($posts as $post): ?>

					<div class="b-blog-entry" id="post_<?php echo($post['board_id']); ?>_<?php echo($post['id']); ?>">
						<div class="b-blog-entry_b-header">
							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/favicons/1chan.ru.gif" width="16" height="16" alt="" />

							<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($post['board_id']); ?>/res/<?php echo($post['id']); ?>/">
							    <?php if (!$ENG): ?>
								<?php echo(empty($post['title']) ? 'Тред №'. $post['id'] : $post['title']); ?>
								<?php else: ?>
								<?php echo(empty($post['title']) ? 'Thread #'. $post['id'] : $post['title']); ?>
								<?php endif; ?>
							</a>

						</div>
						<div class="b-blog-entry_b-body g-clearfix">
							<div class="wrap">
								<p>
								<?php if($post['upload']): ?>
								<a class="b-image-link" target="_blank" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($post['upload']['web_full']); ?>" title="<?php echo($post['upload']['full_size'][0]);?>x<?php echo($post['upload']['full_size'][1]);?>, <?php echo($post['upload']['size']); ?>">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($post['upload']['web_thumb']); ?>" width="<?php echo($post['upload']['thumb_size'][0]); ?>" height="<?php echo($post['upload']['thumb_size'][1]); ?>" alt="" />
								</a>
								<?php endif; ?>
								<?php echo($post['text']); ?>

								</p>
							</div>
						</div>

						<div class="b-blog-entry_b-info" id="post_<?php echo($post['id']); ?>_info">
							<span class="b-blog-entry_b-info_b-control">
								<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/fav/toggle/<?php echo($post['board_id']); ?>/<?php echo($post['id']); ?>/" class="js-favorite-button"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/favorites<?php if(!Board_FavoritesModel::IsFavoritePost($post['board_id'], $post['id'])):?>-false<?php endif; ?>.png" width="16" height="16" alt="" id="post_<?php echo($post['board_id']); ?>_<?php echo($post['id']); ?>_favorite" /></a>
								<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($post['board_id']); ?>/remove/?id=<?php echo($post['id']); ?>" class="js-delete-button"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/delete.gif" width="16" height="16" alt="" id="post_<?php echo($post['board_id']); ?>_<?php echo($post['id']); ?>_favorite" /></a>

								<span>|</span>

								<span><?php echo(TemplateHelper::date((!$ENG) ? 'd M Y @ H:i' : 'Y-m-d @ H:i', $post['created_at'])); ?></span>
								<span>|</span>
								<span>
									<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($post['board_id']); ?>/res/<?php echo($post['id']); ?>/"  class="g-disabled js-post-id-link">
										№<?php echo($post['id']); ?></a>
								</span>
								<?php if (!$ENG): ?>
								<?php if($post['author'] && $post['author'] != 'anonymous'): $author = HomeBoardHelper::getBoard($post['author']); ?>
								<span>|</span>
								<a href="http://<?php echo($post['author']); ?>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<?php echo($author[1]); ?>»">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<?php echo($author[0]); ?>" width="16" height="16" alt="" />
								</a>
								<?php endif; ?>
						        <?php else: ?>
							    <span>|</span>
							    <a href="javascript://" class="b-comment_b-homeboard" title="<?php echo($post['country']); ?>">
								    <img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/flags/<?php echo($post['country']); ?>.png" width="18" height="12" alt="" />
							    </a>
							    <?php endif; ?>
							</span>
							<span class="b-blog-entry_b-info_b-link">
								<a href="javascript://" class="js-update-post-button g-hidden g-dynamic" name="<?php echo($post['id']); ?>"><?php if (!$ENG): ?>Быстрый ответ<?php else: ?>Quick reply<?php endif; ?></a> | 
								<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($post['board_id']); ?>/res/<?php echo($post['id']); ?>/" class="js-add-comment-button">
									<?php if (!$ENG): ?>Ответить<?php else: ?>Reply<?php endif; ?> (<span class="js-comments"><?php echo($post['count']); ?></span>)
								</a>
							</span>
						</div>
					</div>

					<div class="l-comments-wrap">
						<?php if (($post['count'] - 5) > 0): ?>
						<div class="b-comments-load" id="board_<?php echo($post['board_id']); ?>_<?php echo($post['id']); ?>_thread_load">
						    <?php if (!$ENG): ?>
							<span>Пропущено <?php echo(TemplateHelper::ending($post['count'] - 5, 'ответ', 'ответа', 'ответов')); ?><?php if(($post['count'] - 3) <= 44): ?>. <a href="javascript://" class="js-thread-load" name="<?php echo($post['id']); ?>">Загрузить</a><?php endif; ?></span>
						    <?php else: ?>
						    <span><?php echo($post['count'] - 5); ?> post(s) not shown<?php if(($post['count'] - 3) <= 44): ?>. <a href="javascript://" class="js-thread-load" name="<?php echo($post['id']); ?>">Load these posts</a><?php endif; ?></span>
						    <?php endif; ?>
						</div>
						<?php endif; ?>

						<div id="placeholder_comment_<?php echo($post['board_id']); ?>_<?php echo($post['id']); ?>">
						<?php if($post['posts']): ?>
						<?php foreach($post['posts'] as $tail_post): ?>

						<div class="b-comment" id="comment_<?php echo($tail_post['board_id']); ?>_<?php echo($tail_post['id']); ?>">
							<div class="b-comment_b-info">
								<?php echo(TemplateHelper::date((!$ENG) ? 'd M Y @ H:i' : 'Y-m-d @ H:i', $tail_post['created_at'])); ?>, <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($post['board_id']); ?>/res/<?php echo($post['id']); ?>/#<?php echo($tail_post['id']); ?>" name="<?php echo($tail_post['id']); ?>" class="js-paste-link">№<?php echo($tail_post['id']); ?></a>
								
								<?php if (!$ENG): ?>
								<?php if($tail_post['author'] && $tail_post['author'] != 'anonymous'): $author = HomeBoardHelper::getBoard($tail_post['author']); ?>
								<a href="http://<?php echo($tail_post['author']); ?>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<?php echo($author[1]); ?>»">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<?php echo($author[0]); ?>" width="16" height="16" alt="" />
								</a>
								<?php endif; ?>
								<?php else: ?>
								<a href="javascript://" class="b-comment_b-homeboard" title="<?php echo($tail_post['country']); ?>">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/flags/<?php echo($tail_post['country']); ?>.png" width="18" height="12" alt="" />
								</a>
								<?php endif; ?>
							</div>
							<div class="b-comment_b-body g-clearfix">
								<div class="wrap">
									<p>									
									<?php if($tail_post['upload']): ?>
									<a class="b-image-link" target="_blank" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($tail_post['upload']['web_full']); ?>" title="<?php echo($tail_post['upload']['full_size'][0]);?>x<?php echo($tail_post['upload']['full_size'][1]);?>, <?php echo($tail_post['upload']['size']); ?>">
										<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($tail_post['upload']['web_thumb']); ?>" width="<?php echo($tail_post['upload']['thumb_size'][0]); ?>" height="<?php echo($tail_post['upload']['thumb_size'][1]); ?>" alt="" />
									</a>
									<?php endif; ?>
									<?php echo($tail_post['text']); ?>

									</p>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
						<?php endif; ?>
						</div>
						<?php if (!$ENG): ?>
						<div class="b-post-statistics js-postload-link" name="<?php echo($post['id']); ?>" id="board_<?php echo($post['board_id']); ?>_<?php echo($post['id']); ?>_postload" style="display:none;text-align:center;cursor:hand;cursor:pointer">
							Еще <b class="js-postload-num"></b>, нажмите на это сообщение для загрузки
							<span class="b-post-statistics_m-right">
								(<a href="javascript://" class="js-update-post-button" name="<?php echo($post['id']); ?>"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/reload.png" width="16" height="16" alt="" /> быстрый ответ</a>)
							</span>	
						</div>
						<?php else: ?>
						<div class="b-post-statistics js-postload-link" name="<?php echo($post['id']); ?>" id="board_<?php echo($post['board_id']); ?>_<?php echo($post['id']); ?>_postload" style="display:none;text-align:center;cursor:hand;cursor:pointer">
							New posts avaliable (<b class="js-postload-num"></b>). Click on this message to load them.
							<span class="b-post-statistics_m-right">
								(<a href="javascript://" class="js-update-post-button" name="<?php echo($post['id']); ?>"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/reload.png" width="16" height="16" alt="" /> quick reply</a>)
							</span>	
						</div>
						<?php endif; ?>
						<div id="placeholder_form_comment_<?php echo($post['board_id']); ?>_<?php echo($post['id']); ?>"></div>
					</div>

					<?php endforeach; ?>
					<?php endif; ?>

						<?php if ($this -> getParameter('total_pages') > 0): ?>

						<div class="b-paginator">
							<ul class="g-clearfix">
								<li class="b-paginator_b-control"><a href="<?php printf($this -> getParameter('link_pages'), 0) ?>">&lArr;</a></li>
								<li class="b-paginator_b-control"><a href="<?php printf($this -> getParameter('link_pages'), max(0, $this -> getParameter('current_page') - 1)); ?>/">&larr;</a></li>
							<?php foreach(range(max(0, $this -> getParameter('current_page') - 8), max($this -> getParameter('current_page') + (16 - $this -> getParameter('current_page')), min($this -> getParameter('current_page') + 8, $this -> getParameter('total_pages')))) as $p): ?>
							<?php if($p <= $this -> getParameter('total_pages')): ?>
								<?php if ($this -> getParameter('current_page') == $p): ?><li><u><?php echo($p); ?></u></li>
								<?php else: ?><li><a href="<?php printf($this -> getParameter('link_pages'), $p); ?>"><?php echo($p); ?></a></li><?php endif; ?>

		                    <?php endif; ?>
							<?php endforeach; ?>
								<li class="b-paginator_b-control"><a href="<?php printf($this -> getParameter('link_pages'), min($this -> getParameter('total_pages'), $this -> getParameter('current_page') + 1)); ?>">&rarr;</a></li>
								<li class="b-paginator_b-control"><a href="<?php printf($this -> getParameter('link_pages'), $this -> getParameter('total_pages')); ?>">&rArr;</a></li>
							</ul>
						</div>
					<?php endif; ?>
				</div>
