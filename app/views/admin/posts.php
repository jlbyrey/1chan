				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Посты</a> &raquo;
				<a href="?filter=all"<?php if ($this -> getParameter('filter') == "all"): ?> class="active"<?php endif; ?>>Все</a> /
				<a href="?filter=rated"<?php if ($this -> getParameter('filter') == "rated"): ?> class="active"<?php endif; ?>>Одобренные</a> /
				<a href="?filter=hidden"<?php if ($this -> getParameter('filter') == "hidden"): ?> class="active"<?php endif; ?>>Скрытые</a></h2>

                <div id="main">
						<h3>Посты:</h3>
                    	<table cellpadding="0" cellspacing="0">
                    	<?php if (empty($posts)): ?>
							<td>Нет постов для отображения.</td>
                    	<?php else: ?>
                    	<?php $i = 0; ?>
                    	<?php foreach($posts as $post): ?>
							<tr<?php if(++$i % 2): ?> class="odd"<?php endif; ?>>
                                <td>(<?php echo !is_null($post['category']) ? $post['category'] : '-'; ?>:<?php echo $post['id']; ?>) <?php echo $post['title']; ?></td>
                                <td class="action"><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo $post['id']; ?>/" class="view">View</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postEdit?id=<?php echo $post['id']; ?>" class="edit">Edit</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postDelete?id=<?php echo $post['id']; ?>" class="delete">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </table>
                        <?php if ($this -> getParameter('total_pages') > 1): ?>
							<h3>Страницы:</h3>
							<fieldset>
							<?php foreach(range(0, $this -> getParameter('total_pages')) as $p): ?>
								<?php if ($this -> getParameter('current_page') == $p): ?><b><?php echo($p); ?></b>
								<?php else: ?><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/posts?page=<?php echo($p); ?>&filter=<?php echo $this -> getParameter('filter'); ?>"><?php echo($p); ?></a><?php endif; ?>

							<?php endforeach; ?>
							</fieldset>
						<?php endif; ?>
                </div>