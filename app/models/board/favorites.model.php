<?php
/**
 * Управление избранными постами:
 */
class Board_FavoritesModel
{
	/**
	 * Получение избранных постов:
	 */
	public static function GetFavoritePosts()
	{
		$session = Session::getInstance();
		$favorites = $session -> persistenceGet('board_favorites', array());

		if (!empty($favorites))
		{
			$posts = Board_BoardModel::getBulkThreads($favorites);
			$session -> persistenceSet('board_favorites', $favorites);
			return $posts;
		}
		return false;
	}

	/**
	 * Проверка на избранный пост:
	 */
	public static function IsFavoritePost($board, $id)
	{
		static $favorites;

		if (!is_array($favorites))
		{
			$session = Session::getInstance();
			$favorites = $session -> persistenceGet('board_favorites', array());
		}

		return in_array(array($board, $id), $favorites);
	}

	/**
	 * Переключение избранного поста:
	 */
	public static function ToggleFavoritePost($board, $id)
	{
		$board_obj = new Board_BoardModel($board);

		if ($board_obj -> existsPost($id))
		{
			$session = Session::getInstance();
			$favorites = $session -> persistenceGet('board_favorites', array());

			if (($key = array_search(array($board, $id), $favorites)) !== false)
				unset($favorites[$key]);
			else
				array_unshift($favorites, array($board, $id));

			$session -> persistenceSet('board_favorites', $favorites);
			return array_search(array($board, $id), $favorites) !== false;
		}
		return false;
	}
}