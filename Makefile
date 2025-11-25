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

.PHONY: deploy-svn
deploy-svn:
	@if [ -z "$${COMMIT_MESSAGE:-}" ] || [ -z "$${TAG_MESSAGE:-}" ] || [ -z "$${TAG_VERSION:-}" ]; then \
		echo "Usage:"; \
		echo "  COMMIT_MESSAGE=\"Commit message for trunk\" TAG_MESSAGE=\"Commit message for tag\" TAG_VERSION=\"1.14.0\" make deploy-svn"; \
		echo; \
		echo "Description:"; \
		echo "  COMMIT_MESSAGE  - Message for the SVN commit of trunk/"; \
		echo "  TAG_MESSAGE     - Message for the SVN commit of the tag (tags/\$${TAG_VERSION})"; \
		echo "  TAG_VERSION     - Version used for the tag directory under tags/ (e.g. 1.14.0)"; \
		exit 1; \
	fi
	@./deploy.sh
	@cd "$${SVN_FOLDER}/wegetfinancing-payment-gateway/" && \
		svn add trunk/* --force && \
		svn status | grep '^!' | awk '{print $$2}' | xargs svn remove && \
		svn ci trunk -m "$${COMMIT_MESSAGE}" --username wegetfinancing && \
		svn cp trunk "tags/$${TAG_VERSION}" && \
		svn ci "tags/$${TAG_VERSION}" -m "$${TAG_MESSAGE}" --username wegetfinancing
