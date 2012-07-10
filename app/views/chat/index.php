				<div class="b-chat-panel g-clearfix">
					<ul>
						<li class="b-chat-panel_b-add-room<?php if ($this -> getParameter('section') == 'add'): ?> b-chat-panel_m-active<?php endif; ?>">
							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/add-chat.png" width="16" height="16" alt="" />
							<a href="/chat/add/">Добавить комнату</a>
						</li>
						<li class="b-chat-panel_b-all<?php if ($this -> getParameter('section') == 'all'): ?> b-chat-panel_m-active<?php endif; ?>">
							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/chat-rooms.png" width="16" height="16" alt="" />
							<a href="/chat/">Тематические чаты</a>
						</li>
						<li class="b-chat-panel_b-common<?php if ($this -> getParameter('section') == 'common'): ?> b-chat-panel_m-active<?php endif; ?>">
							<img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/chat-common.png" width="16" height="16" alt="" />
							<a href="/chat/common/">Общий чат</a>
						</li>
					</ul>
				</div>

				<div class="b-chat-rooms">
					<div class="b-chat-rooms_b-chat-group">
						Избранные комнаты <a class="b-chat-rooms_b-chat-group_b-add-hidden g-dynamic" href="#">добавить скрытую комнату</a>
					</div>
					<div class="b-chat-rooms_b-empty js-favchatlist-empty<?php if(!empty($favorites)): ?> g-hidden<?php endif; ?>">Перетащите комнату для добавления в избранное.</div>
					<div class="js-favchatlist g-clearfix">
						<?php foreach($favorites as $room): ?>
						<div class="b-chat-rooms_b-room" id="room_<?php echo($room['room_id']); ?>">
							<div class="b-chat-rooms_b-room_b-header">
								<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/chat/<?php echo(!is_null($room['alias']) ? $room['alias'] : $room['room_id']); ?>/"><?php echo($room['title']); ?></a> <img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/invisible.png" width="16" height="16" alt="" class="js-hidden-room-icon<?php if($room['public']): ?> g-hidden<?php endif; ?>" /> <img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/key.gif" width="16" height="16" alt="" class="js-key-icon<?php if(empty($room['password'])): ?> g-hidden<?php endif; ?>"/>
							</div>
							<div class="b-chat-rooms_b-room_b-info">
								<em><?php echo(TemplateHelper::ending(Chat_ChatRoomsModel::GetRoomOnline($room['room_id']), 'участник', 'участника', 'участников')); ?></em>
							</div>
							<div class="b-chat-rooms_b-room_b-description">
								<p><?php echo($room['description']); ?></p>
							</div>
						</div>
						<?php endforeach; ?>

					</div>
				</div>

				<div class="b-chat-rooms">
					<div class="b-chat-rooms_b-chat-group">
						Публичные комнаты
					</div>
					<div class="b-chat-added-notify" id="chat_notify">
						Доступны новые комнаты, <a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/chat/">перезагрузите страницу</a>, чтобы увидеть их.
					</div>

					<div class="b-chat-rooms_b-empty js-allchatlist-empty<?php if(!empty($public)): ?> g-hidden<?php endif; ?>">Нет активных публичных комнат.</div>

					<div class="js-allchatlist g-clearfix">
						<?php foreach($public as $room): ?>
						<div class="b-chat-rooms_b-room" id="room_<?php echo($room['room_id']); ?>">
							<div class="b-chat-rooms_b-room_b-header">
								<a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/chat/<?php echo(!is_null($room['alias']) ? $room['alias'] : $room['room_id']); ?>/"><?php echo($room['title']); ?></a> <img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/invisible.png" width="16" height="16" alt="" class="js-hidden-room-icon<?php if($room['public']): ?> g-hidden<?php endif; ?>" /> <img src="http://<?php echo TemplateHelper::getSiteUrl(); ?>/ico/key.gif" width="16" height="16" alt="" class="js-key-icon<?php if(empty($room['password'])): ?> g-hidden<?php endif; ?>"/>
							</div>
							<div class="b-chat-rooms_b-room_b-info">
								<em><?php echo(TemplateHelper::ending(Chat_ChatRoomsModel::GetRoomOnline($room['room_id']), 'участник', 'участника', 'участников')); ?></em>
							</div>
							<div class="b-chat-rooms_b-room_b-description">
								<p><?php echo($room['description']); ?></p>
							</div>
						</div>
						<?php endforeach; ?>

					</div>
				</div>
