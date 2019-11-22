Платформа для кооперации
========================

Установка
---------

Получение кода
```
    git clone https://github.com/pobedimsu/coop.git
    cd coop
    git submodule update --init
    cp .env .env.local
    composer i
```

Редактирование настроек в .env.local


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
