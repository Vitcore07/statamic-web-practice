#!/usr/bin/env bash

set -euo pipefail

if [[ $# -gt 0 ]] && [[ "$1" != "server" ]] && [[ "$1" != "queue" ]] && [[ "$1" != "scheduler" ]]; then
    exec "$@"
fi

role="${CONTAINER_ROLE:-${1:-server}}"

if [[ "${APP_ENV:-production}" == "production" ]] && [[ -z "${APP_KEY:-}" ]]; then
    echo "APP_KEY must be set for production containers." >&2
    exit 1
fi

require_directory() {
    local path="$1"

    if [[ ! -d "${path}" ]]; then
        echo "Required directory is missing: ${path}. Create it during deployment with the correct ownership." >&2
        exit 1
    fi
}

require_writable_directory() {
    local path="$1"

    require_directory "${path}"

    if [[ ! -w "${path}" ]]; then
        echo "Required directory is not writable: ${path}. Fix ownership and permissions during deployment." >&2
        exit 1
    fi
}

require_writable_parent_directory() {
    local path="$1"

    if [[ ! -e "${path}" ]]; then
        echo "Required path is missing: ${path}. Create it during deployment with the correct ownership." >&2
        exit 1
    fi

    local parent
    parent="$(dirname "${path}")"

    if [[ ! -w "${parent}" ]]; then
        echo "Parent directory is not writable for path ${path}: ${parent}. Fix ownership and permissions during deployment." >&2
        exit 1
    fi
}

require_directory bootstrap/cache
require_directory public
require_writable_directory bootstrap/cache
require_writable_directory content
require_writable_directory database
require_writable_directory public/assets
require_writable_directory public/img
require_writable_directory resources/users
require_writable_directory storage
require_writable_directory storage/app/private
require_writable_directory storage/app/public
require_writable_directory storage/framework/cache/data
require_writable_directory storage/framework/sessions
require_writable_directory storage/framework/views
require_writable_directory storage/logs
require_writable_directory users

if [[ "${DB_CONNECTION:-sqlite}" == "sqlite" ]]; then
    sqlite_database="${DB_DATABASE:-database/database.sqlite}"

    if [[ "${sqlite_database}" != ":memory:" ]]; then
        require_writable_parent_directory "${sqlite_database}"
    fi
fi

if [[ "${role}" == "server" ]] && [[ "${RUN_STORAGE_LINK:-1}" == "1" ]] && [[ ! -L public/storage ]] && [[ ! -e public/storage ]]; then
    php artisan storage:link --no-interaction
fi

if [[ "${role}" == "server" ]] && [[ "${RUN_MIGRATIONS:-0}" == "1" ]]; then
    php artisan migrate --force --no-interaction
fi

if [[ "${RUN_OPTIMIZE:-1}" == "1" ]]; then
    php artisan optimize --no-interaction
fi

case "${role}" in
    server)
        exec frankenphp php-server \
            --root "${SERVER_ROOT:-/app/public}" \
            --listen "${SERVER_NAME:-:80}" \
            --access-log
        ;;
    queue)
        queue_args=(
            queue:work
            --verbose
            --no-interaction
            "--sleep=${QUEUE_WORKER_SLEEP:-1}"
            "--tries=${QUEUE_WORKER_TRIES:-3}"
            "--timeout=${QUEUE_WORKER_TIMEOUT:-90}"
            "--max-time=${QUEUE_WORKER_MAX_TIME:-3600}"
        )

        if [[ -n "${QUEUE_WORKER_QUEUE:-}" ]]; then
            queue_args+=("--queue=${QUEUE_WORKER_QUEUE}")
        fi

        exec php artisan "${queue_args[@]}"
        ;;
    scheduler)
        exec php artisan schedule:work --verbose --no-interaction
        ;;
    *)
        echo "Unsupported container role: ${role}" >&2
        exit 1
        ;;
esac
