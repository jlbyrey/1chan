<?php
/**
 * Модель, отвечающая за функционал разделов:
 */
class Board_BoardModel
{
	/**
	 * Константы разделов:
	 */
	const BUMP_LIMIT  = 500; // posts
	const MAX_THREADS = 420;

	/**
	 * Приватные свойства раздела:
	 */
	private $board    = null;
	private $settings = array();

	/**
	 * Создание объекта раздела:
	 */
	public function __construct($id)
	{
		$kvs = KVS::getInstance();
		$this -> board = $id;

		if ($kvs -> exists(__CLASS__, $id))
			$this -> settings = unserialize($kvs -> get(__CLASS__, $id));
	}

	/**
	 * Получение идентификатора:
	 */
	public function getId()
	{
		return $this -> board;
	}

	/**
	 * Получение свойств разделов:
	 */
	public function getSettings()
	{
		if (!empty($this -> settings))
			return $this -> settings;

		$kvs = KVS::getInstance();
		if ($kvs -> exists(__CLASS__, $this -> board))
			return unserialize($kvs -> get(__CLASS__, $this -> board));
	}

	/**
	 * Установка свойств разделов:
	 */
	public function setSettings($settings = array())
	{
		$kvs = KVS::getInstance();
		$this -> settings = $settings;
		$kvs -> set(__CLASS__, $this -> board, null, serialize($settings));
	}


	/**
	 * Создание треда:
	 */
	public function createThread($params)
	{
		$session = Session::getInstance();
		$kvs       = KVS::getInstance();
		$id   = $kvs -> incr(__CLASS__, $this -> board, 'nextPostId');

		// Обрабатываем тайтл и текст:
		$params['title'] = TexyHelper::typo($params['title']);
		$params['text'] = TexyHelper::markup($params['text'], !$session -> isAdminSession(), true, $this -> board);

		// Обрабатываем загрузку (upload):
		$upload = new Board_UploadModel();
		$upload -> process($this -> board);

		// Составляем запись:
		$record = array(
			'id'                => $id,
			'ip'                => $_SERVER['REMOTE_ADDR'],
			'board_id'    => $this -> board,
			'parent_id'   => null,
			'created_at' => time(),
			'upload'	     => $upload -> getData(),
			'title'            => $params['title'],
			'text'            => $params['text'],
			'password'   => $params['password']
		);

        if ($this -> board == 'int')
            $record['country'] = strtolower(geoip_country_code_by_name($_SERVER['REMOTE_ADDR']));
		
		if (HomeBoardHelper::existsBoard($params['homeboard']))
			$record['author'] = $params['homeboard'];
		else
			$record['author'] = 'anonymous';

		// Записываем созданную запись:
		$kvs -> hashSet(__CLASS__, $this -> board, 'posts', $id, serialize($record));

		// Создаем запись в сортированном списке тредов:
		$kvs -> sortedListAdd(__CLASS__, $this -> board, 'timeline', $id, time());

		if ($this -> board != 'alone' && $this -> board != 'int') {
			$kvs -> listAdd(__CLASS__, null, 'lastPosts', $this -> board .':'. $id);
			$kvs -> listTrim(__CLASS__, null, 'lastPosts', 0, 1000);
		}

		// Количество ответов в треде:
		$kvs -> set(__CLASS__, $this -> board, 'thread('. $id .')_count', 0);

		// Нужно взять последний элемент сортированного списка и удалить последний нахуй:
		if ($kvs -> sortedListLength(__CLASS__, $this -> board, 'timeline') > self::MAX_THREADS)
		{
			$oldest_ids = $kvs -> sortedListGet(__CLASS__, $this -> board, 'timeline', self::MAX_THREADS);

			foreach($oldest_ids as $old_id)
				$this -> removeThread($old_id);
		}
		
		$kvs -> remove(__CLASS__, $this -> board, '0pagecache');

		// Объявляем о создании записи:
		EventModel::getInstance()
			-> Broadcast('add_board_thread', $record);

		return $id;
	}

