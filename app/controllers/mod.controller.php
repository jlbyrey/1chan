<?php
/**
 * Контроллер модераторских действий.
 */
class ModController extends BaseController
{
	/**
	 * Действие просмотра последний комментариев:
	 */
	public function getLastCommentsAction(Application $application, Template $template)
	{
		$template -> setParameter('title', 'Последние комментарии');
		$template -> setParameter('board_id', 'news');
		$this['comments'] = Blog_BlogCommentsModel::GetLastComments();
		return true;
	}

	/**
	 * Действие просмотра лога модераторских действий:
	 */
	public function getModActionsAction(Application $application, Template $template)
	{
		$template -> setParameter('title', 'Последние действия модераторов');
		$template -> setParameter('board_id', 'news');
		$modlog = ControlModel::getLogModEvent();
		$this['comments'] = array_reverse($modlog);
		return true;
	}

	/**
	 * Действие просмотра поста комментария:
	 */
	public function getLastCommentsAjaxAction(Application $application)
	{
		$cache  = KVS::getInstance();

		if ($cache -> exists(__CLASS__, $_GET['id'], 'preview'))
			return unserialize($cache -> get(__CLASS__, $_GET['id'], 'preview'));

		$comment = Blog_BlogCommentsModel::GetComment($_GET['id']);
		$result  = false;

		if ($comment)
		{
			$post = Blog_BlogPostsModel::GetPost($comment['post_id']);
			$comment['post_title'] = $post['title'];
			$comment['created_at'] = TemplateHelper::date('d M Y @ H:i', $comment['created_at']);
			$comment['author']     = array($comment['author'], HomeBoardHelper::getBoard($comment['author']));
			unset($comment['ip']);

			$result = $comment;
		} elseif($post = Blog_BlogPostsModel::GetPost($_GET['id'])) {
			$result = array(
				'id'           => $post['id'],
				'post_id'      => $post['id'],
				'post_title'   => $post['title'],
				'text'         => $post['text'],
				'author'       => array($post['author'], HomeBoardHelper::getBoard($post['author'])),
				'created_at'   => TemplateHelper::date('d M Y @ H:i', $post['created_at']),
				'post_preview' => true
			);
		}

		$cache -> set(__CLASS__, $_GET['id'], 'preview', serialize($result));
		$cache -> expire(__CLASS__, $_GET['id'], 'preview', 60 * 60);
		return $result;
	}

	/**
	 * Действие получения настрооек поста:
	 */
	public function getPostAjaxAction(Application $application)
	{
		if (!Session::getInstance() -> isModeratorSession())
			return false;

		$post = Blog_BlogPostsModel::GetPost($_GET['id']);
		if ($post)
			return array(
				'pinned'   => $post['pinned'],
				'rated'    => $post['rated'],
				'rateable' => $post['rateable'],
				'closed'   => $post['closed']
			);

		return false;
	}

