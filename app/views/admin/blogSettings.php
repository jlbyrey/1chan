				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Настройки</a></h2>

                <div id="main">
                	<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/blogSettings" method="post" class="jsNice">
                		<h3>Настройки:</h3>
						<fieldset>
								<p><label>Глобальное сообщение:</label><input name="global_message" type="text" class="text-long" value="<?php echo($settings['global_message']); ?>" /></p>
								<p><label>Интервал создания тредов (по-умолчанию):</label><input name="post_interval" type="text" class="text-small" value="<?php echo($settings['post_interval']); ?>" /></p>
								<p><label>Интервал создания комментариев (по-умолчанию):</label><input name="post_comment_interval" type="text" class="text-small" value="<?php echo($settings['post_comment_interval']); ?>" /></p>
								<p><label>Интервал ссылок в ротаторе (по-умолчанию):</label><input name="live_interval" type="text" class="text-small" value="<?php echo($settings['live_interval']); ?>" /></p>
								<p><label>Длина капчи (по-умолчанию):</label><input name="captcha_length" type="text" class="text-small" value="<?php echo($settings['captcha_length']); ?>" /></p>
								<p><label>Голоса для одобрения:</label><input name="rated_count" type="text" class="text-small" value="<?php echo($settings['rated_count']); ?>" /></p>
						</fieldset>
						<h3>Глобальные настройки:</h3>
						<fieldset>
							<p><label><input type="checkbox" name="premoderation" <?php if(@$settings['premoderation']): ?> checked="checked"<?php endif;?> /> Глобальная премодерация</label></p>
							<p><label><input type="checkbox" name="handapprove" <?php if(@$settings['handapprove']): ?> checked="checked"<?php endif;?> /> Ручное одобрение постов</label></p>
							<p><label><input type="checkbox" name="post_captcha" <?php if(@$settings['post_captcha']): ?> checked="checked"<?php endif;?> /> Глобальная капча на посты</label></p>
							<p><label><input type="checkbox" name="post_comment_captcha" <?php if(@$settings['post_comment_captcha']): ?> checked="checked"<?php endif;?> /> Глобальная капча на комментарии</label></p>
							<p><label><input type="checkbox" name="post_rate_captcha" <?php if(@$settings['post_rate_captcha']): ?> checked="checked"<?php endif;?> /> Глобальная капча на оценку поста</label></p>
							<p><label><input type="checkbox" name="live_captcha" <?php if(@$settings['live_captcha']): ?> checked="checked"<?php endif;?> /> Глобальная капча на добавление ссылки в ротатор</label></p>
							<p><label><input type="checkbox" name="wordfilter_block" <?php if(@$settings['wordfilter_block']): ?> checked="checked"<?php endif;?> /> Запрет постинга со словами из вордфильтра</label></p>
						</fieldset>
						<fieldset>
							<input type="submit" value="Обновить настройки" />
						</fieldset>
					</form>
                </div>
