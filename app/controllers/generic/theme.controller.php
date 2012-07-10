<?php
/**
 * Контроллер смены тем оформления сайта:
 */
class Generic_ThemeController
{
	/**
	 * Смена темы:
	 */
	public function switchAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		if ($_GET['theme'] == 'normal') {
			$session -> persistenceSet('global_theme', false);
		} else {
			if (is_file(VIEWS_DIR .'/layout_'. str_replace('/', '', $_GET['theme']) .'.php')) {
				$session -> persistenceSet('global_theme', 'layout_'. $_GET['theme']);
			}
		}

		$template -> headerSeeOther(
			'http://'. TemplateHelper::getSiteUrl() .'/'
		);
		return false;
	}
}
