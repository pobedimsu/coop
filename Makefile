.PHONY: bash composer deploy exec restart

# Если существует .env.local, то он будет прочитан, иначе .env
ifneq (",$(wildcard ./.env.local)")
    include .env.local
    DEFAULT_ENV_FILE = '.env.local'
else
    include .env
    DEFAULT_ENV_FILE = '.env'
endif

# Подгрузка настроек докера, чтобы изменять файлы конфигов через sed (nginx, supervisor, etc..)
ifneq (",$(wildcard ./.env.docker.${APP_ENV}.local)")
    include .env.docker.${APP_ENV}.local
endif

INTERACTIVE := $(shell [ -t 0 ] && echo 1)
ifdef INTERACTIVE # is a terminal
	EXEC_TTY :=
else # cron job
	EXEC_TTY := -T
endif

args = `arg="$(filter-out $@,$(MAKECMDGOALS))" && echo $${arg:-${1}}`
env = ${APP_ENV}
pwd = $(shell eval pwd -P)

DOCKER_COMPOSE_BIN := "${pwd}/.docker/docker-compose-v1"

ifneq (",$(wildcard ./docker-compose.local.yml)")
    docker-compose = ${DOCKER_COMPOSE_BIN} --file=./.docker/docker-compose.yml --file=./.docker/docker-compose.${env}.yml --file=./docker-compose.local.yml --env-file=./.env.docker.${env}.local -p "${pwd}_${env}"
else
    docker-compose = ${DOCKER_COMPOSE_BIN} --file=./.docker/docker-compose.yml --file=./.docker/docker-compose.${env}.yml --env-file=./.env.docker.${env}.local -p "${pwd}_${env}"
endif

docker-compose-php-cli = ${docker-compose} run --rm php-cli

