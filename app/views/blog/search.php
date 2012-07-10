			<?php include(dirname(__FILE__) .'/chunks/blog_control.php'); ?>

				<div class="b-blog-form">
					<div class="b-blog-form_b-error" id="blog_form_error"><?php echo(implode(', ', array_values($form_errors))); ?></div>

					<div class="b-blog-form_b-form">
					<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/search/" method="post">
						<div class="b-blog-form_b-form_b-field">
							<h2>Поиск постов:</h2>
							<p>
								<input type="text" name="query" value="<?php echo @htmlspecialchars($blog_form['query']); ?>"<?php if(array_key_exists('query', $form_errors)):?> class="g-input-error"<?php endif; ?> />
							</p>
							<p>
								Из:
								<select name="category" <?php if(array_key_exists('category', $form_errors)):?> class="g-input-error"<?php endif; ?> />
										<option value="0">всех категорий</option>
									<?php foreach(Blog_BlogCategoryModel::GetCategories() as $category): ?>
										<option value="<?php echo($category['id']); ?>"<?php if(@$blog_form['category'] == $category['id']): ?> selected="selected"<?php endif; ?>>«<?php echo($category['title']); ?>»</option>
									<?php endforeach; ?>
								</select>
								&nbsp;
								Сортировать по:
								<select name="sort">
									<option value="desc"<?php if(@$blog_form['sort'] == 'desc'): ?> selected="selected"<?php endif; ?>>убыванию</option>
									<option value="asc"<?php if(@$blog_form['sort'] == 'asc'): ?> selected="selected"<?php endif; ?>>возрастанию</option>
								</select>
								<select name="order" <?php if(array_key_exists('order', $form_errors)):?> class="g-input-error"<?php endif; ?>>
									<option value="relevance"<?php if(@$blog_form['order'] == 'relevance'): ?> selected="selected"<?php endif; ?>>релевантности</option>
									<option value="created_at"<?php if(@$blog_form['order'] == 'created_at'): ?> selected="selected"<?php endif; ?>>даты создания</option>
									<option value="updated_at"<?php if(@$blog_form['order'] == 'updated_at'): ?> selected="selected"<?php endif; ?>>даты обновления</option>
									<option value="rate"<?php if(@$blog_form['order'] == 'rate'): ?> selected="selected"<?php endif; ?>>рейтинга</option>
									<option value="comments"<?php if(@$blog_form['order'] == 'comments'): ?> selected="selected"<?php endif; ?>>кол-ва ответов</option>
								</select>
							</p>
						</div>
					</div>
					<div class="b-blog-form_b-actions">
						<input type="submit" value="Искать" />
						<input type="reset" value="Сбросить" />
					</div>
					</form>
				</div>

			<?php if(!empty($posts)): ?>
				<div class="b-search-statistics">
					<span>Найдено постов: <b><?php echo($this -> getParameter('total_found')); ?></b></span>
					<span class="b-search-statistics_m-right">Отображено: <b><?php echo($this -> getParameter('total')); ?></b></span>
				</div>

				<?php foreach($posts as $post): ?>
				<div class="b-blog-entry" id="post_<?php echo($post['id']); ?>">
					<div class="b-blog-entry_b-header">
						<img src="<?php echo($post['link'] ? TemplateHelper::getIcon($post['link']) : 'http://'. TemplateHelper::getSiteUrl() .'/ico/favicons/1chan.ru.gif'); ?>" width="16" height="16" alt="" />
					<?php if($post['category']): ?>
						<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/cat/<?php echo(TemplateHelper::BlogCategory($post['category'], 'name')); ?>/" class="b-blog-entry_b-header_m-category">
							<?php echo(TemplateHelper::BlogCategory($post['category'], 'title')); ?></a>
						&rarr;
					<?php endif; ?>

						<a href="<?php echo($post['link'] ? $post['link'] : 'http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $post['id'] .'/'); ?>">
							<?php echo($post['title']); ?>

						</a>

					</div>
					<div class="b-blog-entry_b-body g-clearfix">
							<?php echo($post['text']); ?>
						    <?php echo($post['text_full']); ?>

					</div>
					<div class="b-blog-entry_b-info" id="post_<?php echo($post['id']); ?>_info">
						<span class="b-blog-entry_b-info_b-control">
							<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/b/fav/toggle/<?php echo($post['id']); ?>/" class="js-favorite-button">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/favorites<?php if(!Blog_BlogPostsModel::IsFavoritePost($post['id'])):?>-false<?php endif; ?>.png" width="16" height="16" alt="" id="post_<?php echo($post['id']); ?>_favorite" />
							</a>
							<a href="#" class="js-moderator-button g-hidden">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/settings.png" width="16" height="16" alt="<?php echo($post['id']); ?>" />
							</a>
							<span>|</span>
						<?php if($post['rateable']): ?>
						<?php if($post['rate'] >= 0): ?>

							<strong class="g-green js-rate"><?php echo($post['rate']); ?></strong>
						<?php else: ?>

							<strong class="g-red js-rate"><?php echo($post['rate']); ?></strong>
						<?php endif; ?>

							<span>|</span>
						<?php endif; ?>

							<span><?php echo(TemplateHelper::date('d M Y @ H:i', $post['created_at'])); ?></span>
							<span>|</span>
							<span>
								<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo($post['id']); ?>/" class="g-disabled">
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
							<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo($post['id']); ?>/">
								<?php if ($post['closed']): ?>Обсуждение закрыто<?php else: ?>Обсуждение: <span class="js-comments<?php TemplateHelper::isPostUpdated($post) and print(' g-bold'); ?>"><?php echo($post['comments']); ?></span><?php endif; ?>
							</a>
						</span>
					</div>
				</div>
				<?php endforeach; ?>
			<?php endif; ?>
