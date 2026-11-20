# Instalación completa en Ubuntu Server 24.04 LTS

**Dónde ejecutar:** todos los comandos de esta página se ejecutan **dentro de la terminal SSH de EC2**, salvo que se indique lo contrario. No ejecutar scripts de Internet como root sin revisarlos. Se requiere acceso sudo y conexión a repositorios oficiales. El docente debe autorizar recursos antes de instalar modelos.

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

**Prueba de despliegue, cuando el docente autorice recursos y la infraestructura esté validada:**

```bash
sudo docker compose up -d --build
sudo docker compose ps
curl -fsS http://127.0.0.1:8081/api/health
curl -fsS http://127.0.0.1:8082/api/health
bash probar.sh
```

Los frontend se consultan en `http://127.0.0.1:8081` y `http://127.0.0.1:8082` **desde EC2**, o por túneles SSH desde el computador personal. `probar.sh` requiere `jq` (instalado en el tramo A) y crea un ticket de prueba; también cambia una cuenta ficticia a activa. No ejecutarlo sobre datos reales.

**Validación de referencia (20-11-2026):** en una EC2 Ubuntu 24.04 se confirmó Docker/MySQL saludable, ambos servicios PHP iniciados, `bash probar.sh` satisfactorio y ambas interfaces accesibles desde un navegador local mediante túneles SSH. La validación corresponde a esta instalación de referencia, no sustituye las pruebas de cada estudiante. Ante fallos, consultar `validacion.md` antes de reinicializar volúmenes.

Para detener los contenedores sin borrar MySQL:

```bash
sudo docker compose stop
# En una sesión posterior:
sudo docker compose start
```

No ejecutar `sudo docker compose down -v`: elimina los volúmenes y datos. Si el volumen ya existe, modificar `db/init.sql` no vuelve a inicializarlo.

## E. Ollama: instalar, pero no descargar modelos sin autorización

**Advertencia de recursos:** Ollama necesita RAM y espacio de disco adicionales. La instalación del binario no demuestra que la inferencia sea viable. Confirmar primero arquitectura con `uname -m` y revisar [instalación oficial](https://docs.ollama.com/linux). Los comandos siguientes usan el instalador oficial; **inspeccionar antes de ejecutar**.

```bash
curl -fsSL https://ollama.com/install.sh -o /tmp/ollama-install.sh
less /tmp/ollama-install.sh
# Tras revisión y autorización docente:
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

**Solo cuando el docente indique el modelo y la instancia lo soporte:**

```bash
# Sustituir NOMBRE_MODELO_AUTORIZADO por el identificador real.
ollama pull NOMBRE_MODELO_AUTORIZADO
ollama run NOMBRE_MODELO_AUTORIZADO "Responde únicamente: modelo operativo"
ollama list
```

No ejecutar las líneas con el marcador literal. Detener el servicio para ahorrar recursos (detener EC2 es lo que evita horas de cómputo):

```bash
sudo systemctl disable --now ollama
```

## F. Node.js LTS y n8n

La versión de Node compatible con n8n **puede cambiar**. Antes de instalar, verificar el rango admitido en [requisitos oficiales de n8n](https://docs.n8n.io/hosting/installation/npm/). La siguiente instalación usa **Node.js 22.x** como base propuesta, sujeta a verificación de compatibilidad en el momento del laboratorio.

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x -o /tmp/nodesource-setup.sh
less /tmp/nodesource-setup.sh
# Tras revisión del script:
sudo -E bash /tmp/nodesource-setup.sh
sudo apt install -y nodejs
node --version
npm --version
```

Instalar n8n **solo si la versión de Node está dentro del rango oficial**:

```bash
sudo npm install -g n8n
n8n --version
```

Iniciar una sesión **de prueba**, no un servicio público:

```bash
mkdir -p ~/ia-aplicada/n8n-data
export N8N_USER_FOLDER="$HOME/ia-aplicada/n8n-data"
export N8N_LISTEN_ADDRESS=127.0.0.1
export N8N_PORT=5678
export N8N_DIAGNOSTICS_ENABLED=false
export N8N_PERSONALIZATION_ENABLED=false
n8n start
```

Este proceso **ocupa la terminal**. En el computador personal, abrir túnel SSH para 5678 y acceder a `http://localhost:5678`. Crear usuario propietario de laboratorio si la versión lo solicita; **no** usar contraseñas reutilizadas. Para terminar: Ctrl+C. No instalar n8n como servicio de inicio automático en Semana 0. No publicar 5678 ni guardar credenciales en Git.

**Nota:** para uso posterior por túnel, la configuración persistente de n8n y la integración de correo se realizarán en el bloque 3. El laboratorio solo acredita instalación y acceso local.

## G. Estado final y ahorro de recursos

```bash
sudo systemctl disable --now ollama 2>/dev/null || true
sudo systemctl is-active ollama || true
# Desde la carpeta infraestructura-empresa, si los contenedores están activos:
sudo docker compose stop
df -h /
free -h
```

Registrar versiones de Python, Git, PHP, Docker/Compose, Ollama y Node/n8n; para MySQL registrar la versión de la imagen Docker; distinguir claramente `instalado`, `probado`, `no ejecutado por falta de recursos` y `pendiente de autorización`. Al salir, **detener EC2 desde la consola AWS**. No confundir `sudo shutdown` con la comprobación del estado AWS; confirmar `Stopped` en consola.
