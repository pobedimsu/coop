Платформа для кооперации
========================

Установка Debian
----------------

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

После чего, следует перезагрузить сервер:

```
reboot
```

Установка
---------

Отредактировать настройки в ```.env.local```

1. Обязательно указать произвольную строку для APP_SECRET
2. Прописать доступы к БД в параметре DATABASE_URL 

Создать первого пользователя
```    
bin/console user:add
```

В случае, если будут проблеммы с доступами к файлам, то нужно обнулить кеш
```    
bin/clear_cache
```

Запуск в Docker
---------------

Документация тут [docker.md](doc/docker.md) 

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
