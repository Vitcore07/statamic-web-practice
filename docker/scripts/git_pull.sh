!#/bin/bash

# if there are changes, ask the user if they want to pull the latest changes
if [[ $(sudo -u git-deploy git status --porcelain) ]]; then
    read -p "There are uncommitted changes. Do you want to pull the latest changes? (y/n) " -n 1 -r
    echo    # move to a new line
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Pulling latest changes..."
    else
        echo "Pull cancelled."
        exit 1
    fi
fi

# Fetch and pull the latest changes
sudo -u git-deploy git fetch
sudo -u git-deploy git pull
