<?php
/**
 * Контроллер действий раздела "онлайн":
 */
class LiveController extends BaseController
{
	/**
	 * Просмотр главной страницы раздела:
	 */
	public function indexAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		$filter              = $session -> persistenceGet('live_filter', true);
		$this['form_errors'] = $session -> instantGet('live_errors', array());
		$this['live_form']   = $session -> instantGet('live_form', array());

		$template -> setParameter('title', '«Онлайн»');

		$this['links']  = Blog_BlogOnlineModel::GetLinks($filter);
		$this['filter'] = $filter;

		$session -> persistenceSet('live_last_visit', time());
		return true;
	}

	/**
	 * Просмотр главной страницы раздела (ajax):
	 */
	public function indexAjaxAction(Application $application)
	{
		$session = Session::getInstance();
		$filter = $session -> persistenceGet('live_filter', true);
		$session -> persistenceSet('live_last_visit', time());
		$links = Blog_BlogOnlineModel::GetLinks($filter);
		
		if (@$_GET['num'])
		    return array_slice($links, 0, (int)$_GET['num']);
		
		return $links;
	}
	
	/**
	 * Включение/выключение панели:
	 */
	public function toggleLinksPanelAction(Application $application) {
	    $session = Session::getInstance();
	    
	    if ($_GET['status'] == 'on')
	        $session -> persistenceSet('show_links_panel', true);
	    else
	        $session -> persistenceSet('show_links_panel', false);
	        
	    $template -> headerSeeOther(
			$_SERVER['HTTP_REFERER']
		);
		return false;
	}
	
	public function toggleLinksPanelAjaxAction(Application $application) {
	    $session = Session::getInstance();
	    
	    if ($_GET['status'] == 'on')
	        $session -> persistenceSet('show_links_panel', true);
	    else
	        $session -> persistenceSet('show_links_panel', false);
	        
	    return true;
	}

	/**
	 * Добавление новой ссылки:
	 */
	public function addAction(Application $application, Template $template)
	{
		$session = Session::getInstance();

		if (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) != TemplateHelper::getSiteUrl())
			return false;

		if (ControlModel::isLiveCaptcha())
		{
			$catpcha = false;
			$this['live_form'] = $_POST;

			if (@$_POST['captcha'])
			{
				if ($_POST['captcha'] !== $session -> instantGet('captcha_'. $_POST['captcha_key'], false))
				{
					$template -> setParameter('captcha_err', true);
					$captcha = true;
				}
				else
					$session -> persistenceSet('captcha_mode', false);
			}
			else
				$captcha = true;

			$key = 'live';
			$template -> setParameter('title',   'Добавление ссылки');
			$template -> setParameter('captcha_key', $key);
			$session  -> instantSet('captcha_'.$key, true);

			if ($captcha)
				return true;
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			ControlModel::checkContent($_POST['description']);
			$validator = new ValidatorHelper($_POST);

			$validator -> assertExists('link', 'Не введена ссылка');
			$validator -> assertRegexp('link', ValidatorHelper::URL_REGEXP, 'Ссылка введена неверно');

			if ($validator -> fieldValid('link'))
				$validator -> assertTrue(
					'link', Blog_BlogOnlineModel::CheckCategory($_POST['link']) !== false,
					'Данная ссылка не может участвовать в ленте'
				);

			if ($validator -> fieldValid('link'))
				$validator -> assertTrue(
					'link', Blog_BlogOnlineModel::CheckLinkPosted($_POST['link']) != true,
					'Ссылка уже участвует в ленте'
				);

			$validator -> assertTrue(
				'timeout', ControlModel::getLiveInterval() == 0,
				'Таймаут '. TemplateHelper::ending(ControlModel::getLiveInterval(), 'секунда', 'секунды', 'секунд')
			);

			$validator -> assertTrue(
				'link', !ControlModel::CheckLinkfilter($params['link']),
				'Запрещенная ссылка'
			);

			$validator -> assertExists('description', 'Не введено описание');
			$validator -> assertLength('description', 100, 'Описание длиннее 100 символов');

			if ($validator -> isValid())
			{
				Blog_BlogOnlineModel::CreateLink($_POST);
				$template -> headerSeeOther(
					'http://'. TemplateHelper::getSiteUrl() .'/live/'
				);
				return false;
			}

			$session -> instantSet('live_errors', $validator -> getValidationResults());
			$session -> instantSet('live_form', $_POST);

			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/live/'
			);
			return false;
		}
		return false;
	}
	
	/**
	 * Добавление ссылки через букмарклет:
	 */
	public function addCrossSiteAction(Application $application, Template $template)
	{
	    $_GET['link']        = urldecode($_GET['link']);
	    $_GET['description'] = urldecode($_GET['description']); 
	    
	    $validator = new ValidatorHelper($_GET);

		$validator -> assertExists('link', 'Не введена ссылка');
		$validator -> assertRegexp('link', ValidatorHelper::URL_REGEXP, 'Ссылка введена неверно');

		if ($validator -> fieldValid('link'))
			$validator -> assertTrue(
				'link', Blog_BlogOnlineModel::CheckCategory($_GET['link']) !== false,
				'Данная ссылка не может участвовать в ленте'
		);

		if ($validator -> fieldValid('link'))
			$validator -> assertTrue(
				'link', Blog_BlogOnlineModel::CheckLinkPosted($_GET['link']) != true,
				'Ссылка уже участвует в ленте'
		);
			
		$validator -> assertTrue(
			'timeout', ControlModel::getLiveInterval() == 0,
			'Таймаут '. TemplateHelper::ending(ControlModel::getLiveInterval(), 'секунда', 'секунды', 'секунд')
		);

		$validator -> assertTrue(
			'link', !ControlModel::CheckLinkfilter($params['link']),
			'Запрещенная ссылка'
		);

		$validator -> assertExists('description', 'Не введено описание');
		$validator -> assertLength('description', 100, 'Описание длиннее 100 символов');
   
        header('Content-type: image/png');
        
		if ($validator -> isValid())
		{
			Blog_BlogOnlineModel::CreateLink($_GET);
			readfile(WEB_DIR .'/ico/live_added.png');
			return false;
		}
		
		readfile(WEB_DIR .'/ico/live_failed.png');
	    return false;
	}

	/**
	 * Добавление новой ссылки (ajax):
	 */
	public function addAjaxAction(Application $application)
	{
		if (ControlModel::isLiveCaptcha())
			return array('isValid' => 'captcha');

		ControlModel::checkContent($_POST['description']);

		$validator = new ValidatorHelper($_POST);
		$validator -> assertExists('link', 'Не введена ссылка');
		$validator -> assertRegexp('link', ValidatorHelper::URL_REGEXP, 'Ссылка введена неверно');

		if ($validator -> fieldValid('link'))
			$validator -> assertTrue(
				'link', Blog_BlogOnlineModel::CheckCategory($_POST['link']) !== false,
				'Данная ссылка не может участвовать в ленте'
			);

		if ($validator -> fieldValid('link'))
			$validator -> assertTrue(
				'link', Blog_BlogOnlineModel::CheckLinkPosted($_POST['link']) != true,
				'Ссылка уже участвует в ленте'
			);

		$validator -> assertTrue(
			'timeout', ControlModel::getLiveInterval() == 0,
			'Таймаут '. TemplateHelper::ending(ControlModel::getLiveInterval(), 'секунда', 'секунды', 'секунд')
		);

		$validator -> assertTrue(
			'link', !ControlModel::CheckLinkfilter($params['link']),
			'Запрещенная ссылка'
		);

		$validator -> assertExists('description', 'Не введено описание');
		$validator -> assertLength('description', 100, 'Описание длиннее 100 символов');

		if ($validator -> isValid())
		{
			Blog_BlogOnlineModel::CreateLink($_POST);
			return true;
		}

		return array(
			'isValid'           => $validator -> isValid(),
			'validationResults' => $validator -> getValidationResults()
		);
	}

	/**
	 * Установка фильтра:
	 */
	public function setFilterAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
			$session -> persistenceSet('live_filter', array_keys($_POST['boards']));

		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/live/'
		);
		return false;
	}

	/**
	 * Установка фильтра (ajax):
	 */
	public function setFilterAjaxAction(Application $application)
	{
		$session = Session::getInstance();
		if (@$_GET['boards'])
			$session -> persistenceSet('live_filter', array_keys($_GET['boards']));

		return $session -> persistenceGet('live_filter', true);
	}

	/**
	 * Переход по ссылке:
	 */
	public function redirectAction(Application $application, Template $template)
	{
		Blog_BlogOnlineModel::Click($_GET['id']);
		$template -> headerSeeOther($_GET['to']);
	}
}
