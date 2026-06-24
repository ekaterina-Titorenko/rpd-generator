#!/usr/bin/env bash

set -e

cd "$(dirname "$0")"

BRANCH="${DEPLOY_BRANCH:-main}"
COMPOSE="docker-compose -f docker-compose.prod.yml"

echo "Pull latest code"
git fetch origin
git reset --hard "origin/${BRANCH}"

echo "Build new images"
DOCKER_BUILDKIT=0 COMPOSE_DOCKER_CLI_BUILD=0 $COMPOSE build app

echo "Remove old containers"
$COMPOSE down --remove-orphans || true
docker rm -f rpd_app rpd_nginx rpd_meilisearch 2>/dev/null || true

echo "Start new containers"
$COMPOSE up -d --force-recreate

echo "Prepare Laravel"
$COMPOSE exec -T app php artisan optimize:clear
$COMPOSE exec -T app php artisan migrate --force
$COMPOSE exec -T app php artisan scout:sync-index-settings || true
$COMPOSE exec -T app php artisan scout:import "App\\Models\\RpdProgram" || true
$COMPOSE exec -T app php artisan config:cache
$COMPOSE exec -T app php artisan route:cache
$COMPOSE exec -T app php artisan view:cache

echo "Clean dangling Docker images"
docker image prune -f || true

echo "Deployment finished"