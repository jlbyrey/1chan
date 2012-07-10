<?php
/**
 * Класс подключения к базе данных (redis):
 */
class Cache
{
	/**
	 * Соединение с базой данных:
	 */
	private $connection = null;

	/**
	 * Вызов экземпляра класса:
	 */
	static function getInstance()
	{
		static $instance;

		if (!is_object($instance))
			$instance = new Cache();

		return $instance;
	}

	/**
	 * Конструктор:
	 */
	private function __construct()
	{
		if (is_null($this -> connection))
			$this -> connection = new Redis();

		return $this -> connection;
	}

	/**
	 * Получение пути к свойству класса:
	 * $class - имя класса.
	 * $id    - идентификатор.
	 * $name  - имя свойства.
	 */
	private function getNS($class, $id, $name)
	{
		if (is_null($id) && is_null($name))
			return $class;

		elseif (is_null($name))
			return $class .':'. $id;

		else
			return $class .':'. $id .':'. $name;
	}

	/**
	 * Получение значения из базы данных:
	 * $class - имя класса.
	 * $id    - идентификатор.
	 * $name  - имя свойства.
	 */
	public function get($class, $id = null, $name = null)
	{
		return $this -> connection -> get(
			$this -> getNS($class, $id, $name)
		);
	}

	/**
	 * Получение ключей из базы данных по паттерну:
	 * $class   - имя класса.
	 * $id      - идентификатор.
	 * $pattern - имя свойства.
	 */
	public function getKeys($class, $id = null, $pattern = null)
	{
		return $this -> connection -> keys(
			$this -> getNS($class, $id, $pattern)
		);
	}

	/**
	 * Запись значения в базу данных.
	 * $class - имя класса.
	 * $id    - идентификатор.
	 * $name  - имя свойства.
	 * $value - значение свойства.
	 */
	public function set($class, $id = null, $name = null, $value = null)
	{
		return $this -> connection -> set(
			$this -> getNS($class, $id, $name), $value
		);
	}

	/**
	 * Проверка существования значения в базе данных:
	 * $class - имя класса.
	 * $id    - идентификатор.
	 * $name  - имя свойства.
	 */
	public function exists($class, $id = null, $name = null)
	{
		return $this -> connection -> exists(
			$this -> getNS($class, $id, $name)
		);
	}

	/**
	 * Удаление значения из базы данных:
	 * $class - имя класса.
	 * $id    - идентификатор.
	 * $name  - имя свойства.
	 */
	public function remove($class, $id = null, $name = null)
	{
		return $this -> connection -> delete(
			$this -> getNS($class, $id, $name)
		);
	}

	/**
	 * Удаление значения из базы данных по паттерну:
	 * $class   - имя класса.
	 * $id      - идентификатор.
	 * $pattern - имя свойства.
	 */
	public function removeByPattern($class, $id = null, $pattern = null)
	{
		$keys  = $this -> connection -> keys(
			$this -> getNS($class, $id, $pattern)
		);

		if (!empty($keys[0]))
			foreach ($keys as $key)
			{
				$this -> connection -> delete($key);
			}

		if ($pattern == '*')
			$this -> connection -> delete(
				$this -> getNS($class, $id, $pattern)
			);

		return true;
	}

	/**
	 * Установка времени жизни ключа:
	 * $class  - имя класса.
	 * $id     - идентификатор.
	 * $name   - имя свойства.
	 * $expire - время жизни ключа.
	 */
	public function expire($class, $id = null, $name = null, $expire = null, $till = false)
	{
		if ($till)
			return $this -> connection -> expireat(
				$this -> getNS($class, $id, $name), $expire
			);
		else
			return $this -> connection -> expire(
				$this -> getNS($class, $id, $name), $expire
			);
	}

	/**
	 * Получение времени жизни ключа:
	 */
	public function lifetime($class, $id = null, $name = null)
	{
		return $this -> connection -> ttl(
			$this -> getNS($class, $id, $name)
		);
	}