	/**
	 * Добавление поста в категорию:
	 */
	public function categoryPostAjaxAction(Application $application)
	{
		if (!Session::getInstance() -> isModeratorSession())
			return false;

		if ($_GET['cat'] == '')
		    $category_id = 0;
		else
		{
		    if (!Blog_BlogCategoryModel::CategoryExists($_GET['cat']))
                return false;

            $category_id = Blog_BlogCategoryModel::GetCategoryIdByCode($_GET['cat']);
		}


		$post = Blog_BlogPostsModel::GetPost($_GET['id']);
		if ($post && ControlModel::checkModrights($post['category']) && ControlModel::checkModrights($category_id)) {
		    $post['category'] = $category_id;
			Blog_BlogPostsModel::CatPost($_GET['id'], $post, date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] .' изменил категорию поста.');
			ControlModel::logModEvent(
				date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] .'<br /> изменил категорию поста <a href="http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/" class="js-cross-link">&gt;&gt;'. $post['id'] .'</a>'
			);
			JabberBot::send('-=$ /me (модлог) '. $_SESSION['auth']['name'] .' изменил категорию поста http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/');
		}

		return true;
	}

	/**
	 * Действие прикрепления поста:
	 */
	public function pinnedPostAjaxAction(Application $application)
	{
		if (!Session::getInstance() -> isModeratorSession())
			return false;

		$post = Blog_BlogPostsModel::GetPost($_GET['id']);
		if ($post && ControlModel::checkModrights($post['category'])) {
			Blog_BlogPostsModel::PinPost($_GET['id'], !$post['pinned'], date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] . (!$post['pinned'] ? ' прикрепил' : ' открепил') .' пост.');
			ControlModel::logModEvent(
				date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] .'<br />'. (!$post['pinned'] ? ' прикрепил' : ' открепил') .' пост <a href="http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/" class="js-cross-link">&gt;&gt;'. $post['id'] .'</a>'
			);
			JabberBot::send('-=$ /me (модлог) '. $_SESSION['auth']['name'] .(!$post['pinned'] ? ' прикрепил' : ' открепил') .' пост http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/');
		}

		return true;
	}

	/**
	 * Действие одобрения поста:
	 */
	public function ratedPostAjaxAction(Application $application)
	{
		if (!Session::getInstance() -> isModeratorSession())
			return false;

		$post = Blog_BlogPostsModel::GetPost($_GET['id']);
		if ($post && ControlModel::checkModrights($post['category'])) {
			Blog_BlogPostsModel::RatedPost($_GET['id'], !$post['rated'], date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] . (!$post['rated'] ? ' одобрил' : ' убрал из одобренного') .' пост.');
			ControlModel::logModEvent(
				date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] .'<br />'. (!$post['rated'] ? ' одобрил' : ' убрал из одобренного') .' пост <a href="http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/" class="js-cross-link">&gt;&gt;'. $post['id'] .'</a>'
			);
			JabberBot::send('-=$ /me (модлог) '. $_SESSION['auth']['name'] . (!$post['rated'] ? ' одобрил' : ' убрал из одобренного') .' пост http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/');
		}

		return true;
	}

	/**
	 * Действие оцениваемого поста:
	 */
	public function rateablePostAjaxAction(Application $application)
	{
		if (!Session::getInstance() -> isModeratorSession())
			return false;

		$post = Blog_BlogPostsModel::GetPost($_GET['id']);
		if ($post && ControlModel::checkModrights($post['category'])) {
			Blog_BlogPostsModel::RateablePost($_GET['id'], !$post['rateable'], date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] . (!$post['rateable'] ? ' сделал оцениваемым' : ' сделал неоцениваемым') .' пост.');
			ControlModel::logModEvent(
				date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] .'<br />'. (!$post['rateable'] ? ' сделал оцениваемым' : ' сделал неоцениваемым') .' пост <a href="http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/" class="js-cross-link">&gt;&gt;'. $post['id'] .'</a>'
			);
			JabberBot::send('-=$ /me (модлог) '. $_SESSION['auth']['name'] . (!$post['rateable'] ? ' сделал оцениваемым' : ' сделал неоцениваемым') .' пост http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/');

		}

		return true;
	}

	/**
	 * Действие закрытого поста:
	 */
	public function closedPostAjaxAction(Application $application)
	{
		if (!Session::getInstance() -> isModeratorSession())
			return false;

		$post = Blog_BlogPostsModel::GetPost($_GET['id']);
		if ($post && ControlModel::checkModrights($post['category'])) {
			Blog_BlogPostsModel::ClosePost($_GET['id'], !$post['closed'], date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] . (!$post['closed'] ? ' закрыл' : ' открыл') .' пост.');
			ControlModel::logModEvent(
				date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] .'<br />'. (!$post['closed'] ? ' закрыл' : ' открыл') .' пост <a href="http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/" class="js-cross-link">&gt;&gt;'. $post['id'] .'</a>'
			);
			JabberBot::send('-=$ /me (модлог) '. $_SESSION['auth']['name'] . (!$post['closed'] ? ' закрыл' : ' открыл') .' пост http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/');
		}

		return true;
	}

	/**
	 * Действие скрытого поста:
	 */
	public function hiddenPostAjaxAction(Application $application)
	{
		if (!Session::getInstance() -> isAdminSession())
			return false;

		$post = Blog_BlogPostsModel::GetPost($_GET['id']);
		if ($post && ControlModel::checkModrights($post['category'])) {
			Blog_BlogPostsModel::HidePost($_GET['id'], !$post['hidden'], 
				date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] . (!$post['hidden'] ? ' скрыл' : ' показал') .' пост.'.
				'<br />Причина: '. (!empty($_GET['why']) ? '<em>'. $_GET['why'] .'</em>' : 'не указана'));

			if (!$post['hidden'])
			    Blog_BlogPostsModel::SetSpecialComment($_GET['id'], 'Причина: '. (!empty($_GET['why']) ? '<em>'. $_GET['why'] .'</em>' : 'не указана'));
			else
			    Blog_BlogPostsModel::SetSpecialComment($_GET['id'], '');

			ControlModel::logModEvent(
				date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] .'<br />'. (!$post['hidden'] ? ' скрыл' : ' показал') .' пост <a href="http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/" class="js-cross-link">&gt;&gt;'. $post['id'] .'</a>'.
				'<br />Причина: '. (!empty($_GET['why']) ? '<em>'. $_GET['why'] .'</em>' : 'не указана')
			);
			JabberBot::send('-=$ /me (модлог) '. $_SESSION['auth']['name'] . (!$post['hidden'] ? ' скрыл' : ' показал') .' пост http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/' ."\n". 'Причина: '. (!empty($_GET['why']) ? $_GET['why'] : 'не указана'));
		}

		return true;
	}

	/**
	 * Действие удаление комментария:
	 */
	public function removePostCommentAjaxAction(Application $application)
	{
		if (!Session::getInstance() -> isAdminSession())
			return false;

		$comment = Blog_BlogCommentsModel::GetComment($_GET['id']);
		$post    = Blog_BlogPostsModel::GetPost(@$comment['post_id']);
		if ($comment && $post && ControlModel::checkModrights($post['category'])) {
			Blog_BlogCommentsModel::RemoveComment($_GET['id']);
			ControlModel::logModEvent(
				date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] .'<br /> удалил комментарий '. $comment['id'] .' в посте <a href="http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/" class="js-cross-link">&gt;&gt;'. $post['id'] .'</a>'.
				'<br /><em>'.strip_tags($comment['text'],'a').'</em>'
			);
			JabberBot::send('-=$ /me (модлог) '. $_SESSION['auth']['name'] . ' удалил комментарий '. $comment['id'] .' в посте http://'.TemplateHelper::getSiteUrl().'/news/res/'.$post['id'].'/');
			return true;
		}
		return false;
	}

	/**
	 * Действие удаление ссылки из ротатора:
	 */
	public function removeOnlineLinkAjaxAction(Application $application)
	{
		if (!Session::getInstance() -> isAdminSession())
			return false;

		$link = Blog_BlogOnlineModel::GetLink($_GET['id']);
		if ($link) {
			Blog_BlogOnlineModel::RemoveLink($_GET['id']);
			ControlModel::logModEvent(
				date("d-m-Y H:i:s") .' '. $_SESSION['auth']['name'] .'<br /> удалил ссылку '. $link['link'].
				'<br /><em>'.strip_tags($link['description']).'</em>'
			);
			JabberBot::send('-=$ /me (модлог) '. $_SESSION['auth']['name'] . ' удалил ссылку '. $link['link'] .': '. strip_tags($link['description']));
		}

		return true;
	}

	/**
	 * Действие "поделиться ссылкой" через букмарклет:
	 */
	public function shareLinkAction(Application $application, Template $template)
	{
		$kvs   = KVS::getInstance();

		$validator = new ValidatorHelper($_GET);
		$validator -> assertExists('title', '');
		$validator -> assertLength('title', 70, '');
		$validator -> assertExists('link', '');
		$validator -> assertRegexp('link', ValidatorHelper::URL_REGEXP, '');
		$validator -> assertLength('description', 128, '');
		
		if ($validator -> isValid())
		{
			$key     = md5(strtolower($_GET['link']));
			$ip      = md5($_SERVER['REMOTE_ADDR']);
			$counter = $kvs -> get(__CLASS__, 'shared_links_ip', $ip);
			
			if (!$kvs -> exists(__CLASS__, 'shared_links', $key) && !$kvs -> exists(__CLASS__, 'shared_links_ban', $ip))
			{
				if ($kvs -> exists(__CLASS__, 'shared_links_ip', $ip))
				{
					if ($counter > 5)
					{
					    $kvs -> set(__CLASS__, 'shared_links_ban', $ip, true);
					    $kvs -> expire(__CLASS__, 'shared_links_ban', $ip, 5 * 60);
					}
					else
					{
						$lifetime = $kvs -> lifetime(__CLASS__, 'shared_links_ip', $ip);
						$kvs -> set(__CLASS__, 'shared_links_ip', $ip, ++$counter);
						$kvs -> expire(__CLASS__, 'shared_links_ip', $ip, $lifetime);
					}
				}
				else
				{
					$kvs -> set(__CLASS__, 'shared_links_ip', $ip, 1);
					$kvs -> expire(__CLASS__, 'shared_links_ip', $ip, 60);
				}

				JabberBot::send('-=% /me Отправлена ссылка: '. $_GET['link'] .' ('. $_GET['title'] .')'. "\n". (!empty($_GET['description']) ? 'С описанием: '. $_GET['description'] : ''));
				
				$kvs -> set(__CLASS__,    'shared_links', $key, true);
				$kvs -> expire(__CLASS__, 'shared_links', $key, 60 * 60);
				
				$template -> headerOk();
				$template -> headerContentTypeWOCharset('image/png');
				readfile(WEB_DIR .'/ico/tick.png');
				exit;
			}
		}

		$template -> headerBadRequest();
		exit;
	}
}
