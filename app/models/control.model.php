<?php
/**
 * Модель контроля и общих настроек:
 */
class ControlModel
{
	/**
	 * Глобальное сообщение сайта:
	 */
	public static function isGlobalMessage()
	{
		$settings = self::GetSettings();
		return @$settings['global_message'];
	}

	/**
	 * Промодерация сообщения:
	 */
	public static function isPostPremoderation()
	{
		$settings = self::GetSettings();
		return @$settings['premoderation'];
	}

	/**
	 * Ручное одобрение поста:
	 */
	public static function isPostHandApproving()
	{
		$settings = self::GetSettings();
		return @$settings['handapprove'];
	}

	/**
	 * Капча на постинг сообщения:
	 */
	public static function isPostCaptcha()
	{
		$settings = self::GetSettings();
		if(@$settings['post_captcha'])
			return true;

		$session  = Session::getInstance();

		if (@$_SESSION['open_from'] != $_SERVER['REMOTE_ADDR']) {
			$session -> persistenceSet('captcha_mode', true);
			$_SESSION['open_from'] = $_SERVER['REMOTE_ADDR'];
		}

		if ($session -> persistenceGet('captcha_mode', true))
			return true;

		return false;
	}

	/**
	 * Капча на постинг комментария:
	 */
	public static function isCommentCaptcha()
	{
		$kvs = KVS::getInstance();
		$settings = self::GetSettings();
		if(@$settings['post_comment_captcha'])
			return true;

		if ($kvs -> exists('ControlModel', 'timeblock', $_SERVER['REMOTE_ADDR']))
			return true;

		$session  = Session::getInstance();

		if (@$_SESSION['open_from'] != $_SERVER['REMOTE_ADDR']) {
			$session -> persistenceSet('captcha_mode', true);
			$_SESSION['open_from'] = $_SERVER['REMOTE_ADDR'];
		}

		if ($session -> persistenceGet('captcha_mode', true))
			return true;

		return false;

	}

	/**
	 * Капча на оценку новости:
	 */
	public static function isPostRateCaptcha()
	{
		$settings = self::GetSettings();
		if(@$settings['post_rate_captcha'])
			return true;

		$session  = Session::getInstance();

		if (@$_SESSION['open_from'] != $_SERVER['REMOTE_ADDR']) {
			$session -> persistenceSet('captcha_mode', true);
			$_SESSION['open_from'] = $_SERVER['REMOTE_ADDR'];
		}

		if ($session -> persistenceGet('captcha_mode', true))
			return true;

		return false;
	}

	/**
	 * Капча на добавление ссылки в ротатор:
	 */
	public static function isLiveCaptcha()
	{
		$settings = self::GetSettings();
		$kvs = KVS::getInstance();

		if(@$settings['live_captcha'])
			return true;

		if ($kvs -> exists('ControlModel', 'timeblock', $_SERVER['REMOTE_ADDR']))
			return true;

		$session  = Session::getInstance();

		if (@$_SESSION['open_from'] != $_SERVER['REMOTE_ADDR']) {
			$session -> persistenceSet('captcha_mode', true);
			$_SESSION['open_from'] = $_SERVER['REMOTE_ADDR'];
		}

		if ($session -> persistenceGet('captcha_mode', true))
			return true;

		return false;
	}

	/**
	 * Получение интервала постинга:
	 */
	public static function getPostInterval()
	{
		$kvs = KVS::getInstance();
		if ($kvs -> exists('ControlModel', 'timeban', $_SERVER['REMOTE_ADDR']))
			return $kvs -> lifetime('ControlModel', 'timeban', $_SERVER['REMOTE_ADDR']);
		
		$time      = time();
		$session   = Session::getInstance();
		$settings  = self::GetSettings();
		$last_post = $session -> persistenceGet('last_post_date', time());
		$interval = $time - $last_post;

		if ($interval > 0 && $interval < $settings['post_interval'])
			return $settings['post_interval'] - $interval;

		return 0;
	}

