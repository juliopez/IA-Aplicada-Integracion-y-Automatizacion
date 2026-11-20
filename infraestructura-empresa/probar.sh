#!/usr/bin/env bash
# Prueba de integración: ejecutar en EC2, después de docker compose up -d --build.
set -euo pipefail
cd "$(dirname "$0")"
command -v jq >/dev/null || { echo "Instalar jq"; exit 1; }
[[ -f .env ]] || { echo "Falta .env; ejecutar bash preparar.sh"; exit 1; }
TOKEN="$(sed -n 's/^LAB_API_KEY=//p' .env)"
[[ -n "$TOKEN" ]] || { echo "Token ausente"; exit 1; }
echo "[1/7] Salud de ambos servicios"
curl -fsS http://127.0.0.1:8081/api/health | jq -e '.estado=="ok"' >/dev/null
curl -fsS http://127.0.0.1:8082/api/health | jq -e '.estado=="ok"' >/dev/null
echo "[2/7] Datos iniciales"
curl -fsS http://127.0.0.1:8081/api/tickets | jq -e '.tickets|length>=3' >/dev/null
curl -fsS http://127.0.0.1:8082/api/usuarios | jq -e '.usuarios|length>=3' >/dev/null
curl -fsS http://127.0.0.1:8082/api/servicios | jq -e '.servicios|length>=3' >/dev/null
echo "[3/7] Rechazar escrituras sin token"
code="$(curl -s -o /dev/null -w '%{http_code}' -X POST http://127.0.0.1:8081/api/tickets -H 'Content-Type: application/json' -d '{}')"
[[ "$code" == 403 ]] || { echo "Esperado HTTP 403, obtenido $code"; exit 1; }
echo "[4/7] Crear ticket por API"
result="$(curl -fsS -X POST http://127.0.0.1:8081/api/tickets -H "X-API-Key: $TOKEN" -H 'Content-Type: application/json' -d '{"asunto":"Smoke test","descripcion":"Validacion automatica","solicitante":"test@ejemplo.test","categoria":"otro","prioridad":"baja"}')"
id="$(jq -r '.id' <<<"$result")"
[[ "$id" =~ ^[0-9]+$ ]] || { echo "No se creó ticket"; exit 1; }
echo "[5/7] Actualizar y verificar ticket"
curl -fsS -X PATCH "http://127.0.0.1:8081/api/tickets/$id" -H "X-API-Key: $TOKEN" -H 'Content-Type: application/json' -d '{"estado":"en_proceso"}' | jq -e '.estado=="actualizado"' >/dev/null
curl -fsS "http://127.0.0.1:8081/api/tickets/$id" | jq -e '.ticket.estado=="en_proceso"' >/dev/null
echo "[6/7] Desbloquear cuenta y verificar"
curl -fsS -X PATCH http://127.0.0.1:8082/api/usuarios/1 -H "X-API-Key: $TOKEN" -H 'Content-Type: application/json' -d '{"cuenta_estado":"activa"}' | jq -e '.cuenta_estado=="activa"' >/dev/null
curl -fsS http://127.0.0.1:8082/api/usuarios | jq -e '.usuarios[] | select(.id==1 and .cuenta_estado=="activa")' >/dev/null
echo "[7/7] Verificar auditorías"
curl -fsS http://127.0.0.1:8081/api/auditoria | jq -e '.auditoria|length>=2' >/dev/null
curl -fsS http://127.0.0.1:8082/api/auditoria | jq -e '.auditoria|length>=1' >/dev/null
echo "PRUEBAS OK. Ticket de prueba ID=$id (se conserva como evidencia)."
