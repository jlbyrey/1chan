<?php if (empty($posts)): ?>
			<?php include(dirname(__FILE__) .'/chunks/blog_control.php'); ?>

				<div class="b-static">
					<h1>У вас нет избранных постов</h1>
					<p>Добавить в избранное можно нажав на <img src="http://1chan.ru/ico/favorites-false.png" width="16" height="16" alt="" /> в информации об интересном посте.</p>
				</div>
<?php else: ?>
<?php include(dirname(__FILE__) .'/viewAll.php'); ?>
<?php endif; ?>