	/**
	 * Получение интервала комментариев:
	 */
	public static function getPostCommentInterval()
	{
		$session  = Session::getInstance();
		$settings = self::GetSettings();

		$kvs = KVS::getInstance();

		if ($kvs -> exists('ControlModel', 'timeban', $_SERVER['REMOTE_ADDR']))
			return $kvs -> lifetime('ControlModel', 'timeban', $_SERVER['REMOTE_ADDR']);

		$last_post = $session -> persistenceGet('last_comment_date', time());
		$interval = time() - $last_post;

		if ($interval > 0 && $interval < $settings['post_comment_interval'])
			return $settings['post_comment_interval'] - $interval;

		return 0;
	}

	/**
	 * Получение интервала комментариев:
	 */
	public static function getBoardPostInterval()
	{
		$session  = Session::getInstance();

		$kvs = KVS::getInstance();
		if ($kvs -> exists('ControlModel', 'timeban', $_SERVER['REMOTE_ADDR']))
			return $kvs -> lifetime('ControlModel', 'timeban', $_SERVER['REMOTE_ADDR']);

		$last_post = $session -> persistenceGet('last_board_post_date', time());
		$interval = time() - $last_post;

		if ($interval > 0 && $interval < 7)
			return 7 - $interval;

		return 0;
	}

	/**
	 * Получение интервала ротатора:
	 */
	public static function getLiveInterval()
	{
		$time     = time();
		$session  = Session::getInstance();
		$settings = self::GetSettings();
		$interval = $time - $session -> persistenceGet('last_live_date', $time);

		if ($interval > 0 && $interval < $settings['live_interval'])
			return $settings['live_interval'] - $interval;

		return 0;
	}

	/**
	 * Получение длины каптчи:
	 */
	public static function getCaptchaLength()
	{
		$settings = self::GetSettings();
		$session  = Session::getInstance();
		$length = $session -> persistenceGet('captcha_mode_length');

		if ($length == false || $length == '' || $length == 0)
			return $settings['captcha_length'];

		return $length;
	}

	/**
	 * Получение количества голосов для одобрения:
	 */
	public static function getRatedCount()
	{
		$settings = self::GetSettings();
		return @$settings['rated_count'];
	}

	/**
	 * Проверка текста на слова из вордфильтра:
	 */
	public static function checkContent($text)
	{
		$settings = self::GetSettings();
		$session  = Session::getInstance();
		$words    = self::GetWordfilter();
		$text_cl  = preg_replace("/[^a-zа-я0-9 ]/iu", "", trim(preg_replace("/(&#?[^ ]+;)/", "", $text)));

		if (is_array($words) && sizeof($words) > 0 && $words[0] != '') {
			if (preg_match('~('. implode('|', $words) .')~iu', $text_cl)) {
				if (@$settings['wordfilter_block'])
					return false;

				$session -> persistenceSet('captcha_mode', true);
				$session -> persistenceSet('captcha_mode_length', 10);
				return true;
			}
		}

		if ($session -> persistenceGet('last_post_text', false)) {
			similar_text($session -> persistenceGet('last_post_text'), $text, $percent);
			if ($percent > 70) {
				$session -> persistenceSet('captcha_mode', true);
				$session -> persistenceSet('captcha_mode_length', @$settings['captcha_length'] + 2);
			}
		}

		return true;
	}

	/**
	 * Проверка контента (борды):
	 */
	public static function checkBoardPost($text)
	{
		$session  = Session::getInstance();
		$text     = trim(htmlspecialchars_decode($text));

		if (empty($text))
			return true;

		if ($session -> persistenceGet('last_board_post_text', false)) {
			similar_text($session -> persistenceGet('last_board_post_text'), $text, $percent);
			if ($percent > 70) {
				$session -> persistenceSet('captcha_mode', true);
				$session -> persistenceSet('captcha_mode_length', @$settings['captcha_length'] + 2);
				return false;
			}
		}

		$session -> persistenceSet('last_board_post_text', $text);

		return true;
	}

