				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Статические страницы</a></h2>

                <div id="main">
						<h3>Страницы:</h3>
                    	<table cellpadding="0" cellspacing="0">
                    	<?php if (empty($pages)): ?>
							<td>Нет страниц для отображения.</td>
                    	<?php else: ?>
                    	<?php $i = 0; ?>
                    	<?php foreach($pages as $uri => $page): ?>
							<tr<?php if(++$i % 2): ?> class="odd"<?php endif; ?>>
                                <td><?php echo $page['title']; ?></td>
                                <td class="action"><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo $uri; ?>/" class="view">View</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/staticEdit?page=<?php echo $uri; ?>" class="edit">Edit</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/staticDelete?page=<?php echo $uri; ?>" class="delete">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </table>
                        <br /><br />
                </div>