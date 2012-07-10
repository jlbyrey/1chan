<?php
/**
 * Контроллер авторизации:
 */
class Generic_AuthorizeController extends BaseController
{
	/**
	 * Метод авторизации:
	 */
	public function authorizeAjaxAction(Application $application)
	{
		$session = Session::getInstance();

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if ($_POST['hash'] == 0)
				return array('authorized' => $session -> isModerator($_POST['key']));
			else
				return array('authorized' => $session -> isAdmin($_POST['key'], $_POST['hash']));
		}
		return array('authorized' => false);
	}
}