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
                	<form id="static_form" action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/staticAdd" method="post" class="jsNice">
                		<h3>Основные поля страницы:</h3>
						<fieldset>
							<p><label>Путь:</label><input type="text" name="page" class="text-medium" value="" /></p>
							<p><label>Заголовок:</label><input type="text" name="title" class="text-long" value="" /></p>
							<p><label>Содержание:</label><textarea name="content" rows="30" cols="75"></textarea></p>
						</fieldset>
						<fieldset>
							<p><label><input type="checkbox" name="published" checked="checked" /> Опубликована</label></p>
							<input type="submit" value="Добавить страницу" />
							<input type="button" onclick="preview()" value="Предварительный просмотр" />
						</fieldset>
					</form>
                </div>
