# Exec sh on php container
init:
	docker-compose -f .docker/docker-compose.yml run php composer install

up:
	docker-compose -f .docker/docker-compose.yml run php sh

analyse:
	docker-compose -f .docker/docker-compose.yml run php bin/watchdog analyse app tests tests2
