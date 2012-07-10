1chan.ru
=====

Этот код - движок сайта 1chan.ru, который был выложен из-за взлома и утечки кода от 10.11.11.
Код движка лицензирован под AGPLv3, обратите внимание, что этот тип лицензии обязывает всех,
кто использует движок, независимо от того, передается ли пользователю сам программный продукт,
или пользователи взаимодействуют с ним только через интернет, выкладывать измененный код под
той же лицензией, публично.

# Установка

Для запуска движка вам понадобится php5-fpm (встроен в php5), nginx, mysql, redis.io, dklab_realplexor и sphinxsearch.

1. Установите и настройке php5-fpm и nginx.
2. Залейте код в директорию, например /var/www.
3. Настройте конфигурацию nginx для работы с php5-fpm, при этом необходимо установить rewrite rule для location /, например вот так:
	    location / {
	        root   /var/www/1chan.ru/www;
	        index  index.php;
	
	        if (!-e $request_filename) {
	            rewrite  ^(/.*)$  /index.php?q=$1  last;
	            break;
	        }
	    }
4. Установите Dklab\_Realplexor, согласно документации: http://dklab.ru/lib/dklab_realplexor/
Для добавления в nignx используйте:
	    server {
	        listen   80;
	        server_name pipe.1chan.ru;
		    
	        location / {
	            proxy_pass http://127.0.0.1:8088;
	            proxy_connect_timeout 15;
	            proxy_read_timeout 90;
	            proxy_send_timeout 90;
	        }
	    }
5. Установите redis.io последней версии с сайта: http://redis.io/
6. Установите redis php5 расширение: https://github.com/nicolasff/phpredis
7. Установите MySQL и отредактируйте файлы /app/config.php и /1chan.conf, указав настройки подключения. Залейте первоначальный дамп из файла /dump.sql.
8. Установите sphinxsearch и установите индексацию с кофигурацией /1chan.conf. Для правильной работы поиска вам, скорее всего, придется заменить файл /app/classes/3rdparty/sphinx.class.php на файл клиента своей версии. Для автоматического запуска индексатора вам может понадобиться установить cron-задачи (код для crontab):
		0 0 * * * /usr/bin/indexer --config /var/www/1chan.ru/1chan.conf --all --rotate
9. Для смены адреса сайта вам будет необходимо отредактировать файл /app/helpers/template.helper.php (изменить результат вызова getSiteUrl()), а также изменить адрес в файле /www/js/production.js - на новый адрес realplexor.
10. Для работы проверки доступности ссылок используется скрипт, запускаемый следующим cron-скриптом:
		*/2 * * * * /bin/sh /var/www/1chan.ru/scripts/cron.sh
