<?php
/**
 * Модель комментариев:
 */
class Blog_BlogCommentsModel
{
	/**
	 * Добавление комментария:
	 */
	public static function CreateComment($params, $safeMode = true)
	{
		if (Blog_BlogPostsModel::PostExists($params['post_id']))
		{
			$session = Session::getInstance();
			$post = Blog_BlogPostsModel::GetPost($params['post_id']);

			// Запрет комментирования закрытых тредов:
			if ($post['closed'] == true)
				return false;

			$cache = KVS::getInstance();
			$id    = $cache -> incr('Blog_BlogPostsModel', null, 'nextPostId');
			$text_original = $params['text'];

			// Обработка:
			$params['text'] = TexyHelper::markup($params['text'], !$session -> isAdminSession());

			$record = array(
				'id'         => $id,
				'post_id'    => $params['post_id'],
				'ip'         => $_SERVER['REMOTE_ADDR'],
				'created_at' => time(),
				'text'       => $params['text']
			);

			if (HomeBoardHelper::existsBoard($params['homeboard']))
				$record['author'] = $params['homeboard'];

			if (!$safeMode)
			{
				$record = array_merge($record, array(
					'special_comment' => @$params['special_comment']
				));
			}

			$dbh = PDOQuery::getInstance();
			$dbh -> insert('1chan_comment', $record);

			if ($post['bumpable'] && (time() - $post['created_at'] < 60 * 60 * 24 * 30))
				$dbh -> update_insecure('1chan_post', array('updated_at' => time(), 'comments' => '`comments`+1'), 'id = '. $dbh -> q($params['post_id']), 1, true);
			else
				$dbh -> update_insecure('1chan_post', array('comments' => '`comments`+1'), 'id = '. $dbh -> q($params['post_id']), 1, true);

			$record['text_original'] = $text_original;
			EventModel::getInstance()
				-> Broadcast('add_comment', $record);

			/**
			 * Сохраняем в кеше последние комментарии:
			 */
			$record['post_title'] = $post['title'];
			$cache -> listAdd(__CLASS__, null, 'lastComments', $id);
			$cache -> listTrim(__CLASS__, null, 'lastComments', 0, 31);

			return $id;
		}
		return false;
	}

	/**
	 * Получение комментариев новости:
	 */
	public static function GetComments($post_id)
	{
		$cache = KVS::getInstance();
		$dbh = PDOQuery::getInstance();
		if ($cache -> exists(__CLASS__, $post_id, 'comments'))
			return unserialize($cache -> get(__CLASS__, $post_id, 'comments'));

		$comments = $dbh -> select('1chan_comment', '*', 'post_id = '. $dbh -> q($post_id), 'created_at ASC');
		$cache -> set(__CLASS__, $post_id, 'comments', serialize($comments));
		$cache -> expire(__CLASS__, $post_id, 'comments', 60 * 60 * 24);

		return $comments;
	}

	/**
	 * Получение комментариев новости:
	 */
	public static function GetLastComments($num = 30)
	{
		$cache = KVS::getInstance();
		$comments = $cache -> listGet(__CLASS__, null, 'lastComments');
		$result = array();
		foreach ($comments as $id)
		{
			if ($comment = self::GetComment($id))
				$result[] = $comment;
			else
				$cache -> listRemove(__CLASS__, null, 'lastComments', $id);
		}
		return $result;
	}

	/**
	 * Получение одного комментария:
	 */
	public static function GetComment($id)
	{
		$dbh   = PDOQuery::getInstance();
		$posts = $dbh -> select('1chan_comment', '*', 'id = '. $dbh -> q($id), null, 1);

		if (!empty($posts[0]))
			return $posts[0];

		return false;
	}

	/**
	 * Проверка существования комментария:
	 */
	public static function CommentExists($id)
	{
		$dbh   = PDOQuery::getInstance();
		$posts = $dbh -> select('1chan_comment', '*', 'id = '. $dbh -> q($id), null, 1);

		if ($posts && !empty($posts[0]))
			return true;

		return false;
	}