	/**
	 * Проверка на существование треда:
	 */
	public function existsThread($id)
	{
		$kvs = KVS::getInstance();
		return $kvs -> exists(__CLASS__, $this -> board, 'thread('. $id .')_count');
	}

	/**
	 * Обновление (редактирование) треда:
	 */
	public function updateThread($id, $params)
	{
		$kvs = KVS::getInstance();

		if ($this -> existsThread($id))
			return $this -> updatePost($id, $params);

		return false;
	}

	/**
	 * Удаление треда:
	 */
	public function removeThread($id)
	{
		$kvs = KVS::getInstance();

		// Удаляем тред из списка тредов:
		$kvs -> sortedListRemove(__CLASS__, $this -> board, 'timeline', $id);

		// Получаем список всех постов треда:
		$posts = $kvs -> listGet(__CLASS__, $this -> board, 'thread('. $id .')');

		$kvs -> listRemove(__CLASS__, null, 'lastPosts', $this -> board .':'. $id);

		// Удаляем пост треда:
		$this -> removePost($id, true);

		// Удаляем все ответы треда:
		foreach ($posts as $post_id)
			$this -> removePost($post_id, true);

		// Удаляем всю инфу о постах треда:
		$kvs -> remove(__CLASS__, $this -> board, 'thread('. $id .')');
		$kvs -> remove(__CLASS__, $this -> board, 'thread('. $id .')_count');
		$kvs -> remove(__CLASS__, $this -> board, 'thread('. $id .')_tail');
		$kvs -> remove(__CLASS__, $this -> board, '0pagecache');
		$kvs -> hashRemove('Board_Thread', 'Subscribers', $id);

		EventModel::getInstance()
			-> Broadcast('remove_board_thread', array($this -> board, $id));

		return true;
	}

	/**
	 * Созадние поста (ответа):
	 */
	public function createPost($thread_id, $params)
	{
		if ($this -> existsThread($thread_id))
		{
			$session = Session::getInstance();
			$kvs       = KVS::getInstance();
			$id         = $kvs -> incr(__CLASS__, $this -> board, 'nextPostId');

			// Обрабатываем тайтл и текст:
			$params['text'] = TexyHelper::markup($params['text'], !$session -> isAdminSession(), true, $this -> board);

			// Обрабатываем загрузку (upload):
			$upload = new Board_UploadModel();
			$upload -> process($this -> board);

			// Составляем запись:
			$record = array(
				'id'                => $id,
				'ip'                => $_SERVER['REMOTE_ADDR'],
				'board_id'    => $this -> board,
				'parent_id'   => $thread_id,
				'created_at' => time(),
				'upload'	     => $upload -> getData(),
				'title'            => '',
				'text'            => $params['text']
			);

            if ($this -> board == 'int')
                $record['country'] = strtolower(geoip_country_code_by_name($_SERVER['REMOTE_ADDR']));

			if (HomeBoardHelper::existsBoard($params['homeboard']))
				$record['author'] = $params['homeboard'];
			else
				$record['author'] = 'anonymous';

			// Записываем созданную запись:
			$kvs -> hashSet(__CLASS__, $this -> board, 'posts', $id, serialize($record));

			// Добавляем ответ в тред:
			$kvs -> listAdd(__CLASS__, $this -> board, 'thread('. $thread_id .')', $id);

			if ($this -> board != 'alone' && $this -> board != 'int') {
				$kvs -> listAdd(__CLASS__, null, 'lastPosts', $this -> board .':'. $id);
				$kvs -> listTrim(__CLASS__, null, 'lastPosts', 0, 1000);
			}

			// Обновляем количество ответов в треде:
			$post_count = $kvs -> incr(__CLASS__, $this -> board, 'thread('. $thread_id .')_count');

			// Добавляем сообщения в хвост:
			$tail = unserialize($kvs -> get(__CLASS__, $this -> board, 'thread('. $thread_id .')_tail'));

			if (empty($tail))     $tail = array();
			if (sizeof($tail) >= 5) array_shift($tail);
			array_push($tail, $record);

			$kvs -> set(__CLASS__, $this -> board, 'thread('. $thread_id .')_tail', serialize($tail));

			// Обновляем положение треда в списке, если это нужно и возможно:
			if ($post_count <= self::BUMP_LIMIT && !preg_match('/\bsage\b/i', $params['text']))
				$kvs -> sortedListAdd(__CLASS__, $this -> board, 'timeline', $thread_id, time());
			
			$kvs -> remove(__CLASS__, $this -> board, '0pagecache');
			
			// Объявляем о создании записи:
			$record['num'] = $post_count;
			EventModel::getInstance()
				-> Broadcast('add_board_post', $record);

			return $id;
		}
		return false;
	}

