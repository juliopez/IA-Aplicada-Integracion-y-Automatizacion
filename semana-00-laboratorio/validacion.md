# Validación, evidencias y recuperación

## Pruebas de aceptación

| Código | Prueba | Comando / acción | Resultado esperado |
|---|---|---|---|
| V01 | Sistema | `cat /etc/os-release` | Ubuntu 24.04 LTS |
| V02 | Recursos | `free -h; df -h /` | RAM/disco registrados |
| V03 | Python | `python3 -m venv /tmp/ia-venv` | Entorno creado |
| V04 | Git | `git --version` | Versión informada |
| V05 | Docker | `sudo docker version; sudo docker compose version` | Cliente, daemon y plugin disponibles |
| V06 | PHP | `php -v` | Versión disponible |
| V07 | Sistemas empresariales | `sudo docker compose ps; bash probar.sh` (en `infraestructura-empresa/`) | Dos APIs, MySQL y operaciones verificadas en prueba de referencia EC2 (20-11-2026); repetir por estudiante |
| V07b | Interfaces gráficas | Túnel SSH y navegador local para tickets y académico | Ambas interfaces y registros visibles; comprobado en EC2 de referencia (20-11-2026) |
| V08 | Ollama e inferencia | `ollama --version; ollama show qwen2.5:1.5b` y consulta de texto en `instalacion.md` | Servicio activo, respuesta de texto y capacidad `tools` declarada |
| V08b | Selección real de herramienta | Solicitud a `/api/chat` (comando siguiente) | `message.tool_calls[0].function.name` es `consultar_tickets`; no implica ejecutar la API |
| V09 | Node | `node --version; npm --version` | Versiones disponibles |
| V10 | n8n | `n8n --version; sudo systemctl is-enabled n8n; sudo systemctl is-active n8n` y `curl -I http://127.0.0.1:5678/` | Versión 2.35.7 en referencia; servicio `enabled`, `active`, respuesta HTTP 200 |
| V11 | Seguridad | `sudo ss -lntp` | 8081, 8082 y 3307 solo en loopback; Ollama/n8n sin exposición pública |
| V12 | Presupuesto | AWS Academy/EC2 | Crédito revisado; EC2 detenida al finalizar |

```bash
# Dentro de EC2, tras la instalación:
mkdir -p ~/ia-aplicada/semana-00/evidencias
{
  date -u
  cat /etc/os-release
  uname -m
  python3 --version
  git --version
  php -v | head -n 1
  sudo docker --version
  sudo docker compose version
  node --version
  npm --version
  command -v ollama && ollama --version || true
  command -v n8n && n8n --version || true
  free -h
  df -h /
} > ~/ia-aplicada/semana-00/evidencias/versiones.txt 2>&1
```

**Antes de publicar evidencias**, revisar y eliminar IP, nombres de host identificables, tokens, usuarios personales y cualquier secreto. Las capturas de AWS deben ocultar datos sensibles.

## V08b — Prueba funcional de selección de herramientas (Ollama)

Ejecutar **en la EC2** después de descargar `qwen2.5:1.5b` y comprobar que Ollama esté activo. La herramienta declarada es simulada: esta prueba **no** consulta ni modifica los tickets reales.

```bash
curl -fsS http://127.0.0.1:11434/api/chat \
  -H 'Content-Type: application/json' \
  -d '{
    "model": "qwen2.5:1.5b",
    "stream": false,
    "messages": [
      {"role": "user", "content": "Consulta los tickets registrados utilizando la herramienta consultar_tickets."}
    ],
    "tools": [
      {
        "type": "function",
        "function": {
          "name": "consultar_tickets",
          "description": "Obtiene la lista de tickets de la mesa de ayuda.",
          "parameters": {"type": "object", "properties": {}}
        }
      }
    ],
    "options": {"temperature": 0}
  }' | python3 -m json.tool
```

**Criterio de aprobación:** dentro de `message.tool_calls` debe existir una entrada cuya `function.name` sea `consultar_tickets` y cuyos `arguments` sean `{}`. `content` puede estar vacío: no es fallo si aparece la llamada a herramienta. **No confundir** el objeto `tools` enviado en la petición con el objeto `tool_calls` recibido en la respuesta.

**Evidencia de referencia, 20-11-2026:** primera prueba sin instrucción explícita: respuesta vacía y sin `tool_calls` (no aprobada). Segunda prueba con instrucción explícita y temperatura 0: `tool_calls` con `consultar_tickets` y `arguments: {}` (aprobada). El modelo puede variar su comportamiento entre solicitudes; esta prueba no acredita consistencia estadística ni ejecución de la API empresarial. La ejecución efectiva se evaluará posteriormente al integrar el agente con las aplicaciones Docker.

## Registro de pruebas

Crear `semana-00/evidencias.md` con tabla:

| Prueba | Fecha | Resultado observado | Estado (OK / pendiente / falla) | Acción correctiva |
|---|---|---|---|---|
| V01 | | | | |
| ... | | | | |

