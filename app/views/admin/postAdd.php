				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Добавить пост</a></h2>

                <div id="main">
						<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postAdd" method="post" class="jNice">
							<h3>Основные поля:</h3>
							<fieldset>
								<p><label>Категория:</label><select name="category">
									<option value="0"></option>
								<?php foreach(Blog_BlogCategoryModel::GetCategories() as $cat): ?>
									<option value="<?php echo($cat['id']); ?>"><?php echo($cat['title']); ?></option>
								<?php endforeach; ?>
								</select></p>
								<p><label>Заголовок:</label><input name="title" type="text" class="text-long" /></p>
								<p><label>Ссылка:</label><input name="link" type="text" class="text-long" value="http://" /></p>
								<p><label>Вводный текст:</label><textarea name="text" rows="5"></textarea></p>
								<p><label>Общий текст:</label><textarea name="text_full" rows="5"></textarea></p>
							</fieldset>

							<h3>Дополнительные поля:</h3>
							<fieldset>
								<p><label><input type="checkbox" name="hidden" /> Скрыта</label></p>
								<p><label><input type="checkbox" name="pinned" /> Прикреплена</label></p>
								<p><label><input type="checkbox" name="rated" /> Одобрена</label></p>
								<p><label><input type="checkbox" name="closed" /> Закрыта</label></p>
								<p><label><input type="checkbox" name="rateable" checked="checked" /> Оцениваема</label></p>
								<p><label><input type="checkbox" name="bumpable" checked="checked" /> Поднимаемая</label></p>
								<p><label>Специальный комментарий:</label><input name="special_comment" type="text" class="text-long" /></p>
							</fieldset>
							<fieldset>
								<input type="submit" value="Добавить запись" />
							</fieldset>
						</form>
                </div>