<?php
/**
 * Устанавливаем уровень ошибок:
 */
error_reporting(0);

/**
 * Запускаем фреймворк:
 */
require_once '../app/bootstrap.php';

/**
 * Заглушка:
 */
//$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];

/**
 * Запускаем приложение:
 */
$app = Application::getInstance();
$app -> run();
