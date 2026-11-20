#!/usr/bin/env bash
set -Eeuo pipefail

if (( $# < 1 || $# > 2 )); then
    echo "Usage: $0 IMAGE [sqlite|mysql|pgsql]" >&2
    exit 2
fi

repo_dir=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
cd "$repo_dir"
export COMPOSE_PROJECT_NAME="lsky-smoke-$$-${RANDOM}"
export LSKY_IMAGE="$1"
export LSKY_PORT=0
export LSKY_LISTEN_ADDRESS=127.0.0.1
export APP_KEY="base64:$(openssl rand -base64 32)"
export APP_URL=http://127.0.0.1
export SESSION_SECURE_COOKIE=false
export DB_CONNECTION="${2:-sqlite}"
export DB_HOST=lsky-test-database
export DB_USERNAME=lsky
export DB_PASSWORD="$(openssl rand -hex 24)"
database_container="${COMPOSE_PROJECT_NAME}-database"
case "$DB_CONNECTION" in
    sqlite) export DB_DATABASE=/var/www/html/storage/app/lsky.sqlite ;;
    mysql) export DB_DATABASE=lsky DB_PORT=3306 ;;
    pgsql) export DB_DATABASE=lsky DB_PORT=5432 ;;
    *) echo "Unsupported smoke-test database: $DB_CONNECTION" >&2; exit 2 ;;
esac
export LSKY_ADMIN_EMAIL=smoke-admin@example.test
export LSKY_ADMIN_PASSWORD="$(openssl rand -hex 24)"

work=$(mktemp -d)
compose=(docker compose --env-file /dev/null -f compose.yaml)

cleanup() {
    local status=$?
    trap - EXIT
    if [[ $status != 0 ]]; then
        "${compose[@]}" logs --no-color app scheduler >&2 || true
        if [[ "$DB_CONNECTION" != sqlite ]]; then
            docker logs "$database_container" >&2 || true
        fi
    fi
    if [[ "$DB_CONNECTION" != sqlite ]]; then
        docker rm --force --volumes "$database_container" >/dev/null 2>&1 || true
    fi
    "${compose[@]}" down --volumes --remove-orphans >/dev/null 2>&1 || true
    rm -r -- "$work"
    exit "$status"
}
trap cleanup EXIT

wait_for_status() {
    local expected="$1"
    local code
    for _ in {1..45}; do
        code=$(curl --silent --max-time 2 --output /dev/null --write-out '%{http_code}' "$APP_URL/up") || true
        if [[ "$code" == "$expected" ]]; then
            return 0
        fi
        sleep 1
    done
    printf 'Readiness expected HTTP %s, got %s\n' "$expected" "$code" >&2
    return 1
}

start_database() {
    local network="${COMPOSE_PROJECT_NAME}_default"
    case "$DB_CONNECTION" in
        sqlite) return ;;
        mysql)
            local root_password
            root_password="$(openssl rand -hex 24)"
            docker run -d --name "$database_container" --network "$network" --network-alias "$DB_HOST" \
                --tmpfs /var/lib/mysql -e "MYSQL_ROOT_PASSWORD=$root_password" -e "MYSQL_DATABASE=$DB_DATABASE" \
                -e "MYSQL_USER=$DB_USERNAME" -e "MYSQL_PASSWORD=$DB_PASSWORD" \
                mysql:8.4@sha256:a81d043930e51745522bd5c430961cf8286f0a23f0a9a2a6e0108f7ea71b0902 >/dev/null
            ;;
        pgsql)
            docker run -d --name "$database_container" --network "$network" --network-alias "$DB_HOST" \
                --tmpfs /var/lib/postgresql/data -e "POSTGRES_DB=$DB_DATABASE" \
                -e "POSTGRES_USER=$DB_USERNAME" -e "POSTGRES_PASSWORD=$DB_PASSWORD" \
                postgres:16-alpine@sha256:1d5b02d3a9941fbd52437db4107f8c52d6297dac553ca1e5c299ebb3756b4857 >/dev/null
            ;;
    esac
    for _ in {1..60}; do
        if "${compose[@]}" exec -T app php -r '
            $connection = getenv("DB_CONNECTION");
            $host = getenv("DB_HOST");
            $port = getenv("DB_PORT");
            $database = getenv("DB_DATABASE");
            try {
                new PDO("$connection:host=$host;port=$port;dbname=$database", getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
            } catch (PDOException) {
                exit(1);
            }
        '; then
            return 0
        fi
        sleep 1
    done
    echo "Database did not become reachable: $DB_CONNECTION" >&2
    return 1
}

