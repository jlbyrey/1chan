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

                <?php if(isset($room)): ?>
				<div class="b-chat" id="room_<?php echo($room['room_id']); ?>">
					<div class="b-chat_b-header">
						<div class="b-chat_b-header_b-title js-room-title"><?php echo($room['title']); ?></div>
						<div class="b-chat_b-header_b-statistics js-room-statistics"><?php $count = Chat_ChatRoomsModel::GetRoomOnline($room['room_id']); echo(TemplateHelper::ending($count == 0 ? 1 : $count, 'участник', 'участника', 'участников')); ?></div>
					</div>
					<div class="b-chat_b-messages g-clearfix">
					
					<textarea id="template_message_password" style="display:none">
						<div class="b-chat_b-message m-info">
							<div class="b-chat_b-message_b-body">
							<form action="" method="post" id="password_form">
								<p>
									Для входа в комнату необходимо ввести пароль: <input type="text" class="js-room-password" value="" /> <input type="submit" value="Вход" />
								</p>
							</form>
							</div>
						</div>
				    </textarea>
					
					<textarea id="template_message_info" style="display:none">
						<div class="b-chat_b-message m-info">
							<div class="b-chat_b-message_b-body">
								<p>
									<%=message%>
								</p>
							</div>
						</div>
				    </textarea>
					
					<textarea id="template_message_error" style="display:none">
						<div class="b-chat_b-message m-error">
							<div class="b-chat_b-message_b-body">
								<p>
									<%=message%>
								</p>
							</div>
						</div>
				    </textarea>
					
					<textarea id="template_message_normal" style="display:none">
						<div class="b-chat_b-message m-normal">
							<a href="#" class="b-chat_b-message_b-link js-message-from-link" title="<%=id%>">№<%=id%></a>: <em class="b-chat_b-message_b-date"><%=date%></em>
							<div class="b-chat_b-message_b-body">
								<p>
									<%=message%>
								</p>
							</div>
						</div>
				    </textarea>
					
					<div id="placeholder_messages"></div>
					</div>
					<div class="b-chat_b-form">
					<form action="" method="post" id="message_form">
						<textarea class="js-message-textarea" rows="3" disabled="disabled" id="comment_form_text"></textarea>
						<input class="js-message-submit" type="submit" disabled="disabled" value="Отправить (Ctrl+Enter)" />
				    </form>
					</div>
				</div>
				<?php else: ?>
					<div class="b-static"> 
						<h1>Запрашиваемая комната не найдена</h1> 
						<p> 
							Либо она не существовала, либо была удалена модератором.
						</p> 
					</div>
				
			    <?php endif; ?>
