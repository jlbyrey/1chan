				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Добавить категорию</a></h2>

                <div id="main">
						<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postCategoryAdd" method="post" class="jNice">
							<h3>Основные поля:</h3>
							<fieldset>
								<p><label>Заголовок:</label><input name="title" type="text" class="text-long" /></p>
								<p><label>Ссылка:</label><input name="name" type="text" class="text-long" value="" /></p>
								<p><label>Описание:</label><textarea name="description" rows="5"></textarea></p>
								<p><label>Код:</label><input name="code" type="text" class="text-long" /></p>
								<p><label>Публичная:</label><input name="public" type="checkbox" /></p>
							</fieldset>
							<fieldset>
								<input type="submit" value="Добавить категорию" />
							</fieldset>
						</form>
                </div>