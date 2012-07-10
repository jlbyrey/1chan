				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Редактировать пост</a></h2>

                <div id="main">
						<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postEdit" method="post" class="jNice">
						<input type="hidden" name="id" value="<?php echo $post['id']; ?>" />
							<h3>Основные поля:</h3>
							<fieldset>
								<p><label>Категория:</label><select name="category">
									<option value="0"></option>
								<?php foreach(Blog_BlogCategoryModel::GetCategories() as $cat): ?>
									<option value="<?php echo($cat['id']); ?>"<?php if($cat['id'] == $post['category']): ?> selected="selected"<?php endif; ?>><?php echo($cat['title']); ?></option>
								<?php endforeach; ?>
								</select></p>
								<p><label>Заголовок:</label><input name="title" type="text" class="text-long" value="<?php echo $post['title']; ?>" /></p>
								<p><label>Ссылка:</label><input name="link" type="text" class="text-long" value="<?php echo $post['link']; ?>" /></p>
								<p><label>Вводный текст:</label><textarea name="text" rows="5"><?php echo $post['text']; ?></textarea></p>
								<p><label>Общий текст:</label><textarea name="text_full" rows="5"><?php echo $post['text_full']; ?></textarea></p>
							</fieldset>

							<h3>Дополнительные поля:</h3>
							<fieldset>
							    <p><label>IP:</label><input name="ip" type="text" class="text-long" value="<?php echo $post['ip']; ?>" /></p>
								<p><label><input type="checkbox" name="hidden"<?php if($post['hidden']): ?> checked="checked"<?php endif; ?>/> Скрыта</label></p>
								<p><label><input type="checkbox" name="pinned"<?php if($post['pinned']): ?> checked="checked"<?php endif; ?>/> Прикреплена</label></p>
								<p><label><input type="checkbox" name="rated"<?php if($post['rated']): ?> checked="checked"<?php endif; ?>/> Одобрена</label></p>
								<p><label><input type="checkbox" name="closed"<?php if($post['closed']): ?> checked="checked"<?php endif; ?>/> Закрыта</label></p>
								<p><label><input type="checkbox" name="rateable"<?php if($post['rateable']): ?> checked="checked"<?php endif; ?> /> Оцениваема</label></p>
								<p><label><input type="checkbox" name="bumpable"<?php if($post['bumpable']): ?> checked="checked"<?php endif; ?> /> Поднимаемая</label></p>
								<p><label>Специальный комментарий:</label><input name="special_comment" type="text" class="text-long" value="<?php echo $post['special_comment']; ?>" /></p>
							</fieldset>
							<fieldset>
								<input type="submit" value="Отредактировать запись" />
							</fieldset>
						</form>
                </div>
