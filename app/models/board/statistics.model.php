<?php
/**
 * Модель, отвечающая за функционал статистики разделов:
 */
class Board_StatisticsModel
{
	/**
	 * Статистика просмотров поста:
	 */
	public static function updatePostStats($board, $id, $writing = false)
	{
		$ip    = $_SERVER['REMOTE_ADDR'];
		$cache = KVS::getInstance();

		if ($cache -> exists(__CLASS__, $board.':'.$id, 'stats'))
			$stats = unserialize($cache -> get(__CLASS__, $board.':'.$id, 'stats'));
		else
			$stats = array('online' => array(), 'writers' => array());


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

		$cache -> set(__CLASS__, $board.':'.$id, 'stats', serialize($stats));

		EventModel::getInstance()
			-> Broadcast('board_stats_updated', array($board, $id));

		return true;
	}

	/**
	 * Статистика поста:
	 */
	public static function getPostStats($board, $id)
	{
		$cache = KVS::getInstance();
		$stats = unserialize($cache -> get(__CLASS__, $board.':'.$id, 'stats'));

		return array(
			'online'  => max(1, sizeof($stats['online']) == 3 ? 3.5 : sizeof($stats['online'])),
			'writers' => sizeof($stats['writers'])
		);
	}
}

/**
 * Обработчики событий:
 */
EventModel::getInstance()
	/**
	 * Обновление статистики поста:
	 */
	-> AddEventListener('board_stats_updated', function($data) {
		$stats = Board_StatisticsModel::getPostStats($data[0], $data[1]);

		EventModel::getInstance()
			-> ClientBroadcast(
				'boardpost_'. $data[0] .'_'. $data[1], 'stats_updated',
				array(
					'online'  => $stats['online'],
					'writers' => $stats['writers']
				)
			);
	});
