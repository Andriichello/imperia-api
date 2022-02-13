## `imperia-api`
There are multiple steps to make the application running locally via docker. By default, docker container is mapped to `localhost:8080`.

- Have docker installed and running.
- Make sure you have `make` command available. In Windows, you can install it with `choco install make` command (run cmd as admin).
- Clone the project and checkout `master` branch.
- Run `make init` inside the project root directory. It will build the docker image and execute `init.sh` script in `imperia_api` container.
- Run `make start` in order to start `imperia_api` container for development.
- Run `make bash` in order to connect to the `imperia_api` container's bash.

Swagger is used for api documentation, and it generates OpenAPI specification file, which can be accessed via `/openapi`. To see the UI version of the api documentation visit `/api/docs`. 
