<?php
/**
 * Контроллер разделов на одинчане:
 */
class BoardController extends BaseController
{
	/**
	 * Создание темы:
	 */
	public function createAction(Application $application, Template $template)
	{
		$board   = new Board_BoardModel($_GET['board']);
		$session = Session::getInstance();

		if ($session -> isJustCreated())
			return false;

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$validator = new ValidatorHelper($_POST);

			$validator -> assertLength('title', 60,     $_GET['board'] != 'int' ? 'Заголовок слишком длинный' : 'Title is too long');

			if (!array_key_exists('upload', $_FILES) || $_FILES['upload']['error'] == 4)
				$validator -> assertExists('text',     $_GET['board'] != 'int' ? 'Не введен текст' : 'Please enter the message');

			$validator -> assertLength('text', 2048,  $_GET['board'] != 'int' ? 'Текст слишком длинный' : 'Post\'s text is too long');
			$validator -> assertNotExists('email',    $_GET['board'] != 'int' ? 'Заполнено лишнее поле' : 'Spam omitted');

			$validator -> assertTrue(
				'text', ControlModel::checkSpam($_POST['text']),
				$_GET['board'] != 'int' ? 'Ваше сообщение определено, как спам' : 'Spam message was detected'
			);

			$validator -> assertTrue(
				'upload', Board_UploadModel::checkUpload(),
				$_GET['board'] != 'int' ? 'Изображение имеет неизвестный формат, либо превышает допустимый размер' :
				                          'Unknown image type or file is too heavy to process'
			);

			$validator -> assertTrue(
				'timeout', ControlModel::getBoardPostInterval() == 0,
				$_GET['board'] != 'int' ? 
				    'Таймаут '. TemplateHelper::ending(ControlModel::getBoardPostInterval(), 'секунда', 'секунды', 'секунд') :
				    ControlModel::getBoardPostInterval() .' seconds timeout'
			);

			$validator -> assertLengthMore('captcha', 1, $_GET['board'] != 'int' ? 'Не введена капча' : 'Please enter the Captcha code');
			if ($validator -> fieldValid('captcha'))
				$validator -> assertEqual(
					'captcha', $session -> instantGet('captcha_board', false),
					$_GET['board'] != 'int' ? 
					    'Капча введена неверно' :
					    'Captcha code is incorrect'
				);

			if ($validator -> isValid())
			{
				$id = $board -> createThread($_POST);

				$template -> headerSeeOther(
					'http://'. TemplateHelper::getSiteUrl() .'/'. $board -> getId() .'/res/'. $id .'/'
				);
				return false;
			}

			$session  -> instantSet('captcha_board', true);
			$session -> instantSet('board_errors', $validator -> getValidationResults());
			$session -> instantSet('board_form', $_POST);
		}

		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/'. $board -> getId() .'/'
		);
		return false;
	}

	/**
	 * Создание темы (форма ajax):
	 */
	public function createAjaxFormAction(Application $application, Template $template)
	{
		$board   = new Board_BoardModel($_GET['board']);
		$session = Session::getInstance();

		if ($session -> isJustCreated())
			return false;

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$validator = new ValidatorHelper($_POST);

			$validator -> assertLength('title', 60,     $_GET['board'] != 'int' ? 'Заголовок слишком длинный' : 'Title is too long');

			if (!array_key_exists('upload', $_FILES) || $_FILES['upload']['error'] == 4)
				$validator -> assertExists('text',      $_GET['board'] != 'int' ? 'Не введен текст' : 'Please enter the message');

			$validator -> assertLength('text', 2048, $_GET['board'] != 'int' ? 'Текст слишком длинный' : 'Post\'s text is too long');
			$validator -> assertNotExists('email',   $_GET['board'] != 'int' ? 'Заполнено лишнее поле' : 'Spam omitted');

			$validator -> assertTrue(
				'text', ControlModel::checkSpam($_POST['text']),
				$_GET['board'] != 'int' ? 'Ваше сообщение определено, как спам' : 'Spam message was detected'
			);

			$validator -> assertTrue(
				'upload', Board_UploadModel::checkUpload(),
				$_GET['board'] != 'int' ? 'Изображение имеет неизвестный формат, либо превышает допустимый размер' :
				                          'Unknown image type or file is too heavy to process'
			);

			$validator -> assertTrue(
				'timeout', ControlModel::getBoardPostInterval() == 0,
				$_GET['board'] != 'int' ? 
				    'Таймаут '. TemplateHelper::ending(ControlModel::getBoardPostInterval(), 'секунда', 'секунды', 'секунд') :
				    ControlModel::getBoardPostInterval() .' seconds timeout'
			);

			$validator -> assertLengthMore('captcha', 1, $_GET['board'] != 'int' ? 'Не введена капча' : 'Please enter the Captcha code');
			if ($validator -> fieldValid('captcha'))
				$validator -> assertEqual(
					'captcha', $session -> instantGet('captcha_board', false),
					$_GET['board'] != 'int' ? 
					    'Капча введена неверно' :
					    'Captcha code is incorrect'
				);

			if ($validator -> isValid())
			{
				$id = $board -> createThread($_POST);

				$template -> renderJSONP('board_callback', array('sucess' => true, 'id' => $id));
				return false;
			}
			
			$session  -> instantSet('captcha_board', true);
			$template -> renderJSONP('board_callback', array('success' => false, 'errors' => $validator -> getValidationResults()));
			return false;
		}

		return false;
	}

	/**
	 * Создание поста:
	 */
	public function createPostAction(Application $application, Template $template)
	{
		$board   = new Board_BoardModel($_GET['board']);
		$session = Session::getInstance();

		if ($session -> isJustCreated())
			return false;

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$validator = new ValidatorHelper($_POST);

			if (!array_key_exists('upload', $_FILES) || $_FILES['upload']['error'] == 4)
				$validator -> assertExists('text', $_GET['board'] != 'int' ? 'Не введен текст' : 'Please enter the message');

			$validator -> assertLength('text', 2048, $_GET['board'] != 'int' ? 'Текст слишком длинный' : 'Post\'s text is too long');
			$validator -> assertNotExists('email',   $_GET['board'] != 'int' ? 'Заполнено лишнее поле' : 'Spam omitted');

			$validator -> assertTrue(
				'timeout', ControlModel::getBoardPostInterval() == 0,
				$_GET['board'] != 'int' ? 
				    'Таймаут '. TemplateHelper::ending(ControlModel::getBoardPostInterval(), 'секунда', 'секунды', 'секунд') :
				    ControlModel::getBoardPostInterval() .' seconds timeout'
			);

			if ($validator -> fieldValid('timeout'))
			{
				ControlModel::checkBoardPost($_POST['text']);
			}

			if (ControlModel::isCommentCaptcha()) {
				$validator -> assertLengthMore('captcha', 1, $_GET['board'] != 'int' ? 'Не введена капча' : 'Please enter the Captcha code');

			if ($validator -> fieldValid('captcha'))
				$validator -> assertEqual(
					'captcha', $session -> instantGet('captcha_board_comment', false),
					$_GET['board'] != 'int' ? 
					    'Капча введена неверно' :
					    'Captcha code is incorrect'
				);
			}

			$validator -> assertTrue(
				'text', ControlModel::checkSpam($_POST['text']),
				$_GET['board'] != 'int' ? 'Ваше сообщение определено, как спам' : 'Spam message was detected'
			);

			$validator -> assertTrue(
				'upload', Board_UploadModel::checkUpload(),
				$_GET['board'] != 'int' ? 'Ошибка загрузки изображения' :
				                          'File upload error'
			);

			if (ControlModel::isCommentCaptcha()) {
				$validator -> assertLengthMore('captcha', 1, $_GET['board'] != 'int' ? 'Не введена капча' : 'Please enter the Captcha code');
				if ($validator -> fieldValid('captcha'))
					$validator -> assertEqual(
						'captcha', $session -> instantGet('captcha_board_comment', false),
					    $_GET['board'] != 'int' ? 
					        'Капча введена неверно' :
					        'Captcha code is incorrect'
					);
			}

			if ($validator -> isValid())
			{
				$id = $board -> createPost($_POST['parent_id'], $_POST);

				$session -> persistenceSet('captcha_mode', false);
				$session -> persistenceSet('captcha_mode_length', @$settings['captcha_length']);

				$template -> headerSeeOther(
					'http://'. TemplateHelper::getSiteUrl() .'/'. $board -> getId() .'/res/'. $_POST['parent_id'] .'/#'. $id
				);
				return false;
			}

			$session -> instantSet('board_post_errors', $validator -> getValidationResults());
			$session -> instantSet('board_post_form', $_POST);
		}

		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/'. $board -> getId() .'/res/'. $_POST['parent_id'] .'/'
		);
		return false;
	}

	/**
	 * Создание поста (форма ajax):
	 */
	public function createPostAjaxFormAction(Application $application, Template $template)
	{
		$board   = new Board_BoardModel($_GET['board']);
		$session = Session::getInstance();

		if ($session -> isJustCreated())
			return false;

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$validator = new ValidatorHelper($_POST);

			if (!array_key_exists('upload', $_FILES) || $_FILES['upload']['error'] == 4)
				$validator -> assertExists('text', $_GET['board'] != 'int' ? 'Не введен текст' : 'Please enter the message');

			$validator -> assertLength('text', 2048, $_GET['board'] != 'int' ? 'Текст слишком длинный' : 'Post\'s text is too long');
			$validator -> assertNotExists('email',   $_GET['board'] != 'int' ? 'Заполнено лишнее поле' : 'Spam omitted');


			$validator -> assertTrue(
				'timeout', ControlModel::getBoardPostInterval() == 0,
				$_GET['board'] != 'int' ? 
				    'Таймаут '. TemplateHelper::ending(ControlModel::getBoardPostInterval(), 'секунда', 'секунды', 'секунд') :
				    ControlModel::getBoardPostInterval() .' seconds timeout'
			);

			if ($validator -> fieldValid('timeout'))
			{
				ControlModel::checkBoardPost($_POST['text']);
			}

			if (ControlModel::isCommentCaptcha()) {
				$validator -> assertLengthMore('captcha', 1, $_GET['board'] != 'int' ? 'Не введена капча' : 'Please enter the Captcha code');

			if ($validator -> fieldValid('captcha'))
				$validator -> assertEqual(
					'captcha', $session -> instantGet('captcha_board_comment', false),
					    $_GET['board'] != 'int' ? 
					        'Капча введена неверно' :
					        'Captcha code is incorrect'
					);
			}

			$validator -> assertTrue(
				'text', ControlModel::checkSpam($_POST['text']),
				$_GET['board'] != 'int' ? 'Ваше сообщение определено, как спам' : 'Spam message was detected'
			);

			$validator -> assertTrue(
				'upload', Board_UploadModel::checkUpload(),
				$_GET['board'] != 'int' ? 'Ошибка загрузки изображения' :
				                          'File upload error'
			);

			$session  -> instantSet('captcha_board_comment', true);

			if ($validator -> isValid())
			{
				$id = $board -> createPost($_POST['parent_id'], $_POST);

				$session -> persistenceSet('captcha_mode', false);
				$session -> persistenceSet('captcha_mode_length', @$settings['captcha_length']);

				$template -> renderJSONP('comment_callback', array('sucess' => true, 'id' => $id));
				return false;
			}

			$template -> renderJSONP('comment_callback', array('success' => false, 'errors' => $validator -> getValidationResults()));
		}

		return false;
	}

	/**
	 * Получение поста:
	 */
	public function getAction(Application $application, Template $template)
	{
		$board = new Board_BoardModel($_GET['board']);

		if ($board -> existsPost($_GET['id']))
		{
			$post = $board -> getPost($_GET['id']);

			if ($post['parent_id'] == null)
			{
				$template -> headerSeeOther(
					'http://'. TemplateHelper::getSiteUrl() .'/'. $board -> getId() .'/res/'. $post['post_id'] .'/'
				);
				return false;
			}

			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/'. $board -> getId() .'/res/'. $post['parent_id'] .'/#'. $post['post_id']
			);
			return false;
		}

		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/'. $board -> getId() .'/'
		);
		return false;
	}

	/**
	 * Получение поста(ов) (ajax):
	 */
	public function getAjaxAction(Application $application)
	{
		$board = new Board_BoardModel($_GET['board']);
		$settings = $board -> getSettings();

		if (array_key_exists('id', $_GET))
		{
			if (is_array($_GET['id']))
			{
				$result = array();
				foreach ($_GET['id'] as $id)
				{
					if ($post = $board -> getPost($id))
					{
						$post['created_at']  = TemplateHelper::date($_GET['board'] != 'int' ? 'd M Y @ H:i' : 'Y-m-d @ H:i', $post['created_at']);
						$post['board_title'] = $settings['title'];						
						$post['author']      = array($post['author'] ? $post['author'] : 'anonymous', HomeBoardHelper::getBoard($post['author']));
						unset($post['ip']);

						$result[] = $post;
					}
				}

				return $result;
			}
			else
			{
				if ($post = $board -> getPost($_GET['id']))
				{
					$post['created_at']  = TemplateHelper::date($_GET['board'] != 'int' ? 'd M Y @ H:i' : 'Y-m-d @ H:i', $post['created_at']);
					$post['board_title'] = $settings['title'];
					$post['author']      = array($post['author'] ? $post['author'] : 'anonymous', HomeBoardHelper::getBoard($post['author']));
					unset($post['ip']);
					return $post;
				}
				return false;
			}
		}
		
		if (array_key_exists('thread_id', $_GET))
		{
			if ($thread = $board -> getThread($_GET['thread_id'], true))
			{
				$result = array();
				foreach ($thread['posts'] as $id => $post)
				{
					$post['created_at']  = TemplateHelper::date($_GET['board'] != 'int' ? 'd M Y @ H:i' : 'Y-m-d @ H:i', $post['created_at']);
					$post['board_title'] = $settings['title'];
					$post['author']     = array($post['author'] ? $post['author'] : 'anonymous', HomeBoardHelper::getBoard($post['author']));
					unset($post['ip']);
					$result[] = $post;
				}

				return $result;
			}
		}
		return false;
	}

	/**
	 * Удаление поста:
	 */
	public function removeAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		
		if ($session -> isAdminSession() && $_GET['prompt'] == 'yes')
		{
			echo 'Done!';
			fastcgi_finish_request();

			$board = new Board_BoardModel($_GET['board']);
			$board -> removeBoard();

			return false;
		}

		// Не имплементировано:
		$template -> headerSeeOther(
			$_SERVER['HTTP_REFERER']
		);
		return false;
	}

	/**
	 * Удаление поста (ajax):
	 */
	public function removeAjaxAction(Application $application)
	{
		$board   = new Board_BoardModel($_GET['board']);
		$session = Session::getInstance();

		if ($board -> existsPost($_GET['id']))
		{
			$post = $board -> getPost($_GET['id']);

			if ((!empty($post['password']) && $post['password'] == $_GET['password']))
			{
				if ($board -> existsThread($_GET['id']))
					$board -> removeThread($_GET['id']);
				else
					$board -> removePost($_GET['id']);

				return true;
			}

			if ($session -> isAdminSession())
			{
                if (@$_GET['delall'] == true)
                {
                    $board -> removePostsByAuthor($_GET['id']);
                    return true;
                }
                else
                {
				    if ($board -> existsThread($_GET['id']))
					    $board -> removeThread($_GET['id']);
				    else
					    $board -> removePost($_GET['id']);

				    if ($board -> getId() != 'alone')
					    ControlModel::logModEvent(
						    date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] .'<br /> удалил пост №'. $_GET['id'] .' в разделе '. $_GET['board']
					    );
                }
			}

			return false;
		}
		return true;
	}

	/**
	 * Действие: добавление/удаление из избранного:
	 */
	public function toggleFavoriteAction(Application $application, Template $template)
	{
		$state = Board_FavoritesModel::ToggleFavoritePost($_GET['board'], $_GET['id']);
		$key = Session::getInstance() -> getKey();

		if ($state)
			Board_BoardModel::subscribeThread($key, $_GET['board'] .'_'. $_GET['id']);
		else
			Board_BoardModel::unsubscribeThread($key, $_GET['board'] .'_'. $_GET['id']);

		$template -> headerSeeOther(
			$_SERVER['HTTP_REFERER']
		);
		return false;
	}

	/**
	 * Действие: добавление/удаление из избранного (ajax):
	 */
	public function toggleFavoriteAjaxAction(Application $application)
	{
		$state = Board_FavoritesModel::ToggleFavoritePost($_GET['board'], $_GET['id']);
		$key = Session::getInstance() -> getKey();

		if (Board_FavoritesModel::IsFavoritePost($_GET['board'], $_GET['id']))
			Board_BoardModel::subscribeThread($key, $_GET['board'] .'_'. $_GET['id']);
		else
			Board_BoardModel::unsubscribeThread($key, $_GET['board'] .'_'. $_GET['id']);

		return array('favorite' => $state);
	}

	/**
	 * Действие обновление статистики:
	 */
	public function postStatsAjaxAction(Application $application)
	{
		Board_StatisticsModel::updatePostStats($_GET['board'], $_GET['thread_id'], $_GET['writing']);
		return Board_StatisticsModel::getPostStats($_GET['board'], $_GET['thread_id']);
	}

	/**
	 * Просмотр списка тредов борды:
	 */
	public function viewThreadsAction(Application $application, Template $template)
	{
		$board   = new Board_BoardModel($_GET['board']);

		$session  = Session::getInstance();
		$settings = $board -> getSettings();
		$key = $session -> getKey();

		$template -> setParameter('title',             $settings['title']);
		$template -> setParameter('description', $settings['description']);
		$template -> setParameter('board_id',      $board -> getId());

		$page   = @$_GET['page'] ? $_GET['page'] : 0;

		$this['posts'] = $board -> getThreads($page, $pages);
		$this['form_errors'] = $session -> instantGet('board_errors', array());
		$this['board_form'] = $session -> instantGet('board_form', array());
		$this['subscribed'] = Board_BoardModel::subscribedBoard($key, $_GET['board']);

		$template -> setParameter('total_pages',  ceil($pages - 1));
		$template -> setParameter('current_page', $page);
		$template -> setParameter('link_pages', 'http://'. TemplateHelper::getSiteUrl() .'/'. $board -> getId() .'/%d/');
		
		$session  -> instantSet('captcha_board', true);
		$session  -> instantSet('captcha_board_comment', true);

		EventModel::getInstance()
			-> Broadcast('view_board_threads', $_GET['board']);

		return true;
	}

	/**
	 * Просмотр треда:
	 */
	public function viewThreadAction(Application $application, Template $template)
	{
		$board   = new Board_BoardModel($_GET['board']);

		$session  = Session::getInstance();
		$settings = $board -> getSettings();

		$template -> setParameter('title',             $settings['title']);
		$template -> setParameter('description', $settings['description']);
		$template -> setParameter('board_id',      $board -> getId());

		$thread = $board -> getThread($_GET['thread_id']);
		
		if ($thread === false) {
			$template -> headerNotFound();
		}

		$this['form_errors'] = $session -> instantGet('board_post_errors', array());
		$this['board_form'] = $session -> instantGet('board_post_form', array());

		$stats = Board_StatisticsModel::getPostStats($thread['board_id'], $thread['id']);

		$template -> setParameter('total_read',   $stats['online']);
		$template -> setParameter('total_write',  $stats['writers']);
		$template -> setParameter('total_unique', $stats['unique']);

		$session  -> instantSet('captcha_board_comment', true);

		$this['post'] = $thread;

		EventModel::getInstance()
			-> Broadcast('view_board_thread', array($_GET['board'], $_GET['thread_id']));

		return true;
	}

	/**
	 * Просмотр избранных тредов:
	 */
	public function viewFavoritesAction(Application $application, Template $template)
	{
		$session  = Session::getInstance();
		$template -> setParameter('title', 'Избранные треды');
		$template -> setParameter('description', 'Список постов, отмеченных вами');
		$template -> setParameter('board_id', 'fav');

		$this['posts'] = Board_FavoritesModel::GetFavoritePosts();
		$session  -> instantSet('captcha_board_comment', true);

		EventModel::getInstance()
			-> Broadcast('view_board_favorites');

		return true;
	}

	/**
	 * Изменение заголовков:
	 */
	public function changeTitleAjaxAction(Application $application)
	{
		$session  = Session::getInstance();
		if ($session -> isAdminSession())
		{
			$board = new Board_BoardModel($_GET['board']);
			$board -> setSettings(array(
				'title' => $_GET['title'],
				'description' => $_GET['description']
			));
		}
		return false;
	}

	/**
	 * Просмотр последних постов:
	 */
	public function lastBoardPostsAction(Application $application, Template $template)
	{
		$template -> setParameter('title', 'Последние посты разделов');

		$page   = @$_GET['page'] ? $_GET['page'] : 0;
		$this['posts'] = Board_BoardModel::getLastPosts($page, $pages);

		$template -> setParameter('total_pages',  ceil($pages - 1));
		$template -> setParameter('current_page', $page);
		$template -> setParameter('link_pages', 'http://'. TemplateHelper::getSiteUrl() .'/service/last_board_posts/%d/');

		return true;
	}

	/**
	 * Подписка на раздел:
	 */
	public function subscribeBoardAjaxAction(Application $application, Template $template)
	{
		$key = Session::getInstance() -> getKey();
		return Board_BoardModel::subscribeBoard($key, $_GET['board']);
	}

	/**
	 * Отписка от раздела:
	 */
	public function unsubscribeBoardAjaxAction(Application $application, Template $template)
	{
		$key = Session::getInstance() -> getKey();
		return Board_BoardModel::unsubscribeBoard($key, $_GET['board']);
	}

	/**
	 * Проверка нотифаера:
	 */
	public function notifyCheckAjaxAction(Application $application)
	{
		$kvs = KVS::getInstance();
		$key = Session::getInstance() -> getKey();
		$kvs -> hashRemove('Notify', $key, 'messages', $_GET['board'] .'_'. $_GET['id']);
		return true;
	}

	/**
	 * Получение нотифаеров:
	 */
	public function notifyGetAjaxAction(Application $application)
	{
		$kvs = KVS::getInstance();
		$key = Session::getInstance() -> getKey();

		$messages = $kvs -> hashGetAll('Notify', $key, 'messages');
		$kvs -> remove('Notify', $key, 'messages');

		return $messages;
	}
}

/**
 * Обработчики событий:
 */
EventModel::getInstance()
	/**
	 * Статистика просмотра:
	 */
	-> AddEventListener('view_*', function() {
		Blog_BlogStatisticsModel::updateGlobalVisitors();
	})
	/**
	 * Статистика просмотра поста:
	 */
	-> AddEventListener('view_board_thread', function($data) {
		Board_StatisticsModel::updatePostStats($data[0], $data[1], false);
	});
