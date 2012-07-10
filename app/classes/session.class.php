<?php
/**
 * Класс работы с профилем (и сессией):
 */
class Session
{
	/**
	 * Время жизни долгой сессии:
	 */
	const LIFETIME = 604800;

	/**
	 * Флаг создания сессии:
	 */
	private $justCreated = false;

	/**
	 * Ключ сессии:
	 */
	private $key = null;

	/**
	 * Параметры временной сессии:
	 */
	private $activeSession = array();

	/**
	 * Параметры постоянной сессии:
	 */
	private $persistenceSession = array();
	private $persistenceUpdated = false;

	/**
	 * Параметры "моментальной" сессии:
	 */
	private $instantSession = array();


	/**
	 * Вызов экземпляра класса:
	 */
	static function getInstance()
	{
		static $instance;

		if (!is_object($instance))
			$instance = new Session();

		return $instance;
	}

	/**
	 * Конструктор:
	 */
	private function __construct()
	{
		session_start();
		if (array_key_exists('session', $_SESSION))
		{
			$cache = KVS::getInstance();

			$this -> key                = @$_SESSION['session']['key'];
			$this -> activeSession      = @$_SESSION['session']['active'];
			$this -> persistenceSession = unserialize($cache -> get('Session', md5($this -> key), 'storage'));
			$this -> instantSession     = @$_SESSION['session']['instant'] ?
										   $_SESSION['session']['instant'] : array();

			return;
		}

		$this -> start();
	}

	/**
	 * Обновление сессии:
	 */
	private function start()
	{
		$cache = KVS::getInstance();

		if (!array_key_exists('key', $_COOKIE) || !$cache -> exists('Session', md5($_COOKIE['key']), 'storage')) 
			if ($cache -> exists('Session_ip', $_SERVER['REMOTE_ADDR'], 'key'))
				$this -> key = $_COOKIE['key'] = $cache -> get('Session_ip', $_SERVER['REMOTE_ADDR'], 'key');

		if (array_key_exists('key', $_COOKIE))
		{
			$this -> key = $_COOKIE['key'];

			if ($cache -> exists('Session', md5($this -> key), 'storage'))
			{
				$this -> persistenceSession =
					unserialize($cache -> get('Session', md5($this -> key), 'storage'));
				$cache -> expire('Session', md5($this -> key), 'storage', Session::LIFETIME);
			}
		}
		else
		{
			$this -> key = uniqid(md5(time()));
			$this -> justCreated = true;
		}

		setcookie('key', $this -> key, time() + Session::LIFETIME, '/');
		$_SESSION['open_from'] = $_SERVER['REMOTE_ADDR'];
		$this -> update();
	}

	/**
	 * Обновление сессии:
	 */
	private function update()
	{
		$_SESSION['session']['key']            = $this -> key;
		$_SESSION['session']['active']         = $this -> activeSession;
		$_SESSION['session']['persistence']    = $this -> persistenceSession;

		return true;
	}

	/**
	 * Получить ключ сессии:
	 */
	public function getKey()
	{
		return $this -> key;
	}

	/**
	 * Проверка флага создания сессии:
	 */
	public function isJustCreated()
	{
		return $this -> justCreated;
	}

	/**
	 * Установка параметра активной сессии:
	 */
	public function activeSet($key, $value)
	{
		$this -> activeSession[$key] = $value;
		$_SESSION['session']['active'][$key] = $value;
	}

	/**
	 * Получение параметра активной сессии:
	 */
	public function activeGet($key, $default = false)
	{
		if (array_key_exists($key, $this -> activeSession))
			return $this -> activeSession[$key];

		return $default;
	}

	/**
	 * Очистка активной сессии:
	 */
	public function activeFree()
	{
	    $this -> activeSession = array();
		$this -> update();
	    return true;
	}

	/**
	 * Установка параметра долгой сессии:
	 */
	public function persistenceSet($key, $value)
	{
		$this -> persistenceSession[$key] = $value;
		$this -> persistenceSave();
	}

	/**
	 * Сохранение долгой сессии:
	 */
	private function persistenceSave()
	{
		$cache = KVS::getInstance();
		$cache -> set('Session', md5($this -> key), 'storage', serialize($this -> persistenceSession));
		$cache -> expire('Session', md5($this -> key), 'storage', Session::LIFETIME);
	}

	/**
	 * Получение параметра долгой сессии:
	 */
	public function persistenceGet($key, $default = false)
	{
		if (array_key_exists($key, $this -> persistenceSession))
			return $this -> persistenceSession[$key];

		return $default;
	}

	/**
	 * Установка параметра мгновенной сессии:
	 */
	public function instantSet($key, $value)
	{
		$this -> instantSession[$key] = $value;
		$_SESSION['session']['instant'][$key] = $value;
	}

	/**
	 * Получение параметра мгновенной сессии:
	 */
	public function instantGet($key, $default = false)
	{
		if (array_key_exists($key, $this -> instantSession))
		{
			unset($_SESSION['session']['instant'][$key]);
			return $this -> instantSession[$key];
		}

		return $default;
	}

	/**
	 * Проверка на админство:
	 */
	public function isAdmin($key, $hash)
	{
		$moderators = ControlModel::GetModerators();

		foreach($moderators as $mod)
		{
			if ($mod['key'] == $key .'%'. $hash)
			{
				$_SESSION['auth'] = $mod;
				return true;
			}
		}
		return false;
	}

	/**
	 * Проверка на админскую сессию:
	 */
	public function isAdminSession()
	{
		if (array_key_exists('auth', $_SESSION))
		{
			if ($_SESSION['auth']['class'] == 0)
				return true;
		}
		return false;
	}

	/**
	 * Проверка на модераторство:
	 */
	public function isModerator($key)
	{
		$moderators = ControlModel::GetModerators();
		foreach($moderators as $mod)
		{
			if ($mod['key'] === $key)
			{
				$_SESSION['auth'] = $mod;
				return true;
			}
		}
		return false;
	}

	/**
	 * Проверка на модераторскую сессию:
	 */
	public function isModeratorSession()
	{
		if (array_key_exists('auth', $_SESSION))
		{
			if ($_SESSION['auth']['class'] >= 0)
				return true;
		}
		return false;
	}

	/**
	 * Деструктор (сохраняет данные в бд):
	 */
	public function __destruct()
	{
		$cache = KVS::getInstance();

		$cache -> set('Session_ip', $_SERVER['REMOTE_ADDR'], 'key', $this -> key);
		$cache -> expire('Session_ip', $_SERVER['REMOTE_ADDR'], 'key', 60 * 5);

		$this -> update();
	}
}
