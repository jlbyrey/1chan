<?php
/**
 * Модель чата:
 */
class Chat_ChatModel
{
    /**
     * Соль генерации скрытых каналов:
     */
    private static $SALT = 'salt1293921';

    /**
     * Проверка блокировки в канале:
     */
    public static function hasVoice($id, $ip)
    {
		$cache = KVS::getInstance();
		$devoice_list = unserialize($cache -> get(__CLASS__, $id, 'devoiced'));
		return !in_array($ip, $devoice_list);
    }

    /**
     * Блокировка пользователя:
     */
    public static function deVoice($id, $message_id)
    {
		$cache = KVS::getInstance();
        if (($ip = $cache -> get(__CLASS__, $id, $message_id .':ip')) !== false) {
            $devoice_list = unserialize($cache -> get(__CLASS__, $id, 'devoiced'));
            $key = md5(self::$SALT . $ip);

            if (!is_array($devoice_list))      $devoice_list = array();
            if (!in_array($ip, $devoice_list)) $devoice_list[md5(self::$SALT . $ip)] = $ip;

            $cache -> set(__CLASS__, $id, 'devoiced', serialize($devoice_list));
            return $key;
        }
        return false;
    }

    /**
     * Разблокировка пользователя:
     */
    public static function enVoice($id, $key)
    {
		$cache = KVS::getInstance();
		$devoice_list = unserialize($cache -> get(__CLASS__, $id, 'devoiced'));
		unset($devoice_list[$key]);
        $cache -> set(__CLASS__, $id, 'devoiced', serialize($devoice_list));
		return true;
    }

    /**
     * Очистка войс листа:
     */
    public static function clearVoice($id)
    {
		$cache = KVS::getInstance();
		$cache -> remove(__CLASS__, $id, 'devoiced');
		return true;
    }

    /**
     * Добавление слова в блоклист:
     */
     public static function blockWord($id, $blocked_word)
     {
         $kvs = KVS::getInstance();
         if ($kvs -> exists(__CLASS__, $id, 'blockwords'))
		$blockwords = unserialize($kvs -> get(__CLASS__, $id, 'blockwords'));
	 else
		$blockwords = array();

	$blockwords[] = $blocked_word;

	$kvs -> set(__CLASS__, $id, 'blockwords', serialize($blockwords));
	return true;
     }

    /**
     * Получение слов в блеклисте:
     */
    public static function getBlockList($id)
    {
	$kvs = KVS::getInstance();
         if ($kvs -> exists(__CLASS__, $id, 'blockwords'))
		return unserialize($kvs -> get(__CLASS__, $id, 'blockwords'));
	return array();
    }

    /**
     * Удаление слова из блоклиста:
     */
     public static function unblockWord($id, $blocked_word_id)
     {
         $kvs = KVS::getInstance();
         if ($kvs -> exists(__CLASS__, $id, 'blockwords'))
		$blockwords = unserialize($kvs -> get(__CLASS__, $id, 'blockwords'));
	 else
		return false;

	array_splice($blockwords, $blocked_word_id, 1);
	$kvs -> set(__CLASS__, $id, 'blockwords', serialize($blockwords));
	return true;
     }

    /**
     * Проверка пароля (и получение id канала):
     */
    public static function TestChatChannel($id, $password = false)
    {
		$cache = KVS::getInstance();
        if (($room = unserialize($cache -> get('Chat_ChatRoomsModel', $id, 'room'))) != false)
        {
            if ($room['password'] && $password !== false)
            {
                if ($room['password'] == $password)
                    return self::GetChatChannel($room['room_id'], $password);

                return false;
            }
            elseif ($room['password'])
                return false;
            else
                return $room['room_id'];
        }
        return null;
    }

    /**
     * Получение id канала:
     */
    public static function GetChatChannel($id, $password = false)
    {
        if ($password == false)
            return $id;

        return md5(self::$SALT . $id . $password);
    }

    /**
     * Отправка сообщения в канал:
     */
    public static function AddMessage($id, $message, $password = false)
    {
		$cache = KVS::getInstance();
		$session = Session::getInstance();
        $channel = self::GetChatChannel($id, $password);

        $message_id = $cache -> incr(__CLASS__, $id, 'nextId');
        $message    = TexyHelper::markup($message, !$session -> isAdminSession(), false);
        $date       = time();

        $cache -> set(__CLASS__, $id, $message_id .':ip', $_SERVER['REMOTE_ADDR']);
        $cache -> expire(__CLASS__, $id, $message_id .':ip', 60 * 5);

        EventModel::getInstance()
            -> ClientBroadcast('chat_'. $channel, 'message', array('type' => 'normal', 'id' => $message_id, 'message' => $message, 'date' => date('H:i:s', $date)));

        /**
         * Отправка сообщения администратору:
         */
        if ($id == '475266264b1e9696e0370039311f83ee4cee8d413c47c')
        {
        	JabberBot::send(
					'-=# '. '>>'. $message_id ."\n". strip_tags($message)
			);
        }

        self::LogMessage($channel, $message, $message_id, $date);

        return $message_id;
    }

    /**
     * Отправка сообщения ошибки в канал:
     */
    public static function AddErrorMessage($id, $message, $password = false)
    {
        $channel = self::GetChatChannel($id, $password);

        EventModel::getInstance()
            -> ClientBroadcast('chat_'. $channel, 'message', array('type' => 'error', 'message' => $message));

        return true;
    }

    /**
     * Отправка информационного сообщения в канал:
     */
    public static function AddInfoMessage($id, $message, $password = false)
    {
        $channel = self::GetChatChannel($id, $password);

        EventModel::getInstance()
            -> ClientBroadcast('chat_'. $channel, 'message', array('type' => 'info', 'message' => $message));

        return true;
    }

    /**
     * Логирование сообщения:
     */
    public static function LogMessage($channel, $message, $message_id, $date)
    {
        $cache = KVS::getInstance();
        $log_text = '<div class="b-chat_b-message m-normal">
							<a href="#" class="b-chat_b-message_b-link" title="'.$message_id.'">№'.$message_id.'</a>: <em class="b-chat_b-message_b-date">'.date('H:i:s d.m.Y', $date).'</em>
							<div class="b-chat_b-message_b-body">
								<p>
									'.$message.'
								</p>
							</div>
					 </div>';

		$cache -> listAdd(__CLASS__, $channel, 'log', $log_text);
		$cache -> listTrim(__CLASS__, $channel, 'log', 0, 1000);
    }

    /**
     * Получение лога:
     */
    public static function GetLog($channel)
    {
        $cache = KVS::getInstance();
        $log = $cache -> listGet(__CLASS__, $channel, 'log');
        return implode("\n", $log);
    }
}
