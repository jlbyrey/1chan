<?php
/**
 * Контроллер каптчи:
 */
class Generic_CaptchaController
{
	/**
	 * Вывод каптчи:
	 */
	public function indexAction(Application $application, Template $template)
	{
		$kvs = KVS::getInstance();
/*
		if ($kvs -> exists(__CLASS__, 'captcha_ban', $_SERVER['REMOTE_ADDR']))
			return false;
		$kvs -> set(__CLASS__, 'captcha', $_SERVER['REMOTE_ADDR'], $kvs -> get(__CLASS__, 'captcha', $_SERVER['REMOTE_ADDR']) + 1);
		$kvs -> expire(__CLASS__, 'captcha', $_SERVER['REMOTE_ADDR'], 5);

		if ($kvs -> get(__CLASS__, 'captcha', $_SERVER['REMOTE_ADDR']) > 10) {
			$kvs -> set(__CLASS__, 'captcha_ban', $_SERVER['REMOTE_ADDR'], true);
			$kvs -> expire(__CLASS__, 'captcha_ban', $_SERVER['REMOTE_ADDR'], 60 * 15);
			return false;
		}
*/
		if (!isset($_GET['key']))
			return false;
/*
		if (!preg_match('~^http://1chan\.ru/~i', $_SERVER['HTTP_REFERER']))
		    return false;
*/
		$session = Session::getInstance();

		if ($session -> instantGet('captcha_'. $_GET['key'], false))
		{
			$captcha = new KCAPTCHA();
			$session -> instantSet('captcha_'. $_GET['key'], $captcha -> getKeyString());
		}

		return false;
	}
}
