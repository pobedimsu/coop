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


Для разработчиков
-----------------

Получение последней версии песочницы:
```
    git pull;git submodule update --init
``` 

Обновление подмодулей из удалённых репозиториев:
```
    git submodule update --remote
``` 

Решение проблемы с detached head, переключение всех подмодулей в master:
```
    git submodule foreach 'git checkout master'
``` 


@TODO
-----

1. СЗ: Метод подсчёта общей суммы заказа для твига
2. СЗ: Логику при изменении цены
3. Возможность менять БД (сейчас настройки для MariaDB) на MySQL и PostgreSQL