	/**
	 * Проверка на существование:
	 */
	public function existsPost($id)
	{
		$kvs = KVS::getInstance();
		return $kvs -> hashExists(__CLASS__, $this -> board, 'posts', $id);
	}

	/**
	 * Обновление (редактировние) ответа:
	 */
	public function updatePost($id, $params)
	{
		$kvs = KVS::getInstance();

		if ($this -> existsPost($id))
		{
			// Получаем данные поста:
			$post = $this -> getPost($id);

			// Удаление приложения:
			if ($params['upload'] == false)
			{
				$upload = new Board_UploadModel($post['upload']);

				if ($upload -> exists())
					$upload -> remove();
			}

			// Обновляем данные поста:
			$record = $post + $params;

			// Сохраняем данные:
			$kvs -> hashSet(__CLASS__, $this -> board, 'posts', $id, serialize($record));

			return true;
		}

		return false;
	}

    /**
     * Удаление всех ответов от автора:
     */
    public function removePostsByAuthor($id)
    {
        $kvs = KVS::getInstance();

		if ($this -> existsPost($id))
		{
            // Get post:
            $post   = $this -> getPost($id);
            
            // Get thread:
            $thread = $this -> getThread($post['parent_id'], true);

            foreach($thread['posts'] as $tpost)
            {
                if ($tpost['ip'] == $post['ip'])
                    $this -> removePost($tpost['id']);
            }
        }
    }

	/**
	 * Удаление ответа:
	 */
	public function removePost($id, $auto = false)
	{
		$kvs = KVS::getInstance();

		if ($this -> existsPost($id))
		{
			// Получаем пост:
			$post = $this -> getPost($id);

            if (!$auto) {
                if ($kvs -> exists('ControlModel', 'timeblock', $post['ip']))
			    {
				    $kvs -> set('ControlModel', 'timeban', $post['ip'], true);
				    $kvs -> expire('ControlModel', 'timeban', $post['ip'], 60 * 60);
			    }

			    if ($kvs -> exists('ControlModel', 'timeban', $post['ip']))
			    {
				    $life = $kvs -> lifetime('ControlModel', 'timeban', $post['ip']);
				    $kvs -> expire('ControlModel', 'timeban', $post['ip'], $life + (60 * 60));
			    }

			    $kvs -> set('ControlModel', 'timeblock', $post['ip'], true);
			    $kvs -> expire('ControlModel', 'timeblock', $post['ip'], 60 * 60);
            }

			// Удаляем пост из треда:
			if ($post['parent_id'] && $this -> existsThread($post['parent_id']))
			{
				$kvs -> listRemove(__CLASS__, $this -> board, 'thread('. $post['parent_id'] .')', $id);
				$kvs -> decr(__CLASS__, $this -> board, 'thread('. $post['parent_id'] .')_count');
			}

			$upload = new Board_UploadModel($post['upload']);

			if ($upload -> exists())
				$upload -> remove();

			// Удаляем данные поста:
			$kvs -> hashRemove(__CLASS__, $this -> board, 'posts', $id);

			// update post's tail:
			$tail_ids = $kvs -> listGet(__CLASS__, $this -> board, 'thread('. $post['parent_id'] .')', 0, 2);

			$tail = $this -> getPosts(array_reverse($tail_ids));
			$kvs -> set(__CLASS__, $this -> board, 'thread('. $post['parent_id'] .')_tail', serialize($tail));
			$kvs -> remove(__CLASS__, $this -> board, '0pagecache');
			$kvs -> listRemove(__CLASS__, null, 'lastPosts', $this -> board .':'. $id);
			
			EventModel::getInstance()
				-> Broadcast('remove_board_post', $post);

			return true;
		}

		return false;
	}


