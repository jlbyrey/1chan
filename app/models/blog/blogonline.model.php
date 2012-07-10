<?php
/**
 * Модель ротатора ссылок:
 */
class Blog_BlogOnlineModel
{
	/**
	 * Функция получения категорий:
	 */
	public static function GetCategories()
	{
		$cache = KVS::getInstance();
		return unserialize($cache -> get(__CLASS__, null, 'categories'));
	}

	/**
	 * Функция получения категории по ссылке:
	 */
	public static function CheckCategory($link)
	{
		$categories = self::GetCategories();
		$filtered   = array('a', 'b', 's', 'vg', 'mu', 'tv');
		foreach ($categories as $category)
		{
			if (preg_match('#^'.$category['regexp'].'$#i', $link, $matches)) {
				return array(
					'title' => str_replace('$', $matches[1], $category['title']),
					'url'   => str_replace('$', $matches[1], $category['url']),
					'board' => $matches[1] ? (in_array($matches[1], $filtered) ? $matches[1] : 'other') : 'other',
				);
			}
		}

		return false;
	}

	/**
	 * Функция установки категорий:
	 */
	public static function SetCategories($categories)
	{
		$cache = KVS::getInstance();
		return $cache -> set(__CLASS__, null, 'categories', serialize($categories));
	}

	/**
	 * Функция добавления ссылки:
	 */
	public static function CreateLink($params)
	{
		$cache     = KVS::getInstance();
		$id        = $cache -> incr(__CLASS__, null, 'nextId');
		if (!ControlModel::CheckLinkfilter($params['link']) && (($category = self::CheckCategory($params['link'])) !== false))
		{
			$params['description'] = TexyHelper::typo($params['description']);

			$record = array(
				'id'          => $id,
				'link'        => preg_replace('/(#.*)$/i', '', $params['link']),
				'description' => $params['description'],
				'category'    => $category,
				'board'       => $category['board'],
				'clicks'      => 0,
				'visitors'    => array($_SERVER['REMOTE_ADDR'])
			);

			$cache -> set(__CLASS__, 'links', $id, serialize($record));
			$cache -> expire(__CLASS__, 'links', $id, 60 * 60 * 24); // expire after 24 hours

			$cache -> listAdd(__CLASS__, null, 'links', $id);
			$cache -> set(__CLASS__, null, 'lastUpdate', time());

			EventModel::getInstance()
				-> Broadcast('add_online_link', $record);

			return true;
		}

		return false;
	}

	/**
	 * Функция получения ссылок:
	 */
	public static function GetLinks($boards = true)
	{
		$cache   = KVS::getInstance();
		$links   = $cache -> listGet(__CLASS__, null, 'links');
		if (!$links) return array();

		$results = array();
		foreach($links as $link)
		{
			if (($current = unserialize($cache -> get(__CLASS__, 'links', $link))) != null)
			{
				if ($boards !== true)
				{
					if (in_array($current['board'], $boards))
						$results[] = $current;
				}
				else
					$results[] = $current;
			}
			else
			{
				$cache -> listRemove(__CLASS__, null, 'links', $link);
			}
		}

		return $results;
	}

	/**
	 * Функция получения ссылки по идентификатору:
	 */
	public static function GetLink($id)
	{
		$cache = KVS::getInstance();
		return unserialize($cache -> get(__CLASS__, 'links', $id));
	}

	/**
	 * Функция проверки участвует ли ссылка:
	 */
	public static function CheckLinkPosted($link_url)
	{
		$links = self::GetLinks();
		foreach ($links as $link)
		{
			if ($link['link'] == $link_url)
				return true;
		}

		return false;
	}

	/**
	 * Функция удаления ссылки:
	 */
	public static function RemoveLink($id)
	{
		$cache = KVS::getInstance();
		$cache -> listRemove(__CLASS__, null, 'links', $id);

		EventModel::getInstance()
			-> Broadcast('remove_online_link', $id);
	}

	/**
	 * Функция счетчика переходов:
	 */
	public static function Click($id)
	{
		$cache = KVS::getInstance();
		$link  = unserialize($cache -> get(__CLASS__, 'links', $id));

		if (!in_array($_SERVER['REMOTE_ADDR'], $link['visitors']))
		{
			$link['visitors'][] = $_SERVER['REMOTE_ADDR'];
			$link['clicks']++;

			$ttl = $cache -> lifetime(__CLASS__, 'links', $id);
			$cache -> set(__CLASS__, 'links', $id, serialize($link));
			$cache -> expire(__CLASS__, 'links', $id, $ttl);

			EventModel::getInstance()
				-> Broadcast('visit_online_link', array('id' => $id, 'clicks' => $link['clicks']));
		}

		return true;
	}
}

/**
 * Обработчики событий:
 */
EventModel::getInstance()
	-> AddEventListener('add_online_link', function($data) {
		$session = Session::getInstance();
		$session -> persistenceSet('last_live_date', time());

		unset($data['visitors']);

		EventModel::getInstance()
			-> ClientBroadcast(
				'live', 'add_online_link',
				$data
			)
			-> ClientBroadcast(
				'global', 'add_online_link'
			);
	})
	-> AddEventListener('remove_online_link', function($data) {
		EventModel::getInstance()
			-> ClientBroadcast(
				'live', 'remove_online_link', array('id' => $data)
			);
	})
	-> AddEventListener('visit_online_link', function($data) {
		EventModel::getInstance()
			-> ClientBroadcast(
				'live', 'visit_online_link', $data
			);
	});
