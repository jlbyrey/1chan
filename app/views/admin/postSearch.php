				<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Фильтр постов</a></h2>

                <div id="main">
                	<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postSearch" method="post">
                		<h3>Основные поля:</h3>
						<fieldset>
							<p><label>ID:</label><input name="id" type="text" class="text-small" value="<?php echo(@$_POST['id']); ?>" /></p>
							<p><label>Поиск в тексте:</label><input name="query" type="text" class="text-long"  value="<?php echo(@$_POST['query']); ?>" /></p>
								<p><label>Категория:</label><select name="category[]" multiple="multiple" size="5">
									<option value="0"></option>
								<?php foreach(Blog_BlogCategoryModel::GetCategories() as $cat): ?>
									<option value="<?php echo($cat['id']); ?>"><?php echo($cat['title']); ?></option>
								<?php endforeach; ?>
								</select></p>
							<input type="submit" value="Фильтр постов" />
						</fieldset>
					</form>

                    	<?php if (!empty($posts)): ?>
						<h3>Посты:</h3>
                    	<table cellpadding="0" cellspacing="0">
                    	<?php $i = 0; ?>
                    	<?php foreach($posts as $post): ?>
							<tr<?php if(++$i % 2): ?> class="odd"<?php endif; ?>>
                                <td>(<?php echo !is_null($post['category']) ? $post['category'] : '-'; ?>:<?php echo $post['id']; ?>) <?php echo $post['title']; ?></td>
                                <td class="action"><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo $post['id']; ?>/" class="view">View</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postEdit?id=<?php echo $post['id']; ?>" class="edit">Edit</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/postDelete?id=<?php echo $post['id']; ?>" class="delete">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </table>
                        <?php if ($this -> getParameter('total_pages') > 0): ?>
							<h3>Страницы:</h3>
							<fieldset>
							<?php foreach(range(0, $this -> getParameter('total_pages')) as $p): ?>
								<?php if ($this -> getParameter('current_page') == $p): ?><b><?php echo($p); ?></b>
								<?php else: ?><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/posts?page=<?php echo($p); ?>&filter=<?php echo $this -> getParameter('filter'); ?>"><?php echo($p); ?></a><?php endif; ?>

							<?php endforeach; ?>
							</fieldset>
						<?php endif; ?>
                </div>