# Установка на DEV для локальной разработки

Перейти в папку с исходным кодом проекта, например:
```
cd /var/www/coop
```

Создать заготовки для конфигов:
```
make env=dev init-configs
```

Отредактировать настройки:
```
mcedit .env.local
```

Где:
1. APP_ENV=dev
2. APP_DEBUG=1
3. APP_SECRET - обязательно заменить `_ChangeThis!!!_` на произвольную строку из множества разных символов.


Отредактировать настройки:
```
mcedit .env.docker.dev.local
```

Где:

1. прописать явно `WORKING_DIR=/app`
2. указать порты которые удобны для: `WEB_PORT`

После редактирования конфигов, нужно собрать и запустить приложение (первый раз этот процесс занимает примерно 15 минут, дальше обновления будут происходить быстрее, примерно за 40 секунд):
```
./deploy.sh
```

Для доступа к БД снаружи, нужно скопировать файл `docker-compose.local.yml` в корень проекта:
```
cp ./.docker/docker-compose.local.yml ./docker-compose.local.yml
```

и там указать порты...


Работа с Composer через make, например:
```
make composer outdated
make composer update
make composer require symfony/messenger
```

Потушить докер контейнеры:
```
make down
```

Если нужно удалить полностью БД, то сначала надо получить название вольюма, командой:
```
docker volume ls
```

Которая выдаст список всех вольюмов, нас интересует, тот который оканчивается на `coop_dev_pg13`, например: 
```
docker volume rm varwwwcoop_dev_pg13
```
