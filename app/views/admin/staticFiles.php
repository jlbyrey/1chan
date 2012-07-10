				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Загруженные файлы</a></h2>

                <div id="main">
				<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/staticFiles" method="post" enctype="multipart/form-data" class="jsNice">
                		<h3>Загрузка нового файла:</h3>
						<fieldset>
							<p><label>Файл:</label><input type="file" name="upload" /><input type="submit" value="Загрузить" /></p>
						</fieldset>
					</form>

						<h3>Страницы:</h3>
                    	<table cellpadding="0" cellspacing="0">
                    	<?php if (empty($files)): ?>
							<td>Нет файлов для отображения.</td>
                    	<?php else: ?>
                    	<?php $i = 0; ?>
                    	<?php foreach($files as $file): ?>
							<tr<?php if(++$i % 2): ?> class="odd"<?php endif; ?>>
                                <td><?php echo $file['name']; ?></td>
                                <td class="action"><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/uploads/<?php echo $file['name']; ?>" class="view">Download</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/staticFilesDelete?name=<?php echo $file['name']; ?>" class="delete">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </table>
                        <br /><br />
                </div>
