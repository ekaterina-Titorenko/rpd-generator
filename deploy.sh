#!/usr/bin/env bash

set -e

cd "$(dirname "$0")"

BRANCH="${DEPLOY_BRANCH:-main}"

echo "Pull latest code"
git fetch origin
git reset --hard "origin/${BRANCH}"

echo "Build app image"
docker compose -f docker-compose.prod.yml build app

echo "Start containers"
docker compose -f docker-compose.prod.yml up -d

echo "Prepare Laravel"
docker compose -f docker-compose.prod.yml exec -T app php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan scout:sync-index-settings || true
docker compose -f docker-compose.prod.yml exec -T app php artisan scout:import "App\\Models\\RpdProgram" || true
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache

echo "Deployment finished"