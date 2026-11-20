# Instalación completa en Ubuntu Server 24.04 LTS

**Dónde ejecutar:** todos los comandos de esta página se ejecutan **dentro de la terminal SSH de EC2**, salvo que se indique lo contrario. No ejecutar scripts de Internet como root sin revisarlos. Se requiere acceso sudo y conexión a repositorios oficiales. El laboratorio utiliza la configuración indicada en la [guía autónoma](guia-paso-a-paso.md).

## A. Diagnóstico, actualización y herramientas base

```bash
set -e
cat /etc/os-release
uname -m
free -h
df -h /
sudo apt update
sudo DEBIAN_FRONTEND=noninteractive apt upgrade -y
sudo apt install -y git python3 python3-pip python3-venv \
  curl wget jq unzip ca-certificates gnupg lsb-release \
  build-essential openssh-client ufw
python3 --version
git --version
curl --version
```

Si la actualización informa que debe reiniciarse el sistema:

```bash
sudo reboot
```

La sesión SSH se cerrará. Esperar a que EC2 esté operativa y reconectar; la IP podría cambiar si se detuvo/inició, pero un reinicio normal no suele reasignarla.

## B. Python y proyecto de prueba

```bash
mkdir -p ~/ia-aplicada/semana-00
cd ~/ia-aplicada/semana-00
python3 -m venv .venv
source .venv/bin/activate
python -m pip install --upgrade pip
python -c 'import sys, json, sqlite3; print(sys.version); print("Python OK")'
deactivate
git config --global init.defaultBranch main
git config --global user.name "Estudiante IA Aplicada"
git config --global user.email "estudiante@example.invalid"
git init ~/ia-aplicada/semana-00
```

**Sustituir** nombre y correo ficticios por los datos que el estudiante utilice para sus commits, si va a publicar código. **No** subir `.venv`; crear `~/ia-aplicada/semana-00/.gitignore`:

```bash
printf '.venv/\n.env\n__pycache__/\n*.pem\n' > ~/ia-aplicada/semana-00/.gitignore
```

## C. PHP para desarrollo en Ubuntu (sin Apache del host)

El estudiante programará su integración PHP/Ollama directamente en Ubuntu. Apache/PHP para los **dos sistemas empresariales** se instala dentro de sus imágenes Docker. **No instalar** `apache2`, `libapache2-mod-php` ni `mysql-server` en Ubuntu.

```bash
sudo apt install -y php-cli php-mysql php-curl php-mbstring php-xml
php --version
php -m | grep -E 'PDO|pdo_mysql|curl'
mkdir -p ~/ia-aplicada/php-prueba
printf '<?php header("Content-Type: text/plain"); echo "PHP OK\\n";' > ~/ia-aplicada/php-prueba/index.php
cd ~/ia-aplicada/php-prueba
php -S 127.0.0.1:8083 > /tmp/php-prueba.log 2>&1 &
PHP_TEST_PID=$!
# Esperar a que el servidor acepte conexiones (máximo ~10 segundos).
PHP_READY=0
for intento in $(seq 1 20); do
  if curl -fsS http://127.0.0.1:8083/; then
    PHP_READY=1
    break
  fi
  sleep 0.5
done
kill "$PHP_TEST_PID" 2>/dev/null || true
if [ "$PHP_READY" -ne 1 ]; then
  echo "ERROR: PHP no respondió; revisar /tmp/php-prueba.log" >&2
  exit 1
fi
```

Esperado: `PHP OK`. El servidor integrado es **solo de desarrollo**, no de producción. No publicar 8083 en el security group.

## D. Docker Engine, Compose y sistemas empresariales