	/**
	 * Получение нескольких тредов (статика):
	 */
	public static function getBulkThreads(&$full_ids)
	{
		$board = new Board_BoardModel('');
		$result = array();

		foreach($full_ids as $key => $full_id)
		{
			list($board_id, $id) = $full_id;
			$board -> board = $board_id;

			$thread   = $board -> getThread($id, false);
			$settings = $board -> getSettings();

			if ($thread === false)
			{
				unset($full_ids[$key]);
				continue;
			}

			$thread['board_title'] = $settings['title'];
			$result[] = $thread;
		}

		return $result;
	}

	/**
	 * Получение последних постов:
	 */
	public static function getLastPosts($page = 0, &$pages)
	{
		$kvs = KVS::getInstance();

		$start = $page * 50;
		$end   = $start + 49;

		$posts  = $kvs -> listGet(__CLASS__, null, 'lastPosts', $start, $end);
		$length = $kvs -> listLength(__CLASS__, null, 'lastPosts');
		$pages  = ceil($length / 50);

		$board = new Board_BoardModel('');
		$result = array();

		foreach($posts as $post) {
			list($board_id, $id) = explode(':', $post);
			$board -> board = $board_id;

			$post     = $board -> getPost($id);
			$settings = $board -> getSettings();

			$post['board_title'] = $settings['title'];
			$result[] = $post;
		}

		return $result;
	}


	/**
	 * Получение страницы тредов:
	 */
	public function getThreads($page = 0, &$pages)
	{
		$kvs = KVS::getInstance();

		if ($page == 0 && $kvs -> exists(__CLASS__, $this -> board, '0pagecache'))
		{
			$length   = $kvs -> sortedListLength(__CLASS__, $this -> board, 'timeline');
			$pages    = ceil($length / 30);
			return unserialize($kvs -> get(__CLASS__, $this -> board, '0pagecache'));
		}

		$start = $page * 30;
		$end  = $start + 29;

		// Получаем список тредов и количество страниц:
		$threads = $kvs -> sortedListGet(__CLASS__, $this -> board, 'timeline', $start, $end);
		$length   = $kvs -> sortedListLength(__CLASS__, $this -> board, 'timeline');
		$pages    = ceil($length / 30);

		if ($length > 0)
		{
			$posts = $this -> getPosts($threads);

			foreach ($posts as &$thread)
			{
				if ($thread)
				{
					$thread['posts'] = unserialize($kvs -> get(__CLASS__, $this -> board, 'thread('. $thread['id'] .')_tail'));
					$thread['count'] = (int)$kvs -> get(__CLASS__, $this -> board, 'thread('. $thread['id'] .')_count');
				}
			}

			if ($page == 0)
				$kvs -> set(__CLASS__, $this -> board, '0pagecache', serialize($posts));

			return $posts;
		}

		return false;
	}

	/**
	 * Получение одного треда:
	 */
	public function getThread($id, $full = true)
	{
		$kvs = KVS::getInstance();

		if ($this -> existsThread($id))
		{
			if ($full)
			{
				$post_ids             = $kvs -> listGet(__CLASS__, $this -> board, 'thread('. $id .')');
				$thread               = $this -> getPost($id);
				$thread['posts'] = $this -> getPosts(array_reverse($post_ids));
				$thread['count'] = (int)$kvs -> get(__CLASS__, $this -> board, 'thread('. $id .')_count');

				return $thread;
			}
			else
			{
				$thread = $this -> getPost($id);

				if ($thread)
				{
					$thread['posts'] = unserialize($kvs -> get(__CLASS__, $this -> board, 'thread('. $thread['id'] .')_tail'));
					$thread['count'] = (int)$kvs -> get(__CLASS__, $this -> board, 'thread('. $thread['id'] .')_count');
				}

				return $thread;
			}
		}

		return false;
	}

	/**
	 * Получение списка постов:
	 */
	public function getPosts($ids)
	{
		$kvs    = KVS::getInstance();
		$posts = array();

		foreach($ids as $id)
		{
			$posts[] = $this -> getPost($id);
		}

		return $posts;
	}

