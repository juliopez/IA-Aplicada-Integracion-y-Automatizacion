#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
if [[ -e .env ]]; then
  if [[ -f db/init.local.sql ]]; then
    chmod 644 db/init.local.sql
    echo "Configuración existente conservada; SQL legible para el contenedor MySQL."
    exit 0
  fi
  echo "ERROR: existe .env pero falta db/init.local.sql. No se regeneran contraseñas." >&2
  exit 1
fi
umask 077
root="$(openssl rand -hex 24)"
mesa="$(openssl rand -hex 24)"
academico="$(openssl rand -hex 24)"
token="$(openssl rand -hex 24)"
printf 'MYSQL_ROOT_PASSWORD=%s\nMESA_DB_PASSWORD=%s\nACADEMICO_DB_PASSWORD=%s\nLAB_API_KEY=%s\n' "$root" "$mesa" "$academico" "$token" > .env
sed -e "s/REEMPLAZAR_MESA/$mesa/g" -e "s/REEMPLAZAR_ACADEMICO/$academico/g" db/init.sql > db/init.local.sql
# El entrypoint de MySQL lee este bind mount como usuario no root.
chmod 644 db/init.local.sql
echo "Preparación completa: .env y db/init.local.sql privados."
