<?php
/**
 * Класс валидатора данных с формы:
 */
class ValidatorHelper
{
	/**
	 * Флаг валидности:
	 */
	private $isValid = true;

	/**
	 * Массив результатов валидации (сообщений):
	 */
	private $validationResults = array();

	/**
	 * Массив валидируемых значений:
	 */
	private $validationTarget = array();

	/**
	 * Конструктор:
	 */
	public function __construct($validationTarget)
	{
		$this -> validationTarget = $validationTarget;
	}

	/**
	 * Проверка заполнености значения:
	 */
	public function assertExists($key, $message = '')
	{
		if (!array_key_exists($key, $this -> validationTarget))
		{
			$this -> isValid = false;
			return false;
		}

		if (strlen(trim(html_entity_decode($this -> validationTarget[$key]))) == 0)
		{
			$this -> validationResults[$key] = $message;
			$this -> isValid = false;
			return false;
		}

		return true;
	}

	/**
	 * Проверка незаполнености значения:
	 */
	public function assertNotExists($key, $message = '')
	{
		if (strlen(trim(@$this -> validationTarget[$key])) != 0)
		{
			$this -> validationResults[$key] = $message;
			$this -> isValid = false;
			return false;
		}

		return true;
	}

	/**
	 * Проверка длинны значения:
	 */
	public function assertLength($key, $length, $message = '')
	{
		if (mb_strlen(trim(html_entity_decode($this -> validationTarget[$key])), 'UTF-8') > $length)
		{
			$this -> validationResults[$key] = $message;
			$this -> isValid = false;
			return false;
		}

		return true;
	}

	/**
	 * Проверка длинны значения:
	 */
	public function assertLengthMore($key, $length, $message = '')
	{
		if (mb_strlen(trim($this -> validationTarget[$key]), 'UTF-8') < $length)
		{
			$this -> validationResults[$key] = $message;
			$this -> isValid = false;
			return false;
		}

		return true;
	}

	/**
	 * Проверка значения на регулярное выражение:
	 */
	public function assertRegexp($key, $regexp, $message = '')
	{
		if (!preg_match($regexp, $this -> validationTarget[$key]))
		{
			$this -> validationResults[$key] = $message;
			$this -> isValid = false;
			return false;
		}

		return true;
	}

	/**
	 * Проверка на точное совпадение:
	 */
	public function assertEqual($key, $value, $message = '')
	{
		if ($this -> validationTarget[$key] !== $value)
		{
			$this -> validationResults[$key] = $message;
			$this -> isValid = false;
			return false;
		}

		return true;
	}

	/**
	 * Проверка на истинность:
	 */
	public function assertTrue($key, $expr, $message = '')
	{
		if ($expr !== true)
		{
			$this -> validationResults[$key] = $message;
			$this -> isValid = false;
			return false;
		}

		return true;
	}

	/**
	 * Проверка флага валидности:
	 */
	public function isValid()
	{
		return $this -> isValid;
	}

	/**
	 * Получение массива результатов валидации:
	 */
	public function getValidationResults()
	{
		return $this -> validationResults;
	}

	/**
	 * Проверка валидности определенного поля:
	 */
	public function fieldValid($field)
	{
		return !array_key_exists($field, $this -> validationResults);
	}

	/**
	 * Константы некоторых регулярных выражений:
	 */
	const URL_REGEXP = "{
			  ^
			  (
			    (https?)://[-\\w]+(\\.\\w[-\\w]*)+
			  |
			    (?i: [a-z0-9] (?:[-a-z0-9]*[a-z0-9])? \\. )+
			    (?-i: com\\b
			        | edu\\b
			        | biz\\b
			        | gov\\b
			        | in(?:t|fo)\\b # .int or .info
			        | mil\\b
			        | net\\b
			        | org\\b
			        | [a-z][a-z]\\.[a-z][a-z]\\b # two-letter country code
			    )
			  )
			  ( : \\d+ )?
			  (
			    /
			    [^.!,?;\"\\'<>()\[\]\{\}\s\x7F-\\xFF]*
			    (
			      [.!,?]+ [^.!,?;\"\\'<>()\\[\\]\{\\}\s\\x7F-\\xFF]+
			    )*
			  )?
			}ix";

	const EMAIL_REGEXP = "/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/";
}
