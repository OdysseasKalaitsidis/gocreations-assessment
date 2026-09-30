#!/usr/bin/env bash

set -e

BASE_URL="${BASE_URL:-http://localhost:8088}"

echo "Starting API..."
docker compose up --build -d

echo "Waiting for API..."
for attempt in {1..30}; do
    curl --silent --fail "${BASE_URL}/vehicles" >/dev/null && break
    sleep 2
done

echo
echo "1. GET /vehicles"
curl -i "${BASE_URL}/vehicles"

echo
echo "2. GET /vehicles with filter and sorting"
curl -i "${BASE_URL}/vehicles?price_min=100&sort=price_asc"

echo
echo "3. POST /vehicles"
created_vehicle="$(curl --silent --show-error \
    -X POST "${BASE_URL}/vehicles" \
    -H "Content-Type: application/json" \
    --data '{"model_name":"API Test Vehicle","type_id":1,"doors":5,"transmission":"automatic","fuel":"hybrid","price":150}')"

echo "$created_vehicle"

vehicle_id="$(printf '%s' "$created_vehicle" \
    | sed -n 's/.*"id":[[:space:]]*\([0-9][0-9]*\).*/\1/p')"

if [[ -z "$vehicle_id" ]]; then
    echo "Could not read the new vehicle ID."
    exit 1
fi

echo
echo "4. PUT /vehicles/${vehicle_id}"
curl -i -X PUT "${BASE_URL}/vehicles/${vehicle_id}" \
    -H "Content-Type: application/json" \
    --data '{"model_name":"Updated API Test Vehicle","type_id":1,"doors":5,"transmission":"automatic","fuel":"electric","price":175}'

echo
echo "5. DELETE /vehicles/${vehicle_id}"
curl -i -X DELETE "${BASE_URL}/vehicles/${vehicle_id}"

echo
echo "6. Invalid POST (expected 422)"
curl -i -X POST "${BASE_URL}/vehicles" \
    -H "Content-Type: application/json" \
    --data '{"model_name":"","type_id":0,"doors":256,"transmission":"invalid","fuel":"invalid","price":-1}'

echo
echo "Finished."
