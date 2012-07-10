			<?php include(dirname(__FILE__) .'/chunks/blog_control.php'); ?>

				<div class="b-blog-form">
					<h1>Добавить запись:</h1>
				<?php if(ControlModel::isPostPremoderation()): ?>
					<div class="b-blog-form_b-info">Премодерация: ваше сообщение будет опубликовано после проверки модератором.</div>
				<?php endif; ?>

					<div class="b-blog-form_b-error" id="blog_form_error"><?php echo(implode(', ', array_values($form_errors))); ?></div>

					<div id="placeholder_preview"></div>
					<textarea id="template_preview" style="display:none">
						<div class="b-blog-entry">
							<div class="b-blog-entry_b-header">
								<img src="<%=icon%>" width="16" height="16" alt="" />
							<% if (category) { %>
								<a href="#" class="b-blog-entry_b-header_m-category"><%=category%></a>
								&rarr;
							<% } %>
								<a href="#"><%=title%></a>
							</div>
							<div class="b-blog-entry_b-body g-clearfix"><%=text%></div>
						</div>
					</textarea>

					<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/add/" method="post" id="blog_form">
					<input type="text" name="email" value="" class="g-hidden" />
					<input type="hidden" name="homeboard" value="anonymous" />

						<div class="b-blog-form_b-form">
							<div class="b-blog-form_b-form_b-field" id="blog_form_category">
								<h2>Категория:</h2>
								<p>Для отправки поста в категорию, необходимо выбрать её из выпадающего списка,<br />или ввести приватный ключ категории.</p>
								<input type="text" name="category" value="<?php echo @$blog_form['category']; ?>"<?php if(array_key_exists('category', $form_errors)):?> class="g-input-error"<?php endif; ?> />
							    <div><small>Если ты собрался запостить <strong>хуйню</strong>, то оставь это поле пустым.</small></div>
                            </div>
							<div class="b-blog-form_b-form_b-field">
								<h2>Заголовок:</h2>
								<p>Размер заголовка не должен превышать 70 символов (сейчас введено — <span id="blog_form_title_length">0 символов</span>).</p>
								<input type="text" name="title" value="<?php echo @htmlspecialchars($blog_form['title']); ?>"<?php if(array_key_exists('title', $form_errors)):?> class="g-input-error"<?php endif; ?> />
							</div>
							<div class="b-blog-form_b-form_b-field">
								<h2>Ссылка:</h2>
								<p>Ссылка на внешний материал. Оставьте поле пустым, если материал самодостаточен.</p>
								<input type="text" name="link" value="<?php echo @htmlspecialchars($blog_form['link']); ?>"<?php if(array_key_exists('link', $form_errors)):?> class="g-input-error"<?php endif; ?> />
							</div>
							<div class="b-blog-form_b-form_b-field">
								<h2>Содержание:</h2>
								<p>Можно использовать <a href="/help/markup/" target="_blank">язык разметки</a>.</p>
								<p>
									Если текст записи большой, он должен быть разделен на две части: вводную и подробную.
									Размер вводной части не должен превышать 1024 символа.
								</p>
								<textarea name="text" rows="6"<?php if(array_key_exists('text', $form_errors)):?> class="g-input-error"<?php endif; ?>><?php echo @htmlspecialchars($blog_form['text']); ?></textarea>
								<br />
								<textarea name="text_full" rows="9"<?php if(array_key_exists('text_full', $form_errors)):?> class="g-input-error"<?php endif; ?>><?php echo @htmlspecialchars($blog_form['text_full']); ?></textarea>
							<?php if(ControlModel::isPostCaptcha()): ?>
							</div>
							<div class="b-blog-form_b-form_b-field">
								<h2>Обратный тест тьюринга:</h2>
								<input type="hidden" name="captcha_key" value="<?php echo($this -> getParameter('captcha_key')); ?>" />
								<p>
									Введите символы, изображенные на картинке.
								</p>
								<p>
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/captcha/?key=<?php echo($this -> getParameter('captcha_key')); ?>&<?php echo session_name()?>=<?php echo session_id()?>">
									<input type="text" name="captcha" value=""<?php if(array_key_exists('captcha', $form_errors)):?> class="g-input-error"<?php endif; ?> />
								</p>
							<?php endif; ?>

								<p>
									Будьте добры, соблюдайте <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/help/news/" target="_blank">простые правила</a> при написании сообщений.
								</p>
							</div>
							<div class="b-blog-form_b-form_b-field g-hidden" id="blog_form_moderation">
								<h2>Модерирование:</h2>
								<p>После публикации выполнить следующие действия:</p>
								<label><input type="checkbox" name="rated" /> Одобрить</label>
								<label><input type="checkbox" name="pinned" /> Закрепить</label>
								<label><input type="checkbox" name="notrateable" /> Запретить оценивать</label>
								<label><input type="checkbox" name="closed" /> Запретить комментировать</label>
							</div>
							<div class="b-blog-form_b-form_b-field">
								<h2>Принадлежность:</h2>
								<p>
									Вы, как автор, можете указать <em>свою принадлежность к аудитории определенной имиджборды</em>.
									Иконка выбранной имиджборды будет отображаться рядом с вашим постом.
								</p>
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
							</div>
						</div>
				<?php if(ControlModel::isPostHandApproving()): ?>
						<div class="b-blog-form_b-info">Премодерация: ваше сообщение может быть одобрено только модератором.</div>
				<?php endif;?>

						<div class="b-blog-form_b-actions">
							<input type="submit" value="Отправить" />
							<input type="button" value="Предпросмотр" disabled="disabled" id="blog_form_preview_button" />
						</div>

						<div class="b-blog-form_b-form"  id="blog_form_last_posts" style="display:none">
							<div class="b-blog-form_b-form_b-field">
								<h2>Новые сообщения:</h2>
								<p>С момента начала написания нового поста, были добавлены нижеперечисленные записи. Пожалуйста, избегайте повторяющихся тем сообщений.</p>
								<div id="placeholder_last_posts"></div>
								<textarea id="template_last_posts" style="display:none">
							        <div class="b-live-entry">
								        <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<%=id%>/" class="b-live-entry_b-description"><%=title%></a> <% if (category) { %> &larr; <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/cat/<%=category%>/" class="b-live-entry_b-board"><%=category_title%></a><% } %>
							        </div>
						        </textarea>
							</div>
						</div>
					</form>
				</div>
