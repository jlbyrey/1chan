<?php
/**
 * Контроллер блога:
 */
class BlogController extends BaseController
{
    /**
     * Получение статистики:
     */
    public function getGlobalStatsAjaxAction(Application $application)
    {
        $stats = Blog_BlogStatisticsModel::getGlobalStats();
        return array(
					'global_online' => $stats['online'],
					'global_unique' => $stats['unique'],
					'global_posts'  => $stats['posts'],
					'global_speed'  => $stats['speed']
				);
    }

	/**
	 * Действие просмотра правил сайта:
	 */
	public function viewRulesAction(Application $application, Template $template) 
	{
		$template -> setParameter('title', 'Правила и советы');
		$template -> setParameter('board_id', 'news');

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (array_key_exists('accept', $_POST) && $_POST['accept'])
			{
				$session = Session::getInstance();
	    			$session -> persistenceSet('rules_accepted', true);

				$template -> headerSeeOther(
					'http://'. TemplateHelper::getSiteUrl() .'/news/add/'
				);
			}
		}
		
		$template -> setParameter('confirm', array_key_exists('confirm', $_GET) ? true : false);

		return true;
	}

	/**
	 * Действие просмотра одобренных новостей:
	 */
	public function viewApprovedAction(Application $application, Template $template)
	{
		$session = Session::getInstance();

		$template -> setParameter('title', 'Одобренные посты');
		$template -> setParameter('board_id', 'news');
		$template -> setParameter('section', 'rated');

		$page = @$_GET['page'] ? $_GET['page'] : 0;
		$sortby = $session -> persistenceGet('posts_sortby', 'created_at');

		$this['posts'] = Blog_BlogPostsModel::GetRatedPosts($page, 30, $sortby == 'updated_at', $pages);

		$template -> setParameter('total_pages',  ceil($pages - 1));
		$template -> setParameter('current_page', $page);
		$template -> setParameter('link_pages', 'http://'. TemplateHelper::getSiteUrl() .'/news/%d/');
		$template -> setParameter('sortby',       $sortby);

		EventModel::getInstance()
			-> Broadcast('view_approved_post');

		return true;
	}

	/**
	 * Действие просмотра всех новостей:
	 */
	public function viewAllAction(Application $application, Template $template)
	{
		$session = Session::getInstance();

		$template -> setParameter('title', 'Все посты');
		$template -> setParameter('board_id', 'news');
		$template -> setParameter('section', 'all');

		$page = @$_GET['page'] ? $_GET['page'] : 0;
		$sortby = $session -> persistenceGet('posts_sortby', 'created_at');

		$this['posts'] = Blog_BlogPostsModel::GetAllPosts($page, 30, $sortby == 'updated_at', $pages);

		$template -> setParameter('total_pages',  ceil($pages - 1));
		$template -> setParameter('current_page', $page);
		$template -> setParameter('link_pages', 'http://'. TemplateHelper::getSiteUrl() .'/news/all/%d/');
		$template -> setParameter('sortby',       $sortby);

		EventModel::getInstance()
			-> Broadcast('view_all_post');

		return true;
	}

	/**
	 * Дествие очистки сессии чтений (все прочитано):
	 */
    public function markAsReadAction(Application $application, Template $template)
	{
	    $session = Session::getInstance();
	    $session -> persistenceSet('last_visit', time());
	    $session -> activeFree();

	    $template -> headerSeeOther(
		    $_SERVER['HTTP_REFERER']
		);

		return false;
	}

	/**
	 * Действие просмотра избранных новостей:
	 */
	public function viewFavoriteAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		$sortby  = $session -> persistenceGet('posts_sortby', 'created_at');

		$template -> setParameter('title',   'Избранные посты');
		$template -> setParameter('board_id', 'news');
		$template -> setParameter('section', 'favorite');
		$template -> setParameter('sortby',   $sortby);

		$this['posts'] = Blog_BlogPostsModel::GetFavoritePosts($sortby == 'updated_at');

		EventModel::getInstance()
			-> Broadcast('view_favorite_post');


		return true;
	}

	/**
	 * Действие просмотра категорий:
	 */
	public function viewCategoriesAction(Application $application, Template $template)
	{
		$template -> setParameter('title', 'Категории');
		$template -> setParameter('board_id', 'news');
		$this['categories'] = Blog_BlogCategoryModel::GetCategories();

		EventModel::getInstance()
			-> Broadcast('view_categories_post');

		return true;
	}

	/**
	 * Действие просмотра категорий (ajax):
	 */
	public function viewCategoriesAjaxAction(Application $application)
	{
		$categories = Blog_BlogCategoryModel::GetPublicCategories();
		$result = array();

		foreach($categories as $category)
		{
			$result[] = array(
				'value' =>  $category['code'],
				'label'  =>  $category['title'] .' '. $category['code'] .' '. $category['description'],
				'title'   =>  $category['title'],
				'desc'   => $category['description']
			);
		}

		EventModel::getInstance()
			-> Broadcast('view_categories_post');

		return $result;
	}

	/**
	 * Действие просмотра новостей из категории:
	 */
	public function viewCategoryAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		$template -> setParameter('title', 'Посты из категории');
		$template -> setParameter('board_id', 'news');

		$page = @$_GET['page'] ? $_GET['page'] : 0;
		$sortby = $session -> persistenceGet('posts_sortby', 'created_at');

		if (($category = Blog_BlogCategoryModel::GetCategoryByName($_GET['category'])) !== false)
		{
			$template -> setParameter('title', 'Посты из категории «'. $category['title'] .'»');
			$template -> setParameter('section', 'category');
			$this['posts'] = Blog_BlogPostsModel::GetAllPostsFromCategory($category['id'], $page, 30, $sortby == 'updated_at', $pages);

			$template -> setParameter('total_pages',  ceil($pages - 1));
			$template -> setParameter('current_page', $page);
			$template -> setParameter('link_pages', 'http://'. TemplateHelper::getSiteUrl() .'/news/cat/'.$_GET['category'].'/%d/');
			$template -> setParameter('category_title', $category['title']);
			$template -> setParameter('rss_link', 'http://'. TemplateHelper::getSiteUrl() .'/news/cat/'.$_GET['category'].'/rss.xml');
			$template -> setParameter('sortby',       $sortby);
		}

		EventModel::getInstance()
			-> Broadcast('view_category_post');

		return true;
	}

	/**
	 * Действие просмотр rss ленты из категори:
	 */
	public function viewCategoryRssAction(Application $application, Template $template)
	{
		if (($category = Blog_BlogCategoryModel::GetCategoryByName($_GET['category'])) !== false)
		{
			$posts = Blog_BlogPostsModel::GetAllPostsFromCategory($category['id'], 0, 30, 'created_at');

			if ($posts)
			{
				$rss = new rss('utf-8');

				$rss->channel('Первый канал', 'http://1chan.ru/', 'Новости имиджборд и не только.');
				$rss->language('ru-ru');
				$rss->copyright('Все права пренадлежат вам © 2010');
				$rss->managingEditor('1kun.ebet.sobak@gmail.com');
				$rss->category('Посты из категории «'. $category['title'] .'»');

				$rss->startRSS();
				foreach($posts as $key => $post) {
					$title = $post['category'] ?
						TemplateHelper::BlogCategory($post['category'], 'title') .' — '. $post['title'] :
						$post['title'];

				    $rss->itemTitle($title);
				    $rss->itemLink('http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $post['id'] .'/');
				    $rss->itemDescription(($post['link'] ? '<a href="'.$post['link'].'">'.$post['link'].'</a><br />'.$post['text'] : $post['text']));
				    $rss->itemAuthor('anonymous');
				    $rss->itemGuid('http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $post['id'] .'/', true);
				    $rss->itemPubDate(date('D, d M Y H:i:s O', $post['created_at']));
				    $rss->addItem();
				}

				$result = $rss->RSSdone();
			}

			EventModel::getInstance()
				-> Broadcast('view_rss_approved_post');

			$template -> headerOk();
			$template -> headerContentType('application/rss+xml', 'UTF-8');
			echo $result;
			return false;
		}
	}

	/**
	 * Действие просмотра скрытых новостей:
	 */
	public function viewHiddenAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		$template -> setParameter('title', 'Скрытые посты');
		$template -> setParameter('board_id', 'news');
		$template -> setParameter('section', 'hidden');

		$page = @$_GET['page'] ? $_GET['page'] : 0;
		$sortby = $session -> persistenceGet('posts_sortby', 'created_at');

		$this['posts'] = Blog_BlogPostsModel::GetHiddenPosts($page, 30, $sortby == 'updated_at', $pages);

		$template -> setParameter('total_pages',  ceil($pages - 1));
		$template -> setParameter('current_page', $page);
		$template -> setParameter('link_pages', 'http://'. TemplateHelper::getSiteUrl() .'/news/hidden/%d/');
		$template -> setParameter('sortby',       $sortby);

		EventModel::getInstance()
			-> Broadcast('view_hidden_posts');

		return true;
	}

	/**
	 * Действие поиск поста:
	 */
	public function searchAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		$this['form_errors'] = array();
		$this['blog_form']   = array();

		$template -> setParameter('title', 'Поиск постов');
		$template -> setParameter('board_id', 'news');

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$validator = new ValidatorHelper($_POST);
			//$validator -> assertExists('query', 'Введите текст запроса');
			$validator -> assertLength('query', 80, 'Слишком длинный запрос поиска');
			$validator -> assertExists('order', 'Не указан порядок сортировки');
			$validator -> assertExists('sort', 'Не указано направление сортировки');

			$this['form_errors'] = $validator -> getValidationResults();
			$this['blog_form']   = $_POST;

			if ($validator -> isValid())
			{
				$search = new SphinxClient();
       			$search -> SetServer('localhost', 3312);

       			switch($_POST['order'])
       			{
       				default:
       				case 'relevance':
       					$sort = $_POST['sort'] == 'desc' ? '@relevance DESC, created_at DESC, @id DESC' : '@relevance ASC, created_at ASC, @id ASC';
       					$search -> SetSortMode(SPH_SORT_EXTENDED, $sort);
       					break;

					case 'created_at':
						$sort = $_POST['sort'] == 'desc' ? SPH_SORT_ATTR_DESC : SPH_SORT_ATTR_ASC;
       					$search -> SetSortMode($sort, 'created_at');
						break;

					case 'updated_at':
						$sort = $_POST['sort'] == 'desc' ? SPH_SORT_ATTR_DESC : SPH_SORT_ATTR_ASC;
       					$search -> SetSortMode($sort, 'updated_at');
						break;

					case 'rate':
						$sort = $_POST['sort'] == 'desc' ? SPH_SORT_ATTR_DESC : SPH_SORT_ATTR_ASC;
       					$search -> SetSortMode($sort, 'rate');
						break;

					case 'comments':
						$sort = $_POST['sort'] == 'desc' ? SPH_SORT_ATTR_DESC : SPH_SORT_ATTR_ASC;
       					$search -> SetSortMode($sort, 'comments');
						break;
       			}

				$search -> SetWeights (array('link' => 50, 'title' => 40, 'text' => 20, 'text_full' => 10));
				$search -> SetLimits(0, 60);

				if (isset($_POST['category']) && $_POST['category'] != 0)
					$search -> SetFilter('category', array($_POST['category']));

				$result = $search -> Query($_POST['query'], 'posts');

				if ($result && array_key_exists('matches', $result) && is_array($result['matches']))
				{
					$ids   = array_keys($result['matches']);
					$posts = Blog_BlogPostsModel::GetPostsByIds($ids);
					$this['posts'] = $posts;

					$template -> setParameter('total', sizeof($result['matches']));
					$template -> setParameter('total_found', $result['total_found']);
				}
				else
				{
					$this['form_errors'] = array('По данному запросу ничего не найдено');
				}
			}
		}

		EventModel::getInstance()
			-> Broadcast('view_search_post');

		return true;
	}

	/**
	 * Перессылка на первый непрочитанный пост:
	 */
	public function viewNewPostAction(Application $application, Template $template)
	{
	    $session = Session::getInstance();
		$posts = Blog_BlogPostsModel::GetAllPosts(0, 30, 'updated_at', $pages);
        foreach($posts as $post) {
            if (TemplateHelper::isPostUpdated($post, false)) {
                $template -> headerSeeOther(
						'http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $post['id'] .'/#new'
				);
			    return false;
            }
        }
		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/news/all/'
		);
	    return false;
	}

	/**
	 * Действие просмотра поста:
	 */
	public function viewPostAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		$this['form_errors'] = $session -> instantGet('comment_errors', array());
		$this['blog_form']   = $session -> instantGet('comment_form', array());

		$template -> setParameter('title',   'Пост не найден');
		$template -> setParameter('board_id', 'news');
		$template -> setParameter('section', 'entry');

		if (isset($_GET['id']))
		{
			$post = Blog_BlogPostsModel::GetPost($_GET['id']);
			if ($post)
			{
				// Сложная и ебанутая система определения прочитан ли пост:
				if (!$session -> activeGet('last_visit_post_'. $_GET['id'], false)) {
					$session -> instantSet('last_visit_post_'. $_GET['id'], $session -> activeGet('last_visit'));
					$session -> activeSet('last_visit_post_'. $_GET['id'], time());
				}
				else
				{
					$session -> instantSet('last_visit_post_'. $_GET['id'], $session -> activeGet('last_visit_post_'. $_GET['id']));
					$session -> activeSet('last_visit_post_'. $_GET['id'], time());
				}

				$template -> setParameter('title', 'Пост №'. $_GET['id']);
				$this['comments'] = Blog_BlogCommentsModel::GetComments($_GET['id']);
				$this['post']     = $post;

				if (ControlModel::isCommentCaptcha())
				{
					$key     = 'comment';
					$template -> setParameter('captcha_key', $key);
					$session  -> instantSet('captcha_'.$key, true);
				}

				EventModel::getInstance()
					-> Broadcast('view_post', $post['id']);
			}
		}

		$stats = Blog_BlogStatisticsModel::getPostStats($post['id']);

		$template -> setParameter('total_read',   $stats['online']);
		$template -> setParameter('total_write',  $stats['writers']);
		$template -> setParameter('total_unique', $stats['unique']);

		return true;
	}

	/**
	 * Просмотр поста (ajax):
	 */
	public function viewPostAjaxAction(Application $application)
	{
		$session = Session::getInstance();
		$session -> activeSet('last_visit_post_'. $_GET['id'], time());
		return true;
	}

	/**
	 * Получение коммента:
	 */
	public function getPostCommentAjaxAction()
	{
	    $cache  = KVS::getInstance();
		$comment = Blog_BlogCommentsModel::GetComment($_GET['id']);

		$session = Session::getInstance();
		$session -> activeSet('last_visit_post_'. $comment['post_id'], time());

		if ($comment)
		{
		    if ($_GET['title']) {
			    $post = Blog_BlogPostsModel::GetPost($comment['post_id']);
			    $comment['post_title'] = $post['title'];
			}

			$comment['created_at'] = TemplateHelper::date('d M Y @ H:i', $comment['created_at']);
			$comment['author']     = array($comment['author'], HomeBoardHelper::getBoard($comment['author']));
			unset($comment['ip']);

			return $comment;
		}

		return false;
	}

	/**
	 * Действие оценки поста:
	 */
	public function ratePostAction(Application $application, Template $template)
	{
		if (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) != TemplateHelper::getSiteUrl())
			return false;

		if (ControlModel::isPostRateCaptcha())
		{
			$settings = ControlModel::GetSettings();
			$session  = Session::getInstance();
			$template -> setParameter('referer', $_SERVER['HTTP_REFERER']);

			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				if ($_POST['captcha'] === $session -> instantGet('captcha_'. $_POST['captcha_key'], false))
				{
					if (@$_GET['vote'] == 'up')
					Blog_BlogPostsModel::RatePost($_GET['id'], true);

					elseif(@$_GET['vote'] == 'down')
						Blog_BlogPostsModel::RatePost($_GET['id'], false);

					$session -> persistenceSet('captcha_mode', false);
					$session -> persistenceSet('captcha_mode_length', @$settings['captcha_length']);

					$template -> headerSeeOther(
						$_POST['referer']
					);
					return false;
				}
				else
				{
					$template -> setParameter('captcha_err', true);
				}

				$template -> setParameter('referer', $_POST['referer']);
			}

			$key = 'rate';
			$template -> setParameter('title',   'Голосование за пост');
			$template -> setParameter('board_id', 'news');
			$template -> setParameter('captcha_key', $key);
			$session  -> instantSet('captcha_'.$key, true);
			return true;
		}

		if (@$_GET['vote'] == 'up')
			Blog_BlogPostsModel::RatePost($_GET['id'], true);

		elseif(@$_GET['vote'] == 'down')
			Blog_BlogPostsModel::RatePost($_GET['id'], false);

		$template -> headerSeeOther(
			$_SERVER['HTTP_REFERER']
		);
		return false;
	}

	/**
	 * Действие оценки поста (ajax):
	 */
	public function ratePostAjaxAction(Application $application)
	{
		$ip    = $_SERVER['REMOTE_ADDR'];
		$cache = KVS::getInstance();

		$raters = (array)unserialize($cache -> get('Blog_BlogPostsModel', $_GET['id'], 'raters'));

		if (in_array($ip, $raters))
			return array('rate' => false);

		if (ControlModel::isPostRateCaptcha())
			return array('rate' => 'captcha');

		if (@$_GET['vote'] == 'up')
			return array('rate' => Blog_BlogPostsModel::RatePost($_GET['id'], true));

		elseif(@$_GET['vote'] == 'down')
			return array('rate' => Blog_BlogPostsModel::RatePost($_GET['id'], false));
	}

	/**
	 * Действие: добавление/удаление из избранного:
	 */
	public function toggleFavoriteAction(Application $application, Template $template)
	{
		Blog_BlogPostsModel::ToggleFavoritePost($_GET['id']);
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
		return array('favorite' => Blog_BlogPostsModel::ToggleFavoritePost($_GET['id']));
	}

	/**
	 * Действие добавления поста:
	 */
	public function addPostAction(Application $application, Template $template)
	{
		$session  = Session::getInstance();
		$settings = ControlModel::GetSettings();

		if ($session -> isJustCreated())
			return false;

		if (!$session -> persistenceGet('rules_accepted'))
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/help/news/?confirm'
			);

		$this['form_errors'] = array();
		$this['blog_form']   = array();

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$text_test      = ControlModel::checkContent($_POST['text']);
			$text_full_test = ControlModel::checkContent($_POST['text_full']);

			$validator = new ValidatorHelper($_POST);
			if (ControlModel::isPostCaptcha())
			{
				$validator -> assertExists('captcha_key', 'Введите капчу');
				if ($_POST['captcha_key'])
					$validator -> assertEqual(
						'captcha', $session -> instantGet('captcha_'. $_POST['captcha_key'], false),
						'Капча введена неверно'
					);
			}

			$validator -> assertExists('title',           'Не введен заголовок');
			$validator -> assertLength('title',     70,   'Заголовок слишком длинный');
			$validator -> assertExists('text',            'Не введен вводный текст');
			$validator -> assertLength('text',      1024, 'Вводный текст слишком длинный');

			if (!$session -> isModeratorSession())
			    $validator -> assertLength('text_full', 5096, 'Подробный текст слишком длинный');

			$validator -> assertNotExists('email',        'Заполнено лишнее поле');

			if ($validator -> fieldValid('title'))
				$validator -> assertLengthMore('title', 3, 'Заголовок слишком короткий');

			if ($validator -> fieldValid('text'))
				$validator -> assertLengthMore('text', 15, 'Вводный текст слишком короткий');

			if ($validator -> fieldValid('title'))
				$validator -> assertTrue('title', mb_substr($_POST['title'], -1, 1, 'UTF-8') != '.', 'Точка в конце заголовка');


			$validator -> assertTrue(
				'text', $text_test && $text_full_test,
				'Запрещенное слово из вордфильтра'
			);

			$validator -> assertTrue(
				'timeout', ControlModel::getPostInterval() == 0,
				'Таймаут '. TemplateHelper::ending(ControlModel::getPostInterval(), 'секунда', 'секунды', 'секунд')
			);

			if ($_POST['link'] != '')
				$validator -> assertRegexp('link', ValidatorHelper::URL_REGEXP, 'Ссылка введена неверно');

			if ($validator -> fieldValid('link'))
				$validator -> assertTrue('link', ControlModel::CheckLinkfilter($_POST['link']) == false, 'Ссылка запрещена');

			if ($_POST['category'] != '')
				$validator -> assertTrue('category', Blog_BlogCategoryModel::CategoryExists($_POST['category']), 'Неверный ключ категории');

			if ($validator -> isValid())
			{
				$id = Blog_BlogPostsModel::CreatePost($_POST, true);
				if (ControlModel::checkModrights(Blog_BlogCategoryModel::GetCategoryIdByCode($_POST['category'])))
				{
					if ($_POST['rated'])
						Blog_BlogPostsModel::RatedPost($id, true);

					if ($_POST['pinned'])
						Blog_BlogPostsModel::PinPost($id, true);

					if ($_POST['notrateable'])
						Blog_BlogPostsModel::RateablePost($id, false);

					if ($_POST['closed'])
						Blog_BlogPostsModel::ClosePost($id, true);
				}

				if (ControlModel::isPostPremoderation()) {
					Blog_BlogPostsModel::HidePost($id, true);
			        Blog_BlogPostsModel::SetSpecialComment($id, 'Пост ожидает модерации');
			    }

				if (ControlModel::isPostHandApproving())
					Blog_BlogPostsModel::RateablePost($id, false);


				$session -> persistenceSet('captcha_mode', false);
				$session -> persistenceSet('captcha_mode_length', @$settings['captcha_length']);

				$template -> headerSeeOther(
					'http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $id .'/'
				);
				return false;
			}

			$this['form_errors'] = $validator -> getValidationResults();
			$this['blog_form']   = $_POST;
		}

		if (ControlModel::isPostCaptcha())
		{
			$key     = 'post';
			$template -> setParameter('captcha_key', $key);
			$session  -> instantSet('captcha_'.$key, true);
		}

		$template -> setParameter('title', 'Добавить пост');
		$template -> setParameter('board_id', 'news');
		$template -> setParameter('section', 'add');

		EventModel::getInstance()
			-> Broadcast('view_add_post');

		return true;
	}

	/**
	 * Действие валидации поста (ajax):
	 */
	public function validatePostAjaxAction(Application $application)
	{
	    $session = Session::getInstance();

		$validator = new ValidatorHelper($_POST);
		$validator -> assertExists('title',           'Не введен заголовок');
		$validator -> assertLength('title',     70,   'Заголовок слишком длинный');
		$validator -> assertExists('text',            'Не введен вводный текст');
		$validator -> assertLength('text',      1024, 'Вводный текст слишком длинный');


		if (!$session -> isModeratorSession())
		    $validator -> assertLength('text_full', 5096, 'Подробный текст слишком длинный');

		if ($_POST['link'] != '')
			$validator -> assertRegexp('link', ValidatorHelper::URL_REGEXP, 'Ссылка введена неверно');

		if ($validator -> fieldValid('link'))
			$validator -> assertTrue('link', ControlModel::CheckLinkfilter($_POST['link']) == false, 'Ссылка запрещена');

		if ($validator -> fieldValid('title'))
			$validator -> assertLengthMore('title', 3, 'Заголовок слишком короткий');

		if ($validator -> fieldValid('text'))
			$validator -> assertLengthMore('text', 15, 'Вводный текст слишком короткий');

		if ($validator -> fieldValid('title'))
			$validator -> assertTrue('title', mb_substr($_POST['title'], -1, 1, 'UTF-8') != '.', 'Точка в конце заголовка');

		if ($_POST['category'] != '')
			$validator -> assertTrue('category', Blog_BlogCategoryModel::CategoryExists($_POST['category']), 'Неверный ключ категории');

		return array(
			'isValid'           => $validator -> isValid(),
			'validationResults' => $validator -> getValidationResults()
		);
	}

	/**
	 * Действие предпросмотра поста (ajax):
	 */
	public function previewPostAjaxAction(Application $application)
	{
		$preview = array();
		$preview['title']     = TexyHelper::typo(@$_POST['title']);
		$preview['text']      = TexyHelper::markup(@$_POST['text'], true);
		$preview['text_full'] = TexyHelper::markup(@$_POST['text_full'], true);
		$preview['icon']      = @$_POST['link'] ? TemplateHelper::getIcon(@$_POST['link']) : 'http://'. TemplateHelper::getSiteUrl() .'/ico/favicons/1chan.ru.gif';

		if (array_key_exists('category', $_POST) && !empty($_POST['category']))
		{
			if (Blog_BlogCategoryModel::CategoryExists($_POST['category']))
			{
				$category = Blog_BlogCategoryModel::GetCategoryByCode($_POST['category']);
				$preview['category'] = TemplateHelper::BlogCategory($category['id'], 'title');
			}
		}

		return $preview;
	}

	/**
	 * Действие обновление статистики:
	 */
	public function postStatsAjaxAction(Application $application)
	{
		Blog_BlogStatisticsModel::updatePostStats($_GET['post_id'], $_GET['writing']);
		return Blog_BlogStatisticsModel::getPostStats($_GET['post_id']);
	}

	/**
	 * Действие добавление комментария:
	 */
	public function addCommentAction(Application $application, Template $template)
	{
		$session  = Session::getInstance();
		$settings = ControlModel::GetSettings();

		if ($session -> isJustCreated())
			return false;

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$text_test = ControlModel::checkContent($_POST['text']);

			$validator = new ValidatorHelper($_POST);
			if (ControlModel::isCommentCaptcha())
			{
				$validator -> assertExists('captcha_key', 'Введите капчу');
				if ($_POST['captcha_key'])
					$validator -> assertEqual(
						'captcha', $session -> instantGet('captcha_'. $_POST['captcha_key'], false),
						'Капча введена неверно'
					);
			}
			$validator -> assertExists('text',       'Не введен текст комментария');
			$validator -> assertExists('post_id',    'Не указан идентификатор поста');
			$validator -> assertLength('text', 2048, 'Текст комментария слишком длинный');
			$validator -> assertNotExists('email',   'Заполнено лишнее поле');

			$validator -> assertTrue(
				'text', $text_test,
				'Запрещенное слово из вордфильтра'
			);

			$validator -> assertTrue(
				'timeout', ControlModel::getPostCommentInterval() == 0,
				'Таймаут '. TemplateHelper::ending(ControlModel::getPostCommentInterval(), 'секунда', 'секунды', 'секунд')
			);

			if ($validator -> isValid())
			{
				$id = Blog_BlogCommentsModel::CreateComment($_POST, true);
				$session -> activeSet('last_visit_post_'. $_POST['post_id'], time());

				$session -> persistenceSet('captcha_mode', false);
				$session -> persistenceSet('captcha_mode_length', @$settings['captcha_length']);

				$template -> headerSeeOther(
					'http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $_GET['post_id'] .'/#'. $id
				);
				return false;
			}

			$session -> instantSet('comment_errors', $validator -> getValidationResults());
			$session -> instantSet('comment_form', $_POST);
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $_GET['post_id'] .'/#comment_form'
			);
			return false;
		}

		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/news/'
		);
		return false;
	}

	/**
	 * Действие добавления комментария (ajax):
	 */
	public function addCommentAjaxAction(Application $application)
	{
		$session = Session::getInstance();

		if ($session -> isJustCreated())
			return false;

		$validator = new ValidatorHelper($_POST);
		$validator -> assertExists('text',       'Не введен текст комментария');
		$validator -> assertExists('post_id',    'Не указан идентификатор поста');
		$validator -> assertLength('text', 2048, 'Текст комментария слишком длинный');
		$validator -> assertNotExists('email',   'Заполнено лишнее поле');

		$text_test = ControlModel::checkContent($_POST['text']);
		if (ControlModel::isCommentCaptcha())
			return array('captcha' => true);

		$validator -> assertTrue(
			'text', $text_test,
			'Запрещенное слово из вордфильтра'
		);

		$validator -> assertTrue(
			'timeout', ControlModel::getPostCommentInterval() == 0,
			'Таймаут '. TemplateHelper::ending(ControlModel::getPostCommentInterval(), 'секунда', 'секунды', 'секунд')
		);

		if ($validator -> isValid()) {
			$id = Blog_BlogCommentsModel::CreateComment($_POST, true);
			$session -> activeSet('last_visit_post_'. $_POST['post_id'], time());
		}

		return array(
			'isValid'           => $validator -> isValid(),
			'validationResults' => $validator -> getValidationResults()
		);
	}

	/**
	 * Действие просмотр rss ленты одобренных постов:
	 */
	public function rssApprovedAction(Application $application, Template $template)
	{
		$posts = Blog_BlogPostsModel::GetRatedPosts(0, 20, false);

		if ($posts)
		{
			$rss = new rss('utf-8');

			$rss->channel('Первый канал - Одобренные', 'http://1chan.ru/', 'Новости имиджборд и не только.');
			$rss->language('ru-ru');
			$rss->copyright('Все права пренадлежат вам © 2010');
			$rss->managingEditor('1kun.ebet.sobak@gmail.com');
			$rss->category('Одобренные');

			$rss->startRSS();
			foreach($posts as $key => $post) {
				$title = $post['category'] ?
					TemplateHelper::BlogCategory($post['category'], 'title') .' — '. $post['title'] :
					$post['title'];

			    $rss->itemTitle($title);
			    $rss->itemLink('http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $post['id'] .'/');
			    $rss->itemDescription(($post['link'] ? '<a href="'.$post['link'].'">'.$post['link'].'</a><br />'.$post['text'] : $post['text']));
			    $rss->itemAuthor('anonymous');
			    $rss->itemGuid('http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $post['id'] .'/', true);
			    $rss->itemPubDate(date('D, d M Y H:i:s O', $post['created_at']));
			    $rss->addItem();
			}

			$result = $rss->RSSdone();
		}

		EventModel::getInstance()
			-> Broadcast('view_rss_approved_post');

		$template -> headerOk();
		$template -> headerContentType('application/rss+xml', 'UTF-8');
		echo $result;
		return false;
	}

	/**
	 * Действие просмотр rss ленты всех постов:
	 */
	public function rssAllAction(Application $application, Template $template)
	{
		$posts = Blog_BlogPostsModel::GetAllPosts(0, 20, false);

		if ($posts)
		{
			$rss = new rss('utf-8');

			$rss->channel('Первый канал - Все', 'http://1chan.ru/', 'Новости имиджборд и не только.');
			$rss->language('ru-ru');
			$rss->copyright('Все права пренадлежат вам © 2010');
			$rss->managingEditor('1kun.ebet.sobak@gmail.com');
			$rss->category('Все');

			$rss->startRSS();
			foreach($posts as $key => $post) {
				$title = $post['category'] ?
					TemplateHelper::BlogCategory($post['category'], 'title') .' — '. $post['title'] :
					$post['title'];

			    $rss->itemTitle($title);
			    $rss->itemLink('http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $post['id'] .'/');
			    $rss->itemDescription(($post['link'] ? '<a href="'.$post['link'].'">'.$post['link'].'</a><br />'.$post['text'] : $post['text']));
			    $rss->itemAuthor('anonymous');
			    $rss->itemGuid('http://'. TemplateHelper::getSiteUrl() .'/news/res/'. $post['id'] .'/', true);
			    $rss->itemPubDate(date('D, d M Y H:i:s O', $post['created_at']));
			    $rss->addItem();
			}

			$result = $rss->RSSdone();
		}

		EventModel::getInstance()
			-> Broadcast('view_rss_all_post');

		$template -> headerOk();
		$template -> headerContentType('application/rss+xml', 'UTF-8');
		echo $result;

		return false;
	}

	/**
	 * Действие выбора сортировки:
	 */
	public function sortAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		$session -> persistenceSet('posts_sortby', $_GET['sortby']);

		$template -> headerSeeOther($_SERVER['HTTP_REFERER']);
		return false;
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
	-> AddEventListener('view_post', function($data) {
		Blog_BlogStatisticsModel::updatePostStats($data, false);
	});
