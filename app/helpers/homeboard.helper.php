<?php
/**
 * Хелпер по домашней борде пользователя:
 */
class HomeBoardHelper {
	static private $boards = array(
		'anonymous'    => array('anonymous.png', 'Аноним'),
		'2ch.so'       => array('2ch.so.png', 'Двач.so'),
		'0chan.ru'     => array('0chan.ru.png', 'Нульчан'),
		'iichan.ru'    => array('iichan.ru.png', 'Иичан'),
		'iichan.ru/b'  => array('iichan.ru_b.png', 'Сырно'),
		'dobrochan.ru' => array('dobrochan.ru.png', 'Доброчан'),
		'samechan.org' => array('samechan.ru.png', 'Сеймчан'),
		'2--ch.ru'  => array('2--ch.ru.png', 'Тиреч'),
		'4chan.org'  => array('4chan.org.png', 'Форчан'),
		'krautchan.net'  => array('krautchan.net.png', 'Краутчан'),
		'hivemind.me'  => array('hivemind.me.png', 'Хайвмайнд'),
		'olanet.ru'    => array('olanet.ru.png', 'Оланет'),
		'1chan.ru'     => array('1chan.ru.png', 'Одинчан'),
		'xmpp.org'     => array('xmpp.org.png', 'Конференции'),
	);

	static public function getBoards() {
		return self::$boards;
	}

	static public function existsBoard($id) {
		return array_key_exists($id, self::$boards);
	}

	static public function getBoard($id) {
		if (self::existsBoard($id))
			return self::$boards[$id];
		return self::$boards['anonymous'];
	}
}
