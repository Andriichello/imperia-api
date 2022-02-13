eof:
	find //var/www/imperia-api/resources/docker/scripts/ -type f -print0 | xargs -0 dos2unix --

init:
	docker-compose up --no-recreate --detach \
 	&& docker exec -it imperia_api dos2unix //var/www/imperia-api/resources/docker/scripts/init.sh \
 	&& docker exec -it imperia_api //var/www/imperia-api/resources/docker/scripts/init.sh

start:
	docker-compose up --no-recreate --detach \
 	&& docker exec -it imperia_api dos2unix //var/www/imperia-api/resources/docker/scripts/start.sh \
 	&& docker exec -it imperia_api //var/www/imperia-api/resources/docker/scripts/start.sh

bash:
	docker-compose up --no-recreate --detach \
 	&& docker exec -it imperia_api //bin/bash
