<?php
/**
 * Модель статистики постинга:
 */
class Blog_BlogStatisticsModel
{
	/**
	 * Глобальная статистика посещений:
	 */
	public static function updateGlobalVisitors()
	{
		$ip     = $_SERVER['REMOTE_ADDR'];
		$cache  = KVS::getInstance();
		$update = false;

		if ($cache -> exists(__CLASS__, null, 'global_stats'))
			$stats = unserialize($cache -> get(__CLASS__, null, 'global_stats'));
		else
			$stats = array('set_date' => date("d"), 'posts' => 0, 'posts_array' => array(), 'unique' => array(), 'unique_posters' => array(), 'online' => array());

		/**
		 * Скидываем при смене дня:
		 */
		if ($stats['set_date'] != date("d")) {
			$stats  = array('set_date' => date("d"), 'posts' => 0, 'posts_array' => array(), 'unique' => array(), 'unique_posters' => array(), 'online' => array());
			$update = true;
		}

		if (!array_key_exists($ip, $stats['unique'])) {
			$stats['unique'][$ip] = time();
			$update = true;
		}

		if (!array_key_exists($ip, $stats['online']))
			$update = true;

		$stats['online'][$ip] = time();
		$stats['online'] = array_filter($stats['online'], function($value) {
			if ((time() - $value) < 60 * 2)
				return true;

			global $update;
			$update = true;

			return false;
		});

		$cache -> set(__CLASS__, null, 'global_stats', serialize($stats));

		if ($update) {
			EventModel::getInstance()
				-> Broadcast('global_stats_updated');
		}

		return true;
	}

	/**
	 * Глобальная статистика постинга:
	 */
	public static function updateGlobalPosting()
	{
		$ip    = $_SERVER['REMOTE_ADDR'];
		$cache = KVS::getInstance();
		$stats = unserialize($cache -> get(__CLASS__, null, 'global_stats'));

		if (!array_key_exists('unique_posters', $stats))
			$stats['unique_posters'] = array();

		if (!array_key_exists($ip, $stats['unique_posters']))
			$stats['unique_posters'][$ip] = time();

		$stats['posts_array'][] = time();
		$stats['posts']++;

		$stats['posts_array'] = array_filter($stats['posts_array'], function($value) {
			return (time() - $value) < 60 * 60;
		});

		$cache -> set(__CLASS__, null, 'global_stats', serialize($stats));

		EventModel::getInstance()
			-> Broadcast('global_stats_updated');

		return true;
	}

	/**
	 * Статистика просмотров поста:
	 */
	public static function updatePostStats($id, $writing = false)
	{
		$ip    = $_SERVER['REMOTE_ADDR'];
		$cache = KVS::getInstance();

		if ($cache -> exists(__CLASS__, $id, 'stats'))
			$stats = unserialize($cache -> get(__CLASS__, $id, 'stats'));
		else
			$stats = array('online' => array(), 'unique' => array(), 'writers' => array());


		if (!array_key_exists($ip, $stats['unique']))
			$stats['unique'][$ip] = time();

		$stats['online'][$ip] = time();
		$stats['online'] = array_filter($stats['online'], function($value) {
			return (time() - $value) < 60 * 2;
		});

		if (array_key_exists($ip, $stats['writers']) && !$writing)
			unset($stats['writers'][$ip]);
		elseif ($writing)
			$stats['writers'][$ip] = time();

		foreach ($stats['writers'] as $ip => $time)
			if (time() - $time > 20)
				unset($stats['writers'][$ip]);

		$cache -> set(__CLASS__, $id, 'stats', serialize($stats));

		EventModel::getInstance()
			-> Broadcast('post_stats_updated', $id);

		return true;
	}

	/**
	 * Глобальная статистика:
	 */
	public static function getGlobalStats()
	{
		$cache = KVS::getInstance();
		$stats = unserialize($cache -> get(__CLASS__, null, 'global_stats'));

		return array(
			'unique' => sizeof($stats['unique']),
			'online' => sizeof($stats['online']) == 3 ? 3.5 : sizeof($stats['online']),
			'unique_posters' => sizeof($stats['unique_posters']),
			'posts'  => @$stats['posts'],
			'speed'  => sizeof($stats['posts_array'])
		);
	}

	/**
	 * Статистика поста:
	 */
	public static function getPostStats($id)
	{
		$cache = KVS::getInstance();
		$stats = unserialize($cache -> get(__CLASS__, $id, 'stats'));

		return array(
			'online'  => sizeof($stats['online']) == 3 ? 3.5 : sizeof($stats['online']),
			'writers' => sizeof($stats['writers']),
			'unique'  => sizeof($stats['unique'])
		);
	}
}

/**
 * Обработчики событий:
 */
EventModel::getInstance()
	/**
	 * Обновление глобальной статистики:
	 */
	-> AddEventListener('global_stats_updated', function() {})
	/**
	 * Обновление статистики поста:
	 */
	-> AddEventListener('post_stats_updated', function($id) {
		$stats = Blog_BlogStatisticsModel::getPostStats($id);

		EventModel::getInstance()
			-> ClientBroadcast(
				'post_'. $id, 'stats_updated',
				array(
					'online'  => $stats['online'],
					'writers' => $stats['writers'],
					'unique'  => $stats['unique']
				)
			);
	});
