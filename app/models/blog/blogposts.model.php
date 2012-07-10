<?php
/**
 * Модель постов:
 */
class Blog_BlogPostsModel
{
	/**
	 * Добавление поста в базу данных:
	 */
	public static function CreatePost($params, $safeMode = true)
	{
		$session = Session::getInstance();
		$cache = KVS::getInstance();
		$id    = $cache -> incr('Blog_BlogPostsModel', null, 'nextPostId');
		$text_original = $params['text'];

		$params['title']     = TexyHelper::typo($params['title']);
		$params['text']      = TexyHelper::markup($params['text'], !$session -> isAdminSession());
		$params['text_full'] = TexyHelper::markup($params['text_full'], !$session -> isAdminSession());

		$record = array(
			'id'         => $id,
			'ip'         => $_SERVER['REMOTE_ADDR'],
			'category'   => 0,
			'created_at' => time(),
			'updated_at' => time(),
			'link'       => htmlspecialchars($params['link']),
			'title'      => $params['title'],
			'text'       => $params['text'],
			'text_full'  => $params['text_full']
		);

		if (HomeBoardHelper::existsBoard($params['homeboard']))
			$record['author'] = $params['homeboard'];

		if (!$safeMode)
		{
			$record = array_merge($record, array(
				'hidden'          => (bool)@$params['hidden'],
				'pinned'          => (bool)@$params['pinned'],
				'rated'           => (bool)@$params['rated'],
				'closed'          => (bool)@$params['closed'],
				'rateable'        => (bool)@$params['rateable'],
				'bumpable'        => (bool)@$params['bumpable'],
				'special_comment' => @$params['special_comment']
			));
		}

		if (array_key_exists('category', $params) && !empty($params['category']))
		{
			$category = Blog_BlogCategoryModel::GetCategoryByCode($params['category']);
			if ($category)
			{
				 Blog_BlogCategoryModel::CountCategory($category['id'], true);
				$record['category'] = $category['id'];
			}
		}

		$dbh = PDOQuery::getInstance();
		$dbh -> insert('1chan_post', $record);
		$cache -> set(__CLASS__, $id, 'raters', serialize(array($_SERVER['REMOTE_ADDR'])));

		$record['text_original'] = $text_original;
		EventModel::getInstance()
			-> Broadcast('add_post', $record);

		return $id;
	}

	/**
	 * Проверка существования поста:
	 */
	public static function PostExists($id)
	{
		$dbh   = PDOQuery::getInstance();
		$posts = $dbh -> select('1chan_post', '*', 'id = '. $dbh -> q($id), null, 1);
		return (bool)$posts;
	}

	/**
	 * Получение поста по id:
	 */
	public static function GetPost($id)
	{
		$dbh   = PDOQuery::getInstance();
		$posts = $dbh -> select('1chan_post', '*', 'id = '. $dbh -> q($id), null, 1);

		if (!empty($posts[0]))
			return $posts[0];

		return false;
	}

	/**
	 * Получение постов по списку id:
	 */
	public static function GetPostsByIds($ids = array())
	{
		$dbh = PDOQuery::getInstance();
		$posts = $dbh -> select('1chan_post', '*', 'id IN ("'.implode('", "', $ids).'")', 'FIELD(id,"'.implode('", "', $ids).'") ASC');

		return $posts;
	}

	/**
	 * Получение всех постов:
	 */
	public static function GetAllPosts($page, $postsByPage = 20, $byUpdTime = false, &$pages = null)
	{
		$start = $page * $postsByPage;

		$dbh = PDOQuery::getInstance();
		$posts = $dbh -> select('1chan_post', '*', 'hidden = 0', 'pinned DESC, '.($byUpdTime ? 'updated_at' : 'created_at').' DESC', $start .','. $postsByPage, $count);
		$pages = $count / $postsByPage;

		return $posts;
	}

