			<?php include(dirname(__FILE__) .'/chunks/blog_control.php'); ?>

				<div class="b-blog-form">
					<h1>Категории:</h1>
					<div class="b-blog-form_b-form">
			<?php if (empty($categories)): ?>
						<div class="b-blog-form_b-form_b-field">
							<h2>Нет категорий для вывода</h2>
						</div>
			<?php else: ?>
					<?php foreach($categories as $category): ?>
					<?php if ($category['posts'] == 0) continue; ?>
						<div class="b-blog-form_b-form_b-field">
							<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/cat/<?php echo($category['name']); ?>/" class="b-blog-form_b-form_b-field_b-header-link"><?php echo($category['title']); ?> (<?php echo($category['posts']); ?>)</a>
							<p>
								<?php echo($category['description']); ?>
							</p>
						</div>
					<?php endforeach; ?>
			<?php endif; ?>
					</div>
				</div>