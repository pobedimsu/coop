Платформа для кооперации
========================

Системные требования
--------------------

* PHP v7.3 c расширениями:
    * ctype
    * gd
    * iconv    
    * intl
    * json 
    * mbstring 
    * PDO
    * SimpleXML
    * tokenizer 
    * xml
* [Composer](https://getcomposer.org/)
* СУБД: MariaDB, MySQL, PostgreSQL

Чтобы узнать соотвествует соответствие системным требованиямм, можно выполнить команду:

```
composer check-platform-reqs
```

Установка
---------

Получение кода:
```
git clone https://github.com/pobedimsu/coop.git
cd coop
cp .env .env.local
composer i
```

Создать БД.

Отредактировать настройки в ```.env.local```

1. Обязательно указать произвольную строку для APP_SECRET
2. Прописать доступы к БД в параметре DATABASE_URL 

Далее выполнить в консоле:
```    
bin/console doctrine:schema:update --force
bin/console app:init
```

В завершени нужно создать первого пользователя
```    
bin/console user:add
```

В случае, если будут проблеммы с доступак к файлам, то нужно обнулить кеш
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

@TODO
-----

1. СЗ: Метод подсчёта общей суммы заказа для твига
2. СЗ: Логику при изменении цены
3. Возможность менять БД (сейчас настройки для MariaDB) на MySQL и PostgreSQL
