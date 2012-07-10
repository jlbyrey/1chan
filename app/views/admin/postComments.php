				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Комментарии</a></h2>

                <div id="main">
                	<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postCommentEdit" method="get">
                		<h3>Редактирование комментария:</h3>
						<fieldset>
							<p><label>ID:</label><input name="id" type="text" class="text-medium" value="" /> <input type="submit" value="Открыть" /></p>
						</fieldset>
					</form>
                </div>