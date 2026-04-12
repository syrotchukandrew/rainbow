#!/usr/bin/env bash
set -euo pipefail

# Rainbow — install / update helper
# Requires: Docker, Docker Compose, Node.js + npm (on host, for asset builds)

show_menu() {
    echo ""
    echo "Rainbow — what would you like to do?"
    echo ""
    echo "  1) Fresh install  (build containers, install deps, migrate DB, load dev fixtures)"
    echo "  2) Update         (rebuild containers, update deps, run new migrations)"
    echo "  3) Reset database (drop → create → migrate → load dev fixtures)"
    echo "  4) Build assets   (npm install + Webpack Encore production build)"
    echo "  5) Run tests      (php bin/phpunit inside the web container)"
    echo "  6) Exit"
    echo ""
}

wait_for_db() {
    echo "Waiting for MySQL to be ready..."
    local retries=30
    until docker compose exec -T db mysql -uroot -proot -e "SELECT 1" &>/dev/null; do
        retries=$((retries - 1))
        if [ "$retries" -le 0 ]; then
            echo "ERROR: MySQL did not become ready in time." >&2
            exit 1
        fi
        sleep 2
    done
    echo "MySQL is ready."
}

fresh_install() {
    echo "=== Fresh install ==="
    docker compose up -d --build
    wait_for_db
    docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction
    docker compose exec web php bin/console doctrine:fixtures:load \
        --group=Dev --no-interaction --purge-with-truncate
    npm ci
    npm run build
    docker compose exec web php bin/console cache:clear
    echo ""
    echo "Done. App available at http://localhost:8080"
}

update() {
    echo "=== Update ==="
    docker compose up -d --build
    wait_for_db
    docker compose exec web composer install --no-interaction --optimize-autoloader
    docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction
    npm ci
    npm run build
    docker compose exec web php bin/console cache:clear
    echo ""
    echo "Done. App available at http://localhost:8080"
}

reset_db() {
    echo "=== Reset database ==="
    docker compose up -d
    wait_for_db
    docker compose exec web php bin/console doctrine:database:drop --force --if-exists
    docker compose exec web php bin/console doctrine:database:create
    docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction
    docker compose exec web php bin/console doctrine:fixtures:load \
        --group=Dev --no-interaction --purge-with-truncate
    docker compose exec web php bin/console cache:clear
    echo ""
    echo "Database reset complete."
}

build_assets() {
    echo "=== Build assets ==="
    npm ci
    npm run build
    echo ""
    echo "Assets compiled to public/build/"
}

run_tests() {
    echo "=== Run tests ==="
    docker compose exec web php bin/phpunit -c phpunit.xml.dist
}

show_menu
read -rp "Choice [1-6]: " choice

case "$choice" in
    1) fresh_install ;;
    2) update ;;
    3) reset_db ;;
    4) build_assets ;;
    5) run_tests ;;
    6) exit 0 ;;
    *) echo "Invalid choice." && exit 1 ;;
esac
