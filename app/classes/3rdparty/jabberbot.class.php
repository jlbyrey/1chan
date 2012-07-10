<?php
/**
 * Класс взаимодействия с ботом Jabber:
 */
class JabberBot
{
	/**
	 * Адрес сокета:
	 */
	private static $socket = '/tmp/test.sock';

    /**
     * Рассылка сообщения:
     */
    public static function send($message)
    {
    	$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
		$con = socket_connect($socket, self::$socket);

		if(!$socket or !$con) {
			socket_close($socket);
		}

		socket_write($socket, $message);
		socket_close($socket);
    }
}