	/**
	 * Получение поста:
	 */
	public function getPost($id)
	{
		$kvs = KVS::getInstance();

		if ($this -> existsPost($id))
		{
			$post = $kvs -> hashGet(__CLASS__, $this -> board, 'posts', $id);
			return unserialize($post);
		}
	}

	/**
	 * Удаление раздела:
	 */
	public function removeBoard()
	{
		do {
			$threads = $this -> getThreads(0, $pages_full);

			foreach($threads as $thread)
				$this -> removeThread($thread['id']);
		} 
		while($pages_full !== 0);

		return true;
	}

	/**
	 * Подписаться на раздел:
	 */
	public static function subscribeBoard($s_id, $board)
	{
		$kvs = KVS::getInstance();
		$kvs -> hashSet('Board', 'Subscribers', $board, $s_id, time());
	}

	/**
	 * Проверить подписку на раздел:
	 */
	public static function subscribedBoard($s_id, $board)
	{
		$kvs = KVS::getInstance();
		return $kvs -> hashExists('Board', 'Subscribers', $board, $s_id);
	}

	/**
	 * Удалить подписку на раздел:
	 */
	public static function unsubscribeBoard($s_id, $board)
	{
		$kvs = KVS::getInstance();
		return $kvs -> hashRemove('Board', 'Subscribers', $board, $s_id);
	}

	/**
	 * Получить список подписанных на раздел:
	 */
	public static function subscribersBoard($board)
	{
		$kvs = KVS::getInstance();
		return $kvs -> hashGetKeys('Board', 'Subscribers', $board);
	}

	/**
	 * Подписаться на тред:
	 */
	public static function subscribeThread($s_id, $thread)
	{
		$kvs = KVS::getInstance();
		$kvs -> hashSet('Board_Thread', 'Subscribers', $thread, $s_id, time());
	}

	/**
	 * Проверить подписку на тред:
	 */
	public static function subscribedThread($s_id, $thread)
	{
		$kvs = KVS::getInstance();
		return $kvs -> hashExists('Board_Thread', 'Subscribers', $thread, $s_id);
	}

	/**
	 * Удалить подписку на тред:
	 */
	public static function unsubscribeThread($s_id, $thread)
	{
		$kvs = KVS::getInstance();
		return $kvs -> hashRemove('Board_Thread', 'Subscribers', $thread, $s_id);
	}

	/**
	 * Получить список подписанных на тред:
	 */
	public static function subscribersThread($thread)
	{
		$kvs = KVS::getInstance();
		return $kvs -> hashGetKeys('Board_Thread', 'Subscribers', $thread);
	}
}

/**
 * Обработчики событий класса:
 */
