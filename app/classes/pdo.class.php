<?php
/**
 * Класс управления запросами к базе данных:
 */
class PDOQuery
{
	/**
	 * Соединение с базой данных:
	 */
	private $connection = null;

	/**
	 * Вызов экземпляра класса:
	 */
	public static function getInstance()
	{
		static $instance;

		if (!is_object($instance))
			$instance = new PDOQuery();

		return $instance;
	}

	/**
	 * Конструктор (подключение к базе данных):
	 */
	private function __construct()
	{
		$config = Config::getInstance();

		$engine   = $config['database']['engine'];
		$host     = $config['database']['host'];
		$database = $config['database']['name'];
		$user     = $config['database']['user'];
		$pass     = $config['database']['pass'];

		$this -> connection = new PDO(
			$engine.':dbname='.$database.';host='.$host, $user, $pass
		);

		$this -> connection
			-> exec('SET NAMES utf8');
	}

	/**
	 * Фильтрация кавычек:
	 */
	public function q($string)
	{
		return $this -> connection -> quote($string);
	}

	/**
	 * Запрос на вставку значений:
	 */
	public function insert($to, $fields)
	{
		$query = $this -> connection
			-> prepare(
				'INSERT INTO '. $to .
				' (`'.implode('`,`', array_keys($fields)).'`)' .
				' VALUES (:'. implode(', :', array_keys($fields)) .')'
			);

		if ($query -> execute($fields))
			return $this -> connection -> lastInsertId();

		return false;
	}

	/**
	 * Запрос на получение значений:
	 */
	public function select($from, $fields = '*', $where = null, $orderBy = null, $limit = null, &$count = null)
	{
		$query = $this -> connection
			-> prepare(
				'SELECT SQL_CALC_FOUND_ROWS '. $fields .' FROM '. $from .
				($where ? '  WHERE '. $where : '') .
				($orderBy ? ' ORDER BY '. $orderBy : '') .
				($limit ? ' LIMIT '. $limit : '')
			);

		if ($query -> execute())
		{
			$found_rows = $this -> connection -> query('SELECT FOUND_ROWS() as count;');
			$count = $found_rows -> fetchColumn();

			return $query -> fetchAll(PDO::FETCH_ASSOC);
		}

 		return false;
	}

	/**
	 * Запрос на обновление значений:
	 */
	public function update($table, $fields = array(), $where = null, $limit = null)
	{
		$set = array();
		foreach ($fields as $key => $value)
		{
			$set[] = $key .' = :'. $key;
		}

		$query = $this -> connection -> prepare(
			'UPDATE '. $table .' SET '.
			 implode(', ', $set) .
			($where ? '  WHERE '. $where : '') .
			($limit ? ' LIMIT '. $limit : '')
		);

		return $query -> execute($fields);
	}

	/**
	 * Запрос на обновление значений:
	 */
	public function update_insecure($table, $fields = array(), $where = null, $limit = null)
	{
		$set = array();
		foreach ($fields as $key => $value)
		{
			$set[] = $key .' = '. $value;
		}

		return $this -> connection -> exec(
			'UPDATE '. $table .' SET '.
			 implode(', ', $set) .
			($where ? '  WHERE '. $where : '') .
			($limit ? ' LIMIT '. $limit : '')
		);
	}

	/**
	 * Запрос на удаление значений:
	 */
	public function delete($table, $where = null, $limit = null)
	{
		return $this -> connection -> exec(
			'DELETE FROM '. $table .
			($where ? '  WHERE '. $where : '') .
			($limit ? ' LIMIT '. $limit : '')
		);
	}
}