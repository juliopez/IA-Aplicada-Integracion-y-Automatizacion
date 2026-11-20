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
| V07 | Sistemas empresariales | `sudo docker compose ps; bash probar.sh` (en `infraestructura-empresa/`) | Dos APIs, MySQL y operaciones verificadas; **pendiente hasta ejecutar** |
| V08 | Ollama | `ollama --version` | Versión disponible; inferencia pendiente si no autorizada |
| V09 | Node | `node --version; npm --version` | Versiones disponibles |
| V10 | n8n | `n8n --version` | Versión disponible si se instaló |
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

**n8n no inicia**  
```bash
node --version
n8n --version
sudo ss -lntp | grep ':5678' || true
```
Verificar compatibilidad Node/n8n y memoria. No publicar el puerto para resolver el problema.

**MySQL no inicia**  
```bash
sudo systemctl status mysql --no-pager
sudo journalctl -u mysql -n 40 --no-pager
```

## Reanudación en otra sesión

1. Abrir laboratorio AWS Academy y comprobar que el crédito sigue disponible.
2. Iniciar **la misma** EC2 (`Start`, nunca `Launch` de otra).
3. Consultar IP pública actual y actualizar `~/.ssh/config`.
4. Conectarse y verificar `df -h /` y archivos `~/ia-aplicada`.
5. Iniciar **solo** lo necesario: en `infraestructura-empresa/`, `sudo docker compose start` (o `sudo docker compose up -d --build` en el primer despliegue); para Ollama, `sudo systemctl start ollama`; para n8n, iniciar la sesión según la guía.
6. Al terminar, `sudo docker compose stop` desde la carpeta del proyecto, detener Ollama/n8n si siguen activos y luego EC2; confirmar `Stopped`.
