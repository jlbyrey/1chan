<?php
/**
 * Контроллер чатов:
 */
class ChatController extends BaseController
{
	/**
	 * Список комнат чата:
	 */
	public function indexAction(Application $application, Template $template)
	{
		$template -> setParameter('title', 'Анонимные чаты');
		$template -> setParameter('section', 'all');

		$rooms = Chat_ChatRoomsModel::GetRooms();

		$this['favorites'] = $rooms['favorites'];
		$this['public']    = $rooms['public'];

		EventModel::getInstance()
				-> Broadcast('view_chat_rooms');

		return true;
	}

	/**
	 * Добавление комнаты чата:
	 */
	public function addAction(Application $application, Template $template)
	{
		$session = Session::getInstance();
		$this['form_errors'] = array();
		$this['blog_form']   = array();

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$validator = new ValidatorHelper($_POST);
			$validator -> assertExists('captcha_key', 'Введите капчу');

			if ($_POST['captcha_key'])
				$validator -> assertEqual(
					'captcha', $session -> instantGet('captcha_'. $_POST['captcha_key'], false),
					'Капча введена неверно'
				);

			$validator -> assertExists('title',              'Не введен заголовок');
			$validator -> assertLength('title',       25,    'Заголовок слишком длинный');
			$validator -> assertExists('description',        'Не введен вводный текст');
			$validator -> assertLength('description', 75,    'Вводный текст слишком длинный');
			$validator -> assertExists('controlword',        'Не введен контрольный пароль');

			if ($validator -> fieldValid('title'))
				$validator -> assertLengthMore('title', 3, 'Заголовок слишком короткий');

			if ($validator -> fieldValid('description'))
				$validator -> assertLengthMore('description', 5, 'Описание слишком короткое');

			if ($validator -> fieldValid('controlword'))
				$validator -> assertLengthMore('controlword', 3, 'Контрольный пароль слишком короткий');

			$validator -> assertNotExists('email',           'Заполнено лишнее поле');

			if ($validator -> isValid())
			{
				$id = Chat_ChatRoomsModel::CreateRoom($_POST);

				$template -> headerSeeOther(
					'http://'. TemplateHelper::getSiteUrl() .'/chat/'. $id .'/'
				);
				return false;
			}

			$this['form_errors'] = $validator -> getValidationResults();
			$this['blog_form']   = $_POST;
		}

		$key     = 'chat';
		$template -> setParameter('captcha_key', $key);
		$session  -> instantSet('captcha_'.$key, true);

