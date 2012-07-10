<?php
/**
 *
 */
class AdminController extends Controller
{
	/**
	 * Конструктор (проверка авторизации):
	 */
	public function __construct(Application $application, Template $template)
	{
		$session = Session::getInstance();

		if (!$session -> isAdminSession())
			die($application -> go('errors_error403'));
	}

	/**
	 * Выход:
	 */
	public function logoutAction(Application $application, Template $template)
	{
		unset($_SESSION['auth']);
		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/b/'
		);
		return false;
	}

	/**
	 * Главная страница (список всех постов):
	 */
	public function postsAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'post_list');

		$page = @$_GET['page'] ? $_GET['page'] : 0;

		switch(@$_GET['filter'])
		{
			default:
			case 'all':
				$template -> setParameter('filter', 'all');
				$this['posts'] = Blog_BlogPostsModel::GetAllPosts($page, 30, false, $pages);

				break;
			case 'rated':
				$template -> setParameter('filter', 'rated');
				$this['posts'] = Blog_BlogPostsModel::GetRatedPosts($page, 30, false, $pages);

				break;
			case 'hidden':
				$template -> setParameter('filter', 'hidden');
				$this['posts'] = Blog_BlogPostsModel::GetHiddenPosts($page, 30, true, $pages);

				break;
		}
		$template -> setParameter('total_pages',  $pages);
		$template -> setParameter('current_page', $page);
		return true;
	}

	/**
	 * Добавить пост:
	 */
	public function postAddAction(Application $application, Template $template)
	{
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$template -> setParameter('menu', 'posts');
			$template -> setParameter('submenu', 'post_add');

			return true;
		}
		else
		{
			Blog_BlogPostsModel::CreatePost($_POST, false);
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/admin/posts/'
			);

			exit;
		}
	}

	/**
	 * Редактировать пост:
	 */
	public function postEditAction(Application $application, Template $template)
	{
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$template -> setParameter('menu', 'posts');
			$this['post'] = Blog_BlogPostsModel::GetPost($_GET['id']);
			return true;
		}
		else
		{
			Blog_BlogPostsModel::EditPost($_POST['id'], $_POST, false);
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/admin/postEdit?id='. $_POST['id']
			);

			exit;
		}
	}

	/**
	 * Удаление поста:
	 */
	public function postDeleteAction(Application $application, Template $template)
	{
		Blog_BlogPostsModel::RemovePost($_GET['id']);
		$template -> headerSeeOther(
			$_SERVER['HTTP_REFERER']
		);

		exit;
	}

	/**
	 * Поиск постов:
	 */
	public function postSearchAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'post_search');

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (!empty($_POST['id']))
			{
				$post = Blog_BlogPostsModel::GetPost($_POST['id']);

				if ($post)
					$this['posts'] = array($post);
			}
			else
			{
				$search = new SphinxClient();
       			$search -> SetServer('localhost', 3312);
        		$search -> SetSortMode(SPH_SORT_EXTENDED, '@relevance DESC, created_at DESC, @id DESC');
				$search -> SetLimits(0, 60);
				$search -> SetWeights ( array ('title' => 40, 'text' => 20, 'text_full' => 10 ) );

				if (isset($_POST['category']))
					$search -> SetFilter('category', $_POST['category']);

				$result = $search -> Query($_POST['query'], '*');

				if (is_array($result['matches']))
				{
					$ids = array_keys($result['matches']);
					$this['posts'] = Blog_BlogPostsModel::GetPostsByIds($ids);
				}
			}
		}

		return true;
	}

	/**
	 * Управление комментариями:
	 */
	public function postCommentsAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'post_comments');

		return true;
	}

	/**
	 * Редактирование комментария:
	 */
	public function postCommentEditAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'post_comments');

		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$this['post'] = Blog_BlogCommentsModel::GetComment($_GET['id']);
			return true;
		}
		else
		{
			Blog_BlogCommentsModel::EditComment($_POST['id'], $_POST, false);
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/admin/postCommentEdit?id='. $_POST['id']
			);

			exit;
		}

		return true;
	}

	/**
	 * Удаление поста:
	 */
	public function postCommentDeleteAction(Application $application, Template $template)
	{
		Blog_BlogCommentsModel::RemoveComment($_GET['id']);
		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/admin/postComments'
		);

		exit;
	}

	/**
	 * Список категорий:
	 */
	public function postCategoryAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'post_category');

		$this['cats'] = Blog_BlogCategoryModel::GetCategories();

		return true;
	}

	/**
	 * Список категорий:
	 */
	public function postCategoryAddAction(Application $application, Template $template)
	{
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$template -> setParameter('menu', 'posts');
			$template -> setParameter('submenu', 'post_category');

			return true;
		}
		else
		{
			Blog_BlogCategoryModel::CreateCategory($_POST, false);
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/admin/postCategory/'
			);

			exit;
		}
	}

	/**
	 * Редактировать категорию:
	 */
	public function postCategoryEditAction(Application $application, Template $template)
	{
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$template -> setParameter('menu', 'posts');
			$template -> setParameter('submenu', 'post_category');
			$this['cat'] = Blog_BlogCategoryModel::GetCategoryById($_GET['id']);
			return true;
		}
		else
		{
			Blog_BlogCategoryModel::EditCategory($_POST['id'], $_POST);
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/admin/postCategoryEdit?id='. $_POST['id']
			);

			exit;
		}
	}

	/**
	 * Сортировка категорий:
	 */
	public function postCategoryResortAction(Application $application, Template $template)
	{
		Blog_BlogCategoryModel::Resort($_POST['pos']);
		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/admin/postCategory/'
		);
		exit;
	}

	/**
	 * Удаление категория:
	 */
	public function postCategoryDeleteAction(Application $application, Template $template)
	{
		Blog_BlogCategoryModel::RemoveCategory($_GET['id']);
		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/admin/postCategory/'
		);

		exit;
	}

	/**
	 * Управление настройками:
	 */
	public function blogSettingsAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'settings');

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			ControlModel::SetSettings($_POST);
		}

		$this['settings'] = ControlModel::GetSettings();
		return true;
	}

	/**
	 * Управление вордфильтром:
	 */
	public function blogWordfilterAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'wordfilter');

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$words = explode("\r\n", $_POST['wordfilter']);
			array_filter($words);
			ControlModel::SetWordfilter($words);

			$links = explode("\r\n", $_POST['linkfilter']);
			array_filter($links);
			ControlModel::SetLinkfilter($links);

			$links = explode("\r\n", $_POST['spamfilter']);
			array_filter($links);
			ControlModel::SetSpamfilter($links);
		}

		$words = ControlModel::GetWordfilter();
		$links = ControlModel::GetLinkfilter();
		$spam  = ControlModel::GetSpamfilter();

		$this['wordfilter'] = implode("\n", (array)$words);
		$this['linkfilter'] = implode("\n", (array)$links);
		$this['spamfilter'] = implode("\n", (array)$spam);
		return true;
	}

	/**
	 * Управление модераторами:
	 */
	public function blogModeratorsAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'moderators');

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$moderators = array();
			$strings    = explode("\r\n", $_POST['moderators']);

			foreach ($strings as $str)
			{
				if (empty($str)) continue;

				list($name, $key, $class, $category)   = explode(' | ', $str);
				$moderators[] = array(
					'name' => $name,   'key' => $key,
					'class' => $class, 'category' => $category
				);
			}

			ControlModel::SetModerators($moderators);
		}

		$moderators = ControlModel::GetModerators();
		$mods       = '';

		foreach ($moderators as $mod)
		{
			$mods .= $mod['name'] ." | ". $mod['key'] ." | ". $mod['class'] ." | ". $mod['category'] ."\n";
		}

		$this['moderators'] = $mods;
		return true;
	}

	/**
	 * Управление ссылками:
	 */
	public function blogLinksAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'links');

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$links     = array();
			$imgboards = explode("\n", $_POST['imgboards']);
			$services  = explode("\n", $_POST['services']);

			foreach ($imgboards as $str)
			{
				if (empty($str)) continue;

				list($href, $title)   = explode(' | ', $str);
				$links['imgboards'][] = array('href' => $href, 'title' => $title);
			}

			foreach ($services as $str)
			{
				if (empty($str)) continue;

				list($href, $title)  = explode(' | ', $str);
				$links['services'][] = array('href' => $href, 'title' => $title);
			}

			Blog_BlogLinksModel::SetLinks($links);
		}

		$links = Blog_BlogLinksModel::GetLinks();
		$imgboards = '';
		$services  = '';

		foreach ($links['imgboards'] as $link)
		{
			$imgboards .= $link['href'] ." | ". $link['title'] ."\n";
		}

		foreach ($links['services'] as $link)
		{
			$services .= $link['href'] ." | ". $link['title'] ."\n";
		}

		$this['imgboards'] = $imgboards;
		$this['services']  = $services;
		return true;
	}

	/**
	 * Управление ссылками:
	 */
	public function blogLogAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'log');
		$log = ControlModel::getLogModEvent();
		$this['log'] = '<p>'. implode("</p><p>", array_reverse($log)) .'</p>';
		return true;
	}

	/**
	 * Управление каналами ротатора:
	 */
	public function onlineChannelAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'posts');
		$template -> setParameter('submenu', 'online_channel');

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$cats = array();
			$strings    = explode("\n", $_POST['channels']);

			foreach ($strings as $str)
			{
				if (empty($str)) continue;

				list($title, $regexp, $url)   = explode(' :|: ', $str);
				$cats[] = array(
					'title' => $title, 'regexp' => $regexp, 'url' => $url
				);
			}

			Blog_BlogOnlineModel::SetCategories($cats);
		}

		$cats = Blog_BlogOnlineModel::GetCategories();
		$result       = '';

		foreach ($cats as $cat)
		{
			$result .=  $cat['title'] .' :|: '.  $cat['regexp'] .' :|: '. $cat['url'] ."\n";
		}

		$this['channels'] = $result;
		return true;
	}

	/**
	 * Список чатов:
	 */
	public function chatsAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'chats');
		$template -> setParameter('submenu', 'chats');
		
		$this['chats'] = Chat_ChatRoomsModel::GetAllRooms();
		
		return true;
	}
	
	/**
	 * Добавление чата:
	 */
	public function chatAddAction(Application $application, Template $template)
	{
	    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
	    {
			Chat_ChatRoomsModel::CreateRoom($_POST);
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/admin/chats/'
			);

			exit;
	    }
	    
		$template -> setParameter('menu', 'chats');
		$template -> setParameter('submenu', 'chat_add');
	    return true;
	}
	
	/**
	 * Редактирование чата:
	 */
	public function chatEditAction(Application $application, Template $template)
	{
	    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
	    {
	        if (!isset($_POST['public'])) $_POST['public'] = false;
	        
			Chat_ChatRoomsModel::EditRoom($_POST['id'], $_POST);
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/admin/chatEdit?id='. $_POST['id']
			);

			exit;
	    }
	    
		$template -> setParameter('menu', 'chats');
		$template -> setParameter('submenu', 'chat_add');
	    
	    $this['room'] = Chat_ChatRoomsModel::GetRoom($_GET['id']);
	    
	    return true;
	}
	
	/**
	 * Удаление чата:
	 */
	public function chatDeleteAction(Application $application, Template $template)
	{
		Chat_ChatRoomsModel::RemoveRoom($_GET['id']);
		$template -> headerSeeOther(
			$_SERVER['HTTP_REFERER']
		);

		exit;
	}

	/**
	 * Просмотр страниц:
	 */
	 public function staticPagesAction(Application $application, Template $template)
	{
		$template -> setParameter('menu', 'static');
		$template -> setParameter('submenu', 'static_pages');

		$pages = StaticModel::GetPages();
		ksort($pages);
		$this['pages'] = $pages;
		return true;
	}

	/**
	 * Добавление страницы:
	 */
	public function staticAddAction(Application $application, Template $template)
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			StaticModel::SetPage($_POST['page'], $_POST['title'], $_POST['content'], $_POST['published']);
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/admin/staticPages/'
			);

			exit;
		}

		$template -> setParameter('menu', 'static');
		$template -> setParameter('submenu', 'static_add');

		return true;
	}

	/**
	 * Редактирование страницы:
	 */
	public function staticEditAction(Application $application, Template $template)
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($_POST['old_page'] != $_POST['page'])
				StaticModel::RemovePage($_POST['old_page']);

			StaticModel::SetPage($_POST['page'], $_POST['title'], $_POST['content'], $_POST['published']);
			$template -> headerSeeOther(
				'http://'. TemplateHelper::getSiteUrl() .'/admin/staticEdit?page='.$_POST['page']
			);

			exit;
		}

		$template -> setParameter('menu', 'static');
		$this['page'] = StaticModel::GetPage($_GET['page']);
		return true;
	}

	/**
	 * Удаление страницы:
	 */
	public function staticDeleteAction(Application $application, Template $template)
	{
		StaticModel::RemovePage($_GET['page']);
		$template -> headerSeeOther(
			$_SERVER['HTTP_REFERER']
		);

		exit;
	}

	/**
	 * Список загруженных файлов:
	 */
	public function staticFilesAction(Application $application, Template $template)
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (array_key_exists('upload', $_FILES))
				StaticModel::CreateFile($_FILES['upload']);
		}
		
		$template -> setParameter('menu', 'static');
		$template -> setParameter('submenu', 'static_files');

		$this['files'] = StaticModel::GetFiles();
		return true;
	}

	/**
	 * Удаление страницы:
	 */
	public function staticFilesDeleteAction(Application $application, Template $template)
	{
		StaticModel::RemoveFile($_GET['name']);
		$template -> headerSeeOther(
			$_SERVER['HTTP_REFERER']
		);

		exit;
	}
	
	/**
	 * Выбор лейаута:
	 */
	public function process(Template $template)
	{
		return $template -> render($this -> viewParams, 'admin_layout');
	}
}
