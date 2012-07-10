<?php
/**
 * Обработчик ошибки 404:
 */
class Errors_Error404Controller extends BaseController
{
	public function indexAction(Application $application, Template $template)
	{
		$template -> setParameter('title', 'Ничего не найдено');
		$template -> headerNotFound();
		return true;
	}
}
