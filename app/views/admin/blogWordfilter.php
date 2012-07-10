				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Вордфильтр</a></h2>

                <div id="main">
                	<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/blogWordfilter" method="post" class="jsNice">
                		<h3>Запрещенные слова:</h3>
						<fieldset>
							<p><label>Регулярные выражения:</label><textarea name="wordfilter" rows="5"><?php echo($wordfilter); ?></textarea></p>
						</fieldset>
						<h3>Запрещенные ссылки:</h3>
						<fieldset>
							<p><label>Регулярные выражения:</label><textarea name="linkfilter" rows="5"><?php echo($linkfilter); ?></textarea></p>
						</fieldset>
						<h3>Спам-лист (борды):</h3>
						<fieldset>
							<p><label>Регулярные выражения:</label><textarea name="spamfilter" rows="5"><?php echo($spamfilter); ?></textarea></p>
						</fieldset>
						<fieldset>
							<input type="submit" value="Обновить список" />
						</fieldset>
					</form>
                </div>
