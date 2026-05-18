#!/bin/bash
set -e

sudo -u git-deploy git add .
read -p "Enter commit message: " commit_message
sudo -u git-deploy git commit -m "$commit_message"
sudo -u git-deploy git push
