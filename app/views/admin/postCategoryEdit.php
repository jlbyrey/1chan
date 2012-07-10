				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Редактировать категорию</a></h2>

                <div id="main">
						<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postCategoryEdit" method="post" class="jNice">
						<input type="hidden" name="id" value="<?php echo($cat['id']) ?>" />
							<h3>Основные поля:</h3>
							<fieldset>
								<p><label>Заголовок:</label><input name="title" type="text" value="<?php echo($cat['title']); ?>" class="text-long" /></p>
								<p><label>Ссылка:</label><input name="name" type="text" value="<?php echo($cat['name']); ?>" class="text-long" value="" /></p>
								<p><label>Описание:</label><textarea name="description" rows="5"><?php echo($cat['description']); ?></textarea></p>
								<p><label>Код:</label><input name="code" type="text" value="<?php echo($cat['code']); ?>" class="text-long" /></p>
								<p><label>Публичная:</label><input name="public" type="checkbox" <?php if($cat['public']): ?>checked="checked" <?php endif; ?> /></p>
							</fieldset>
							<fieldset>
								<input type="submit" value="Добавить категорию" />
							</fieldset>
						</form>
                </div>