Añadir: enlace a commit del proyecto, capturas depuradas de AWS y comprobación final `Stopped`. No inventar resultados: si Ollama no puede inferir por falta de RAM, registrar la limitación y no declarar la prueba exitosa.

## Diagnóstico de fallas frecuentes

**SSH: `Permission denied (publickey)`**  
Comprobar `chmod 600 ~/.ssh/ia-aplicada.pem`, usuario `ubuntu`, llave correcta y regla de entrada SSH limitada a IP actual. Probar desde computador local: `ssh -v -i ~/.ssh/ia-aplicada.pem ubuntu@IP_PUBLICA_EC2`. No pegar trazas completas con IP en repositorios públicos.

**SSH: timeout**  
Verificar estado `Running`, IP pública actual, región y security group. La IP puede cambiar tras `Stop/Start`.

**`apt` bloqueado**  
Verificar si hay otra operación de paquetes activa: `ps aux | grep -E 'apt|dpkg'`. No borrar archivos lock ni matar procesos de actualización sin diagnóstico.

**`No space left on device`**  
Ejecutar `df -h /` y `sudo du -sh /var/cache/apt/archives ~/ia-aplicada 2>/dev/null`. No ampliar EBS sin autorización docente.

**Ollama no responde**  
```bash
sudo systemctl status ollama --no-pager
sudo journalctl -u ollama -n 40 --no-pager
free -h
```
Si falta RAM, detener la prueba y registrar el límite; no cambiar instancia ni descargar modelos mayores por cuenta propia.

**n8n no inicia o queda en `activating (auto-restart)`**  
```bash
node --version
n8n --version
sudo systemctl status n8n --no-pager
sudo journalctl -u n8n -n 50 --no-pager
sudo ss -lntp | grep ':5678' || true
```
Si el registro indica `n8n's port 5678 is already in use`, **detener primero con Ctrl+C la instancia manual de n8n**, si sigue abierta, y después ejecutar `sudo systemctl restart n8n`. Verificar `active (running)` y HTTP 200; no dar por aprobado un estado `activating` ni solo la respuesta HTTP. El aviso sobre `confluenceCloudOAuth2Api` se observó en la referencia sin impedir el arranque final. Si persiste el error, revisar proceso dueño del puerto antes de detenerlo. No publicar el puerto para resolverlo.

**MySQL de Docker no inicia**  
Desde `infraestructura-empresa/`:
```bash
sudo docker compose ps
sudo docker compose logs --tail=60 db
sudo docker compose config --quiet
```
Si aparece `01-init.sql: Permission denied`, comprobar permisos con `ls -l db/init.local.sql` y ejecutar la versión actualizada de `bash preparar.sh`. El SQL debe ser legible para el usuario MySQL dentro del contenedor. **No borrar el volumen automáticamente:** un arranque fallido puede haber dejado una inicialización parcial; verificar antes de cualquier reinicialización.

**Recuperación excepcional de primer despliegue fallido, sin datos de trabajo:** confirmar que `preparar.sh` corrigió los permisos (`-rw-r--r--`), inspeccionar `sudo docker compose ps -a`, `sudo docker volume ls` y, si es necesario, examinar el volumen en modo lectura. **Solo si se ha confirmado que el volumen pertenece exclusivamente a una instalación inicial descartable y no contiene datos que conservar**, reinicializar con:

```bash
sudo docker compose down -v
sudo docker compose up -d --build
sudo docker compose ps
bash probar.sh
```

`down -v` **elimina definitivamente las bases de datos del proyecto**. Nunca utilizarlo como solución general para instalaciones con datos. La prueba de referencia EC2 del 20-11-2026 requirió esta recuperación después de un fallo de permisos; posteriormente MySQL quedó saludable y `probar.sh` fue satisfactorio.

**Túnel SSH: llave inexistente o puerto local ocupado**  
Verificar la ruta real de `.pem` en el computador personal y usarla en `ssh -i`; ejecutar `chmod 600` sobre esa llave. Si `bind [127.0.0.1]:8081: Address already in use`, detener el intento con Ctrl+C y utilizar `-L 18081:127.0.0.1:8081 -L 18082:127.0.0.1:8082` según `aws-ec2.md`. Verificar en el navegador `http://localhost:18081` y `http://localhost:18082`.

## Reanudación en otra sesión

1. Abrir laboratorio AWS Academy y comprobar que el crédito sigue disponible.
2. Iniciar **la misma** EC2 (`Start`, nunca `Launch` de otra).
3. Consultar IP pública actual y actualizar `~/.ssh/config`.
4. Conectarse y verificar `df -h /` y archivos `~/ia-aplicada`.
5. Iniciar **solo** lo necesario: en `infraestructura-empresa/`, `sudo docker compose start` (o `sudo docker compose up -d --build` en el primer despliegue); para Ollama, `sudo systemctl start ollama`; para n8n, `sudo systemctl start n8n` (si no está ya activo por inicio automático).
6. Al terminar, `sudo docker compose stop` desde la carpeta del proyecto, detener Ollama/n8n con `sudo systemctl stop ollama n8n` si siguen activos y luego EC2; confirmar `Stopped`.
