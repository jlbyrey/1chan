				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Ссылки</a></h2>

                <div id="main">
                	<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/blogLinks" method="post" class="jsNice">
                		<h3>Редактирование ссылок:</h3>
						<fieldset>
							<p><label>Имиджборды:</label><textarea name="imgboards" rows="5"><?php echo($imgboards); ?></textarea></p>
							<p><label>Другие ссылки:</label><textarea name="services" rows="5"><?php echo($services); ?></textarea></p>
						</fieldset>
						<fieldset>
							<input type="submit" value="Отредактировать" />
						</fieldset>
					</form>
                </div>