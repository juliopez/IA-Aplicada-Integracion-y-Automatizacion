# AWS Academy → AWS → EC2 → SSH

## 1. Entrar al laboratorio

Consultar [video del docente: acceso desde AWS Academy](https://youtu.be/_bdFuZeAa_8).

1. Ingresar a AWS Academy con la cuenta institucional habilitada.
2. Abrir el laboratorio asignado y **iniciarlo** según la interfaz disponible.
3. Acceder a la consola AWS **desde el laboratorio**, no desde una cuenta personal de pago.
4. Registrar la región habilitada y comprobar que el laboratorio permite crear una instancia `t2.large`.
5. Si el laboratorio está deshabilitado o no permite crear la instancia indicada, detener el procedimiento y consultar al docente.

## 2. Crear EC2

Consultar [video del docente: creación de EC2](https://youtu.be/MTgy0p7-ZGk).

En **EC2 → Instances → Launch instances**:

1. Nombre: `ia-aplicada-estudiante` (sin nombres personales ni RUT).
2. AMI: **Ubuntu Server 24.04 LTS**, preferentemente imagen oficial publicada por Canonical. Verificar arquitectura.
3. **Instance type:** `t2.large` (2 vCPU, 8 GiB RAM), configuración utilizada en la prueba docente. Confirmar que aparece como disponible en el laboratorio.
4. **Key pair:** crear llave SSH (RSA o ED25519 si el entorno lo admite), formato `.pem` para OpenSSH; descargarla **una sola vez** y protegerla.
5. **Network/security group:** entrada TCP 22 **solo desde la IP pública actual del estudiante (/32)**; no usar `0.0.0.0/0`. Si la IP cambia, actualizar la regla.
6. **Storage:** configurar **99 GiB EBS**, como en la prueba docente; verificar que el laboratorio permite esta configuración. No activar volúmenes adicionales.
7. Revisar la configuración y lanzar **una sola instancia**.

No crear IP elástica, balanceador, NAT Gateway ni RDS. No modificar políticas IAM ni crear usuarios con privilegios adicionales.

## 3. Preparar llave en computador personal (Linux/macOS/WSL o Windows)

```bash
mkdir -p ~/.ssh
mv ~/Descargas/NOMBRE_LLAVE.pem ~/.ssh/ia-aplicada.pem
chmod 600 ~/.ssh/ia-aplicada.pem
ls -l ~/.ssh/ia-aplicada.pem
```

Sustituir `NOMBRE_LLAVE.pem` por el nombre real. En sistemas con carpeta `Downloads`, adaptar la ruta. **No ejecutar estos comandos en EC2**. Si se prefiere conservar la llave en Descargas, no moverla: ejecutar `chmod 600 ~/Descargas/NOMBRE_LLAVE.pem` y utilizar **esa misma ruta** en cada comando `ssh -i`. Comprobar su ubicación con `ls -l ~/Descargas/*.pem ~/Downloads/*.pem ~/.ssh/*.pem 2>/dev/null` (no compartir la llave).

### Alternativa: Windows 10/11 (PowerShell y OpenSSH)

**COMPUTADOR PERSONAL — PowerShell**, no en EC2. Windows no necesita `chmod`; se restringen los permisos de la llave mediante `icacls`.

1. Abre **PowerShell** (sin permisos de administrador). Comprueba que OpenSSH está disponible con `ssh -V`. Si no reconoce `ssh`, instala **OpenSSH Client** desde *Configuración → Sistema → Características opcionales* (la ubicación puede variar según la versión de Windows o las políticas del laboratorio).
2. Copia el archivo `.pem` descargado desde `Descargas` a tu carpeta personal `.ssh`. Sustituye `mi-llave.pem` por el nombre real:

```powershell
New-Item -ItemType Directory -Force "$env:USERPROFILE\.ssh" | Out-Null
Copy-Item "$env:USERPROFILE\Downloads\mi-llave.pem" "$env:USERPROFILE\.ssh\ia-aplicada.pem"
$llave = "$env:USERPROFILE\.ssh\ia-aplicada.pem"
icacls.exe "$llave" /inheritance:r
icacls.exe "$llave" /grant:r "${env:USERDOMAIN}\${env:USERNAME}:R"
icacls.exe "$llave"
```

3. Comprueba que la lista de permisos permite leer la llave a **tu usuario**, y no a grupos como `Everyone` o `Users`. Si la política del laboratorio impide modificar los permisos, solicita ayuda al administrador del laboratorio; **no compartas la llave**.
4. Conecta por SSH, reemplazando `IP_PUBLICA_EC2`:

```powershell
ssh -i "$env:USERPROFILE\.ssh\ia-aplicada.pem" ubuntu@IP_PUBLICA_EC2
```

**Importante:** si utilizas Windows con **WSL**, sigue los comandos Linux anteriores **dentro de WSL** y guarda la llave en su sistema de archivos Linux. Los comandos de PowerShell se aplican al cliente OpenSSH nativo de Windows.

## 4. Conectarse por SSH (computador personal)

Desde EC2 → Instance details, copiar la **IP pública actual**. En Windows PowerShell, utilizar la ruta `"$env:USERPROFILE\.ssh\ia-aplicada.pem"` en lugar de `~/.ssh/ia-aplicada.pem`. No pegarla en repositorios ni evidencias públicas.

```bash
ssh -i ~/.ssh/ia-aplicada.pem ubuntu@IP_PUBLICA_EC2
```

Si se solicita confirmar la huella del servidor, contrastarla con la huella obtenida por un canal confiable antes de aceptar. Para Ubuntu oficial, el usuario inicial suele ser `ubuntu`.

**Dentro de EC2:**

```bash
whoami
hostnamectl
cat /etc/os-release
uname -m
df -h /
free -h
```

Esperado: usuario `ubuntu`, Ubuntu 24.04 LTS y capacidad real de RAM/disco. Registrar valores, **sin publicar la IP**.

## 5. Acceso por VS Code (opcional)

Instalar VS Code y extensión **Remote - SSH** en el computador personal. En `~/.ssh/config` del computador:

```sshconfig
Host ia-aplicada
    HostName IP_PUBLICA_EC2
    User ubuntu
    IdentityFile ~/.ssh/ia-aplicada.pem
    IdentitiesOnly yes
```

Ejecutar `chmod 600 ~/.ssh/config`; conectarse a `ia-aplicada`. Actualizar `HostName` si cambia la IP tras reiniciar EC2.

## 6. Túneles de acceso (desde computador personal)

Ejecutar los túneles **desde el computador personal**, mientras EC2 está encendida. En Windows PowerShell, reemplazar la ruta `~/.ssh/ia-aplicada.pem` por `"$env:USERPROFILE\.ssh\ia-aplicada.pem"` en los comandos siguientes. Los dos primeros permiten abrir los sistemas empresariales:

```bash
# Mesa de ayuda y plataforma académica (ambos en un túnel)
ssh -N -i ~/.ssh/ia-aplicada.pem \
  -L 8081:127.0.0.1:8081 -L 8082:127.0.0.1:8082 ubuntu@IP_PUBLICA_EC2

# PHP del estudiante, cuando ejecute php -S 127.0.0.1:8083
ssh -N -i ~/.ssh/ia-aplicada.pem -L 8083:127.0.0.1:8083 ubuntu@IP_PUBLICA_EC2

# n8n, solo cuando esté iniciado
ssh -N -i ~/.ssh/ia-aplicada.pem -L 5678:127.0.0.1:5678 ubuntu@IP_PUBLICA_EC2

# Ollama, solo cuando esté iniciado
ssh -N -i ~/.ssh/ia-aplicada.pem -L 11434:127.0.0.1:11434 ubuntu@IP_PUBLICA_EC2
```

**Si 8081 o 8082 ya están ocupados en el computador personal**, cerrar el túnel anterior con Ctrl+C y utilizar puertos locales alternativos, sin modificar Docker ni AWS:

```bash
ssh -N -o ExitOnForwardFailure=yes -i ~/.ssh/ia-aplicada.pem \
  -L 18081:127.0.0.1:8081 \
  -L 18082:127.0.0.1:8082 \
  ubuntu@IP_PUBLICA_EC2
```

En ese caso abrir `http://localhost:18081` y `http://localhost:18082`. Si la llave sigue en Descargas, reemplazar `~/.ssh/ia-aplicada.pem` por su ruta real. **Mantener abierta la terminal SSH mientras se utilizan las interfaces**.

Abrir `http://localhost:8081` (tickets), `http://localhost:8082` (académico), `http://localhost:8083` (PHP del estudiante), `http://localhost:5678` (n8n) o usar `curl http://localhost:11434/api/tags` en el **computador personal**, según el servicio. Detener cada túnel con Ctrl+C. Si un puerto local ya está ocupado, cambiar el **número izquierdo** del túnel.

## 7. Finalizar

Al terminar la sesión, salir de SSH con `exit`, ir a EC2 → Instances → seleccionar instancia → **Instance state → Stop instance**. Esperar estado **Stopped**. **No seleccionar Terminate**. El volumen EBS permanece asociado a la instancia detenida.