	/**
	 * Проверка на спам:
	 */
	public static function checkSpam($text)
	{
		$words = self::GetSpamfilter();
		$text  = html_entity_decode($text);

		if (is_array($words) && sizeof($words) > 0 && $words[0] != '') {
			if (preg_match('~('. implode('|', $words) .')~iu', $text)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Получение все настроек:
	 */
	public static function GetSettings()
	{
		static $settings;
		if (!is_array($settings))
		{
			$cache    = KVS::getInstance();
			$settings = unserialize($cache -> get(__CLASS__, null, 'settings'));
		}
		return $settings;
	}

	/**
	 * Установка настроек:
	 */
	public static function SetSettings($mods = array())
	{
		$cache = KVS::getInstance();
		return $cache -> set(__CLASS__, null, 'settings', serialize($mods));
	}

	/**
	 * Получение блеклиста:
	 */
	public static function GetWordfilter()
	{
		$cache = KVS::getInstance();
		return unserialize($cache -> get(__CLASS__, null, 'words'));
	}

	/**
	 * Установка блеклиста:
	 */
	public static function SetWordfilter($words = array())
	{
		$cache = KVS::getInstance();
		return $cache -> set(__CLASS__, null, 'words', serialize($words));
	}

	/**
	 * Получение спам:
	 */
	public static function GetSpamfilter()
	{
		$cache = KVS::getInstance();
		return unserialize($cache -> get(__CLASS__, null, 'spam'));
	}

	/**
	 * Установка спам:
	 */
	public static function SetSpamfilter($words = array())
	{
		$cache = KVS::getInstance();
		return $cache -> set(__CLASS__, null, 'spam', serialize($words));
	}

	/**
	 * Получение блеклиста ссылок:
	 */
	public static function GetLinkfilter()
	{
		$cache = KVS::getInstance();
		return unserialize($cache -> get(__CLASS__, null, 'links'));
	}

	/**
	 * Проверка ссылки:
	 */
	public static function CheckLinkfilter($test_link)
	{
		$links = self::GetLinkfilter();

		foreach($links as $link)
			if (preg_match('#^'.$link.'#i', urldecode($test_link)))
				return true;

		return false;
	}

	/**
	 * Установка блеклиста ссылок:
	 */
	public static function SetLinkfilter($words = array())
	{
		$cache = KVS::getInstance();
		return $cache -> set(__CLASS__, null, 'links', serialize($words));
	}

	/**
	 * Получение списка модераторов:
	 */
	public static function GetModerators()
	{
		$cache = KVS::getInstance();
		return unserialize($cache -> get(__CLASS__, null, 'mods'));
	}

	/**
	 * Установка списка модераторов:
	 */
	public static function SetModerators($mods = array())
	{
		$cache = KVS::getInstance();
		return $cache -> set(__CLASS__, null, 'mods', serialize($mods));
	}

	/**
	 * Проверка прав модератора:
	 */
	public static function checkModrights($category_id)
	{
		$session = Session::getInstance();
		if ($session -> isModeratorSession())
		{
			if ($_SESSION['auth']['category'] == '*' || (int)$_SESSION['auth']['category'] == $category_id)
				return true;
		}
		return false;
	}

	/**
	 * Добавление события в лог управления:
	 */
	public static function logModEvent($event)
	{
		$cache = KVS::getInstance();

		$log   = (array)unserialize($cache -> get(__CLASS__, null, 'log'));
		$log[] = $event;

		if (sizeof($log) > 100)
			array_shift($log);

		$cache -> set(__CLASS__, null, 'log', serialize($log));
	}

	/**
	 * Получение лога управления:
	 */
	public static function getLogModEvent()
	{
		$cache = KVS::getInstance();
		return (array)unserialize($cache -> get(__CLASS__, null, 'log'));
	}
}
