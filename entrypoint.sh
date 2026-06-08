#!/bin/sh
set -eu

PORT="${PORT:-8080}"

echo "Starting PHP server on 0.0.0.0:${PORT}"

exec php -S 0.0.0.0:"$PORT" -t /app