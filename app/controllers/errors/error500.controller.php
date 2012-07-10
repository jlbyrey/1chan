<?php
/**
 * Обработчик ошибки 500:
 */
class Errors_Error500Controller extends BaseController
{
	public function indexAction(Application $application, Template $template)
	{
		$template -> setParameter('title', 'Свершилась ошибка');
		return true;
	}
}