	/**
	 * Получение всех постов из определенной категории:
	 */
	public static function GetAllPostsFromCategory($category, $page, $postsByPage = 20, $byUpdTime = false, &$pages = null)
	{
		$start = $page * $postsByPage;

		$dbh = PDOQuery::getInstance();
		$posts = $dbh -> select('1chan_post', '*', 'hidden = 0 AND category = '. $category, 'pinned DESC, '.($byUpdTime ? 'updated_at' : 'created_at').' DESC', $start .','. $postsByPage, $count);
		$pages = $count / $postsByPage;

		return $posts;
	}

	/**
	 * Получение одобренных постов:
	 */
	public static function GetRatedPosts($page, $postsByPage = 20, $byUpdTime = false, &$pages = null)
	{
		$start = $page * $postsByPage;

		$dbh = PDOQuery::getInstance();
		$posts = $dbh -> select('1chan_post', '*', 'hidden = 0 AND rated = 1', 'pinned DESC, '.($byUpdTime ? 'updated_at' : 'created_at').' DESC', $start .','. $postsByPage, $count);
		$pages = $count / $postsByPage;

		return $posts;
	}

	/**
	 * Получение скрытых постов:
	 */
	public static function GetHiddenPosts($page, $postsByPage = 20, $byUpdTime = false, &$pages = null)
	{
		$start = $page * $postsByPage;

		$dbh = PDOQuery::getInstance();
		$posts = $dbh -> select('1chan_post', '*', 'hidden = 1', 'pinned DESC, '.($byUpdTime ? 'updated_at' : 'created_at').' DESC', $start .','. $postsByPage, $count);
		$pages = $count / $postsByPage;

		return $posts;
	}

	/**
	 * Получение избранных постов:
	 */
	public static function GetFavoritePosts($byUpdTime = false)
	{
		$session = Session::getInstance();
		$favorites = $session -> persistenceGet('posts_favorites', array());

		if (!empty($favorites))
		{
			$dbh = PDOQuery::getInstance();
			$posts = $dbh -> select('1chan_post', '*', 'id IN ("'.implode('", "', $favorites).'")', ($byUpdTime ? 'updated_at DESC' : 'FIELD(id,"'.implode('", "', $favorites).'") ASC'));

			return $posts;
		}
		return false;
	}

	/**
	 * Проверка на избранный пост:
	 */
	public static function IsFavoritePost($id)
	{
		static $favorites;
		if (!is_array($favorites))
		{
			$session = Session::getInstance();
			$favorites = $session -> persistenceGet('posts_favorites', array());
		}

		return in_array($id, $favorites);
	}

	/**
	 * Переключение избранного поста:
	 */
	public static function ToggleFavoritePost($id)
	{
		if (self::PostExists($id))
		{
			$session = Session::getInstance();
			$favorites = $session -> persistenceGet('posts_favorites', array());
			if (($key = array_search($id, $favorites)) !== false)
				unset($favorites[$key]);
			else
				array_unshift($favorites, $id);

			$session -> persistenceSet('posts_favorites', $favorites);
			return array_search($id, $favorites) !== false;
		}
		return false;
	}

	/**
	 * Переключение избранного поста (ajax):
	 */
	public static function ToggleFavoriteAjaxPost($id)
	{
		return array('favorite' => self::ToggleFavoritePost($id));
	}

	/**
	 * Назначение категории:
	 */
	public static function CatPost($id, $params, $comment = '')
	{
	    $session = Session::getInstance();
		$dbh = PDOQuery::getInstance();

		$post = self::GetPost($id);
		if ($post['category'] != $params['category'])
		{
			Blog_BlogCategoryModel::CountCategory($post['category'], false);
			Blog_BlogCategoryModel::CountCategory($params['category'], true);

			EventModel::getInstance()
				-> Broadcast('info_post', array($id, $comment));
		}

		return $dbh -> update('1chan_post', array('category' => $params['category']), 'id = '. $dbh -> q($id));
	}

