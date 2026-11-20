# Semana 0 — Guía autónoma de instalación (AWS Academy)

**Objetivo:** terminar con una instancia EC2 Ubuntu 24.04 que ejecute Python, PHP, los dos sistemas empresariales (Docker/MySQL), Ollama y n8n, y que permita abrir las interfaces gráficas desde el navegador del computador personal.

**Configuración de este laboratorio:** Ubuntu Server 24.04 LTS (64 bits x86), EC2 **t2.large** (2 vCPU, 8 GiB RAM), **99 GiB EBS**, usuario inicial `ubuntu`, modelo **`qwen2.5:1.5b`**. Usa **una sola instancia**. Esta guía se ejecuta de principio a fin: no requiere reconstruir la prueba del docente.

**Cómo usarla:** sigue los pasos en orden. Los bloques identificados como **COMPUTADOR PERSONAL** se ejecutan en tu equipo; los identificados como **EC2** se ejecutan en la terminal SSH del servidor. No pegues los títulos ni los símbolos `$`. Si algo no coincide con el resultado esperado, consulta [soluciones de problemas](validacion.md) antes de seguir. Nunca compartas llaves `.pem`, contraseñas, tokens o archivos `.env`.

## Paso 1. Abrir AWS Academy y crear la instancia

**COMPUTADOR PERSONAL — navegador.**

