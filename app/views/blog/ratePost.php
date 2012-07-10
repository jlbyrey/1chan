			<?php include(dirname(__FILE__) .'/chunks/blog_control.php'); ?>

				<div class="b-blog-form">
					<div class="b-blog-form_b-form">
					<form action="" method="post">
					<input type="hidden" name="referer" value="<?php echo($this -> getParameter('referer')); ?>" />
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