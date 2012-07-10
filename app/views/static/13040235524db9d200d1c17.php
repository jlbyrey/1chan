<div class="l-static-wrap">
					<div class="b-static m-justify">
						<h1>Сообщить новость!</h1>
						<p>
							Теперь сообщить об интересном событии становится намного проще, все,
							что от вас требуется, это отправить ссылку и краткое описание события.
						</p>

						<p style="text-align:right;padding-right:10px;">
							<b>Ссылка:</b> <input type="text" value="http://" id="share_link" style="width:500px" /><br />
							<b>Описание:</b> <input type="text" id="share_desc" style="width:500px" size="50" />
							<input type="button" value="Отправить" onclick="var i=new Image();i.onload=function(){alert('Ваша ссылка принята в обработку, спасибо!');};i.onerror=function(){alert('Произошла ошибка, возможно ссылка уже была отправлена, либо вы отправили слишком много ссылок.');};i.src='http://1chan.ru/service/share/?link='+encodeURIComponent($('#share_link').val().replace(/#(.*)$/,''))+'&description='+encodeURIComponent($('#share_desc').val())+'&title='+encodeURIComponent('Отправлено через форму');$('#share_desc, #share_link').val('');" />
						</p>
						<p>
							С помощью этой формы вы не отправляете новости на сам одинчан, они транслируются в специальную
							конференцию, где модераторы и авторы будут отслеживать новые ссылки и писать правильно оформленные
							и максимально полные посты на сам Одинчан.
						</p>
						<p>
							Вы также можете добавить букмарклет 
							(<a href="http://ru.wikipedia.org/wiki/Букмарклет" target="_blank">что это?</a>)
							в вашу панель закладок (избранное) и кликнуть на неё, когда вы находитесь на
							странице, где размещена новость, или другой материал, о котором бы вы хотели сообщить:
						</p>
						<p style="text-align:center;padding: 10px 0;">
							<a onclick="return false;" style="background:#FFB975;padding:3px 8px;color:white;font-weight:bold" href="javascript:(function(){f=document.getElementsByTagName('frame');with((f.length&&f[f.length-1].contentWindow)||window){d=prompt('Введите краткое описание ссылки:','без описания');if (d){var i=new Image();i.onload=function(){alert('Ваша ссылка принята в обработку, спасибо!');};i.onerror=function(){alert('Произошла ошибка, возможно ссылка уже была отправлена, либо вы отправили слишком много ссылок.');};i.src='http://1chan.ru/service/share/?link='+encodeURIComponent(location.href.replace(/#(.*)$/,''))+'&description='+encodeURIComponent(d)+'&title='+encodeURIComponent(document.title.substring(0, 70));}}})();">
								Сообщить новость (1chan.ru)
							</a>
							<br />
							<small>(перетащите ссылку на панель закладок, или добавьте в избранное)</small>
						</p>
						<p>
							Добавьте ссылку в закладки и просто нажимайте, если находитесь на странице, о которой бы
							хотели сообщить. Но помните, отправка более, чем 5 ссылок в минуту приведет к кратковременной
							блокировке, а ссылка, которая уже была отправлена через букмарклет, не может быть отправлена повторно.
						</p>
						<h1>Авторам:</h1>
						<p>
							Если вы хотите помочь Одинчану, отправляя новости и модерируя раздел /news/, вам необходимо
							завести джаббер и посетить конференцию <em>press@conference.1chan.ru</em>. 
						</p>
						<p>
							Эта конференция была создана для того, чтобы постоянные авторы постов Одинчана могли 
							скооперировавшись вовремя описывать происходящие события (в т.ч. сообщаемые другими
							пользователями через букмарклет), грамотно и с правильным оформлением.
						</p>
						<p>
							Безусловно, отличившиеся авторы будут назначены модераторами, кроме того, при желании
							может быть получен Jabber ID на сервере 1chan.ru, для внутреннего пользования.
						</p>
						<p>
							Если у вас есть желание помочь Одинчану, упорство и немного самоотдачи, то милости просим
							в конференцию <em>press@conference.1chan.ru</em>.
						</p>
					</div>
				</div> 