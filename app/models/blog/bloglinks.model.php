<?php
/**
 * Модель управления ссылками в футере:
 */
class Blog_BlogLinksModel
{
	/**
	 * Получение списка ссылок:
	 */
	public static function GetLinks()
	{
		$cache = KVS::getInstance();
		return unserialize($cache -> get(__CLASS__, null, 'links'));
	}

	/**
	 * Установка списка ссылок:
	 */
	public static function SetLinks($links = array())
	{
		$cache = KVS::getInstance();
		return $cache -> set(__CLASS__, null, 'links', serialize($links));
	}
}