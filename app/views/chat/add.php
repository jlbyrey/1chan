				<div class="b-chat-panel g-clearfix">
					<ul>
						<li class="b-chat-panel_b-add-room<?php if ($this -> getParameter('section') == 'add'): ?> b-chat-panel_m-active<?php endif; ?>">
							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/add-chat.png" width="16" height="16" alt="" />
							<a href="/chat/add/">Добавить комнату</a>
						</li>
						<li class="b-chat-panel_b-all<?php if ($this -> getParameter('section') == 'all'): ?> b-chat-panel_m-active<?php endif; ?>">
							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/chat-rooms.png" width="16" height="16" alt="" />
							<a href="/chat/">Тематические чаты</a>
						</li>
						<li class="b-chat-panel_b-common<?php if ($this -> getParameter('section') == 'common'): ?> b-chat-panel_m-active<?php endif; ?>">
							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/chat-common.png" width="16" height="16" alt="" />
							<a href="/chat/common/">Общий чат</a>
						</li>
					</ul>
				</div>

				<div class="b-blog-form">
					<h1>Добавить комнату:</h1>
					<div class="b-blog-form_b-error" id="chat_form_error"><?php echo(implode(', ', array_values($form_errors))); ?></div>

					<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/chat/add/" method="post" id="chat_form">
					<input type="text" name="email" value="" class="g-hidden" />

						<div class="b-blog-form_b-form">
							<div class="b-blog-form_b-form_b-field">
								<h2>Заголовок:</h2>
								<p>Размер заголовка не должен превышать 25 символов (сейчас введено — <span id="chat_form_title_length">0 символов</span>).</p>
								<input type="text" name="title" value="<?php echo @htmlspecialchars($blog_form['title']); ?>"<?php if(array_key_exists('title', $form_errors)):?> class="g-input-error"<?php endif; ?> />
							</div>
							<div class="b-blog-form_b-form_b-field">
								<h2>Пароль управления:</h2>
								<p>Этот пароль используется для выполнения команд модерации в создаваемой комнате.</p>
								<input type="text" name="controlword" value="<?php echo @htmlspecialchars($blog_form['controlword']); ?>"<?php if(array_key_exists('controlword', $form_errors)):?> class="g-input-error"<?php endif; ?> />
							</div>
							<div class="b-blog-form_b-form_b-field">
								<h2>Краткое описание:</h2>
								<p>
									Текст краткого описания не должен превышать 75 символов. Описание отображается
									в списке тематических чатов и при входе пользователя в соответствующую комнату.
								</p>
								<textarea name="description" rows="2"<?php if(array_key_exists('description', $form_errors)):?> class="g-input-error"<?php endif; ?>><?php echo @htmlspecialchars($blog_form['description']); ?></textarea>
							</div>
							<div class="b-blog-form_b-form_b-field">
								<h2>Пароль на вход:</h2>
								<p>
									Если это поле заполнено, то для входа в комнату пользователю будет необходимо
									ввести этот пароль. Оставьте поле пустым, чтобы все пользователи могли зайти
									в создаваемую комнату. Обратите внимание, после создания комнаты пароль на вход <em>сменить нельзя</em>. 
								</p>
								<input type="text" name="password" value="<?php echo @htmlspecialchars($blog_form['password']); ?>"<?php if(array_key_exists('password', $form_errors)):?> class="g-input-error"<?php endif; ?> />
							</div>
							<div class="b-blog-form_b-form_b-field">
								<h2>Настройка видимости:</h2>
								<label><input type="checkbox" name="public"<?php if(empty($blog_form) || isset($blog_form['public'])): ?> checked="checked"<?php endif; ?> /> Комната доступна публично.</label>
								<p>
									Настройка видимости влияет на отображение чата в общем списке тематических комнат.
									Если пункт не отмечен, комната будет доступна только по прямой ссылке, но
									может быть добавлена пользователем в избранное.
								</p>
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

								<p>
									Будте добры, соблюдайте <a href="/help/rules/#chat">правила раздела</a>.
								</p>
							</div>
						</div>

						<div class="b-blog-form_b-actions">
							<input type="submit" value="Создать комнату" />
						</div>
					</form>
				</div>
