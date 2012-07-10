<?php
chdir(dirname(__FILE__));

/**
 * Проверка статуса доступности серверов:
 */
require '../app/classes/kvs.class.php';

$cache = KVS::getInstance();
$links = unserialize($cache -> get('Blog_BlogLinksModel', null, 'links'));

foreach($links as $section => $section_links)
{
	foreach($section_links as $key => $link)
	{
		$headers = @get_headers($link['href']);
		if(!in_array(substr($headers[0], 9, 3), array(200, 303, 302)))
		{
			$links[$section][$key]['offline'] = 1;
		} else {
			$links[$section][$key]['offline'] = 0;
		}
	}
}
$cache -> set('Blog_BlogLinksModel', null, 'links', serialize($links));


$links   = $cache -> listGet('Blog_BlogOnlineModel', null, 'links');
if ($links) {
    foreach($links as $link)
	{
		if (($current = unserialize($cache -> get('Blog_BlogOnlineModel', 'links', $link))) != null)
		{
			$headers = @get_headers($current['link']);
		    if(!in_array(substr($headers[0], 9, 3), array(200, 303, 302)))
		    {
		        $cache -> listRemove('Blog_BlogOnlineModel', null, 'links', $link);
		    }
		}
		else
		{
			$cache -> listRemove('Blog_BlogOnlineModel', null, 'links', $link);
		}
	}
}
