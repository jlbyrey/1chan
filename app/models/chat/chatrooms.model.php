<?php
/**
 * Модель комнат чата:
 */
class Chat_ChatRoomsModel
{
	/**
	 * Создание комнаты:
	 */
	public static function CreateRoom($params)
	{
		$cache = KVS::getInstance();

		$params['title']       = TexyHelper::typo($params['title']);
		$params['description'] = TexyHelper::typo($params['description']);

		$id = uniqid(md5(time()));

		$record = array(
			'room_id'     => $id,
			'alias'       => null,
			'title'       => $params['title'],
			'description' => $params['description'],
			'controlword' => $params['controlword'],
			'password'    => $params['password'],
			'public'      => $params['public']
		);

		$cache -> set(__CLASS__, $id, 'room', serialize($record));
		$cache -> set(__CLASS__, $id, 'update', time());
		$cache -> listAdd(__CLASS__, 'rooms', null, $id);

		EventModel::getInstance()
			-> Broadcast('add_room', $record);

		return $id;
	}

	/**
	 * Создание общей комнаты:
	 */
	public static function CreateCommonRoom()
	{
		$cache = KVS::getInstance();

		$id = substr(md5('common') . md5('common'), 0, 45);

		$record = array(
		    'room_id'     => $id,
            'title'       => 'Общая комната',
            'description' => 'Свободное общение',
            'password'    => '',
            'controlword' => 'x16S#21b',
            'public'      => true
        );

		$cache -> set(__CLASS__, $id, 'room', serialize($record));
		$cache -> set(__CLASS__, $id, 'update', time());
	}

	/**
	 * Получение избранных и остальных комнат:
	 */
	public static function GetRooms()
	{
		$cache = KVS::getInstance();
		$rooms = $cache -> listGet(__CLASS__, 'rooms', null);

		$session = Session::getInstance();
		$favorites = $session -> persistenceGet('chats_favorites', array());

		$result = array(
			'favorites' => array(),
			'public'    => array()
		);

		foreach($favorites as $id)
		{
			$data = unserialize($cache -> get(__CLASS__, $id, 'room'));
			if ($data)
				$result['favorites'][$id] = $data;
			else
				unset($favorites[$id]);
		}

		foreach($rooms as $id)
		{
			$update = $cache -> get(__CLASS__, $id, 'update');

			// Если время обновления слишком старое, удаляем:
			if ((time() - $update) > 60 * 60 * 48) {
				self::EditRoom($id, array('public' => false));
				continue;
			}

			// Если комната уже в списке избранного, пропускаем:
			if (in_array($id, $favorites)) continue;

			$data   = unserialize($cache -> get(__CLASS__, $id, 'room'));
			if ($data) {
				if ($data['public'])
					$result['public'][$id] = $data;
			} else
				$cache -> listRemove(__CLASS__, 'rooms', null, $id);
		}

		$session -> persistenceSet('chats_favorites', $favorites);
		shuffle($result['public']);

		return $result;
	}

	/**
	 * Получение всех комнат:
	 */
	public static function GetAllRooms()
	{
		$cache = KVS::getInstance();
		$rooms = $cache -> listGet(__CLASS__, 'rooms', null);

		$rooms_data = array();
		foreach($rooms as $id)
		{
			$data = unserialize($cache -> get(__CLASS__, $id, 'room'));
			if ($data)
				$rooms_data[$id] = $data;
			else
				$cache -> listRemove(__CLASS__, 'rooms', null, $id);
		}

		return $rooms_data;
	}

	/**
	 * Получение комнаты по ID:
	 */
	public static function GetRoom($id)
	{
		$cache = KVS::getInstance();
		$data = unserialize($cache -> get(__CLASS__, $id, 'room'));
		return $data;
	}

	/**
	 * Получение комнаты по Alias:
	 */
	public static function GetRoomByAlias($alias)
	{
		$cache = KVS::getInstance();
		$id    = $cache -> get(__CLASS__, 'alias', $alias);

		if ($id)
			return unserialize($cache -> get(__CLASS__, $id, 'room'));
		
		return false;
	}

	public static function SetAlias($id, $newAlias) {
		$cache = KVS::getInstance();
		if (!$cache -> exists(__CLASS__, 'alias', $newAlias))
		{
			$cache -> set(__CLASS__, 'alias', $newAlias, $id);
			return true;
		}
		return false;
	}

	public static function RemoveAlias($alias)
	{
		$cache = KVS::getInstance();
		$cache -> remove(__CLASS__, 'alias', $alias);
	}

