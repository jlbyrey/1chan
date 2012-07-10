				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Каналы</a></h2>

                <div id="main">
                	<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/onlineChannel" method="post" class="jsNice">
                		<h3>Каналы:</h3>
						<fieldset>
							<p><label>Имя канала :|: Регулярное выражение :|: Полная ссылка</label>
							<textarea name="channels" rows="5"><?php echo($channels); ?></textarea></p>
						</fieldset>
						<fieldset>
							<input type="submit" value="Обновить список" />
						</fieldset>
					</form>
                </div>
