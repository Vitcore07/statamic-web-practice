#!/bin/bash
set -e

# Rebuild necessary containers
docker compose -f compose.production.yaml build server queue scheduler

# Ask if the user wants to restart the containers
read -p "Do you want to restart the containers? (y/n) " -n 1 -r
echo    # move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Restarting containers..."
    docker compose -f compose.production.yaml up -d
else
    echo "Restart cancelled."
fi