	/**
	 * Увеличение значения ключа в базе данных:
	 * $class  - имя класса.
	 * $id     - идентификатор.
	 * $name   - имя свойства.
	 * $amount - количество приращения.
	 */
	public function incr($class, $id = null, $name = null, $amount = 1)
	{
		return $this -> connection -> incr(
			$this -> getNS($class, $id, $name), $amount
		);
	}

	/**
	 * Уменьшение значения ключа в базе данных:
	 * $class - имя класса.
	 * $id    - идентификатор.
	 * $name  - имя свойства.
	 * $amount - количество приращения.
	 */
	public function decr($class, $id = null, $name = null, $amount = 1)
	{
		return $this -> connection -> decr(
			$this -> getNS($class, $id, $name), $amount
		);
	}

	/**
	 * Получение длины списка:
	 * $class - имя класса.
	 * $id    - идентификатор.
	 * $name  - имя свойства.
	 */
	public function listLength($class, $id = null, $name = null)
	{
		return $this -> connection -> llen(
			$this -> getNS($class, $id, $name)
		);
	}

	/**
	 * Получение значение из списка:
	 * $class  - имя класса.
	 * $id     - идентификатор.
	 * $name   - имя свойства.
	 * $offset - начальная позиция результатов.
	 * $limit  - количество результатов.
	 */
	public function listGet($class, $id = null, $name = null, $offset = 0, $limit = -1)
	{
		return $this -> connection -> lrange(
			$this -> getNS($class, $id, $name), $offset, $limit
		);
	}

	/**
	 * Добавление значения в список:
	 * $class  - имя класса.
	 * $id     - идентификатор.
	 * $name   - имя свойства.
	 * $value  - значение.
	 */
	public function listAdd($class, $id = null, $name = null, $value = null)
	{
		return $this -> connection -> push(
			$this -> getNS($class, $id, $name), $value, false
		);
	}

	/**
	 * Удаление значения из списка:
	 * $class  - имя класса.
	 * $id     - идентификатор.
	 * $name   - имя свойства.
	 * $value  - значение.
	 */
	public function listRemove($class, $id = null, $name = null, $value = null)
	{
		return $this -> connection -> ldelete(
			$this -> getNS($class, $id, $name), 1, $value
		);
	}

	/**
	 * Сокращение списка:
	 * $class  - имя класса.
	 * $id     - идентификатор.
	 * $name   - имя свойства.
	 * $value  - значение.
	 */
	public function listTrim($class, $id = null, $name = null, $from = 0, $to = 0)
	{
		return $this -> connection -> ltrim(
			$this -> getNS($class, $id, $name), $from, $to
		);
	}

	/**
	 * Получение длины сортировочного списка:
	 * $class  - имя класса.
	 * $id     - идентификатор.
	 * $name   - имя свойства.
	 */
	public function sortedListLength($class, $id = null, $name = null)
	{
		return $this -> connection -> scard(
			$this -> getNS($class, $id, $name)
		);
	}

	/**
	 * Получение длины сортированного списка:
	 * $class  - имя класса.
	 * $id     - идентификатор.
	 * $name   - имя свойства.
	 */
	public function sortedListGet($class, $id = null, $name = null, $offset = 0, $limit = -1)
	{
		return $this -> connection -> zrevrange(
			$this -> getNS($class, $id, $name), $offset, $limit
		);
	}

	/**
	 * Добавление значения в сотрированный список:
	 * $class  - имя класса.
	 * $id     - идентификатор.
	 * $name   - имя свойства.
	 * $value  - значение.
	 * $score  - значение индекса сортировки.
	 */
	public function sortedListAdd($class, $id = null, $name = null, $value = null, $score = null)
	{
		return $this -> connection -> zadd(
			$this -> getNS($class, $id, $name), $score, $value
		);
	}

	/**
	 * Удаление значения из сортированного списка:
	 * $class  - имя класса.
	 * $id     - идентификатор.
	 * $name   - имя свойства.
	 * $value  - значение.
	 */
	public function sortedListRemove($class, $id = null, $name = null, $value = null)
	{
		return $this -> connection -> zrem(
			$this -> getNS($class, $id, $name), $value
		);
	}
}
