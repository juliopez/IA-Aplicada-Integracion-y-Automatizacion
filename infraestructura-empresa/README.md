# Empresa simulada — SIGS

Dos sistemas empresariales **independientes** para las prácticas de IA. Los estudiantes desarrollarán el agente, la integración Ollama/PHP y n8n **fuera de Docker**, consumiendo sus APIs HTTP.

- **Mesa de ayuda**: http://127.0.0.1:8081 — tickets, estados, prioridades y auditoría.
- **Sistema académico**: http://127.0.0.1:8082 — usuarios ficticios, bloqueo de cuentas, estado de servicios y auditoría.
- **MySQL**: 127.0.0.1:3307 — dos bases de datos separadas. Nunca exponer a Internet.

## Preparación en EC2 Ubuntu 24.04

Requisitos: Docker Engine + plugin Docker Compose, Git y OpenSSL. La configuración de referencia de EC2 está documentada en la [arquitectura de Semana 0](../semana-00-laboratorio/arquitectura.md). En la carpeta del proyecto:

```bash
bash preparar.sh
docker compose config --quiet
docker compose up -d --build
docker compose ps
curl -fsS http://127.0.0.1:8081/api/health
curl -fsS http://127.0.0.1:8082/api/health
```

El script genera contraseñas y token aleatorios en `.env`, además de `db/init.local.sql`. Ambos están excluidos de Git. `.env` permanece privado (600); `db/init.local.sql` debe ser legible por el usuario MySQL del contenedor (644). Utilizar exclusivamente datos y contraseñas de laboratorio, sin otros usuarios locales no confiables en EC2. La inicialización SQL ocurre **solo en el primer arranque del volumen**.

## Probar APIs

```bash
curl -fsS http://127.0.0.1:8081/api/tickets
curl -fsS http://127.0.0.1:8082/api/usuarios
curl -fsS http://127.0.0.1:8082/api/servicios
TOKEN="$(sed -n 's/^LAB_API_KEY=//p' .env)"
curl -fsS -X POST http://127.0.0.1:8081/api/tickets -H "X-API-Key: $TOKEN" -H 'Content-Type: application/json' -d '{"asunto":"Prueba API","descripcion":"Caso sintetico","solicitante":"test@ejemplo.test","categoria":"otro","prioridad":"media"}'
curl -fsS -X PATCH http://127.0.0.1:8082/api/usuarios/1 -H "X-API-Key: $TOKEN" -H 'Content-Type: application/json' -d '{"cuenta_estado":"activa"}'
```

Para abrir ambos frontend desde el computador personal, crear túnel SSH a EC2:

```bash
ssh -N -i ~/.ssh/ia-aplicada.pem -L 8081:127.0.0.1:8081 -L 8082:127.0.0.1:8082 ubuntu@IP_EC2
```

Abrir http://localhost:8081 y http://localhost:8082. Los puertos se publican **solo en loopback**; no abrirlos en el security group.

## Contrato de endpoints

| Sistema | Método | Endpoint | Acción |
|---|---|---|---|
| Mesa | GET | /api/health | Salud |
| Mesa | GET | /api/tickets | Listar |
| Mesa | GET | /api/tickets/{id} | Detalle |
| Mesa | POST | /api/tickets | Crear ticket |
| Mesa | PATCH | /api/tickets/{id} | Actualizar estado, prioridad o categoría |
| Mesa | GET | /api/auditoria | Historial |
| Académico | GET | /api/health | Salud |
| Académico | GET | /api/usuarios | Usuarios |
| Académico | GET | /api/servicios | Servicios |
| Académico | PATCH | /api/usuarios/{id} | Bloquear o desbloquear cuenta |
| Académico | GET | /api/auditoria | Historial |

Las escrituras requieren cabecera `X-API-Key`; se registran en auditoría. El token compartido es un **control básico de laboratorio**, no autenticación empresarial de producción. No utilizar datos reales.

## Incidencia conocida: Permission denied al inicializar MySQL

Si `sudo docker compose logs --tail=60 db` muestra `01-init.sql: Permission denied`, actualizar el repositorio y ejecutar `bash preparar.sh` para ajustar los permisos **sin cambiar las contraseñas**. El primer arranque fallido puede haber creado parcialmente el volumen MySQL; no ejecutar `down -v` sin verificar que no contiene datos que deban conservarse. Solicitar instrucciones antes de reinicializar.

## Detener y recuperar

```bash
docker compose logs --tail=50
docker compose stop
docker compose start
docker compose down
```

`down` conserva el volumen; **no ejecutar `down -v`** salvo que se desee borrar todos los datos. Detener EC2 desde la consola AWS al terminar. Los sistemas no están destinados a exposición pública.

**Resultado esperado:** `bash probar.sh` finaliza satisfactoriamente y las dos interfaces son accesibles mediante túnel SSH. Esto comprueba los sistemas empresariales, no la integración de los agentes de IA. Los resultados de la prueba docente se conservan en la [bitácora técnica](../docs/bitacora-tecnica-semana-00.md).
