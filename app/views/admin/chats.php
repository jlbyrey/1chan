		<h2><a href="#">Первый канал</a> &raquo; <a href="#" class="active">Список активных чатов</a></h2>

                <div id="main">
                	<h3>Чаты:</h3>
                    	<table cellpadding="0" cellspacing="0">
                    	<?php if (empty($chats)): ?>
							<td>Нет чатов для отображения.</td>
                    	<?php else: ?>
                    	<?php $i = 0; ?>
                    	<?php foreach($chats as $room): ?>
							<tr<?php if(++$i % 2): ?> class="odd"<?php endif; ?>>
                                <td><?php echo $room['title']; ?></td>
                                <td class="action"><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/chat/<?php echo($room['room_id']); ?>/" class="view">View</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/chatEdit?id=<?php echo($room['room_id']); ?>" class="edit">Edit</a><a href="http://<?php echo TemplateHelper::getSiteUrl(); ?>/admin/chatDelete?id=<?php echo($room['room_id']); ?>" class="delete">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </table>
                        <br /><br />
                </div>
