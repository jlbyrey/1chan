
				<div class="b-board-header">
					<div class="b-board-header_name">
						<h1>Последние сообщения</h1>
						<div class="b-board-header_desc">
							Последние сообщения всех разделов
						</div>
					</div>
					<div class="b-board-header_options">
						 <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/help/board/">Правила разделов</a>
					</div>
				</div>

				<div class="b-board m-thread">

					<div id="placeholder_comment"></div>
					<?php if (sizeof($posts) > 0): ?>
					<?php foreach($posts as $comment): ?>

						<div class="b-comment" id="comment_<?php echo($comment['board_id']); ?>_<?php echo($comment['id']); ?>">
							<div class="b-comment_b-info">
								<?php echo(TemplateHelper::date('d M Y @ H:i', $comment['created_at'])); ?>, <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($comment['board_id']); ?>/"><?php echo($comment['board_title']); ?></a>: <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($comment['board_id']); ?>/res/<?php echo($comment['parent_id'] ? $comment['parent_id'] : $comment['id']); ?>/#<?php echo($comment['id']); ?>" class="js-paste-link">№<?php echo($comment['id']); ?></a>
								<?php if($comment['parent_id']): ?>(в треде: <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($comment['board_id']); ?>/res/<?php echo($comment['parent_id']); ?>/#<?php echo($comment['id']); ?>" class="js-cross-link" name="<?php echo($comment['board_id']); ?>/<?php echo($comment['parent_id']); ?>">&gt;&gt;<?php echo($comment['parent_id']); ?></a>)<?php endif; ?>
								<a href="#" class="js-remove-button g-hidden"><img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/remove.gif" width="16" height="16" alt="<?php echo($comment['board_id']); ?>/<?php echo($comment['id']); ?>" /></a>
								<?php if($comment['author'] && $comment['author'] != 'anonymous'): $author = HomeBoardHelper::getBoard($comment['author']); ?>
								<a href="http://<?php echo($comment['author']); ?>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<?php echo($author[1]); ?>»">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<?php echo($author[0]); ?>" width="16" height="16" alt="" />
								</a>
								<?php endif; ?>
							</div>
							<div class="b-comment_b-body g-clearfix">
								<div class="wrap">
									<p>									
									<?php if($comment['upload']): ?>
									<a class="b-image-link" target="_blank" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($comment['upload']['web_full']); ?>" title="<?php echo($comment['upload']['full_size'][0]);?>x<?php echo($comment['upload']['full_size'][1]);?>, <?php echo($comment['upload']['size']); ?>">
										<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<?php echo($comment['upload']['web_thumb']); ?>" width="<?php echo($comment['upload']['thumb_size'][0]); ?>" height="<?php echo($comment['upload']['thumb_size'][1]); ?>" alt="" />
									</a>
									<?php endif; ?>
									<?php echo($comment['text']); ?>

									</p>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
					<?php endif; ?>

						<textarea id="template_comment" style="display:none">
							<div class="b-comment m-new" id="comment_<%=board_id%>_<%=id%>">
								<div class="b-comment_b-info">
									<%=created_at%>, <span class="js-comment-id"><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<%=board_id%>/" class="js-paste-link"><%=board_title%></a>, <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<%=board_id%>/res/<% if(parent_id) { %><%=parent_id%><% } else { %><%=id%><% } %>/#<%=id%>" class="js-paste-link" name="<%=id%>">№<%=id%></a></span>
									<% if(parent_id) { %>(в треде: <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<%=board_id%>/res/<%=parent_id%>/#<%=id%>" class="js-cross-link" name="<%=board_id%>/<%=parent_id%>">&gt;&gt;<%=parent_id%></a>)<% } %>									
									<a href="#" class="js-remove-button g-hidden">
										<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/remove.gif" width="16" height="16" alt="<%=board_id%>/<%=id%>" />
									</a>
									<% if (author[0] != "anonymous") { %>
									<a href="http://<%=author[0]%>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<%=author[1][1]%>»">
										<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<%=author[1][0]%>" width="16" height="16" alt="" />
									</a>
									<% } %>
								</div>
								<div class="b-comment_b-body g-clearfix">
									<p>
									<% if (upload) { %>
									<a class="b-image-link" target="_blank" href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<%=upload['web_full']%>" title="<%=upload['full_size'][0]%>x<%=upload['full_size'][1]%>, <%=upload['size']%>">
										<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/<%=upload['web_thumb']%>" width="<%=upload['thumb_size'][0]%>" height="<%=upload['thumb_size'][1]%>" alt="" />
									</a>
									<% } %>
									<%=text%>
									</p>
								</div>
							</div>
						</textarea>

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
				</div>
