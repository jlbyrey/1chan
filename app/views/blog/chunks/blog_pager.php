			<?php if ($this -> getParameter('total_pages') > 0): ?>
				<div class="b-paginator">
					<ul class="g-clearfix">
						<li class="b-paginator_b-control"><a href="<?php printf($this -> getParameter('link_pages'), 0) ?>">&lArr;</a></li>
						<li class="b-paginator_b-control"><a href="<?php printf($this -> getParameter('link_pages'), max(0, $this -> getParameter('current_page') - 1)); ?>/">&larr;</a></li>
					<?php foreach(range(max(0, $this -> getParameter('current_page') - 8), max($this -> getParameter('current_page') + (16 - $this -> getParameter('current_page')), min($this -> getParameter('current_page') + 8, $this -> getParameter('total_pages')))) as $p): ?>
					<?php if($p <= $this -> getParameter('total_pages')): ?>
						<?php if ($this -> getParameter('current_page') == $p): ?><li><u><?php echo($p); ?></u></li>
						<?php else: ?><li><a href="<?php printf($this -> getParameter('link_pages'), $p); ?>"><?php echo($p); ?></a></li><?php endif; ?>

                    <?php endif; ?>
					<?php endforeach; ?>
						<li class="b-paginator_b-control"><a href="<?php printf($this -> getParameter('link_pages'), min($this -> getParameter('total_pages'), $this -> getParameter('current_page') + 1)); ?>">&rarr;</a></li>
						<li class="b-paginator_b-control"><a href="<?php printf($this -> getParameter('link_pages'), $this -> getParameter('total_pages')); ?>">&rArr;</a></li>
					</ul>
				</div>
			<?php endif; ?>