Instalar Docker Engine y el plugin Compose **desde el repositorio oficial**, siguiendo la [guía para Ubuntu](https://docs.docker.com/engine/install/ubuntu/). Antes de instalar, comprobar que Ubuntu es 24.04 y que la arquitectura EC2 es compatible. Los siguientes comandos configuran el repositorio oficial de Docker:

```bash
sudo apt update
sudo apt install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
sudo tee /etc/apt/sources.list.d/docker.sources >/dev/null <<EOF
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: $(. /etc/os-release && echo "$VERSION_CODENAME")
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo docker version
sudo docker compose version
```

**Permisos:** utilizar `sudo docker` en el laboratorio. No agregar automáticamente el usuario al grupo `docker`, ya que concede privilegios equivalentes a root. No habilitar puertos Docker públicamente.

Obtener el repositorio del curso y preparar credenciales locales:

```bash
mkdir -p ~/ia-aplicada
cd ~/ia-aplicada
git clone https://github.com/juliopez/IA-Aplicada-Integracion-y-Automatizacion.git
cd IA-Aplicada-Integracion-y-Automatizacion/infraestructura-empresa
bash preparar.sh
sudo docker compose config --quiet
```

El script genera `.env` y `db/init.local.sql` (ambos ignorados por Git). **Nunca publicar esos archivos**. El SQL inicializa dos esquemas y datos sintéticos. MySQL se ejecuta dentro de Docker; el host lo expone solo en `127.0.0.1:3307`.

**Despliegue y prueba de los sistemas empresariales:**

```bash
sudo docker compose up -d --build
sudo docker compose ps
curl -fsS http://127.0.0.1:8081/api/health
curl -fsS http://127.0.0.1:8082/api/health
bash probar.sh
```

Los frontend se consultan en `http://127.0.0.1:8081` y `http://127.0.0.1:8082` **desde EC2**, o por túneles SSH desde el computador personal. `probar.sh` requiere `jq` (instalado en el tramo A) y crea un ticket de prueba; también cambia una cuenta ficticia a activa. No ejecutarlo sobre datos reales.

**Resultado esperado:** ambos endpoints `/api/health` responden sin error, `sudo docker compose ps` muestra los servicios activos y `bash probar.sh` termina satisfactoriamente. Si falla, consultar [validación](validacion.md) antes de modificar volúmenes.

Para detener los contenedores sin borrar MySQL:

```bash
sudo docker compose stop
# En una sesión posterior:
sudo docker compose start
```

No ejecutar `sudo docker compose down -v`: elimina los volúmenes y datos. Si el volumen ya existe, modificar `db/init.sql` no vuelve a inicializarlo.

## E. Ollama: instalación y modelo del laboratorio

**Advertencia de recursos:** Ollama necesita RAM y espacio de disco adicionales. La instalación del binario no demuestra que la inferencia sea viable. Confirmar primero arquitectura con `uname -m` y revisar [instalación oficial](https://docs.ollama.com/linux). Los comandos siguientes usan el instalador oficial; **inspeccionar antes de ejecutar**.

```bash
curl -fsSL https://ollama.com/install.sh -o /tmp/ollama-install.sh
less /tmp/ollama-install.sh
# Tras revisar el instalador:
sh /tmp/ollama-install.sh
ollama --version
sudo systemctl status ollama --no-pager || true
```

Comprobar que escucha solo localmente:

```bash
sudo ss -lntp | grep ':11434' || true
curl -fsS http://127.0.0.1:11434/api/tags || true
```

Si se requiere fijar explícitamente escucha local, crear override:

```bash
sudo mkdir -p /etc/systemd/system/ollama.service.d
printf '[Service]\nEnvironment="OLLAMA_HOST=127.0.0.1:11434"\n' | sudo tee /etc/systemd/system/ollama.service.d/override.conf
sudo systemctl daemon-reload
sudo systemctl restart ollama
sudo ss -lntp | grep ':11434' || true
```

**Modelo utilizado en el laboratorio:** `qwen2.5:1.5b`. Ejecutar:

```bash
ollama pull qwen2.5:1.5b
ollama run qwen2.5:1.5b 'Responde en español: ¿qué es un agente de IA? Utiliza dos frases.'
ollama show qwen2.5:1.5b
ollama list
```

En `ollama show` comprobar que **Capabilities** incluya `tools`. Esto acredita capacidad declarada, pero **no** demuestra una llamada funcional. Ejecutar además la prueba V08b documentada en [validacion.md](validacion.md), que comprueba `message.tool_calls[0].function.name == "consultar_tickets"`. Registrar la salida observada. La selección de una herramienta no significa que se haya ejecutado una API empresarial.

No ejecutar las líneas con el marcador literal. Detener el servicio para ahorrar recursos (detener EC2 es lo que evita horas de cómputo):

```bash
sudo systemctl disable --now ollama
```

## F. Node.js 22 y n8n como servicio systemd

Verificar la compatibilidad vigente en la [documentación oficial de n8n](https://docs.n8n.io/hosting/installation/npm/) antes de instalar. Las versiones pueden variar.

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x -o /tmp/nodesource-setup.sh
less /tmp/nodesource-setup.sh
# Después de revisar el script:
sudo -E bash /tmp/nodesource-setup.sh
sudo apt install -y nodejs
node --version
npm --version
sudo npm install -g n8n
n8n --version
which n8n
whoami
```

**Antes de crear el servicio:** comprobar que `which n8n` muestre `/usr/bin/n8n` y que `whoami` muestre `ubuntu`. Si difieren, adaptar `ExecStart`, `User` y `HOME` en el archivo de servicio; solicitar apoyo si es necesario.

### Primera apertura y creación de cuenta

Para una primera prueba, ejecutar temporalmente `n8n start` como usuario `ubuntu`, con escucha restringida a loopback:

```bash
N8N_LISTEN_ADDRESS=127.0.0.1 N8N_PORT=5678 n8n start
```

Desde el computador personal, crear el túnel SSH del puerto 5678 descrito en [aws-ec2.md](aws-ec2.md), visitar `http://localhost:5678` y configurar la cuenta propietaria con contraseña exclusiva. La personalización y la solicitud de clave gratuita son opcionales. **Detener la ejecución manual con Ctrl+C antes de habilitar systemd.** No definir un `N8N_USER_FOLDER` alternativo si luego se utilizará la configuración por defecto: la cuenta y base de datos local quedan bajo `/home/ubuntu/.n8n`.

### Servicio persistente en Ubuntu

El servicio arranca con la EC2 y se reinicia si falla; no necesita mantener abierta una terminal. Se conserva la cuenta creada por el usuario `ubuntu`.

```bash
sudo tee /etc/systemd/system/n8n.service > /dev/null <<'EOF'
[Unit]
Description=n8n - Automatizacion de flujos IA
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=ubuntu
Group=ubuntu
WorkingDirectory=/home/ubuntu
Environment=HOME=/home/ubuntu
Environment=N8N_LISTEN_ADDRESS=127.0.0.1
Environment=N8N_PORT=5678
Environment=N8N_ENFORCE_SETTINGS_FILE_PERMISSIONS=true
ExecStart=/usr/bin/n8n start
Restart=on-failure
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF
sudo systemctl daemon-reload
sudo systemctl enable --now n8n
sleep 10
sudo systemctl status n8n --no-pager
curl -s -o /dev/null -w 'HTTP %{http_code}\n' http://127.0.0.1:5678/
```

**Criterio de aprobación:** `Active: active (running)`, `enabled` y `HTTP 200`, sin reinicios repetidos. Una respuesta HTTP 200 por sí sola no demuestra que sea la instancia administrada por systemd: puede provenir de una ejecución manual todavía activa.

**Si el servicio no inicia:** revisar los comandos siguientes. Si el puerto 5678 está ocupado por una ejecución manual de n8n, detener esa ejecución con Ctrl+C y ejecutar `sudo systemctl restart n8n`. No mantener dos instancias simultáneas. Para otros errores, consultar [validación](validacion.md).

```bash
sudo journalctl -u n8n -n 50 --no-pager
sudo ss -lntp | grep ':5678' || true
sudo systemctl is-enabled n8n
sudo systemctl is-active n8n
```

Para liberar RAM sin perder la configuración, utilizar `sudo systemctl stop n8n`; para reanudar, `sudo systemctl start n8n`. `enable` mantiene el inicio automático cuando vuelva a arrancar EC2. No exponer el puerto 5678 a Internet.

## G. Estado final y ahorro de recursos

```bash
sudo systemctl stop ollama 2>/dev/null || true
sudo systemctl stop n8n 2>/dev/null || true
sudo systemctl is-active ollama n8n || true
# Desde la carpeta infraestructura-empresa, si los contenedores están activos:
sudo docker compose stop
df -h /
free -h
```

Registrar versiones de Python, Git, PHP, Docker/Compose, Ollama y Node/n8n; para MySQL registrar la versión de la imagen Docker; distinguir claramente `instalado`, `probado` y `pendiente por error`. Al salir, **detener EC2 desde la consola AWS**. No confundir `sudo shutdown` con la comprobación del estado AWS; confirmar `Stopped` en consola.
