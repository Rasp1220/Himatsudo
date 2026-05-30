#!/usr/bin/env bash
set -e

ROOT="$(cd "$(dirname "$0")/.." && pwd)"

echo "Starting BearSunday app on http://localhost:8080 ..."
echo "Starting CMS dev server on http://localhost:5173 ..."
echo "(Press Ctrl+C to stop all)"

trap 'kill 0' SIGINT SIGTERM

php -S localhost:8080 -t "$ROOT/public" &
(cd "$ROOT/cms" && npm run dev) &

wait
