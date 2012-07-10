				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Добавить комнату чата</a></h2>

                <div id="main">
						<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/chatEdit" method="post" class="jNice">
						<input type="hidden" name="id" value="<?php echo($room['room_id']); ?>" />
							<h3>Основные поля:</h3>
							<fieldset>
								<p><label>Заголовок:</label><input name="title" type="text" class="text-long" value="<?php echo($room['title']); ?>" /></p>
								<p><label>Пароль управления:</label><input name="controlword" type="text" class="text-long" value="<?php echo($room['controlword']); ?>" /></p>
								<p><label>Пароль на вход:</label><input name="password" type="text" class="text-long" value="<?php echo($room['password']); ?>" /></p>
								<p><label>0писание:</label><textarea name="description" rows="5"><?php echo($room['description']); ?></textarea></p>
							</fieldset>

							<h3>Дополнительные поля:</h3>
							<fieldset>
								<p><label><input type="checkbox" name="public" <?php if($room['public']):?>checked="checked"<?php endif; ?> /> Публична</label></p>
							</fieldset>
							
							<fieldset>
								<input type="submit" value="Сохранить комнату" />
							</fieldset>
						</form>
                </div>
