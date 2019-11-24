Платформа для кооперации
========================

Установка
---------

Первым делом необходимо зарегистрироваться на Github и подключить свой SSH ключ вот тут https://github.com/settings/keys (этот момент временный, пока используются подмодули гита, в дальнейшем пакеты будут подтягиваться композером) 

Получение кода:
```
    git clone https://github.com/pobedimsu/coop.git
    cd coop
    git submodule update --init
    cp .env .env.local
    composer i
```

Создать БД в MySQL или MariaDB.

Отредактировать настройки в ```.env.local```

1. Обязательно указать произвольную строку для APP_SECRET
2. Прописать доступы к БД в параметре DATABASE_URL 

Далее выполнить в консоле:
```    
    bin/console doctrine:schema:update --force
    bin/console app:init
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

1. Заменить Bill на Transaction, где будет только одна запись, указывающая кто и кому перечислил
2. СЗ: Метод подсчёта общей суммы заказа для твига
3. СЗ: Логику при изменении цены
4. Возможность менять БД (сейчас настройки для MariaDB)
