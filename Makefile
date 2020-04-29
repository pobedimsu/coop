up: docker-up composer-install db-schema-update # db-migrations
upb: docker-build docker-up
down: docker-down
build: docker-build
restart: docker-down docker-up
restart-build: docker-down docker-build docker-up
init: docker-down-clear  docker-pull docker-build docker-up composer-install db-schema-drop db-schema-update app-init

app-init:
	docker-compose run --rm php php bin/console app:init

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build

clear:
	docker run --rm -v ${PWD}:/app --workdir=/app alpine rm -f .ready

cli:
	docker-compose run php bin/console ${c}

composer-install:
	docker-compose run php composer install

db-schema-drop:
	docker-compose run --rm php php bin/console doctrine:schema:drop --force --full-database

db-schema-update:
	docker-compose run --rm php php bin/console doctrine:schema:update --force

db-du:
	docker-compose run --rm php php bin/console db:du

db-re:
	docker-compose run --rm php php bin/console db:re

wait-db:
	until docker-compose exec -T db pg_isready --timeout=0 --dbname=sonm ; do sleep 1 ; done

#db-migrations:
#	docker-compose run --rm php php bin/console doctrine:migrations:migrate --no-interaction

#fixtures:
#	docker-compose run --rm php php bin/console doctrine:fixtures:load --no-interaction

ready:
	docker run --rm -v ${PWD}:/app --workdir=/app alpine touch .ready