	/**
	 * Редактирование комментария:
	 */
	public static function EditComment($id, $params, $safeMode = true)
	{
		if (Blog_BlogPostsModel::PostExists($params['post_id']))
		{
			$session = Session::getInstance();
			$cache = KVS::getInstance();

			// Обработка:
			$params['text'] = TexyHelper::markup($params['text'], !$session -> isAdminSession());

			$record = array(
				'text'       => $params['text']
			);

			if (!$safeMode)
			{
				$record = array_merge($record, array(
					'special_comment' => @$params['special_comment']
				));
			}

			$dbh = PDOQuery::getInstance();
			$dbh -> update('1chan_comment', $record, 'id = '. $dbh -> q($id));

			EventModel::getInstance()
				-> Broadcast('edit_comment', $record);

			return $id;
		}
		return false;
	}

	/**
	 * Установка специального комментария:
	 */
	public static function SetSpecialComment($id, $comment = '')
	{
		$dbh = PDOQuery::getInstance();
		$comment = self::GetComment($id);

		EventModel::getInstance()
			-> Broadcast('special_comment_comment', array('id' => $id, 'post_id' => $comment['post_id'], 'special_comment' => $comment));

		return $dbh -> update('1chan_comment', array('special_comment' => $comment), 'id = '. $dbh -> q($id));
	}

	/**
	 * Удаление комментария:
	 */
	public static function RemoveComment($id)
	{
		$comment = self::GetComment($id);
		$kvs     = KVS::getInstance();
		$cache = KVS::getInstance();
		if ($comment)
		{
			if ($kvs -> exists('ControlModel', 'timeblock', $comment['ip']))
			{
				$kvs -> set('ControlModel', 'timeban', $comment['ip'], true);
				$kvs -> expire('ControlModel', 'timeban', $comment['ip'], 60 * 60);
			}

			if ($kvs -> exists('ControlModel', 'timeban', $comment['ip']))
			{
				$life = $kvs -> lifetime('ControlModel', 'timeban', $comment['ip']);
				$kvs -> expire('ControlModel', 'timeban', $comment['ip'], $life + (60 * 60));
			}

			$kvs -> set('ControlModel', 'timeblock', $comment['ip'], true);
			$kvs -> expire('ControlModel', 'timeblock', $comment['ip'], 60 * 60);

			$dbh = PDOQuery::getInstance();
			$dbh -> update_insecure('1chan_post', array('comments' => '`comments`-1'), 'id = '. $dbh -> q($comment['post_id']), 1, true);

			$cache -> listRemove(__CLASS__, null, 'lastComments', $id);
			EventModel::getInstance()
				-> Broadcast('remove_comment', array('id' => $id, 'post_id' => $comment['post_id']));

			return $dbh -> delete('1chan_comment', 'id = '. $dbh -> q($id), 1);
		}
		return false;
	}
}

/**
 * Обработчики событий:
 */
EventModel::getInstance()
	-> AddEventListener('add_comment', function($data) {

		$session = Session::getInstance();
		$session -> persistenceSet('last_comment_date', time());
		$session -> persistenceSet('last_post_text', $data['text_original']);

		/**
		 * Очистка и фильтарция:
		 */
		$data['created_at'] = TemplateHelper::date('d M Y @ H:i', $data['created_at']);
		$data['author']     = array($data['author'], HomeBoardHelper::getBoard($data['author']));
		unset($data['ip']);
		unset($data['text_original']);

		$post = Blog_BlogPostsModel::GetPost($data['post_id']);
		EventModel::getInstance()
			-> ClientBroadcast(
				'post_'. $data['post_id'], 'add_post_comment',
				$data
			)
			-> ClientBroadcast(
				'post_last_comments', 'add_post_comment',
				array('post_id' => $post['id'], 'id' => $data['id'])
			)
			-> ClientBroadcast('posts', 'add_post_comment', array('id' => $data['post_id'], 'count' => $post['comments']));

		Blog_BlogStatisticsModel::updateGlobalPosting();
	})
	-> AddEventListener('remove_comment', function($data) {
		$post = Blog_BlogPostsModel::GetPost($data['post_id']);
		EventModel::getInstance()
			-> ClientBroadcast(
				'post_'. $data['post_id'], 'remove_post_comment',
				array('id' => $data['id'])
			)
			-> ClientBroadcast(
				'post_last_comments', 'remove_post_comment',
				array('id' => $data['id'])
			)
			-> ClientBroadcast('posts', 'remove_post_comment', array('id' => $data['post_id'], 'count' => $post['comments']));
	})
	-> AddEventListener('*_comment', function($data) {
		if (array_key_exists('post_id', $data))
		{
			$cache = KVS::getInstance();
			$cache -> remove('Blog_BlogCommentsModel', $data['post_id'], 'comments');
		}
	});
