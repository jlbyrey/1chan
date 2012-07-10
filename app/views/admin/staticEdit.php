				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Статическая страница</a></h2>
		<script type="text/javascript">
			function preview() {
				var form = document.getElementById("static_form"),
				    old_action = form.action;
				form.action = "http://<?php echo TemplateHelper::getSiteUrl(); ?>/service/preview/";
				form.target = "_blank";
				form.submit();
				form.action = old_action;
				form.target = "_self";
			}
		</script>
                <div id="main">
                	<form id="static_form" action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/staticEdit" method="post" class="jsNice">
			<input type="hidden" name="old_page" value="<?php echo($_GET['page']); ?>" />
                		<h3>Основные поля страницы:</h3>
						<fieldset>
							<p><label>Путь:</label><input type="text" name="page" class="text-medium" value="<?php echo($_GET['page']); ?>" /></p>
							<p><label>Заголовок:</label><input type="text" name="title" class="text-long" value="<?php echo($page['title']); ?>" /></p>
							<p><label>Содержание:</label><textarea name="content" rows="30" cols="75"><?php echo(file_get_contents(VIEWS_DIR .'/static/'. $page['name'] .'.php')); ?></textarea></p>
						</fieldset>
						<fieldset>
							<p><label><input type="checkbox" name="published" <?php if(@$page['published']): ?> checked="checked"<?php endif;?> /> Опубликована</label></p>
							<input type="submit" value="Отредактировать" />
							<input type="button" onclick="preview()" value="Предварительный просмотр" />
						</fieldset>
					</form>
                </div>
