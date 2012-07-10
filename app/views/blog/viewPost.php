			<?php include(dirname(__FILE__) .'/chunks/blog_control.php'); ?>

			<?php if (empty($post)): ?>

				<div class="b-static">
					<h1>Данное сообщение не найдено</h1>
					<p>Либо его действительно нет, либо его удалили.</p>
				</div>
			<?php else: ?>
				<div class="b-blog-entry<?php if ($post['hidden']): ?> m-hidden<?php endif; ?>" id="post_<?php echo($post['id']); ?>">
					<div class="b-blog-entry_b-header">
						<img src="<?php echo($post['link'] ? TemplateHelper::getIcon($post['link']) : 'http://'. TemplateHelper::getSiteUrl() .'/ico/favicons/1chan.ru.gif'); ?>" width="16" height="16" alt="" />
					<?php if($post['category']): ?>
						<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/cat/<?php echo(TemplateHelper::BlogCategory($post['category'], 'name')); ?>/" class="b-blog-entry_b-header_m-category">
							<?php echo(TemplateHelper::BlogCategory($post['category'], 'title')); ?></a>
						&rarr;
					<?php endif; ?>

						<a href="<?php echo($post['link'] ? $post['link'] : 'http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $post['id'] .'/'); ?>" <?php if(!empty($post['link'])): ?>class="m-external"<?php endif;?>>
							<?php echo($post['title']); ?>

						</a>

					</div>
					<div class="b-blog-entry_b-body g-clearfix">
							<?php echo($post['text']); ?>

							<?php echo($post['text_full']); ?>

							<div class="js-special-comment"><?php echo($post['special_comment']); ?></div>

					</div>
					<div class="b-blog-entry_b-info" id="post_<?php echo($post['id']); ?>_info">
						<span class="b-blog-entry_b-info_b-control">
							<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/fav/toggle/<?php echo($post['id']); ?>/" class="js-favorite-button">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/favorites<?php if(!Blog_BlogPostsModel::IsFavoritePost($post['id'])):?>-false<?php endif; ?>.png" width="16" height="16" alt="" id="post_<?php echo($post['id']); ?>_favorite" />
							</a>
							<a href="#" class="js-moderator-button g-hidden">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/settings.png" width="16" height="16" alt="<?php echo($post['id']); ?>" />
							</a>
							<span>|</span>

						<?php if($post['rateable']): ?>
							<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo($post['id']); ?>/rate_post/up/" class="js-rate-up-button">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/plus_grey.gif" width="9" height="11" alt="" />
							</a>
						<?php if($post['rate'] >= 0): ?>

							<strong class="g-green js-rate"><?php echo($post['rate']); ?></strong>
						<?php else: ?>

							<strong class="g-red js-rate"><?php echo($post['rate']); ?></strong>
						<?php endif; ?>

							<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo($post['id']); ?>/rate_post/down/" class="js-rate-down-button">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/minus_grey.gif" width="9" height="11" alt="" />
							</a>
							<span>|</span>
						<?php endif; ?>

							<span><?php echo(TemplateHelper::date('d M Y @ H:i', $post['created_at'])); ?></span>
							<span>|</span>
							<span>
								<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo($post['id']); ?>/"  class="g-disabled js-post-id-link">
									№<?php echo($post['id']); ?></a>
							</span>
							<?php if($post['author'] != 'anonymous'): $author = HomeBoardHelper::getBoard($post['author']); ?>
							<span>|</span>
							<a href="http://<?php echo($post['author']); ?>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<?php echo($author[1]); ?>»">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<?php echo($author[0]); ?>" width="16" height="16" alt="" />
							</a>
							<?php endif; ?>
						</span>
						<span class="b-blog-entry_b-info_b-link">
						<?php if($post['closed']): ?>
							Обсуждение закрыто
						<?php else: ?>
							<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo($post['id']); ?>/#comment_form" class="js-add-comment-button">
								Добавить комментарий
							</a>
						<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="l-comments-wrap">
					<?php if ($post['comments'] > 0): ?>
					<?php foreach($comments as $comment): ?>

					<div class="b-comment<?php TemplateHelper::isNewComment($comment) and print(" m-new"); ?>" id="comment_<?php echo($comment['id']); ?>">
						<div class="b-comment_b-info">
							<?php echo(TemplateHelper::date('d M Y @ H:i', $comment['created_at'])); ?>, <a href="#<?php echo($comment['id']); ?>">№</a><a href="javascript://" class="js-paste-link" name="<?php echo($comment['id']); ?>"><?php echo($comment['id']); ?></a>
							<a href="#" class="js-remove-button g-hidden">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/remove.gif" width="16" height="16" alt="<?php echo($comment['id']); ?>" />
							</a>
							<?php if($comment['author'] != 'anonymous'): $author = HomeBoardHelper::getBoard($comment['author']); ?>
							<a href="http://<?php echo($comment['author']); ?>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<?php echo($author[1]); ?>»">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<?php echo($author[0]); ?>" width="16" height="16" alt="" />
							</a>
							<?php endif; ?>
						</div>
						<div class="b-comment_b-body g-clearfix">
							<?php echo($comment['text']); ?>

						</div>
					</div>
					<?php endforeach; ?>
					<?php endif; ?>

					<textarea id="template_comment" style="display:none">
						<div class="b-comment m-new" id="comment_<%=id%>">
							<div class="b-comment_b-info">
								<%=created_at%>, <span class="js-comment-id"><a href="#<%=id%>">№</a><a href="javascript://" class="js-paste-link" name="<%=id%>"><%=id%></a></span>
								<a href="#" class="js-remove-button g-hidden">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/remove.gif" width="16" height="16" alt="<%=id%>" />
								</a>
								<% if (author[0] != "anonymous") { %>
								<a href="http://<%=author[0]%>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<%=author[1][1]%>»">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<%=author[1][0]%>" width="16" height="16" alt="" />
								</a>
								<% } %>
							</div>
							<div class="b-comment_b-body g-clearfix">
								<%=text%>
							</div>
						</div>
					</textarea>

					<div id="placeholder_comment"></div>

					<div class="b-post-statistics">
						Читают: <strong id="post_stats_reading"><?php echo($this -> getParameter('total_read', 1)); ?></strong><?php if(!$post['closed']):?> | Отвечают: <strong id="post_stats_writing"><?php echo($this -> getParameter('total_write', 0)); ?></strong><?php endif; ?>
						<span class="b-post-statistics_m-right">
							Всего: <span id="post_stats_total"><?php echo TemplateHelper::ending($this -> getParameter('total_unique', 1), 'просмотр', 'просмотра', 'просмотров'); ?></span>
						</span>
					</div>

					<?php if(!$post['closed']): ?>
					<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo($post['id']); ?>/add_comment/" method="post" id="comment_form">
					<input type="text" name="email" value="" class="g-hidden" />
					<input type="hidden" name="post_id" value="<?php echo($post['id']); ?>" />
					<input type="hidden" name="homeboard" value="anonymous" />
					<div class="b-comment-form">
						<strong>Комментировать:</strong>
						<span class="b-comment-form_b-helplink">
							<a href="/help/markup/" target="_blank">Правила разметки</a>
						</span>

						<div>
						<?php if(ControlModel::isCommentCaptcha()): ?>
							<div class="b-comment-form_b-captcha">
								<input type="hidden" name="captcha_key" value="<?php echo($this -> getParameter('captcha_key')); ?>" />
								<input type="text" name="captcha" value=""<?php if(array_key_exists('captcha', $form_errors)):?> class="g-input-error"<?php endif; ?> />
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/captcha/?key=<?php echo($this -> getParameter('captcha_key')); ?>&<?php echo session_name()?>=<?php echo session_id()?>">
							</div>
						<?php endif; ?>

							<textarea id="comment_form_text" name="text" rows="5"><?php echo @htmlspecialchars($blog_form['text']); ?></textarea>
							<input type="submit" value="Отправить" />

							<span class="b-homeboard-form">
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

							<em id="comment_form_error"><?php echo(implode(', ', array_values($form_errors))); ?></em>
							<span class="b-comment-form_b-uplink">
								<a href="#top">Вверх &uarr;</a>
							</span>
						</div>
					</div>
					</form>
					<?php endif; ?>
                <p style="font-size:0.6em;"><a style="float:left" href="#" class="js-back-link">(&larr; + Сtrl)  вернуться назад</a><a style="float:right" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/all/new/" class="js-next-link">к новым сообщениям (Сtrl + &rarr;)</a></p>
				</div>
			<?php endif; ?>
