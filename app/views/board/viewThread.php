                <?php $ENG = ($this -> getParameter('board_id') == 'int'); ?>
				<div class="b-board-header">
					<div class="b-board-header_name">
						<h1><?php echo($this -> getParameter('title')); ?></h1>
						<div class="b-board-header_desc">
							<?php echo($this -> getParameter('description')); ?>

						</div>
					</div>
					<div class="b-board-header_options">
					    <?php if (!$ENG): ?>
						<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($this -> getParameter('board_id')); ?>/">&larr; Вернуться к разделу</a> |
						 <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/help/board/">Правила разделов</a>
					    <?php else: ?>
						<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($this -> getParameter('board_id')); ?>/">&larr; Back to /int/</a>
					    <?php endif; ?>
					</div>
				</div>

			<?php if (empty($post)): ?>
                <?php if (!$ENG): ?>
				<div class="b-static">
					<h1>Пост с таким идентификатором не найден</h1>
					<p>Либо его действительно нет, либо его удалили.</p>
				</div>
			    <?php else: ?>
				<div class="b-static">
					<h1>Post was not found</h1>
					<p>Do not exist or was removed from board.</p>
				</div>
			    <?php endif; ?>
			<?php else: ?>
			<div class="b-board m-thread">
				<div class="b-blog-entry" id="post_<?php echo($post['id']); ?>">
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
							<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/fav/toggle/<?php echo($post['board_id']); ?>/<?php echo($post['id']); ?>/" class="js-favorite-button">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/favorites<?php if(!Board_FavoritesModel::IsFavoritePost($post['board_id'], $post['id'])):?>-false<?php endif; ?>.png" width="16" height="16" alt="" id="post_<?php echo($post['board_id']); ?>_<?php echo($post['id']); ?>_favorite" />
							</a>
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
							<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($post['board_id']); ?>/res/<?php echo($post['id']); ?>/#comment_form" class="js-add-comment-button">
								<?php if (!$ENG): ?>Добавить ответ<?php else: ?>Add a reply<?php endif; ?>
							</a>
						</span>
					</div>
				</div>
				<div class="l-comments-wrap">
					<?php if ($post['count'] > 0): ?>
					<?php foreach($post['posts'] as $comment): ?>

					<div class="b-comment" id="comment_<?php echo($comment['board_id']); ?>_<?php echo($comment['id']); ?>">
						<div class="b-comment_b-info">
							<?php echo(TemplateHelper::date((!$ENG) ? 'd M Y @ H:i' : 'Y-m-d @ H:i', $comment['created_at'])); ?>, <a href="#<?php echo($comment['id']); ?>">№</a><a href="javascript://" class="js-paste-link" name="<?php echo($comment['id']); ?>"><?php echo($comment['id']); ?></a>
							<a href="#" class="js-remove-button g-hidden"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/remove.gif" width="16" height="16" alt="<?php echo($comment['id']); ?>" /></a>
						    
						    <?php if (!$ENG): ?>
							<?php if($comment['author'] && $comment['author'] != 'anonymous'): $author = HomeBoardHelper::getBoard($comment['author']); ?>
							<a href="http://<?php echo($comment['author']); ?>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<?php echo($author[1]); ?>»">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<?php echo($author[0]); ?>" width="16" height="16" alt="" />
							</a>
							<?php endif; ?>
						    <?php else: ?>
							<a href="javascript://" class="b-comment_b-homeboard" title="<?php echo($comment['country']); ?>">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/flags/<?php echo($comment['country']); ?>.png" width="18" height="12" alt="" />
							</a>
							<?php endif; ?>
						</div>
						<div class="b-comment_b-body g-clearfix">
							<div class="wrap">
								<p>
								<?php if($comment['upload']): ?>
								<a class="b-image-link" target="_blank" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($comment['upload']['web_full']); ?>" title="<?php echo($comment['upload']['full_size'][0]);?>x<?php echo($comment['upload']['full_size'][1]);?>, <?php echo($comment['upload']['size']); ?>">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($comment['upload']['web_thumb']); ?>" width="<?php echo($comment['upload']['thumb_size'][0]); ?>" height="<?php echo($comment['upload']['thumb_size'][1]); ?>" alt="" />
								</a>
								<?php endif; ?>
								<?php echo($comment['text']); ?>

								</p>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
					<?php endif; ?>

					<textarea id="template_comment" style="display:none">
						<div class="b-comment m-new" id="comment_<%=board_id%>_<%=id%>">
							<div class="b-comment_b-info">
								<%=created_at%>, <span class="js-comment-id"><a href="#<%=id%>">№</a><a href="javascript://" class="js-paste-link" name="<%=id%>"><%=id%></a></span>
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

					<div id="placeholder_comment"></div>

					<div class="b-post-statistics">
						<?php if (!$ENG): ?>Читают<?php else: ?>Reading<?php endif; ?>: <strong id="post_stats_reading"><?php echo($this -> getParameter('total_read', 1)); ?></strong> | <?php if (!$ENG): ?>Отвечают<?php else: ?>Writing<?php endif; ?>: <strong id="post_stats_writing"><?php echo($this -> getParameter('total_write', 0)); ?></strong>
					</div>


					<div id="board_comment_captcha" style="display:none;font-size:1.2em;text-align:center;" title="<?php if (!$ENG): ?>Обратный тест Тьюринга<?php else: ?>Please enter Captcha code<?php endif; ?>">
						<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/captcha/?key=board_comment&<?php echo session_name()?>=<?php echo session_id()?>&rand=" /><br />
						<input type="text" value="" size="10" />					
					</div>


					<iframe src="about:blank" name="comment_form_iframe" style="position:absolute;left:-9999px;visibility:hidden"></iframe>
					<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($post['board_id']); ?>/createPost/" method="post" id="comment_form" enctype="multipart/form-data">
					<input type="text" name="email" value="" class="g-hidden" />
					<input type="hidden" name="parent_id" value="<?php echo($post['id']); ?>" />
					<input type="hidden" name="homeboard" value="anonymous" />
					<input type="hidden" name="captcha" value="" />
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
								<textarea id="comment_form_text" name="text" rows="5"></textarea>
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
							    <span class="b-comment-form_b-uplink">
								    <a href="#top"><?php if (!$ENG): ?>Вверх<?php else: ?>Top<?php endif; ?> &uarr;</a>
							    </span>
							</div>
						</div>
					</form>
			</div>
		</div>
			<?php endif; ?>
