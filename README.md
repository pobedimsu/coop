Платформа для кооперации
========================

Установка Debian
----------------

На чистую ОС установка длится примерно 3 минуты.
```
apt install git -qq -y
git clone https://github.com/pobedimsu/coop.git
./coop/install/debian.sh
mv coop/ /var/www/coop
```

Или одной строкой:
```
apt install git -qq -y; git clone https://github.com/pobedimsu/coop.git; ./coop/install/debian.sh; mv coop/ /var/www/coop
```

По умолчанию на сервере будет установлена временная зона Мск, если нужно изменить:
```
dpkg-reconfigure tzdata
```

После чего, следует перезагрузить сервер:
```
reboot
```

Создание телегам бота
----------------------

Зайти в телеграм: @BotFather

Выполнить там команду ```/newbot```, где нужно будет ввести название бота, например ```Мой обменник```, после чего указать имя, которое должно оканчиваться на 'bot', например ```my_barter_bot```, после чего сразу будет сгенерирован API токен, который далее нужно  прописать в конфиг.

Установка сайта
---------------

Перейти в папку с исходным кодом сайта:
```
cd /var/www/coop
```

Создать заготовки для конфигов:
```
make init-configs
```

Отредактировать настройки: 
```
mcedit .env.local
```

Где:
1. CURRENCY - название обменной еденицы
2. TG_BOT_NAME - указать имя бота
3. TG_BOT_TOKEN - сгенерированый API токен бота
4. APP_SECRET - обязательно заменить `_ChangeThis!!!_` на произвольную строку из множества разных символов.  


После редактирования конфигов, нужно собрать и запустить приложение (первый раз этот процесс занимает примерно 15 минут, дальше обновления будут происходить быстрее, примерно за 40 секунд):
```
./deploy.sh
```

Создание конфига для nginx, запустить с правами рута:
```
./install/nginx-config <my-domain.ru>
```

Установить SSL сертификат
```
certbot
```

Подключить телеграм бота к сайту, где заменить `my-domain.ru` на ваш домен:
```
bin/console telegram:bot:webhook:set coop https://my-domain.ru/telegram/
```

Первичная настройка 
-------------------

Создать первого пользователя:
```    
bin/console user:add
```

Посмотреть список всех пользователей:
```
bin/console user:list
```

Назначить роль пользователю, например: ROLE_SUPER_ADMIN
```
bin/console user:role:promote <username> <role>
```

В случае, если будут проблеммы с доступами к файлам, то нужно обнулить кеш
```    
bin/clear_cache
```

@TODO
-----

1. СЗ: Метод подсчёта общей суммы заказа для твига
2. Патч для Gedmo\Tree\Strategy\ORM\Closure.php для совместимости c Ramsey\Uuid, нужно вместо

```php
$this->pendingNodesLevelProcess[$nodeId] = $node;
```
сделать так:
```php
$this->pendingNodesLevelProcess[(string) $nodeId] = $node;
```
