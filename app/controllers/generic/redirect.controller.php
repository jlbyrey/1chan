<?php
/**
 * Контроллер каптчи:
 */
class Generic_RedirectController
{
	/**
	 * Вывод каптчи:
	 */
	public function redirectAction(Application $application, Template $template)
	{
		$template -> headerMovedPermanently(
			$_GET['url']
		);
		return false;
	}
}