EventModel::getInstance()
	/**
	 * Статистика постинга:
	 */
	-> AddEventListener('add_board_thread', function($data) {
		$session = Session::getInstance();
		$session -> persistenceSet('last_board_post_date', time());

		$subscribers = Board_BoardModel::subscribersBoard($data['board_id']);

		Blog_BlogStatisticsModel::updateGlobalPosting();
		EventModel::getInstance()
			-> ClientBroadcast('board_'. $data['board_id'], 'add_post');

		if ($data['board_id'] != 'alone' && $data['board_id'] != 'int')
			EventModel::getInstance()
				-> ClientBroadcast('board_global', 'add_post_comment', array('board_id' => $data['board_id'], 'parent_id' => null, 'id' => $data['id'], 'count' => 0))
			/*-> ClientBroadcast('global', 'add_board_post', array('board_id' => $data['board_id']))*/;

		if ($subscribers)
		{
			$kvs     = KVS::getInstance();
			$message = array('board' => $data['board_id'], 'id' => $data['id'], 'title' => $data['title'], 'upload' => $data['upload']['web_thumb'], 'text' => (mb_strlen($data['text'], 'UTF-8') > 250 ? mb_substr($data['text'], 0, 250, 'UTF-8') .'...' : $data['text']), 'link' => 'http://'. TemplateHelper::getSiteUrl().'/'.$data['board_id'] .'/res/'. $data['id'] .'/');

			foreach($subscribers as $key)
			{
				if ($kvs -> exists('Session', md5($key), 'storage') && $kvs -> hashLength('Notify', $key, 'messages') <= 50)
					$kvs -> hashSet('Notify', $key, 'messages', $data['board_id'] .'_'. $data['id'], json_encode($message));
			}

			EventModel::getInstance()
				-> ClientBroadcast('global', 'new_board_thread', $message, $subscribers);
		}
	})
	-> AddEventListener('remove_board_thread', function($data) {
		EventModel::getInstance()
			-> ClientBroadcast('board_'. $data[0] .'_'. $data[1], 'remove_post');

		if ($data['board_id'] != 'alone' && $data['board_id'] != 'int')
			EventModel::getInstance()
				-> ClientBroadcast('board_global', 'remove_post_comment', array('board_id' => $data[0], 'id' => $data[1]));
	})
	-> AddEventListener('add_board_post', function($data) {
		$session = Session::getInstance();
		$session -> persistenceSet('last_board_post_date', time());

		/*$subscribers = Board_BoardModel::subscribersThread($data['board_id'] .'_'. $data['parent_id']);*/
		$board  = new Board_BoardModel($data['board_id']);
		$thread = $board -> getPost($data['parent_id']);

		/**
		 * Очистка и фильтарция:
		 */
		$data['created_at'] = TemplateHelper::date($data['board_id'] != 'int' ? 'd M Y @ H:i' : 'Y-m-d @ H:i', $data['created_at']);
		$data['author']     = array($data['author'], HomeBoardHelper::getBoard($data['author']));
		unset($data['ip']);

		EventModel::getInstance()
			-> ClientBroadcast(
				'boardpost_'. $data['board_id'] .'_'. $data['parent_id'], 'add_post_comment',
				$data
			)
			-> ClientBroadcast('board_'. $data['board_id'], 'add_post_comment', array('board_id' => $data['board_id'], 'parent_id' => $data['parent_id'], 'id' => $data['id'], 'count' => $data['num']++));
		

		if ($data['board_id'] != 'alone' && $data['board_id'] != 'int')
			EventModel::getInstance()
				-> ClientBroadcast('board_global', 'add_post_comment', array('board_id' => $data['board_id'], 'parent_id' => $data['parent_id'], 'id' => $data['id'], 'count' => $data['num']++));
        /*
		if ($subscribers)	
		{
			$kvs     = KVS::getInstance();
			$message = array('board' => $data['board_id'], 'post' => true, 'id' => $data['parent_id'], 'title' => $thread['title'] ? $thread['title'] : '№'. $thread['id'], 'upload' => $data['upload']['web_thumb'], 'text' => (mb_strlen($data['text'], 'UTF-8') > 250 ? mb_substr($data['text'], 0, 250, 'UTF-8') .'...' : $data['text']), 'link' => 'http://'. TemplateHelper::getSiteUrl().'/'.$data['board_id'] .'/res/'. $data['parent_id'] .'/#'. $data['id']);
			
			foreach($subscribers as $key)
			{
				if ($kvs -> exists('Session', md5($key), 'storage') && $kvs -> hashLength('Notify', $key, 'messages') <= 50)
					$kvs -> hashSet('Notify', $key, 'messages', $data['board_id'] .'_'. $data['parent_id'], json_encode($message));
			}

			EventModel::getInstance()
				-> ClientBroadcast('global', 'new_board_post', $message, $subscribers);
		}*/

		Blog_BlogStatisticsModel::updateGlobalPosting();
	})
	-> AddEventListener('remove_board_post', function($data) {
		EventModel::getInstance()
			-> ClientBroadcast(
				'boardpost_'. $data['board_id'] .'_'. $data['parent_id'], 'remove_post_comment',
				array('id' => $data['id'])
			);

		if ($data['board_id'] != 'alone' && $data['board_id'] != 'int')
			EventModel::getInstance()
				-> ClientBroadcast('board_global', 'remove_post_comment', array('board_id' => $data['board_id'], 'id' => $data['id']));
	});