		$template -> setParameter('title', 'Добавление комнаты чата');
		$template -> setParameter('section', 'add');
		return true;
	}

	/**
	 * Добавление комнаты чата (ajax):
	 */
	public function addAjaxAction(Application $application)
	{
		$validator = new ValidatorHelper($_POST);
		$validator -> assertExists('title',              'Не введен заголовок');
		$validator -> assertLength('title',       25,    'Заголовок слишком длинный');
		$validator -> assertExists('description',        'Не введен вводный текст');
		$validator -> assertLength('description', 75,   'Вводный текст слишком длинный');
		$validator -> assertExists('controlword',        'Не введен контрольный пароль');

		if ($validator -> fieldValid('controlword'))
			$validator -> assertLengthMore('controlword', 3, 'Контрольный пароль слишком короткий');

		return array(
			'isValid'           => $validator -> isValid(),
			'validationResults' => $validator -> getValidationResults()
		);
	}

	/**
	 * Установка избранных комнат:
	 */
	public function setFavoritesAjaxAction(Application $application)
	{
		Chat_ChatRoomsModel::UpdateFavorites(explode(',', $_POST['favorites']));
		return true;
	}

	/**
	 * Общая комната чата:
	 */
	public function commonAction(Application $application, Template $template)
	{
		$template -> setParameter('title', 'Общий чат');
		$template -> setParameter('section', 'common');
        
        //Chat_ChatRoomsModel::CreateCommonRoom();

        $this['room'] = Chat_ChatRoomsModel::GetRoom(substr(md5('common') . md5('common'), 0, 45));

		EventModel::getInstance()
				-> Broadcast('view_chat');

		return true;
	}
	
	/**
	 * Просмотр лога чата:
	 */
	public function logAction(Application $application, Template $template)
	{
		$template -> setParameter('title', 'Лог комнаты чата');
		
	    if ($_SERVER['REQUEST_METHOD'] == 'POST')
	        $channel = Chat_ChatModel::TestChatChannel($_GET['id'], $_POST['password']);
        else
            $channel = Chat_ChatModel::TestChatChannel($_GET['id'], false);
        
        $this['room'] = Chat_ChatRoomsModel::GetRoom($_GET['id']);
        $this['channel'] = $channel;
        
        if ($channel != false) {
	        $log = Chat_ChatModel::GetLog($channel);
	        $this['log'] = $log;
	    }
	    
	    return true;
	}

	/**
	 * Комната чата:
	 */
	public function chatAction(Application $application, Template $template)
	{
            if ($_GET['id'])
	         $room = Chat_ChatRoomsModel::GetRoom($_GET['id']);
	    elseif ($_GET['alias'])
                 $room = Chat_ChatRoomsModel::GetRoomByAlias($_GET['alias']);

	    if ($room)
	    {
		    $template -> setParameter('title', '«'. $room['title'] .'» чат');
		    $this['room'] = $room;
		    
		    EventModel::getInstance()
				-> Broadcast('view_chat');
		    
		    return true;
		}
		
		$template -> setParameter('title', 'Комната не существует');
		
		EventModel::getInstance()
				-> Broadcast('view_chat');

		return true;
	}
	
	/**
	 * Операции с чатом (ajax):
	 */
	public function chatAjaxAction(Application $application)
	{
	    $session = Session::getInstance();
	    if ($_SERVER['REQUEST_METHOD'] == 'GET')
	    {
	        switch(@$_GET['command']) {
	            case 'enter':
	                return Chat_ChatModel::TestChatChannel($_GET['id']);
	                
	            case 'ping':
	                
	                EventModel::getInstance()
				        -> Broadcast('view_chat', $_GET['id']);
				        
	                return true;
	                
	            case 'welcome':
	                $room = Chat_ChatRoomsModel::GetRoom($_GET['id']);
	                $info = Chat_ChatRoomsModel::GetInfo($_GET['id']);
	                
	                EventModel::getInstance()
				        -> Broadcast('view_chat', $_GET['id']);
				        
	                return array('title' => $room['title'], 'description' => $room['description'], 'info' => $info);
	        }
	    }
	    else
	    {
	        switch(@$_POST['command']) {
	            case 'checkpassword':
	                return Chat_ChatModel::TestChatChannel($_GET['id'], $_POST['password']);
	                
	            case 'message':
	                if (($room = Chat_ChatRoomsModel::GetRoom($_GET['id'])) == false) 
	                    return array('error' => true, 'errors' => 'Комнаты чата больше не существует.');
	                
	                if (strpos($_POST['message'], '/') === 0)
	                {
	                    switch(true) {
	                        case (strpos($_POST['message'], '/help') === 0):
                                return array('error' => true, 'errors' => '
                                    <p> 
						                Доступные команды:
						            </p> 
						            <dl> 
							            <dt>Изменение названия комнаты:</dt> 
							            <dd> 
								            <p>/change_title <em>пароль_управления</em> <em>новое название</em></p> 
							            </dd> 
							            <dt>Изменение описания комнаты:</dt> 
							            <dd> 
								            <p>/change_description <em>пароль_управления</em> <em>новое описание</em></p> 
							            </dd> 
							            <dt>Изменение информационного сообщения:</dt> 
							            <dd> 
								            <p>/set_info <em>пароль_управления</em> <em>сообщение</em></p> 
							            </dd> 
							            <dt>«Девойс» (лишение права голоса):</dt> 
							            <dd> 
								            <p>/devoice <em>пароль_управления</em> <em>номер сообщения</em></p> 
							            </dd> 
							            <dt>«Энвойс» (разблокировка):</dt> 
							            <dd> 
								            <p>/envoice <em>пароль_управления</em> <em>хеш (выводится при девойсе)</em></p> 
							            </dd> 
							            <dt>Очистка блокировок:</dt> 
							            <dd> 
								            <p>/envoice <em>пароль_управления</em></p> 
							            </dd> 
							            <dt>Блокировка слова:</dt> 
							            <dd> 
								            <p>/blockword <em>пароль_управления</em> <em>слово или фраза</em></p> 
							            </dd> 
							            <dt>Просмотр блокированых слов:</dt> 
							            <dd> 
								            <p>/ls_blockwords <em>пароль_управления</em></p> 
							            </dd> 
							            <dt>Разблокировка слова:</dt> 
							            <dd> 
								            <p>/rm_blockword <em>пароль_управления</em> <em>номер_слова (из списка слов)</em></p> 
							            </dd> 
							            <dt>Статус публичности:</dt> 
							            <dd> 
								            <p>/set_public <em>пароль_управления</em> 0/1<em>(0 - скрытая, 1 - публичная)</em></p> 
							            </dd>
							            <dt>Удаление комнаты:</dt> 
							            <dd> 
								            <p>/remove_room <em>пароль_управления</em></p> 
							            </dd> 
					                </dl> 
					        ');
					        
					        case (strpos($_POST['message'], '/get_controlword') === 0):
                                if ($session -> isAdminSession())
                                    return array('error' => true, 'errors' => 'Пароль управления: '. $room['controlword']);
                                    
                                return array('error' => true, 'errors' => 'Неверная команда.');
					        
                            case (strpos($_POST['message'], '/devoice') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $message_id = preg_replace("/[^0-9]/", '', array_shift($parts));
                                
                                $key = Chat_ChatModel::deVoice($_GET['id'], $message_id);
                                
                                if ($key !== false)
                                    Chat_ChatModel::AddInfoMessage($_GET['id'], 'Автор сообщения >>'. $message_id .' ('.$key.') был лишен голоса.', $room['password']);
                                else
                                    return array('error' => true, 'errors' => 'Сообщение слишком старое.');
                                
                                return array('error' => false, 'id' => -1);
					        
                            case (strpos($_POST['message'], '/envoice') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $key = array_shift($parts);
                                
                                if ($key)
                                    Chat_ChatModel::enVoice($_GET['id'], $key);
                                else
                                    Chat_ChatModel::clearVoice($_GET['id']);
                                    
                                return array('error' => false, 'id' => -1);

                           case (strpos($_POST['message'], '/set_alias') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $alias = $parts[0];
                                
                                if (!preg_match('/^[0-9A-z\-]{4,20}$/i', $alias))
                                    return array('error' => true, 'errors' => 'Запрещенные символы в алиасе.');
                                
				                if (Chat_ChatRoomsModel::SetAlias($_GET['id'], $alias)) {
		                                        Chat_ChatRoomsModel::EditRoom($_GET['id'], array('alias' => $alias));
		                                        Chat_ChatModel::AddInfoMessage($_GET['id'], 'Установлен новый алиас, теперь комната доступна по адресу http://'. TemplateHelper::getSiteUrl() .'/chat/'. $alias .'/');
                                                	return array('error' => false, 'id' => -1);
				                }
                                return array('error' => true, 'errors' => 'Не удалось установить алиас (возможно он уже занят?).');

                           case (strpos($_POST['message'], '/remove_alias') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
				                Chat_ChatRoomsModel::RemoveAlias($room['alias']);
				                Chat_ChatRoomsModel::EditRoom($_GET['id'], array('alias' => null));
                                return array('error' => false, 'id' => -1);
			
			                case (strpos($_POST['message'], '/blockword') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $blocked = implode(' ', $parts);
                                Chat_ChatModel::blockWord($_GET['id'], $blocked);
                                return array('error' => false, 'id' => -1);
			
			                case (strpos($_POST['message'], '/ls_blockwords') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $result = array();
				                foreach(Chat_ChatModel::getBlockList($_GET['id']) as $num => $value) {
					                $result[] = $num .': '. $value;
				                }
                                return array('error' => true, 'errors' => sizeof($result) ? 'Запрещенные слова:<br />'. implode('<br />', $result) : 'Нет слов в блокировке.');
			
			                case (strpos($_POST['message'], '/rm_blockword') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $blocked = array_shift($parts);
                                Chat_ChatModel::unblockWord($_GET['id'], $blocked);
                                return array('error' => false, 'id' => -1);

                            case (strpos($_POST['message'], '/change_title') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $title = implode(' ', $parts);
                                
                                if (mb_strlen($title, 'UTF-8') > 25)
                                    return array('error' => true, 'errors' => 'Слишком длинное название.');
                                    
                                Chat_ChatRoomsModel::EditRoom($_GET['id'], array('title' => $title));
                                Chat_ChatModel::AddInfoMessage($_GET['id'], 'Название комнаты изменено с «'. $room['title'] .'» на «'. $title .'»', $room['password']);
                                
                                return array('error' => false, 'id' => -1);
                                
                            case (strpos($_POST['message'], '/change_description') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $description = implode(' ', $parts);
                                
                                if (mb_strlen($description, 'UTF-8') > 75)
                                    return array('error' => true, 'errors' => 'Слишком длинное описание.');
                                    
                                Chat_ChatRoomsModel::EditRoom($_GET['id'], array('description' => $description));
                                Chat_ChatModel::AddInfoMessage($_GET['id'], 'Описание комнаты изменено с «'. $room['description'] .'» на «'. $description .'»', $room['password']);
                                
                                return array('error' => false, 'id' => -1);
                            
                            case (strpos($_POST['message'], '/a') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                $symbol = array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $message = '<span style="color:orange">'.$symbol.'</span> '. implode(' ', $parts);
                                $id = Chat_ChatModel::AddMessage($_GET['id'], $message, $_POST['password']);
				            
	                            return array('error' => false, 'id' => $id);
                                
                            case (strpos($_POST['message'], '/set_info') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $info = implode(' ', $parts);
                                
                                if (mb_strlen($info, 'UTF-8') > 612)
                                    return array('error' => true, 'errors' => 'Слишком длинное информационное сообщение.');
                                    
                                $info = Chat_ChatRoomsModel::SetInfo($_GET['id'], $info);
                                Chat_ChatModel::AddInfoMessage($_GET['id'], 'Новое информационное сообщение:'. $info, $room['password']);
                                return array('error' => false, 'id' => -1);
                                
                            case (strpos($_POST['message'], '/set_public') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                $state = array_shift($parts);
                                Chat_ChatRoomsModel::EditRoom($_GET['id'], array('public' => (bool)$state));
                                Chat_ChatModel::AddInfoMessage($_GET['id'], 'Изменен статус комнаты: '. ((bool)$state ? 'Публичная' : 'Приватная'), $room['password']);
                                
                                return array('error' => false, 'id' => -1);
                           
                            case (strpos($_POST['message'], '/remove_room') === 0):
                                $parts = explode(' ', $_POST['message']);
                                array_shift($parts);
                                
                                if ($room['controlword'] !== array_shift($parts))
                                    return array('error' => true, 'errors' => 'Неверный пароль управления.');
                                
                                Chat_ChatModel::AddErrorMessage($_GET['id'], 'Комната была удалена.', $room['password']);
                                Chat_ChatRoomsModel::RemoveRoom($_GET['id']);
                                
                                return array('error' => false, 'id' => -1);
                            default: 
                                return array('error' => true, 'errors' => 'Неверная команда.');
                        }
                    }
	            
	                $validator = new ValidatorHelper($_POST);
	                $validator -> assertExists('message',       'Не введено сообщение');
	                $validator -> assertLength('message', 1024, 'Сообщение слишком длинное');
	                
	                EventModel::getInstance()
				        -> Broadcast('view_chat', $_GET['id']);
				        
				    if ($validator -> isValid()) 
				    { 
				        if (!Chat_ChatModel::hasVoice($_GET['id'], $_SERVER['REMOTE_ADDR']))
				            return array('error' => true, 'errors' => 'Вам запрещено отправлять сообщения в этот чат.');
				        
					$blocked_words = Chat_ChatModel::getBlockList($_GET['id']);
					$messagefilter = htmlspecialchars_decode(strtr($_POST['message'], array('*' => '', '_' => '', '=' => '', '-' => '')));
					foreach($blocked_words as $word) {
						if (preg_match('~'. str_replace('~', '\~', $word) .'~ui', $messagefilter) == true)
							return array('error' => true, 'errors' => 'Запрещенное слово из вордфильтра.');
					}

				        similar_text(trim($session -> persistenceGet('last_chat_text', '')), trim($_POST['message']), $percent);
				        
			            if ($percent < 90) {
				            Chat_ChatRoomsModel::TouchRoom($_GET['id']);
				            $session -> persistenceSet('last_chat_text', $_POST['message']);
				            
				            $id = Chat_ChatModel::AddMessage($_GET['id'], $_POST['message'], $_POST['password']);
				            
	                        return array('error' => false, 'id' => $id);
	                    }
	                    
	                    return array('error' => true, 'errors' => 'Флуд контроль, сообщение проигнорировано.');
	                }
	                else
	                    return array('error' => true, 'errors' => implode(', ', $validator -> getValidationResults()));
	        }
	    }
	}
}

/**
 * Обработчики событий:
 */
EventModel::getInstance()
	/**
	 * Статистика просмотра:
	 */
	-> AddEventListener('view_*', function($data) {
		if ($data)
		    Chat_ChatRoomsModel::SetRoomOnline($data);
		
		Blog_BlogStatisticsModel::updateGlobalVisitors();
	});
