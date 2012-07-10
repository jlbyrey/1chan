				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Редактировать комментарий</a></h2>

                <div id="main">
						<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postCommentEdit" method="post" class="jNice">
						<input type="hidden" name="id" value="<?php echo $post['id']; ?>" />
						<input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>" />
							<h3>Основные поля:</h3>
							<fieldset>
								<p><label>Текст:</label><textarea name="text" rows="5"><?php echo $post['text']; ?></textarea></p>
							</fieldset>

							<h3>Дополнительные поля:</h3>
							<fieldset>
							    <p><label>IP:</label><input name="ip" type="text" class="text-long" value="<?php echo $post['ip']; ?>" /></p>
								<p><label><input type="checkbox" name="hidden"<?php if($post['hidden']): ?> checked="checked"<?php endif; ?>/> Скрыта</label></p>
								<p><label>Специальный комментарий:</label><input name="special_comment" type="text" class="text-long" value="<?php echo $post['special_comment']; ?>" /></p>
							</fieldset>
							<fieldset>
								<input type="submit" value="Отредактировать" />
								<input type="button" onclick="location.href='http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postCommentDelete?id=<?php echo $post['id']; ?>'" value="Удалить" />
							</fieldset>
						</form>
                </div>
