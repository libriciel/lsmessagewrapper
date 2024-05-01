DOCKER_COMPOSE=docker compose -f docker-compose.yml

build:
	$(DOCKER_COMPOSE) build

up-log:
	$(DOCKER_COMPOSE) up -V

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down -v

logs:
	$(DOCKER_COMPOSE) logs -f

ps:
	$(DOCKER_COMPOSE) ps

composer-install:
	$(DOCKER_COMPOSE) run --entrypoint="composer install" fpm-lsmessage-wrapper

composer-update:
	$(DOCKER_COMPOSE) run --entrypoint="composer update" fpm-lsmessage-wrapper
