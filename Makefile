init:
	docker-compose up --no-recreate --detach \
 	&& docker exec -it imperia_api //var/www/imperia-api/resources/docker/scripts/init.sh

start:
	docker-compose up --no-recreate --detach \
 	&& docker exec -it imperia_api //var/www/imperia-api/resources/docker/scripts/start.sh

bash:
	docker-compose up --no-recreate --detach \
 	&& docker exec -it imperia_api //bin/bash
