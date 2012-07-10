			<?php include(dirname(__FILE__) .'/../blog/chunks/blog_control.php'); ?>
				<div class="b-static" style="position:relative">
					<h1>Последние действия модераторов</h1>
					<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/hidden/" style="position:absolute; top: 7px;right:0px;font-size:11px;">Просмотр скрытых тредов</a>
				</div>
				<div class="l-comments-wrap">
					<div id="placeholder_comment"></div>
					<?php if (sizeof($comments) > 0): ?>
					<?php foreach($comments as $comment): if($comment): ?>

					<div class="b-comment">
						<div class="b-comment_b-body g-clearfix">
							<?php echo($comment); ?>
						</div>
					</div>
					<?php endif; endforeach; ?>
					<?php endif; ?>

					<textarea id="template_comment" style="display:none">
						<div class="b-comment" id="comment_<%=id%>">
							<div class="b-comment_b-info">
								<%=created_at%>, <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<%=post_id%>/"><%=post_title%></a>
								<span class="js-comment-id">(<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/news/res/<%=post_id%>/#<%=id%>">#<%=id%></a>)</span>
								<a href="#" class="js-remove-button g-hidden">
									<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/remove.gif" width="16" height="16" alt="<%=id%>" />
								</a>
							</div>
							<div class="b-comment_b-body g-clearfix">
								<%=text%>
							</div>
						</div>
					</textarea>
				</div>
