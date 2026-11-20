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

## C. Apache y PHP

```bash
sudo apt install -y apache2 php libapache2-mod-php php-cli php-mysql php-curl php-mbstring php-xml
php -v
apache2 -v
sudo systemctl enable --now apache2
sudo systemctl status apache2 --no-pager
curl -I http://127.0.0.1/
```

Prueba PHP local, sin mostrar `phpinfo()` públicamente:

```bash
echo '<?php header("Content-Type: text/plain"); echo "PHP OK\n";' | sudo tee /var/www/html/salud.php >/dev/null
curl http://127.0.0.1/salud.php
sudo rm /var/www/html/salud.php
```

Esperado: `PHP OK`. **No abrir el puerto 80 en el security group**: usar el túnel SSH documentado. Detener Apache cuando no se necesite:

```bash
sudo systemctl disable --now apache2
```

## D. MySQL Server y base de prueba

```bash
sudo apt install -y mysql-server
mysql --version
sudo systemctl enable --now mysql
sudo systemctl status mysql --no-pager
sudo mysql -e 'SELECT VERSION() AS version_mysql;'
```

Verificar que MySQL no escucha en todas las interfaces:

```bash
sudo ss -lntp | grep ':3306' || true
sudo mysql -NBe "SHOW VARIABLES LIKE 'bind_address';"
```

**Si el bind_address no es 127.0.0.1**, detener la práctica y revisar configuración con el docente antes de continuar. En Ubuntu suele estar restringido por defecto; no asumirlo sin verificar.

Crear base de datos y tabla **solo de laboratorio**:

```bash
sudo mysql <<'SQL'
CREATE DATABASE IF NOT EXISTS ia_laboratorio;
USE ia_laboratorio;
CREATE TABLE IF NOT EXISTS salud (
  id INT PRIMARY KEY,
  mensaje VARCHAR(80) NOT NULL
);
INSERT INTO salud (id, mensaje) VALUES (1, 'MySQL OK')
ON DUPLICATE KEY UPDATE mensaje = VALUES(mensaje);
SELECT * FROM salud;
SQL
```

Esperado: `1 | MySQL OK`. La autenticación de la aplicación PHP se definirá en el bloque 2: **no utilizar root desde PHP**, ni abrir 3306 públicamente. Detener servicio:

```bash
sudo systemctl disable --now mysql
```

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
sudo systemctl disable --now apache2 mysql ollama 2>/dev/null || true
sudo systemctl is-active apache2 mysql ollama || true
df -h /
free -h
```

Registrar versiones de Python, Git, PHP, MySQL, Ollama y Node/n8n; distinguir claramente `instalado`, `probado`, `no ejecutado por falta de recursos` y `pendiente de autorización`. Al salir, **detener EC2 desde la consola AWS**. No confundir `sudo shutdown` con la comprobación del estado AWS; confirmar `Stopped` en consola.