# https://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux
# Reset
Color_Off = \033[0m
# Regular Colors
Black = \033[0;30m
Red = \033[0;31m
Green = \033[0;32m
Yellow = \033[0;33m
Blue = \033[0;34m
Purple = \033[0;35m
Cyan = \033[0;36m
White = \033[0;37m

help:
	@echo -e "[${env}]: ENV = ${Yellow}${env}${Color_Off} get from ${DEFAULT_ENV_FILE}"
	@if [ ! -f .env.local ]; then \
  		echo -e "[${env}]: No configuration found."; \
  		echo -e "[${env}]: Run '${Yellow}make init-configs${Color_Off}' for default ENV"; \
  		echo -e "[${env}]: or '${Yellow}make env=dev init-configs${Color_Off}' for development ENV."; \
  		echo -e "[${env}]: When edit generated config files!"; \
	else \
		echo -e "[${env}]: For deploing application run '${Yellow}make deploy${Color_Off}'"; \
	fi

restart: down up
restart-build: down build up

open:
	@xdg-open http://localhost:${WEB_PORT}

deploy:
	@make -s down
	@git pull
	@if [ ! -f .env.local ]; then \
  		echo "[${env}]: No configuration found."; \
  		echo -e "[${env}]: Run '${Yellow}make init-configs${Color_Off}' for default ENV or '${Yellow}make env=dev init-configs${Color_Off}' for development ENV."; \
	else \
		make -s build; \
		make -s composer-install; \
		make -s up; \
		bin/console doctrine:migrations:migrate --no-interaction; \
		bin/console app:init; \
	fi

soft-deploy:
	@git pull
	@if [ ! -f .env.local ]; then \
  		echo "[${env}]: No configuration found."; \
  		echo -e "[${env}]: Run '${Yellow}make init-configs${Color_Off}' for default ENV or '${Yellow}make env=dev init-configs${Color_Off}' for development ENV."; \
	else \
		make -s composer-install; \
		bin/console doctrine:migrations:migrate --no-interaction; \
		bin/console app:init; \
	fi

init-configs:
	@if [ ! -f .env.local ]; then \
  		echo "[${env}]: generate => .env.local"; \
		cp .env .env.local; \
		sed -i "s/APP_ENV=prod/APP_ENV=${env}/g" .env.local; \
	else \
	  	echo "[${env}]: already exist => .env.local"; \
	fi
	@if [ ! -f .env.docker.${env}.local ]; then \
  		echo "[${env}]: generate => .env.docker.${env}.local"; \
		cp .docker/_templates/.env.docker.local .env.docker.${env}.local; \
		sed -i "s/APP_ENV=~/APP_ENV=${env}/g" .env.docker.${env}.local; \
		sed -i "s#WORKING_DIR=/app#WORKING_DIR=${pwd}#g" .env.docker.${env}.local; \
	else \
	  	echo "[${env}]: already exist => .env.docker.${env}.local "; \
	fi

_init:
	${docker-compose} run --rm alpine bin/init_dirs
	@if [ ${env} = 'dev' ]; then \
		cp --force .docker/_templates/nginx.conf var/nginx.conf; \
		sed -i "s,{{DOMAIN}},${DOMAIN},g" var/nginx.conf; \
		sed -i "s,{{WORKING_DIR}},${WORKING_DIR},g" var/nginx.conf; \
	fi

build:
	@echo "[${env}]: build containers..."
	@make -s _init
	@${docker-compose} build
	@echo "[${env}]: containers builded!"

rebuild-php-cli:
	@echo "[${env}]: build php-cli container..."
	@make -s _init
	@${docker-compose} build --no-cache php-cli
	@echo "[${env}]: php-cli containers builded!"

up:
	@echo "[${env}]: start containers..."
	@make -s _init
	@${docker-compose} up -d
	@echo "[${env}]: containers started!"

down:
	@echo "[${env}]: stopping containers..."
	@${docker-compose} down --remove-orphans
	@echo "[${env}]: containers stopped!"

ps:
	@${docker-compose} ps

### Cache
cache-clear:
	@if [ -d var/cache/${env} ]; then \
		echo "[${env}]: Clearing var/cache/${env}..."; \
		${docker-compose-php-cli} rm -rf var/cache/${env}; \
	fi

cache-warmup:
	@make -s _init
	@${docker-compose-php-cli} bin/local-console cache:warmup -e ${env}

### Composer
composer-install:
	@if [ ${env} = 'prod' ]; then \
		${docker-compose-php-cli} composer install --no-dev --optimize-autoloader; \
	else \
		${docker-compose-php-cli} composer install; \
	fi
	@echo "[${env}]: Time elapsed $$(date -ud "@$$(($$(date +%s) - $(shell date +%s)))" +%T)"

composer-outdated:
	@if [ ${env} = 'prod' ]; then \
		${docker-compose-php-cli} composer outdated -m --no-dev; \
	else \
		${docker-compose-php-cli} composer outdated -m; \
	fi
	@echo "[${env}]: Time elapsed $$(date -ud "@$$(($$(date +%s) - $(shell date +%s)))" +%T)"

composer-update:
	@if [ ${env} = 'prod' ]; then \
		${docker-compose-php-cli} composer update --no-dev --optimize-autoloader; \
	else \
		${docker-compose-php-cli} composer update; \
	fi
	@echo "[${env}]: Time elapsed $$(date -ud "@$$(($$(date +%s) - $(shell date +%s)))" +%T)"

composer-require-dev:
	@${docker-compose-php-cli} composer require --dev $(call args)

composer:
	@${docker-compose-php-cli} composer $(call args)
	@echo "[${env}]: Time elapsed $$(date -ud "@$$(($$(date +%s) - $(shell date +%s)))" +%T)"

### DataBase
db-dump:
	@${docker-compose-php-cli} bin/local-console -e ${env} db:du

db-restore-from-dump:
	@${docker-compose-php-cli} bin/local-console -e ${env} db:re

db-diff:
	@${docker-compose-php-cli} bin/local-console -e ${env} doctrine:migrations:diff

db-migrate:
	@${docker-compose-php-cli} bin/local-console -e ${env} doctrine:migrations:migrate --no-interaction

db-show-sql-update:
	@${docker-compose-php-cli} bin/local-console -e ${env} doctrine:schema:update --dump-sql

### Helpers
bin-console:
	@${docker-compose-php-cli} bin/local-console -e ${env} ${c}

bin-console-exec:
	@${docker-compose} exec ${EXEC_TTY} php-cli bin/local-console -e ${env} ${c}

run-in-php-cli: # make run-in-php-cli "php -i" | grep apc
	@${docker-compose-php-cli} $(call args)

exec-in-php-cli: # make exec-in-php-cli "php -i" | grep apc
	@${docker-compose} exec ${EXEC_TTY} php-cli $(call args)

exec: # make exec "php-cli php -i" | grep apc
	@${docker-compose} exec ${EXEC_TTY} $(call args)

bash:
	@${docker-compose} exec ${EXEC_TTY} php-cli bash;

# https://stackoverflow.com/questions/54090238/stop-executing-makefile
# make: *** No rule to make target `my'.  Stop.
%:
	@true
