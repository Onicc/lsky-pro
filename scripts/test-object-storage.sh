#!/usr/bin/env bash
set -Eeuo pipefail

if [[ $# != 1 ]]; then
    echo "Usage: $0 DEVELOPMENT_IMAGE" >&2
    exit 2
fi

image="$1"
work=$(mktemp -d)
network="lsky-object-store-$$-${RANDOM}"
container="$network-minio"

cleanup() {
    local status=$?
    trap - EXIT
    if [[ $status != 0 ]]; then docker logs "$container" >&2 || true; fi
    docker rm --force --volumes "$container" >/dev/null 2>&1 || true
    docker network rm "$network" >/dev/null 2>&1 || true
    rm -r -- "$work"
    exit "$status"
}
trap cleanup EXIT

# The upstream release binary is used only by this isolated test fixture.
if [[ -n "${LSKY_TEST_MINIO_BINARY:-}" ]]; then
    cp -- "$LSKY_TEST_MINIO_BINARY" "$work/minio"
else
    curl --fail --location --silent --show-error --retry 3 --max-time 120 \
        https://github.com/minio/minio/releases/download/RELEASE.2025-09-07T16-13-09Z/minio.linux-amd64.RELEASE.2025-09-07T16-13-09Z \
        -o "$work/minio"
fi
printf '%s  %s\n' 7c5bd8512c6e966455b1d198209358b2d191c77a83ab377c4073281065fb855f "$work/minio" | sha256sum --check --quiet
chmod 0755 "$work/minio"

access_key=lsky-fixture
secret_key=$(openssl rand -hex 24)
docker network create --internal "$network" >/dev/null
docker run -d --name "$container" --network "$network" --network-alias object-storage \
    --user 33:33 --read-only --cap-drop ALL \
    --tmpfs /data:uid=33,gid=33,mode=0750,size=64m \
    --tmpfs /tmp:mode=1777,size=64m \
    --mount "type=bind,source=$work/minio,target=/tmp/minio,readonly" \
    -e "MINIO_ROOT_USER=$access_key" -e "MINIO_ROOT_PASSWORD=$secret_key" \
    --entrypoint /tmp/minio "$image" server /data >/dev/null

ready=false
for _ in {1..30}; do
    if docker exec "$container" curl --silent --fail --max-time 2 http://127.0.0.1:9000/minio/health/ready; then
        ready=true
        break
    fi
    sleep 1
done
[[ "$ready" == true ]]

docker run --rm --network "$network" \
    -e LSKY_TEST_S3_ENDPOINT=http://object-storage:9000 \
    -e "LSKY_TEST_S3_ACCESS_KEY=$access_key" -e "LSKY_TEST_S3_SECRET_KEY=$secret_key" \
    "$image" php vendor/bin/phpunit tests/Integration/ObjectStorageTest.php
