#!/bin/bash
set -e

DOCKER_COMPOSE_FILE="compose.production.yaml"

# if the file doesn't exist
if [ ! -f "$DOCKER_COMPOSE_FILE" ]; then
   # try ty check if it is not 2 levels up
    if [ ! -f "../../$DOCKER_COMPOSE_FILE" ]; then
        echo "Docker compose file not found!"
        exit 1
    else
        DOCKER_COMPOSE_FILE="../../$DOCKER_COMPOSE_FILE"
    fi
fi

docker compose -f "$DOCKER_COMPOSE_FILE" up -d