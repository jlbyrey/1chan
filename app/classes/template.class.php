<?php
/**
 *
 */
class Template
{
	/**
	 * Парамтеры вида:
	 */
	private $viewParams = array();
	
	/**
	 * Флаг изменения заголовка:
	 */
	private $headerChanged = false;

	/**
	 * Метод получения параметра вида:
	 * $name    - имя параметра.
	 * $default - значение по-умолчанию.
	 */
	public function getParameter($name, $default = null) {
		if (array_key_exists($name, $this -> viewParams))
			return $this -> viewParams[$name];

		return $default;
	}

	/**
	 * Метод установки параметра вида:
	 * $name    - имя параметра.
	 * $value   .
	 */
	public function setParameter($name, $value) {
		$this -> viewParams[$name] = $value;
	}


	/**
	 * Заголовок кодировки:
	 */
	public function headerContentEncoding($encoding) {
		header('Content-Encoding: '. $encoding);
	}

	/**
	 * Заголовок типа:
	 */
	public function headerContentType($type, $charset) {
		header('Content-Type: '. $type .';charset='. $charset);
	}

	public function headerContentTypeWOCharset($type) {
		header('Content-Type: '. $type);
	}

	/**
	 * Заголовок размера:
	 */
	public function headerContentLength($bytes) {
		header('Content-Length: '. $bytes);
	}

	/**
	 * Заголовок файла:
	 */
	public function headerContentDisposition($filename) {
		header('Content-Disposition: attachment; filename='. $filename);
	}

	/**
	 * Заголовок не кешируемого контента:
	 */
	public function headerNoCache() {
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
	}

	/**
	 * Заголовок "годен до":
	 */
	public function headerExpires($time) {
		header('Expires: '. gmdate('D, d M Y H:i:s', $time) .' GMT');
	}

	/**
	 * Заголовок "модифицирован в":
	 */
	public function headerLastModified($time) {
		header('Last-Modified: '. gmdate('D, d M Y H:i:s', $time) .' GMT');
	}

	/**
	 * Заголовок ETag:
	 */
	public function headerETag($etag) {
		header('ETag: '. $etag);
	}

	/**
	 * Заголовок 200 OK:
	 */
	public function headerOk() {
		header($_SERVER['SERVER_PROTOCOL'] .' 200 OK');
		$this -> headerChanged = true;
	}

	/**
	 * Заголовок 201 Создан:
	 */
	public function headerCreated($redirect) {
		header($_SERVER['SERVER_PROTOCOL'] .' 201 Created');
		header('Location: '.$redirect);
		$this -> headerChanged = true;
	}

	/**
	 * Заголовок 301 Перемещен навечно:
	 */
	public function headerMovedPermanently($redirect) {
		header($_SERVER['SERVER_PROTOCOL'] .' 301 Moved Permanently');
		header('Location: '.$redirect);
		$this -> headerChanged = true;
	}

	/**
	 * Заголовок 307 Временно перемещен:
	 */
	public function headerMovedTemporarily($redirect) {
		header($_SERVER['SERVER_PROTOCOL'] .' 307 Temporary Redirect');
		header('Location: '.$redirect);
		$this -> headerChanged = true;
	}

	/**
	 * Заголовок 303 Смотри в:
	 */
	public function headerSeeOther($redirect) {
		header($_SERVER['SERVER_PROTOCOL'] .' 303 See Other');
		header('Location: '.$redirect);
		$this -> headerChanged = true;
	}

	/**
 	 * Заголовок Location:
	 */
	public function headerLocation($redirect) {
		header('Location: '.$redirect);
		$this -> headerChanged = true;
	}

	/**
	 * Заголовок 400 Неправильный запрос:
	 */
	public function headerBadRequest() {
		header($_SERVER['SERVER_PROTOCOL'] .' 400 Bad Request');
		$this -> headerChanged = true;
	}

	/**
	 * Заголовок 403 Запрещено:
	 */
	public function headerForbidden() {
		header($_SERVER['SERVER_PROTOCOL'] .' 403 Forbidden');
		$this -> headerChanged = true;
	}

	/**
	 * Заголовок 404 Не найдено:
	 */
	public function headerNotFound() {
		header($_SERVER['SERVER_PROTOCOL'] .' 404 Not Found');
		$this -> headerChanged = true;
	}

	/**
	 * Заголовок 410 Пропало:
	 */
	public function headerGone() {
		header($_SERVER['SERVER_PROTOCOL'] .' 410 Gone');
		$this -> headerChanged = true;
	}

	/**
	 * Метод рендеринга вида html:
	 * $data - параметры вида.
	 * $layout - лейаут вида.
	 */
	public function render($data, $layout = 'layout')
	{
		$application = Application::getInstance();

		$controller = $application -> getController();
		$action     = $application -> getAction();

		$content = $this -> renderTemplate(str_replace('_', '/', $controller) .'/'. $action, $data);
		$result  = $this -> renderTemplate($layout, array('content' => $content));


		if (!$this -> headerChanged)
			$this -> headerOk();
	
		$this -> headerNoCache();
		$this -> headerContentType('text/html', 'UTF-8');
		echo $result;

		return true;
	}

	/**
	 * Метод рендеринга вида для шаблона:
	 * $template - путь к шаблону.
	 * $data     - данные вида.
	 */
	public function renderTemplate($template, $data = array())
	{
		extract($data);

		ob_start();
		include(VIEWS_DIR .'/'. $template .'.php');
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Рендеринг JSONP ответа:
	 */
	public function renderJSONP($callback, $data)
	{
		$this -> headerOk();
		$this -> headerNoCache();
		$this -> headerContentType('text/html', 'UTF-8');
		echo '<script type="text/javascript">document.domain = document.domain;</script>';
		echo '<script type="text/javascript">top.'. $callback .'('. json_encode($data) .');</script>';

		return true;
	}
}
