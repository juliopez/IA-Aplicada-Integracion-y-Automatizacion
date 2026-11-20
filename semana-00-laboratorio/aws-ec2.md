# AWS Academy → AWS → EC2 → SSH

## 1. Entrar al laboratorio

Consultar [video del docente: acceso desde AWS Academy](https://youtu.be/_bdFuZeAa_8).

1. Ingresar a AWS Academy con la cuenta institucional habilitada.
2. Abrir el laboratorio asignado y **iniciarlo** según la interfaz disponible.
3. Acceder a la consola AWS **desde el laboratorio**, no desde una cuenta personal de pago.
4. Registrar región habilitada, saldo/crédito mostrado, restricciones de EC2 y horario de expiración del laboratorio.
5. Si el laboratorio está deshabilitado o no hay saldo, **detener el procedimiento** y consultar al docente.

## 2. Crear EC2

Consultar [video del docente: creación de EC2](https://youtu.be/MTgy0p7-ZGk).

En **EC2 → Instances → Launch instances**:

1. Nombre: `ia-aplicada-estudiante` (sin nombres personales ni RUT).
2. AMI: **Ubuntu Server 24.04 LTS**, preferentemente imagen oficial publicada por Canonical. Verificar arquitectura.
3. **Instance type:** seleccionar **solo** el autorizado por el docente; está pendiente de definición.
4. **Key pair:** crear llave SSH (RSA o ED25519 si el entorno lo admite), formato `.pem` para OpenSSH; descargarla **una sola vez** y protegerla.
5. **Network/security group:** entrada TCP 22 **solo desde la IP pública actual del estudiante (/32)**; no usar `0.0.0.0/0`. Si la IP cambia, actualizar la regla.
6. **Storage:** volumen EBS de tamaño **autorizado**; revisar tipo y costo. No activar volúmenes adicionales.
7. Revisar resumen de cargos y lanzar **una sola instancia**.

No crear IP elástica, balanceador, NAT Gateway ni RDS. No modificar políticas IAM ni crear usuarios con privilegios adicionales.

## 3. Preparar llave en computador personal (Linux/macOS/WSL)

```bash
mkdir -p ~/.ssh
mv ~/Descargas/NOMBRE_LLAVE.pem ~/.ssh/ia-aplicada.pem
chmod 600 ~/.ssh/ia-aplicada.pem
ls -l ~/.ssh/ia-aplicada.pem
```

Sustituir `NOMBRE_LLAVE.pem` por el nombre real. En sistemas con carpeta `Downloads`, adaptar la ruta. **No ejecutar estos comandos en EC2**.

## 4. Conectarse por SSH (computador personal)

Desde EC2 → Instance details, copiar la **IP pública actual**. No pegarla en repositorios ni evidencias públicas.

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

Ejecutar **cada túnel en una terminal independiente**, mientras EC2 está encendida:

```bash
# Apache, cuando esté instalado
ssh -N -i ~/.ssh/ia-aplicada.pem -L 8080:127.0.0.1:80 ubuntu@IP_PUBLICA_EC2

# n8n, solo cuando esté iniciado
ssh -N -i ~/.ssh/ia-aplicada.pem -L 5678:127.0.0.1:5678 ubuntu@IP_PUBLICA_EC2

# Ollama, solo cuando esté iniciado
ssh -N -i ~/.ssh/ia-aplicada.pem -L 11434:127.0.0.1:11434 ubuntu@IP_PUBLICA_EC2
```

Abrir `http://localhost:8080`, `http://localhost:5678` o usar `curl http://localhost:11434/api/tags` en el **computador personal**, según el servicio. Detener cada túnel con Ctrl+C. Si un puerto local ya está ocupado, cambiar el **número izquierdo** del túnel.

## 7. Finalizar

Al terminar la sesión, salir de SSH con `exit`, ir a EC2 → Instances → seleccionar instancia → **Instance state → Stop instance**. Esperar estado **Stopped**. **No seleccionar Terminate**. Revisar que EBS y otros cargos persisten aunque EC2 esté detenida.
