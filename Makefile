# Exec sh on php container
init:
	docker-compose -f .docker/docker-compose.yml run php composer install