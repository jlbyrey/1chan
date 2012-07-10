<?php
/**
 * Базовый контроллер:
 */
class BaseController extends Controller
{
	/**
	 * Конструктор:
	 */
	public function __construct()
	{
		$session = Session::getInstance();

		if (!$session -> activeGet('last_visit', false))
			$session -> activeSet('last_visit', $session -> persistenceGet('last_visit', time()));

		$session -> persistenceSet('last_visit', time());
	}

	/**
	 * Процессор:
	 */
	public function process($template)
	{
		$session = Session::getInstance();
		$stats   = Blog_BlogStatisticsModel::getGlobalStats();
		
		$links_on = $session -> persistenceGet('show_links_panel', true);
		if ($links_on) {
		    $filter   = $session -> persistenceGet('live_filter', true);
		    $links    = Blog_BlogOnlineModel::GetLinks($filter);
		
		    $template -> setParameter('online_links', array_slice($links, 0, 12));
		    $template -> setParameter('right_panel', $links_on);
        }

		$template -> setParameter('global_unique',          $stats['unique']);
		$template -> setParameter('global_online',          $stats['online']);
		$template -> setParameter('global_posts',           $stats['posts']);
		$template -> setParameter('global_unique_posters',  $stats['unique_posters']);
		$template -> setParameter('global_speed',           $stats['speed']);

		if ($session -> persistenceGet('global_theme', false)) {
			return $template -> render($this -> viewParams, $session -> persistenceGet('global_theme'));
		}

		parent::process($template);
	}
}
