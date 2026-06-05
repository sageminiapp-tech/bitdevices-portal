#!/bin/sh
# Entrypoint script to handle PORT environment variable
PORT=${PORT:-8080}
exec php -S 0.0.0.0:$PORT
