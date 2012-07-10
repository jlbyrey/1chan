				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Добавить комнату чата</a></h2>

                <div id="main">
						<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/chatAdd" method="post" class="jNice">
							<h3>Основные поля:</h3>
							<fieldset>
								<p><label>Заголовок:</label><input name="title" type="text" class="text-long" /></p>
								<p><label>Пароль управления:</label><input name="controlword" type="text" class="text-long" value="" /></p>
								<p><label>Пароль на вход:</label><input name="password" type="text" class="text-long" value="" /></p>
								<p><label>0писание:</label><textarea name="description" rows="5"></textarea></p>
							</fieldset>

							<h3>Дополнительные поля:</h3>
							<fieldset>
								<p><label><input type="checkbox" name="public" checked="checked" /> Публична</label></p>
							</fieldset>
							
							<fieldset>
								<input type="submit" value="Добавить комнату" />
							</fieldset>
						</form>
                </div>
