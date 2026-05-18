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

# Rebuild necessary containers
docker compose -f "$DOCKER_COMPOSE_FILE" build server queue scheduler

# Ask if the user wants to restart the containers
read -p "Do you want to restart the containers? (y/n) " -n 1 -r
echo    # move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Restarting containers..."
    docker compose -f "$DOCKER_COMPOSE_FILE" up -d
else
    echo "Restart cancelled."
fi