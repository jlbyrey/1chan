				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Модераторы</a></h2>

                <div id="main">
                	<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/blogModerators" method="post" class="jsNice">
                		<h3>Список модераторов:</h3>
						<fieldset>
							<p><label>Имя | Ключ | Класс | Категории:</label>
							<textarea name="moderators" rows="5"><?php echo($moderators); ?></textarea></p>
						</fieldset>
						<fieldset>
							<input type="submit" value="Обновить список" />
						</fieldset>
					</form>
                </div>