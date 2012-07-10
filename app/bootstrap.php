<?php
/**
 * Установка констант путей:
 */
define('WEB_DIR',         getcwd());
define('APPLICATION_DIR', dirname(__FILE__));
define('LIBS_DIR',        APPLICATION_DIR .'/classes');
define('CONTROLLERS_DIR', APPLICATION_DIR .'/controllers');
define('HELPERS_DIR',     APPLICATION_DIR .'/helpers');
define('MODELS_DIR',      APPLICATION_DIR .'/models');
define('VIEWS_DIR',       APPLICATION_DIR .'/views');
define('CONFIG_PATH',     APPLICATION_DIR .'/config.php');
define('UPLOAD_PATH',    WEB_DIR .'/uploads');

/**
 * Подключение библиотек:
 */
require_once(LIBS_DIR .'/kvs.class.php');
require_once(LIBS_DIR .'/config.class.php');
require_once(LIBS_DIR .'/controller.class.php');
require_once(LIBS_DIR .'/pdo.class.php');
require_once(LIBS_DIR .'/session.class.php');
require_once(LIBS_DIR .'/template.class.php');
require_once(LIBS_DIR .'/3rdparty/kcaptcha/kcaptcha.class.php');
require_once(LIBS_DIR .'/3rdparty/sphinx.class.php');
require_once(LIBS_DIR .'/3rdparty/texy.class.php');
require_once(LIBS_DIR .'/3rdparty/realplexor.class.php');
require_once(LIBS_DIR .'/3rdparty/jabberbot.class.php');
require_once(LIBS_DIR .'/3rdparty/rss.class.php');

/**
 * Главный класс приложения:
 */
class Application
{
	/**
	 * Пути к контроллерам:
	 */
	private $routes = array();

	/**
	 * Контроллер и действие:
	 */
	private $controller = null;
	private $action     = null;


	/**
	 * Вызов экземпляра класса:
	 */
	public static function getInstance()
	{
		static $instance;

		if (!is_object($instance))
			$instance = new Application();

		return $instance;
	}

	/**
	 * Конструктор:
	 */
	private function __construct()
	{
		spl_autoload_register(array($this, 'classAutoloader'));

		$config = Config::getInstance();
		$this -> routes = $config['routes'];
	}

	/**
	 * Функция-обработчик загрузчик классов:
	 * $className - имя класса.
	 */
	protected function classAutoloader($className)
	{
		$className = strtolower(str_replace('_', '/', $className));

		switch(true)
		{
			case (substr($className, -10) == 'controller'):
				include(CONTROLLERS_DIR .'/'. substr($className, 0, -10) .'.controller.php');
				break;

			case (substr($className, -6) == 'helper'):
				include(HELPERS_DIR .'/'. substr($className, 0, -6) .'.helper.php');
				break;

			case (substr($className, -5) == 'model'):
				include(MODELS_DIR .'/'. substr($className, 0, -5) .'.model.php');
				break;
		}
	}

	/**
	 * Получение текущего контроллера:
	 */
	public function getController()
	{
		return $this -> controller;
	}

	/**
	 * Получение текущего действия:
	 */
	public function getAction()
	{
		return $this -> action;
	}

	/**
	 * Запуск приложения:
	 */
	public function run()
	{
		Session::getInstance();

		$parameters =& $_GET;
		$request    =  isset($_GET['q']) ?
		                     $_GET['q'] : '/';

		try {
			if (!is_null($this -> routes))
			{
				foreach($this -> routes as $route => $params)
				{
					if (preg_match_all('/^' . addcslashes($route, '/') . '(?:\/*)?$/i', $request, $matches))
					{
						$i = 0;

						foreach($params as $key => $value)
						{
							if (sizeof($matches) > ++$i)
							{
								if (is_int($key))
									$parameters[$value] = $matches[$i][0];
								else
									$parameters[$key] = $matches[$i][0];
							}
							else
							{
								$parameters[$key] = $value;
							}
						}

						break;
					}
				}

				if (array_key_exists('controller', $parameters))
				{
					if (array_key_exists('action', $parameters) && !empty($parameters['action']))
					{
						$this -> go($parameters['controller'], $parameters['action']);
						return true;
					}

					$this -> go($parameters['controller']);
					return true;
				}
			}
		}
		catch(Exception $e)
		{
			$this -> go('errors_error500');
			return false;
		}

		$this -> go('errors_error404');
		return false;
	}

	/**
	 * Метод перехода к определенному контроллеру приложения:
	 * $controller - имя контроллера.
	 * $action     - имя действия контроллера.
	 */
	public function go($controller, $action = 'index')
	{
		if (class_exists($controller .'Controller'))
		{
			$template = new Template();
			$ctrl_class = $controller .'Controller';
			$ctrl       = new $ctrl_class($this, $template);

			if (@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
			{
				if (method_exists($ctrl, $action .'AjaxAction'))
				{
					$this -> controller = $controller;
					$this -> action     = $action;

					$result = json_encode(call_user_func(array($ctrl, $action .'AjaxAction'), $this));
				}
				else
					$result = json_encode(false);

				$template -> headerOk();
				$template -> headerNoCache();
				$template -> headerContentType('text/javascript', 'UTF-8');
				die($result);
			}

			if (method_exists($ctrl, $action .'Action'))
			{
				$this -> controller = $controller;
				$this -> action     = $action;

				if (call_user_func(array($ctrl, $action .'Action'), $this, $template))
					$ctrl -> process($template);

				return true;
			}
			throw new Exception('Controller '. $controller .' has no '. $action .' action!');
		}
		throw new Exception('Application has no '. $controller .' controller!');
	}
}
