<?php
/**
 * Базовый контроллер:
 */
class Controller implements ArrayAccess
{
	/**
	 * Параметры для передачи в вид:
	 */
	protected $viewParams = array();


	/**
	 * Установка параметра вида (ArrayAccess):
	 */
	public function offsetSet($offset, $value) {
        $this -> viewParams[$offset] = $value;
    }

	/**
	 * Проверка на существование параметра вида (ArrayAccess):
	 */
    public function offsetExists($offset) {
        return isset($this -> viewParams[$offset]);
    }

	/**
	 * Удаление параметра вида (ArrayAccess):
	 */
    public function offsetUnset($offset) {
        unset($this -> viewParams[$offset]);
    }

	/**
	 * Получение параметра вида (ArrayAccess):
	 */
    public function offsetGet($offset) {
        return isset($this -> viewParams[$offset]) ? $this -> viewParams[$offset] : null;
    }

    /**
     * Установка параметров из массива:
     */
    public function setValues($values = array())
    {
    	$this -> viewParams =
    		array_merge_recursive($this -> viewParams, $values);

    	return true;
    }

    /**
     * Получение массива параметров:
     */
    public function getValues()
    {
    	return $this -> viewParams;
    }

    /**
     * Метод обработки контроллера, передает данные в вид:
     */
    public function process($template) {
		return $template-> render($this -> getValues());
    }
}
