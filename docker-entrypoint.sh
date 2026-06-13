#!/usr/bin/env bash
set -e

PORT="${PORT:-8080}"

# ---------------------------------------------------------------------------
# Generate .env from system environment variables (Render injects these)
# This is necessary because .env is excluded from the Docker build context.
# CodeIgniter reads CI_ENVIRONMENT and other settings from .env at runtime.
# ---------------------------------------------------------------------------
ENV_FILE="/var/www/html/.env"

echo "Generating .env from environment variables..."

# Always start with CI_ENVIRONMENT=production (never trust a committed .env)
{
  echo "CI_ENVIRONMENT=production"

  # Write all relevant env vars that Render/Docker pass in
  for VAR in \
    app__baseURL \
    app__forceGlobalSecureRequests \
    "database__default__hostname" \
    "database__default__database" \
    "database__default__username" \
    "database__default__password" \
    "database__default__port" \
    "database__default__DBDriver" \
    encryption__key \
    RECAPTCHA_SITE_KEY \
    RECAPTCHA_SECRET_KEY \
    FIREBASE_API_KEY \
    FIREBASE_AUTH_DOMAIN \
    FIREBASE_PROJECT_ID \
    FIREBASE_STORAGE_BUCKET \
    FIREBASE_MESSAGING_SENDER_ID \
    FIREBASE_APP_ID \
    FIREBASE_MEASUREMENT_ID \
    OPENROUTER_API_KEY \
    CORS_ORIGIN \
    INERTIA_VERSION; do
    # Also support dot-notation keys passed as-is (e.g. app.baseURL)
    VALUE="${!VAR}"
    if [ -n "$VALUE" ]; then
      echo "${VAR}=${VALUE}"
    fi
  done

  # Write dot-notation vars that Render passes with dot in name
  # Render replaces dots with underscores internally, so map them back
  [ -n "${app_baseURL}" ]                    && echo "app.baseURL=${app_baseURL}"
  [ -n "${app_forceGlobalSecureRequests}" ]  && echo "app.forceGlobalSecureRequests=${app_forceGlobalSecureRequests}"
  [ -n "${database_default_hostname}" ]      && echo "database.default.hostname=${database_default_hostname}"
  [ -n "${database_default_database}" ]      && echo "database.default.database=${database_default_database}"
  [ -n "${database_default_username}" ]      && echo "database.default.username=${database_default_username}"
  [ -n "${database_default_password}" ]      && echo "database.default.password=${database_default_password}"
  [ -n "${database_default_port}" ]          && echo "database.default.port=${database_default_port}"
  [ -n "${encryption_key}" ]                 && echo "encryption.key=${encryption_key}"

} > "${ENV_FILE}"

chown www-data:www-data "${ENV_FILE}" 2>/dev/null || true
chmod 640 "${ENV_FILE}" 2>/dev/null || true

echo "Generated .env (CI_ENVIRONMENT=production enforced)"

# ---------------------------------------------------------------------------

if grep -q '^Listen ' /etc/apache2/ports.conf; then
    sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
else
    echo "Listen ${PORT}" >> /etc/apache2/ports.conf
fi

sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

# Run database migrations on startup
echo "Running database migrations..."
php spark migrate --force || echo "Warning: Migrations failed or already up to date"

exec apache2-foreground
