<?php
/**
 * Хелпер для шаблонов:
 */
class TemplateHelper
{
	/**
	 * Получение урла сайта:
	 */
	public static function getSiteUrl()
	{
		return '1chan.ru';
	}

	/**
	 * Получение иконки:
	 */
	public static function getIcon($site)
	{
		$site = str_replace('www.', '', parse_url($site, PHP_URL_HOST));
		if (is_file(WEB_DIR .'/ico/favicons/'. $site .'.gif'))
			return 'http://'. TemplateHelper::getSiteUrl() .'/ico/favicons/'. $site .'.gif';

		return 'http://favicon.yandex.net/favicon/'. $site;
	}

	/**
	 * Русская дата:
	 */
	public static function date($pattern, $time = false) {
	    // Не горжусь этим хаком:
	    if ($pattern == 'Y-m-d @ H:i') {
	        return date($pattern, $time ? $time : time());
	    }
	
		if (date('Y') == date('Y', $time))
			$pattern = str_replace(' Y', '', $pattern);

		$date = date($pattern, $time ? $time : time());
		return strtr($date, array(
			'Jan' => 'Января',
			'Feb' => 'Февраля',
			'Mar' => 'Марта',
			'Apr' => 'Апреля',
			'May' => 'Мая',
			'Jun' => 'Июня',
			'Jul' => 'Июля',
			'Aug' => 'Августа',
			'Sep' => 'Сентября',
			'Oct' => 'Октября',
			'Nov' => 'Ноября',
			'Dec' => 'Декабря'
		));
	}

	/**
	 * Русское окончание:
	 */
	public static function ending($chislo, $n1, $n2, $n5){
		$chislo = (int)$chislo;
		$ch = substr($chislo, -1);

		if ($ch==1)
		{
			if (strlen($chislo) > 1)
				$result = substr($chislo,-2,1) == 1 ? $n5 : $n1;
			else
				$result = $n1;
		}
		elseif($ch > 1 && $ch < 5)
		{
			if (strlen($chislo) > 1)
				$result = substr($chislo, -2, 1) == 1 ? $n5 : $n2;
			else
				$result = $n2;
		}
		else
		{
			$result=$n5;
		}
		return $chislo .' '. $result;
	}

	/**
	 * Форматирование размеров файлов:
	 */
	public static function format_bytes($bytes) {
	   if ($bytes < 1024) return $bytes.' B';
	   elseif ($bytes < 1048576) return round($bytes / 1024, 2).' KB';
	   elseif ($bytes < 1073741824) return round($bytes / 1048576, 2).' MB';
	   elseif ($bytes < 1099511627776) return round($bytes / 1073741824, 2).' GB';
	   else return round($bytes / 1099511627776, 2).' TB';
	}

	/**
	 * Получение информации о категории:
	 */
	public static function BlogCategory($id, $field = null)
	{
		static $categories;
		if (!is_array($categories))
		{
			foreach (Blog_BlogCategoryModel::GetCategories() as $row)
				$categories[$row['id']] = $row;
		}

		if (is_null($field))
			return $categories[$id];
		else
			return $categories[$id][$field];
	}

	/**
	 * Функция проверки обновления поста:
	 */
	public static function isPostUpdated(&$post, $comments_only = true)
	{
		if ($post['comments'] == 0 && $comments_only)
			return false;

		$session    = Session::getInstance();
		$last_visit = $session -> activeGet('last_visit');

		if (($last_visit_post = $session -> activeGet('last_visit_post_'. $post['id'], false)) !== false)
		{
			if ($post['updated_at'] > $last_visit_post)
				return true;
		}
		else
		{
			if ($post['updated_at'] > $last_visit)
				return true;
		}

		return false;
	}

	/**
	 * Функция проверки нового поста:
	 */
	public static function isNewComment(&$comment)
	{
		$session    = Session::getInstance();
		$last_visit = $session -> activeGet('last_visit');

		if (($last_visit_post = $session -> instantGet('last_visit_post_'. $comment['post_id'], false)) !== false)
		{
			if ($comment['created_at'] > $last_visit_post)
				return true;
		}
		else
		{
			if ($comment['created_at'] > $last_visit)
				return true;
		}

		return false;
	}

	/**
	 * Функция проверки обновлений в разделе "онлайн":
	 */
	public static function isLiveUpdated()
	{
		$session = Session::getInstance();
		$cache   = KVS::getInstance();
		$last_visit = $session -> persistenceGet('live_last_visit', time());
		return $cache -> get('Blog_BlogOnlineModel', null, 'lastUpdate') > $last_visit;
	}
}
