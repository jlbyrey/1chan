<?php
/**
 * Модель постов:
 */
class Blog_BlogCategoryModel
{
	/**
	 * Создание категории:
	 */
	public static function CreateCategory($params)
	{
		$record = array(
			'name'  => $params['name'],
			'title' => $params['title'],
			'description' => $params['description'],
			'code'  => $params['code'],
			'public' => $params['public'] ? 1 : 0
		);

		$dbh = PDOQuery::getInstance();

		EventModel::getInstance()
			-> Broadcast('add_category', $record);

		return $dbh -> insert('1chan_category', $record);
	}

	/**
	 * Редактирование категории:
	 */
	public static function EditCategory($id, $params)
	{
		$record = array(
			'name'  => $params['name'],
			'title' => $params['title'],
			'description' => $params['description'],
			'code'  => $params['code'],
			'public' => $params['public'] ? 1 : 0
		);

		$dbh = PDOQuery::getInstance();

		EventModel::getInstance()
			-> Broadcast('edit_category', $record);

		return $dbh -> update('1chan_category', $record, 'id = '. $dbh -> q($id));
	}

	/**
	 * Удаление категории:
	 */
	public static function RemoveCategory($id)
	{
		$dbh = PDOQuery::getInstance();

		EventModel::getInstance()
			-> Broadcast('remove_category', $id);

		return $dbh -> delete('1chan_category', 'id = '. $id, 1);
	}

	/**
	 * Изменение счетчика постов категории:
	 */
	public static function CountCategory($id, $increment = true)
	{
	    if ($id)
        {
		    $dbh = PDOQuery::getInstance();
            $posts = $dbh -> select('1chan_post', 'COUNT(*)', 'hidden = 0 AND category = '. $dbh -> q($id));
		    $count = $posts[0]['COUNT(*)'];

		    if ($increment)
		        $count++;
		    else
		        $count--;

		    return $dbh -> update('1chan_category', array('posts' => $count), 'id = '. $dbh -> q($id), 1, true);
		}
		return false;
	}

	/**
	 * Получение списка категорий:
	 */
	public static function GetCategories()
	{
		$dbh = PDOQuery::getInstance();
		return $dbh -> select('1chan_category', '*', null, 'pos ASC');
	}

	/**
	 * Получение списка публичных категорий:
	 */
	public static function GetPublicCategories()
	{
		$dbh = PDOQuery::getInstance();
		return $dbh -> select('1chan_category', '*', 'public = 1', 'pos ASC');
	}

	/**
	 * Получение категории по ключу:
	 */
	public static function GetCategoryByCode($category_code)
	{
		$dbh = PDOQuery::getInstance();
		$category_code= self::getCodeFromString($category_code);
		$category = $dbh -> select('1chan_category', '*', 'code = '. $dbh -> q($category_code), null, 1);

		if ($category)
			return $category[0];
		else
			return false;
	}

	/**
	 * Получение категории по имени:
	 */
	public static function GetCategoryByName($name)
	{
		$dbh = PDOQuery::getInstance();
		$category = $dbh -> select('1chan_category', '*', 'name = '. $dbh -> q($name), null, 1);

		if ($category)
			return $category[0];
		else
			return false;
	}

	/**
	 * Получение категории по имени:
	 */
	public static function GetCategoryById($id)
	{
		$dbh = PDOQuery::getInstance();
		$category = $dbh -> select('1chan_category', '*', 'id = '. $dbh -> q($id), null, 1);

		if ($category)
			return $category[0];
		else
			return false;
	}

	/**
	 * Получение кода категории по идентификатору:
	 */
	public static function GetCategoryCodeById($id)
	{
		$dbh = PDOQuery::getInstance();
		$category = $dbh -> select('1chan_category', '*', 'id = '. $dbh -> q($id), null, 1);

		if ($category)
			return $category[0]['code'];
		else
			return false;
	}

	/**
	 * Получение id категории по коду:
	 */
	public static function GetCategoryIdByCode($code)
	{
		if (self::CategoryExists($code))
		{
			$dbh = PDOQuery::getInstance();
			$code = self::getCodeFromString($code);
			$category = $dbh -> select('1chan_category', '*', 'code = '. $dbh -> q($code), null, 1);

			if ($category)
				return $category[0]['id'];
			else
				return false;
		}
		return null;
	}

	/**
	 * Проверка существования категории по ключу:
	 */
	public static function CategoryExists($category_code)
	{
		$dbh = PDOQuery::getInstance();
		$category_code = self::getCodeFromString($category_code);
		$category = $dbh -> select('1chan_category', '*', 'code = '. $dbh -> q($category_code), null, 1, $count);
		return (bool)$count;
	}

	/**
	 * Сортировка категорий:
	 */
	public static function Resort($order)
	{
		$dbh = PDOQuery::getInstance();
		foreach ($order as $id => $posi)
		{
			$dbh -> update('1chan_category', array('pos' => $posi), 'id = '. $dbh -> q($id));
		}
		return true;
	}

	/**
	 * Получение кода из строки:
	 */
	public static function getCodeFromString($string)
	{
		if (preg_match_all("/<([^>]+)>/", $string, $matches))
		{
			return $matches[1][0];
		}

		return $string;
	}
}
