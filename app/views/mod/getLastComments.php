			<?php include(dirname(__FILE__) .'/../blog/chunks/blog_control.php'); ?>
				<div class="b-static">
					<h1>Последние комментарии</h1>
				</div>
				<div class="l-comments-wrap">
					<div id="placeholder_comment"></div>
					<?php if (sizeof($comments) > 0): ?>
					<?php foreach($comments as $comment): ?>

					<div class="b-comment" id="comment_<?php echo($comment['id']); ?>">
						<div class="b-comment_b-info">
							<?php echo(TemplateHelper::date('d M Y @ H:i', $comment['created_at'])); ?>, <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo($comment['post_id']); ?>/"><?php echo($comment['post_title']); ?></a>
							(<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<?php echo($comment['post_id']); ?>/#<?php echo($comment['id']); ?>">#<?php echo($comment['id']); ?></a>)
							<a href="#" class="js-remove-button g-hidden">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/remove.gif" width="16" height="16" alt="<?php echo($comment['id']); ?>" />
							</a>
							<?php if($comment['author'] != 'anonymous'): $author = HomeBoardHelper::getBoard($comment['author']); ?>
							<a href="http://<?php echo($comment['author']); ?>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<?php echo($author[1]); ?>»">
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<?php echo($author[0]); ?>" width="16" height="16" alt="" />
							</a>
							<?php endif; ?>
						</div>
						<div class="b-comment_b-body g-clearfix">
							<?php echo($comment['text']); ?>

						</div>
					</div>
					<?php endforeach; ?>
					<?php endif; ?>

					<textarea id="template_comment" style="display:none">
						<div class="b-comment" id="comment_<%=id%>">
							<div class="b-comment_b-info">
								<%=created_at%>, <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<%=post_id%>/"><%=post_title%></a>
								<span class="js-comment-id">(<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<%=post_id%>/#<%=id%>">#<%=id%></a>)</span>
								<a href="#" class="js-remove-button g-hidden">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/remove.gif" width="16" height="16" alt="<%=id%>" />
								</a>
								<% if (author[0] != "anonymous") { %>
								<a href="http://<%=author[0]%>/" class="b-comment_b-homeboard" title="Аноним выбрал принадлежность «<%=author[1][1]%>»">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/homeboards/<%=author[1][0]%>" width="16" height="16" alt="" />
								</a>
								<% } %>
							</div>
							<div class="b-comment_b-body g-clearfix">
								<%=text%>
							</div>
						</div>
					</textarea>

				</div>