"${compose[@]}" up -d --no-build --pull never app
bind_address=$("${compose[@]}" port app 8080)
export LSKY_PORT="${bind_address##*:}"
export APP_URL="http://127.0.0.1:${LSKY_PORT}"
wait_for_status 503

start_database
"${compose[@]}" run --rm -T --no-deps -e LSKY_ADMIN_EMAIL -e LSKY_ADMIN_PASSWORD \
    app php artisan lsky:install --no-interaction
"${compose[@]}" up -d --no-build --pull never --force-recreate app
wait_for_status 200

curl --fail --silent --show-error --max-time 10 "$APP_URL/login" -o "$work/login.html"
"${compose[@]}" exec -T app cat public/build/manifest.json > "$work/manifest.json"
for entry in app.css app.js; do
    asset=$(jq -er --arg entry "$entry" '.[$entry].file' "$work/manifest.json")
    grep -Fq "/build/$asset" "$work/login.html"
    curl --fail --silent --show-error --max-time 10 "$APP_URL/build/$asset" >/dev/null
done
for protected_path in /.env /storage/app/lsky.sqlite; do
    status=$(curl --silent --max-time 5 --output /dev/null --write-out '%{http_code}' "$APP_URL$protected_path")
    [[ "$status" == 404 ]]
done

jq -n --arg email "$LSKY_ADMIN_EMAIL" --arg password "$LSKY_ADMIN_PASSWORD" \
    '{email: $email, password: $password}' > "$work/login.json"
token=$(curl --fail-with-body --silent --show-error --max-time 15 \
    --header 'Accept: application/json' --header 'Content-Type: application/json' \
    --data-binary "@$work/login.json" "$APP_URL/api/v1/tokens" \
    | jq -er 'select(.status == true).data.token')

"${compose[@]}" exec -T app php -r '
    require "vendor/autoload.php";
    $app = require "bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $group = App\Models\Group::query()->where("is_default", true)->firstOrFail();
    $group->configs = $group->configs->merge([App\Enums\GroupConfigKey::FileNamingRule => "{filename}"]);
    $group->save();
'

make_png() {
    "${compose[@]}" exec -T app php -r '
        $image = imagecreatetruecolor(40, 40);
        imagefill($image, 0, 0, (int) $argv[1]);
        imagepng($image);
    ' "$1"
}

upload_png() {
    curl --fail-with-body --silent --show-error --max-time 20 \
        --header 'Accept: application/json' --header "Authorization: Bearer $token" \
        --form "permission=${2:-2}" \
        --form "file=@$1;filename=photo.png;type=image/png" "$APP_URL/api/v1/upload" \
        | jq -er 'select(.status == true).data.links.url'
}

check_png() {
    local url="$1"
    local expected="$2"
    [[ "$url" == "$APP_URL/"* ]]
    curl --fail --silent --show-error --max-time 10 "$url" -o "$work/download.png"
    "${compose[@]}" exec -T app php -r '
        $image = imagecreatefromstring(stream_get_contents(STDIN));
        if ($image === false || imagecolorat($image, 0, 0) !== (int) $argv[1]) {
            exit(1);
        }
    ' "$expected" < "$work/download.png"
}

make_png 255 > "$work/first.png"
make_png 16711680 > "$work/second.png"
first_url=$(upload_png "$work/first.png")
second_url=$(upload_png "$work/second.png")
[[ "$first_url" != "$second_url" ]]
check_png "$first_url" 255
check_png "$second_url" 16711680
private_url=$(upload_png "$work/first.png" 0)
private_status=$(curl --silent --max-time 10 --output /dev/null --write-out '%{http_code}' "$private_url")
[[ "$private_status" == 404 ]]

"${compose[@]}" up -d --wait --wait-timeout 50 --no-build --pull never scheduler
"${compose[@]}" exec -T scheduler php artisan schedule:list --no-ansi > "$work/schedule"
grep -q 'lsky:media:prune' "$work/schedule"

"${compose[@]}" restart app
wait_for_status 200
check_png "$first_url" 255
check_png "$second_url" 16711680
printf 'Container smoke passed for %s (%s)\n' "$LSKY_IMAGE" "$DB_CONNECTION"