1. Entra a AWS Academy con tu cuenta institucional, inicia el Learner Lab asignado y abre **AWS Console** desde ese laboratorio. [Video de acceso](https://youtu.be/_bdFuZeAa_8).
2. Ve a **EC2 → Instances → Launch instances**. [Video de creación](https://youtu.be/MTgy0p7-ZGk).
3. Asigna nombre `ia-aplicada-estudiante`.
4. En **Application and OS Images**, elige **Ubuntu Server 24.04 LTS**, arquitectura **64-bit (x86)**, AMI oficial de Canonical.
5. En **Instance type**, selecciona **t2.large**.
6. En **Key pair**, crea una nueva llave RSA para SSH, descarga el archivo **.pem** y guárdalo en tu computador. Si ya tienes una llave válida para esta instancia, úsala; no necesitas crear otra.
7. En **Network settings**, permite **SSH TCP 22** desde **My IP**. Si te conectas desde otro laboratorio o red, actualiza la regla de origen con la IP pública de esa red; **no abras todos los puertos**. Los servicios web se abrirán mediante túneles SSH, sin reglas de entrada adicionales.
8. En **Configure storage**, establece **99 GiB** para el volumen raíz EBS. Conserva el tipo de volumen predeterminado admitido por el laboratorio.
9. Pulsa **Launch instance**. Espera a que aparezca **Running** y que pasen las comprobaciones de estado.
10. Copia la **Public IPv4 address** de la instancia. La usarás solo para conectarte; puede cambiar tras detener/iniciar EC2.

**Comprobación:** en EC2 → Instances aparece una sola instancia `Running` con tipo `t2.large`, Ubuntu 24.04 y volumen de 99 GiB.

## Paso 2. Preparar la llave y conectarte

**COMPUTADOR PERSONAL — terminal Linux, macOS o WSL.**

Si el archivo descargado se llama, por ejemplo, `mi-llave.pem` y está en `Descargas`:

```bash
mkdir -p ~/.ssh
cp ~/Descargas/mi-llave.pem ~/.ssh/ia-aplicada.pem
chmod 600 ~/.ssh/ia-aplicada.pem
```

Si tu carpeta es `Downloads`, cambia `Descargas` por `Downloads`. Sustituye `mi-llave.pem` por el nombre real. No copies la llave al servidor ni la subas a GitHub.

Reemplaza `IP_PUBLICA_EC2` por la IPv4 de tu instancia:

```bash
ssh -i ~/.ssh/ia-aplicada.pem ubuntu@IP_PUBLICA_EC2
```

Confirma la huella SSH solo si corresponde a tu instancia. A partir de aquí, **permanece conectado a EC2**.

**EC2 — comprobar el servidor:**

```bash
whoami
cat /etc/os-release
uname -m
free -h
df -h /
```

**Comprobación:** usuario `ubuntu`, Ubuntu 24.04, arquitectura `x86_64`, aproximadamente 8 GiB RAM y volumen raíz de 99 GiB (el espacio libre será menor).

## Paso 3. Actualizar Ubuntu e instalar Git y Python

**EC2:**

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y git python3 python3-pip python3-venv curl wget jq unzip ca-certificates gnupg lsb-release build-essential openssh-client openssl
python3 --version
git --version
```

Si Ubuntu solicita reiniciar, ejecuta `sudo reboot`, espera y vuelve a entrar con el comando SSH del paso 2.

**EC2 — crear y probar un entorno Python:**

```bash
mkdir -p ~/ia-aplicada/semana-00
cd ~/ia-aplicada/semana-00
python3 -m venv .venv
source .venv/bin/activate
python -c 'import sys; print("Python OK", sys.version)'
deactivate
```

**Comprobación:** aparece `Python OK` y `git --version` muestra una versión.

## Paso 4. Instalar PHP y comprobarlo

**EC2:**

```bash
sudo apt install -y php-cli php-mysql php-curl php-mbstring php-xml
php --version
mkdir -p ~/ia-aplicada/php-prueba
printf '%s\n' '<?php echo "PHP OK\\n";' > ~/ia-aplicada/php-prueba/index.php
php ~/ia-aplicada/php-prueba/index.php
```

**Comprobación:** se imprime `PHP OK`. No instales Apache ni MySQL directamente en Ubuntu: esos componentes se ejecutarán en contenedores Docker.

## Paso 5. Instalar Docker Engine y Docker Compose

**EC2:**

```bash
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
sudo systemctl enable --now docker
sudo docker version
sudo docker compose version
```

**Comprobación:** aparecen las versiones del cliente y servidor Docker, y la versión de Compose. Usa `sudo docker` en los pasos siguientes.

## Paso 6. Desplegar los dos sistemas empresariales

**EC2:**

```bash
cd ~/ia-aplicada
git clone https://github.com/juliopez/IA-Aplicada-Integracion-y-Automatizacion.git
cd IA-Aplicada-Integracion-y-Automatizacion/infraestructura-empresa
bash preparar.sh
sudo docker compose config --quiet
sudo docker compose up -d --build
sudo docker compose ps
```

Si el repositorio ya existe, no vuelvas a clonarlo: entra en su carpeta y continúa desde `bash preparar.sh`.

**EC2 — probar las APIs y los datos sintéticos:**

```bash
curl -fsS http://127.0.0.1:8081/api/health
curl -fsS http://127.0.0.1:8082/api/health
bash probar.sh
```

El arranque inicial puede tardar varios minutos mientras se descargan imágenes y se inicializa MySQL. Si una API todavía no responde, consulta `sudo docker compose ps` y `sudo docker compose logs --tail=60 db`; no borres los volúmenes.

**Comprobación:** los contenedores están activos y `bash probar.sh` termina satisfactoriamente. Las contraseñas y el token quedan en `.env`; **no los compartas**.

## Paso 7. Abrir las interfaces en tu navegador

**COMPUTADOR PERSONAL — abre una segunda terminal, sin cerrar la conexión a EC2.**

Sustituye `IP_PUBLICA_EC2`:

```bash
ssh -N -o ExitOnForwardFailure=yes -i ~/.ssh/ia-aplicada.pem \
  -L 8081:127.0.0.1:8081 \
  -L 8082:127.0.0.1:8082 \
  ubuntu@IP_PUBLICA_EC2
```

La terminal quedará aparentemente inmóvil: **es normal; el túnel está funcionando**. No la cierres mientras usas el navegador.

**COMPUTADOR PERSONAL — navegador:**

- **Mesa de ayuda:** http://localhost:8081
- **Sistema académico:** http://localhost:8082

**Comprobación:** ambas páginas muestran sus datos de prueba. Si el puerto local está ocupado, utiliza los puertos alternativos explicados en [acceso SSH y túneles](aws-ec2.md#6-túneles-de-acceso-desde-computador-personal). Para permitir que un compañero pruebe tu sistema, **no compartas tu llave SSH**; coordina con el docente un mecanismo de acceso autorizado.

## Paso 8. Instalar Ollama y descargar el modelo

**EC2:**

```bash
curl -fsSL https://ollama.com/install.sh -o /tmp/ollama-install.sh
less /tmp/ollama-install.sh
```

Revisa el script; pulsa `q` para salir de `less`. Después:

```bash
sh /tmp/ollama-install.sh
sudo systemctl enable --now ollama
ollama --version
curl -fsS http://127.0.0.1:11434/api/tags
ollama pull qwen2.5:1.5b
ollama run qwen2.5:1.5b 'Responde en español y en dos frases: ¿qué es un agente de IA?'
ollama show qwen2.5:1.5b
```

**Comprobación:** Ollama responde, el modelo produce texto y `ollama show` muestra la capacidad `tools`. Para comprobar una selección real de herramienta, ejecuta la [prueba V08b](validacion.md#v08b--prueba-funcional-de-selección-de-herramientas-ollama). Esa prueba **no modifica tickets**.

## Paso 9. Instalar Node.js y n8n

**EC2:**

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x -o /tmp/nodesource-setup.sh
less /tmp/nodesource-setup.sh
```

Revisa el script, pulsa `q` y ejecuta:

```bash
sudo -E bash /tmp/nodesource-setup.sh
sudo apt install -y nodejs
node --version
npm --version
sudo npm install -g n8n
n8n --version
which n8n
whoami
```

**Comprobación:** Node.js versión 22, npm y n8n instalados. Los comandos `which n8n` y `whoami` deben devolver `/usr/bin/n8n` y `ubuntu` para utilizar sin cambios el servicio del paso 11. Si son distintos, consulta la [guía detallada](instalacion.md#f-nodejs-22-y-n8n-como-servicio-systemd).

## Paso 10. Configurar la cuenta web de n8n

**EC2 — primera terminal:**

```bash
N8N_LISTEN_ADDRESS=127.0.0.1 N8N_PORT=5678 n8n start
```

**COMPUTADOR PERSONAL — otra terminal:**

```bash
ssh -N -o ExitOnForwardFailure=yes -i ~/.ssh/ia-aplicada.pem \
  -L 5678:127.0.0.1:5678 ubuntu@IP_PUBLICA_EC2
```

En tu navegador abre **http://localhost:5678**, crea la cuenta propietaria con una contraseña segura y completa o salta las pantallas opcionales. Comprueba que puedes acceder a **Workflows**.

**IMPORTANTE:** vuelve a la terminal EC2 donde ejecutaste `n8n start` y pulsa **Ctrl+C** para detener la instancia manual. **Debes hacerlo antes del paso 11** para evitar que el puerto 5678 quede ocupado.

## Paso 11. Configurar n8n para que arranque automáticamente

**EC2 — asegúrate de haber detenido la instancia manual:**

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
sudo systemctl is-enabled n8n
sudo systemctl is-active n8n
curl -s -o /dev/null -w 'HTTP %{http_code}\n' http://127.0.0.1:5678/
```

**Comprobación:** aparecen `enabled`, `active` y `HTTP 200`. Abre nuevamente http://localhost:5678 con el túnel SSH: la cuenta y los workflows deben seguir disponibles. Si aparece `activating` o `failed`, consulta [diagnóstico de n8n](validacion.md#diagnóstico-de-fallas-frecuentes).

## Paso 12. Validación final

**EC2:**

```bash
python3 --version
git --version
php --version | head -n 1
sudo docker --version
sudo docker compose version
ollama --version
node --version
npm --version
n8n --version
sudo systemctl is-active ollama n8n
cd ~/ia-aplicada/IA-Aplicada-Integracion-y-Automatizacion/infraestructura-empresa
sudo docker compose ps
bash probar.sh
```

**COMPUTADOR PERSONAL:** comprueba que se abren los tres servicios web: http://localhost:8081, http://localhost:8082 y http://localhost:5678, cada uno con su túnel SSH activo. Registra los resultados usando la [tabla de validación](validacion.md#pruebas-de-aceptación). **No necesitas programar un agente todavía.**

## Paso 13. Detener y reanudar la instancia sin perder el trabajo

**EC2 — al finalizar:**

```bash
cd ~/ia-aplicada/IA-Aplicada-Integracion-y-Automatizacion/infraestructura-empresa
sudo docker compose stop
sudo systemctl stop ollama n8n
exit
```

**COMPUTADOR PERSONAL — consola AWS:** EC2 → Instances → selecciona **la misma** instancia → **Instance state → Stop instance** → espera el estado **Stopped**. **No selecciones Terminate**.

**En una próxima sesión:** abre AWS Academy, pulsa **Start instance** en la misma EC2, copia su IP pública actual, reconecta por SSH y ejecuta:

```bash
cd ~/ia-aplicada/IA-Aplicada-Integracion-y-Automatizacion/infraestructura-empresa
sudo docker compose start
sudo systemctl start ollama n8n
sudo docker compose ps
sudo systemctl is-active ollama n8n
```

Vuelve a crear los túneles SSH desde tu computador y abre las interfaces. **No vuelvas a instalar las herramientas ni a ejecutar `preparar.sh` si ya están funcionando.**

---

**Resultado de Semana 0:** una instancia Ubuntu 24.04 preparada para las prácticas, dos sistemas empresariales accesibles, Ollama con un modelo local y n8n con su interfaz y servicio de inicio automático. Las integraciones y los agentes se desarrollarán en las semanas siguientes.

**Si necesitas ayuda:** [errores frecuentes y recuperación](validacion.md) · [arquitectura](arquitectura.md) · [seguridad y operación](costos-y-seguridad.md).