	/**
	 * Редактирование поста:
	 */
	public static function EditPost($id, $params, $safeMode = true)
	{
		$session = Session::getInstance();
		$dbh = PDOQuery::getInstance();

		$post = self::GetPost($id);
		if ($post['category'] != $params['category'])
		{
			Blog_BlogCategoryModel::CountCategory($post['category'], false);
			Blog_BlogCategoryModel::CountCategory($params['category'], true);
		}

		$params['title']     = TexyHelper::typo($params['title']);
		$params['text']      = TexyHelper::markup($params['text'], !$session -> isAdminSession());
		$params['text_full'] = TexyHelper::markup($params['text_full'], !$session -> isAdminSession());

		$record = array(
			'category'   => $params['category'],
			'link'       => $params['link'],
			'title'      => $params['title'],
			'text'       => $params['text'],
			'text_full'  => $params['text_full']
		);

		if (!$safeMode)
		{
			$record = array_merge($record, array(
				'ip'              => $params['ip'],
				'hidden'          => (bool)$params['hidden'],
				'pinned'          => (bool)$params['pinned'],
				'rated'           => (bool)$params['rated'],
				'closed'          => (bool)$params['closed'],
				'rateable'        => (bool)$params['rateable'],
				'bumpable'        => (bool)$params['bumpable'],
				'special_comment' => (bool)$params['special_comment']
			));
		}

		EventModel::getInstance()
			-> Broadcast('edit_post', $id, $record);

		return $dbh -> update('1chan_post', $record, 'id = '. $dbh -> q($id));
	}

	/**
	 * Удаление поста:
	 */
	public static function RemovePost($id)
	{
		$post = self::GetPost($id);
		Blog_BlogCategoryModel::CountCategory($post['category'], false);

		EventModel::getInstance()
			-> Broadcast('remove_post', $id);

		$dbh = PDOQuery::getInstance();
		$dbh -> delete('1chan_post', 'id = '. $dbh -> q($id), 1);
		$dbh -> delete('1chan_comment', 'post_id = '. $dbh -> q($id));
		return true;
	}

	/**
	 * Оценка поста:
	 */
	public static function RatePost($id, $increment = true)
	{
		$dbh   = PDOQuery::getInstance();
		$ip    = $_SERVER['REMOTE_ADDR'];
		$cache = KVS::getInstance();

		$post = Blog_BlogPostsModel::GetPost($id);
		if ($post && !$post['rateable'])
			return false;

		$raters = unserialize($cache -> get(__CLASS__, $id, 'raters'));
		if (!$raters) return false;

		if (in_array($ip, $raters))
			return false;
		else
		{
			$raters[] = $ip;
			$cache -> set(__CLASS__, $id, 'raters', serialize($raters));
		}

		$dbh -> update_insecure('1chan_post', array('rate' => '`rate`'. ($increment ? '+1' : '-1')), 'id = '. $dbh -> q($id), 1, true);
		$post = Blog_BlogPostsModel::GetPost($id);

		EventModel::getInstance()
			-> Broadcast('rate_post', array($id, $post['rate']));
		/**
		if ($post['rate'] >= ControlModel::getRatedCount() && !$post['rated'])
			self::RatedPost($id, true);
		**/
		return true;
	}

	/**
	 * Установка поста "одобренным":
	 */
	public static function RatedPost($id, $rated = true, $comment = '')
	{
		$dbh = PDOQuery::getInstance();

		EventModel::getInstance()
			-> Broadcast('rated_post', array($id, $rated));

		EventModel::getInstance()
			-> Broadcast('info_post', array($id, $comment));

		return $dbh -> update('1chan_post', array('rated' => $rated), 'id = '. $dbh -> q($id));
	}

	/**
	 * Установка поста "одобряемым":
	 */
	public static function RateablePost($id, $rateable = true, $comment = '')
	{
		$dbh = PDOQuery::getInstance();

		EventModel::getInstance()
			-> Broadcast('info_post', array($id, $comment));

		return $dbh -> update('1chan_post', array('rateable' => $rateable), 'id = '. $dbh -> q($id));
	}

	/**
	 * Установка поста "поднимаемым":
	 */
	public static function BumpablePost($id, $bumpable = true)
	{
		$dbh = PDOQuery::getInstance();
		return $dbh -> update('1chan_post', array('bumpable' => $bumpable), 'id = '. $dbh -> q($id));
	}

