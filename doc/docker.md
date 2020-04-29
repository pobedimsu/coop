Запуск в Docker
---------------

Пока только в режиме разработки!

Получение кода:
```
git clone https://github.com/esmark/coop.git
cd coop
```

Инициализация приложения (займёт примерно 10 минут):

```
make init
```

Запуск докера:
```
make up
```

По умолчанию веб порт задан 8089, открывать проект по адресу:

```
http://localhost:8099/
``` 

Остановка докера:
```
make down
```

Если нужно изменить порт, тогда запускать проект так:
```
make down
WEB_PORT=80 make up
```
в этом случае, проект будет доступен на 80 порту:
```
http://localhost/
``` 

Или можно запустить установку с нуля одной строчкой:

Для Windows:
```
git clone https://github.com/esmark/coop.git;cd coop;make init;make up;start http://localhost:8099/
```

Для Linux:
```
git clone https://github.com/esmark/coop.git;cd coop;make init;make up;xdg-open http://localhost:8099/
```
