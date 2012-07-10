				<div class="b-live-panel g-clearfix">
					<form action="http://<?php echo TemplateHelper::getSiteUrl(); ?>/live/add/" method="post">
					<ul>
						<li class="b-live-panel_b-link">
							<input type="text" name="link" value="<?php echo @$live_form['link']; ?>" />
						</li>
						<li class="b-live-panel_b-description">
							<input type="text" name="description" value="<?php echo @$live_form['description']; ?>" />
							<input type="submit" value="" />
						</li>
					</ul>
					</form>
				</div>
				<div class="b-blog-form">
					<div class="b-blog-form_b-form">
					<form action="" method="post">
					<input type="hidden" name="link" value="<?php echo @$live_form['link']; ?>" />
					<input type="hidden" name="description" value="<?php echo @$live_form['description']; ?>" />
						<div class="b-blog-form_b-form_b-field">
							<h2>Обратный тест тьюринга:</h2>
							<input type="hidden" name="captcha_key" value="<?php echo($this -> getParameter('captcha_key')); ?>" />
							<p>
								Для продолжения введите символы, изображенные на картинке.
							</p>
							<p>
								<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/captcha/?key=<?php echo($this -> getParameter('captcha_key')); ?>&<?php echo session_name()?>=<?php echo session_id()?>">
								<input type="text" name="captcha" value=""<?php if($this -> getParameter('captcha_err')):?> class="g-input-error"<?php endif; ?> />
							</p>
						</div>
						<div class="b-blog-form_b-actions">
							<input type="submit" value="Продолжить" />
						</div>
					</form>
					</div>
				</div>