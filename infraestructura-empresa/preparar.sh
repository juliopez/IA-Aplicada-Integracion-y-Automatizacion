#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
if [[ -e .env ]]; then echo "Ya existe .env; no se sobrescribe"; exit 0; fi
umask 077
root="$(openssl rand -hex 24)"
mesa="$(openssl rand -hex 24)"
academico="$(openssl rand -hex 24)"
token="$(openssl rand -hex 24)"
printf 'MYSQL_ROOT_PASSWORD=%s\nMESA_DB_PASSWORD=%s\nACADEMICO_DB_PASSWORD=%s\nLAB_API_KEY=%s\n' "$root" "$mesa" "$academico" "$token" > .env
sed -e "s/REEMPLAZAR_MESA/$mesa/g" -e "s/REEMPLAZAR_ACADEMICO/$academico/g" db/init.sql > db/init.local.sql
echo "Preparación completa: .env y db/init.local.sql privados."
