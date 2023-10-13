# Exec sh on php container
init:
	docker-compose -f .docker/docker-compose.yml run php composer install;

analyse:
	docker-compose -f .docker/docker-compose.yml run php bin/watchdog analyse test