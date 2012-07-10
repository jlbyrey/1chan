<?php
/**
 * Класс управления конфигурацией:
 */
class Config implements ArrayAccess, Iterator, Countable
{
	/**
	 * Контейнер настроек:
	 */
	private $configArray = array();

	/**
	 * Статус изменения:
	 */
	private $isChanged = false;


	/**
	 * Вызов экземпляра класса:
	 */
	public static function getInstance()
	{
		static $instance;

		if (!is_object($instance))
			$instance = new Config();

		return $instance;
	}

	/**
	 * Конструктор:
	 */
	private function __construct()
	{
		if (is_readable(CONFIG_PATH))
		{
			$config = @include_once(CONFIG_PATH);
			$this -> import($config);
		}
	}

	/**
	 * Метод установки параметра конфигурации (ArrayAccess):
	 */
	public function offsetSet($offset, $value) {
        $this -> isChanged = true;
        $this -> configArray[$offset] = $value;
    }

	/**
	 * Метод проверки существования параметра конфигурации (ArrayAccess):
	 */
    public function offsetExists($offset) {
        return isset($this -> configArray[$offset]);
    }

	/**
	 * Метод удаления параметра конфигурации (ArrayAccess):
	 */
    public function offsetUnset($offset) {
        $this -> isChanged = true;
        unset($this -> configArray[$offset]);
    }

	/**
	 * Метод получения параметра конфигурации (ArrayAccess):
	 */
    public function offsetGet($offset) {
        return isset($this -> configArray[$offset]) ? $this -> configArray[$offset] : null;
    }

	/**
	 * Метод сброса каретки в массиве (Iterator):
	 */
    public function rewind()
	{
		reset($this -> configArray);
    }

	/**
	 * Метод получения текущего значения (Iterator):
	 */
    public function current()
	{
		return current($this -> configArray);
    }

	/**
	 * Метод получения текущего ключа (Iterator):
	 */
    public function key()
	{
		return key($this -> configArray);
    }

	/**
	 * Метод перехода на следующее значение (Iterator):
	 */
    public function next()
	{
		return next($this -> configArray);
    }

	/**
	 * Метод проверки текущего значения (Iterator):
	 */
    public function valid()
    {
		return $this -> current() !== false;
    }

	/**
	 * Метод подсчета количества значений (Count):
	 */
    public function count()
	{
		return count($this -> configArray);
    }

	/**
	 * Метод установки параметров конфигурации из массива:
	 * $options - массив параметров конфигурации.
	 */
	public function import($options)
	{
		if (is_array($options))
		{
			$this -> configArray =
				array_merge_recursive($this -> configArray, $options);
			return true;
		}
		return false;
	}

	/**
	 * Деструктор:
	 */
	public function __destruct()
	{
		if ($this -> isChanged)
		{
			file_put_contents(
				CONFIG_PATH,
				'<?php return('.
					var_export($this -> configArray, true).
				'); ?>'
			);
		}
	}
}