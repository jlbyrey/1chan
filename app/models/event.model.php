<?php
/**
 * Модель, отвечающая за запись обработчиков событий и вызова этих обработчиков:
 */
class EventModel
{
	/**
	 * Массив лямбда-функций обработчиков:
	 */
	private $listeners = array();

	/**
	 * Подключение к рилплексору:
	 */
	private $connection;


	/**
	 * Вызов экземпляра класса:
	 */
	public static function getInstance()
	{
		static $instance;

		if (!is_object($instance))
			$instance = new EventModel();

		return $instance;
	}

	/**
	 * Конструктор:
	 */
	private function __construct()
	{
		$this -> connection = new Dklab_Realplexor('127.0.0.1', 10010, "1chan_");
	}

	/**
	 * Подпись обработчика на событие:
	 */
	public function AddEventListener($event, $lambda)
	{
		$this -> listeners[$event][] = $lambda;
		return $this;
	}

	/**
	 * Очистка обработчиков события:
	 */
	public function RemoveEventListeners($event)
	{
		unset($this -> listeners[$event]);
	}

	/**
	 * Вызов цепочки обработчиков:
	 */
	public function Broadcast($event, $data = array())
	{
		if (!empty($this -> listeners))
		{
			foreach($this -> listeners as $event_name => $listeners)
			{
				if (fnmatch($event_name, $event))
				{
					foreach($listeners as $listener)
					{
						$listener($data, $event);
					}
				}
			}
		}
		return true;
	}

	/**
	 * Сообщение о событии на клиенты:
	 */
	public function ClientBroadcast($channel, $event, $data = true, $ids = null)
	{
		$this -> connection -> send($channel,
			array(
				'event' => $event,
				'data'  => $data
			),
			$ids
		);
/**
		$redis = new Redis();
		$redis -> publish($channel, json_encode(array('event' => $event, 'data' => $data)));
**/
		return $this;
	}
}
