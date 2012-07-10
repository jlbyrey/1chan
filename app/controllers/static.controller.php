<?php
/**
 * Контроллер статических страниц:
 */
class StaticController extends BaseController
{
	/**
	 * Показ статической страницы:
	 */
	public function indexAction(Application $application, Template $template)
	{
		if (($page = StaticModel::GetPage($_GET['page'])) != false)
		{
			if ($page['published'] == true) {
				$template -> setParameter('title', $page['title']);
				$this['page'] = $page['name'];
				return true;
			}
		}

		$application -> go('errors_error404');
		return false;
	}

	/**
	 * Предварительный просмотр:
	 */
	public function previewAction(Application $application, Template $template)
	{
		if ($_SERVER['REQUEST_METHOD'] != 'POST')
			die($application -> go('errors_error404'));

		$session = Session::getInstance();
		if (!$session -> isAdminSession())
			die($application -> go('errors_error403'));
		
		$template -> setParameter('title', $_POST['title']);
		$this['preview_content'] = $_POST['content'];

		return true;
	}

	/**
	 * Poo chan:
	 */
	public function pooAjaxAction(Application $application) {
		$cache = KVS::getInstance();
		if ($cache -> exists(__CLASS__, 'poo', $_SERVER['REMOTE_ADDR'])) {
			return false;
		}

		EventModel::getInstance()
            -> ClientBroadcast('page_'. $_POST['target'], "poo", array('top' => $_POST['top'], 'left' => $_POST['left']));

		$cache -> set(__CLASS__, 'poo', $_SERVER['REMOTE_ADDR'], 1);
		$cache -> expire(__CLASS__, 'poo', $_SERVER['REMOTE_ADDR'], 1);
		return true;
	}
}
