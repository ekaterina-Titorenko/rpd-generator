#!/usr/bin/env bash

set -e

cd "$(dirname "$0")"

BRANCH="${DEPLOY_BRANCH:-main}"
COMPOSE="docker-compose -f docker-compose.prod.yml"

echo "Pull latest code"
git fetch origin
git reset --hard "origin/${BRANCH}"

echo "Build images"
$COMPOSE build

echo "Start containers"
$COMPOSE up -d

echo "Prepare Laravel"
$COMPOSE exec -T app php artisan optimize:clear
$COMPOSE exec -T app php artisan migrate --force
$COMPOSE exec -T app php artisan scout:sync-index-settings || true
$COMPOSE exec -T app php artisan scout:import "App\\Models\\RpdProgram" || true
$COMPOSE exec -T app php artisan config:cache
$COMPOSE exec -T app php artisan route:cache
$COMPOSE exec -T app php artisan view:cache

echo "Deployment finished"