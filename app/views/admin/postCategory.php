				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Категории</a></h2>
                <div id="main">
						<h3>Категории:</h3>
						<input type="button" onclick="location.href='http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postCategoryAdd'" value="Создать категорию" /><br /><br />
                    	<?php if (empty($cats)): ?>
                    	<table cellpadding="0" cellspacing="0">
							<td>Нет категорий для отображения.</td>
                    	<?php else: ?>
                    	<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postCategoryResort?id=<?php echo($cat['pos']); ?>" method="post">
                    	<table cellpadding="0" cellspacing="0">
                    	<?php $i = 0; ?>
                    	<?php foreach($cats as $cat): ?>
							<tr<?php if(++$i % 2): ?> class="odd"<?php endif; ?>>
                                <td><input type="text" name="pos[<?php echo($cat['id']); ?>]" size="1" value="<?php echo($i); ?>" /> <?php echo $cat['title']; ?> (<?php echo($cat['id']); ?>)</td>
                                <td class="action"><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/b/cat/<?php echo $cat['name']; ?>/" class="view">View</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postCategoryEdit?id=<?php echo $cat['id']; ?>" class="edit">Edit</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postCategoryDelete?id=<?php echo $cat['id']; ?>" class="delete">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </table>
                        <br /><input type="submit" value="Отсортировать" />
                        </form>
                        <?php endif; ?>
                </div>