	/**
	 * Редактирование комнаты:
	 */
	public static function EditRoom($id, $params)
	{
		$cache = KVS::getInstance();
		$room = self::GetRoom($id);
		if ($room) {
			$params = array_merge($room, $params);

			$params['title']       = TexyHelper::typo($params['title']);
			$params['description'] = TexyHelper::typo($params['description']);

			$record = array(
				'room_id'     => $id,
				'alias'       => $params['alias'],
				'title'       => $params['title'],
				'description' => $params['description'],
				'controlword' => $params['controlword'],
				'password'    => $params['password'],
				'public'      => $params['public']
			);

			$cache -> set(__CLASS__, $id, 'room', serialize($record));

			EventModel::getInstance()
				-> Broadcast('edit_room', $record);
		}
	}

	/**
	 * Обновление статуса обновления комнаты:
	 */
	public static function TouchRoom($id)
	{
		$cache = KVS::getInstance();
		$cache -> set(__CLASS__, $id, 'update', time());
	}

	/**
	 * Удаление комнаты:
	 */
	public static function RemoveRoom($id)
	{
		$cache = KVS::getInstance();
		$room  = self::GetRoom($id);

		$cache -> listRemove(__CLASS__, 'rooms', null, $id);
		$cache -> remove(__CLASS__, $id, 'room');
		$cache -> remove(__CLASS__, $id, 'info');
		$cache -> remove('Chat_ChatModel', Chat_ChatModel::GetChatChannel($id, $room['password']), 'log');

		EventModel::getInstance()
			-> Broadcast('remove_room', $id);
	}

	/**
	 * Обновление избранного:
	 */
	public static function UpdateFavorites($favorites)
	{
		$session = Session::getInstance();
		$session -> persistenceSet('chats_favorites', $favorites);
	}

	/**
	 * Получение количества участников в комнате:
	 */
	public static function GetRoomOnline($id)
	{
		$cache = KVS::getInstance();
		$stats = unserialize($cache -> get(__CLASS__, $id, 'stats'));

        foreach($stats['visitors'] as $ip => $time)
		{
		    if ((time() - $time) > 60 * 4)
			{
				$stats['online']--;
			}
		}

		return $stats['online'];
	}

	/**
	 * Установка количества участников в комнате:
	 */
	public static function SetRoomOnline($id)
	{
		$ip    = $_SERVER['REMOTE_ADDR'];
		$cache = KVS::getInstance();

		if ($cache -> exists(__CLASS__, $id, 'stats'))
			$stats = unserialize($cache -> get(__CLASS__, $id, 'stats'));
		else
			$stats = array('online' => 0, 'visitors' => array());

	    $online_cache = $stats['online'];

		foreach($stats['visitors'] as $is => $time)
		{
			if ((time() - $time) > 60 * 4)
			{
				unset($stats['visitors'][$is]);
				$stats['online']--;
			}
		}

		if (!array_key_exists($ip, $stats['visitors']))
		{
			$stats['visitors'][$ip] = time();
			$stats['online']++;
		}

		$cache -> set(__CLASS__, $id, 'stats', serialize($stats));

        if ($stats['online'] != $online_cache)
		    EventModel::getInstance()
			    -> Broadcast('room_stats_updated', array('room_id' => $id, 'stats' => $stats));

		return true;
	}

	

    /**
     * Установка "инфорации" канала:
     */
    public static function SetInfo($id, $message)
    {
		$cache   = KVS::getInstance();
        $message = TexyHelper::markup($message, true, false);

        $cache -> set(__CLASS__, $id, 'info', $message);

        return $message;
    }

    /**
     * Получение "информации" канала:
     */
    public static function GetInfo($id)
    {
		$cache   = KVS::getInstance();
		return $cache -> get(__CLASS__, $id, 'info');
    }
}

/**
 * Обработчики событий:
 */
EventModel::getInstance()
	/**
	 * Добавление комнаты:
	 */
	-> AddEventListener('add_room', function($data) {
	    if ($data['public'] == true)
	        EventModel::getInstance()
			    -> ClientBroadcast(
				    'chats', 'add_room'
			    );
	})
	-> AddEventListener('edit_room', function($data) {
	    EventModel::getInstance()
			-> ClientBroadcast(
		       'chat_'. $data['room_id'], 'edit_room', array(
		            'room_id'     => $data['room_id'],
		            'title'       => $data['title'],
		            'description' => $data['description'],
		            'public'      => $data['public'],
		            'password'    => (bool)$data['password']
		        )
	        )
			-> ClientBroadcast(
		       'chats', 'edit_room', array(
		            'room_id'     => $data['room_id'],
		            'title'       => $data['title'],
		            'description' => $data['description'],
		            'public'      => $data['public'],
		            'password'    => (bool)$data['password']
		        )
	        );
	})
	-> AddEventListener('room_stats_updated', function($data) {
	    EventModel::getInstance()
			-> ClientBroadcast(
		       'chat_'. $data['room_id'], 'stats_updated', array(
		            'room_id' => $data['room_id'],
		            'online'  => $data['stats']['online']
		        )
	        )
			-> ClientBroadcast(
		       'chats', 'stats_updated_room', array(
		            'room_id' => $data['room_id'],
		            'online'  => $data['stats']['online']
		        )
	        );
	});