	/**
	 * Установка поста "прикрепленным":
	 */
	public static function PinPost($id, $pinned = true, $comment = '')
	{
		$dbh = PDOQuery::getInstance();

		EventModel::getInstance()
			-> Broadcast('info_post', array($id, $comment));

		return $dbh -> update('1chan_post', array('pinned' => $pinned), 'id = '. $dbh -> q($id));
	}

	/**
	 * Закрытие поста:
	 */
	public static function ClosePost($id, $closed = true, $comment = '')
	{
		$dbh = PDOQuery::getInstance();

		EventModel::getInstance()
			-> Broadcast('info_post', array($id, $comment));

		return $dbh -> update('1chan_post', array('closed' => $closed), 'id = '. $dbh -> q($id));
	}

	/**
	 * Скрытие поста:
	 */
	public static function HidePost($id, $hidden = true, $comment = '')
	{
		$dbh = PDOQuery::getInstance();

		EventModel::getInstance()
			-> Broadcast('info_post', array($id, $comment));

		return $dbh -> update('1chan_post', array('hidden' => $hidden), 'id = '. $dbh -> q($id));
	}

	/**
	 * Установка специального комментария:
	 */
	public static function SetSpecialComment($id, $comment = '')
	{
		$dbh = PDOQuery::getInstance();
		return $dbh -> update('1chan_post', array('special_comment' => $comment), 'id = '. $dbh -> q($id));
	}
}

/**
 * Обработчики событий класса:
 */
EventModel::getInstance()
	/**
	 * Статистика постинга:
	 */
	-> AddEventListener('add_post', function($data) {
		$session = Session::getInstance();
		$session -> persistenceSet('last_post_date', time());
		$session -> persistenceSet('last_post_text', $data['text_original']);

		$title = $data['category'] ?
				TemplateHelper::BlogCategory($data['category'], 'title') .' — '. $data['title'] :
				$data['title'];

		JabberBot::send('-=$ /me «'. $title .'» ( '. 'http://'.TemplateHelper::getSiteUrl().'/news/res/'.$data['id'].'/ )');
		Blog_BlogStatisticsModel::updateGlobalPosting();
		EventModel::getInstance()
			-> ClientBroadcast('posts', 'add_post')
			-> ClientBroadcast('new_posts', 'add_post', array('id' => $data['id'], 'title' => $data['title'], 'category' => $data['category'] ? TemplateHelper::BlogCategory($data['category'], 'name') : 0, 'category_title' => $data['category'] ? TemplateHelper::BlogCategory($data['category'], 'title') : 0));
	})
	/**
	 * Пост оценили:
	 */
	-> AddEventListener('rate_post', function($data) {
		EventModel::getInstance()
			-> ClientBroadcast('posts', 'rate_post', array('id' => $data[0], 'rate' => $data[1]))
			-> ClientBroadcast('post_'. $data[0], 'rate_post', array('id' => $data[0], 'rate' => $data[1]));
	})
	/**
	 * Пост изменили:
	 */
	-> AddEventListener('info_post', function($data) {
		EventModel::getInstance()
			-> ClientBroadcast('post_'. $data[0], 'info_post', array('id' => $data[0], 'comment' => $data[1]));
	})
	/**
	 * Пост оценен (отправка сообщения в джаббер):
	 */
	-> AddEventListener('rated_post', function($data) {
		list($id, $rated) = $data;

		if ($rated) {
			$post  = Blog_BlogPostsModel::GetPost($id);

			$title = $post['category'] ?
				TemplateHelper::BlogCategory($post['category'], 'title') .' — '. $post['title'] :
				$post['title'];

			JabberBot::send(
				$title ."\n". 'http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/'
				."\n". str_repeat('—', 25) ."\n".
				 preg_replace('/\t/', '', trim(strip_tags($post['text'])))
				."\n". str_repeat('—', 25)
			);
		    //JabberBot::send('-=& /me «'. $title .'» ( '. 'http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/ )');

			EventModel::getInstance()
				-> ClientBroadcast('posts', 'rated_post');
		}
	});
