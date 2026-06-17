#!/bin/sh
# Entrypoint for the PRODUCTION image (app / queue / scheduler containers).
# It prepares the application against the *runtime* environment, then hands
# control to the container's command (FrankenPHP, queue:work, schedule:work...).
set -e

echo "[entrypoint] Booting kleverkat-web container..."

# Ensure the public storage symlink exists (idempotent; Filament uploads need it).
php artisan storage:link --force 2>/dev/null || true

# (Re)build framework caches AGAINST THE RUNTIME ENV.
# We cache at start-up rather than at build time so config reflects the real
# environment variables (DB creds, APP_KEY, APP_URL...) of this deployment.
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Filament component/asset cache (no-op if the command is unavailable).
php artisan filament:optimize 2>/dev/null || true

# Migrations are OPT-IN (RUN_MIGRATIONS=true) so that, when scaling to several
# replicas, only one container (the web "app" service) runs them — avoiding races.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
	echo "[entrypoint] Running database migrations..."
	php artisan migrate --force
fi

echo "[entrypoint] Ready. Handing over to: $*"
exec "$@"
