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

Настройка телегам бота
----------------------

@BotFather

Выполнить команду ```/newbot```, где нужно будет ввести название бота, например ```Мой обменник```, после чего указать имя, которое должно оканчиваться на 'bot', например ```my_barter_bot```, после чего сразу будет сгенерирован API токен, который далее нужно  прописать в конфиг.


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

Где в:
1. CURRENCY - название обменной еденицы
2. TG_BOT_NAME - указать имя бота
3. TG_BOT_TOKEN - сгенерированый API токен бота
4. APP_SECRET - обязательно заменить `_ChangeThis!!!_` на произвольную строку из множества разных символов.  


Создать первого пользователя
```    
bin/console user:add
```

В случае, если будут проблеммы с доступами к файлам, то нужно обнулить кеш
```    
bin/clear_cache
```


Дополнительные команды
----------------------

Посмотреть список всех пользователей:
```
bin/console user:list
```

Назначить роль пользователю, например: ROLE_SUPER_ADMIN
```
bin/console user:role:promote <username> <role>
```

Для запуска команд в докере, нужно перед командой написать: `docker-compose run php` итого формат будет такой: 

```
docker-compose run php <command>
# например:
docker-compose run php bin/console user:list
```

Telegram
--------

Создать бота в тг @BotFather

Добавить вебхук

https://api.telegram.org/bot{my_bot_token}/setWebhook?url={url_to_send_updates_to}

Проверить, что вебхук установлен

https://api.telegram.org/bot{my_bot_token}/getWebhookInfo



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
