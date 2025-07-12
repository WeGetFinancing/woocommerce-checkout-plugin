# Check if compose.yml exists, if not throw an error
ifeq (,$(wildcard compose.yaml))
$(error compose.yml file not found! Please ensure compose.yml exists in the project root.)
endif


# Base docker-compose command
CMD_DC_COMPOSE   = docker compose -f compose.yaml

.PHONY: ps
ps:
	@$(CMD_DC_COMPOSE) ps

.PHONY: down-v
down-v:
	@$(CMD_DC_COMPOSE) down -v --remove-orphans
	sudo rm -rf var/wp

.PHONY: down
down:
	@$(CMD_DC_COMPOSE) down --remove-orphans

.PHONY: up-d
up-d:
	@$(CMD_DC_COMPOSE) up -d
