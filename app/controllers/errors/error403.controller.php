<?php
/**
 * Обработчик ошибки 403:
 */
class Errors_Error403Controller extends BaseController
{
	public function indexAction(Application $application, Template $template)
	{
		$template -> setParameter('title', 'Доступ запрещен');
		return true;
	}
}