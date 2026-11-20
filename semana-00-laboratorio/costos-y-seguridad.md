# Seguridad y operación del laboratorio

La instalación completa se encuentra en la [guía autónoma de Semana 0](guia-paso-a-paso.md). Esta página explica cómo proteger la instancia, comprobar puertos y detenerla sin perder el trabajo.

## Seguridad mínima obligatoria

1. **SSH:** regla TCP 22 desde la IP pública de la red utilizada. Si cambias de red, actualiza la regla de origen; no abras todos los puertos.
2. **Puertos internos:** no abrir 8081, 8082, 8083, 3307, 11434 ni 5678 en el security group. Usar túneles SSH.
3. **Llave:** `chmod 600 ~/.ssh/ia-aplicada.pem` y nunca copiar `.pem` a EC2 ni a GitHub.
4. **Credenciales:** no compartir secretos en capturas, código o `.env` versionados.
5. **Datos:** usar únicamente registros y correos ficticios.
6. **Permisos:** no usar usuario root para programar ni conectar PHP como MySQL root. Usar `sudo docker` solo para administrar contenedores; el grupo `docker` confiere privilegios equivalentes a root.
7. **Servicios:** no dejar Ollama, los contenedores Docker ni n8n ejecutándose sin necesidad; detener servicios para liberar recursos cuando no se usen; n8n puede quedar habilitado para arranque automático y detenerse manualmente durante la sesión.
8. **AWS:** no crear servicios adicionales (NAT Gateway, RDS, balanceadores, volúmenes extra, IP elástica) que no formen parte de la guía.
9. **Respaldo:** subir código a GitHub privado/institucional, sin secretos. El disco EBS no sustituye el control de versiones.
10. **Aislamiento:** cada estudiante utiliza exclusivamente su EC2; no compartir llaves ni credenciales.

## Verificación de puertos

En EC2:

```bash
sudo ss -lntp
sudo systemctl is-enabled ollama docker n8n 2>/dev/null || true
sudo systemctl is-active ollama docker n8n 2>/dev/null || true
# Dentro de infraestructura-empresa:
sudo docker compose ps
```

Revisar que los puertos publicados por Docker (8081, 8082 y 3307) estén enlazados a `127.0.0.1`; verificar también la configuración de puertos con `sudo docker compose config`. **Una escucha en 0.0.0.0 no queda justificada por un security group cerrado**: corregir configuración y validar de nuevo.

## Política de apagado

Al finalizar:

```bash
sudo systemctl stop ollama n8n 2>/dev/null || true
# Desde infraestructura-empresa, si se levantaron los sistemas:
sudo docker compose stop
exit
```

Luego en consola AWS: EC2 → Instances → seleccionar **la misma instancia** → Instance state → **Stop instance** → esperar **Stopped**. Detener EC2 interrumpe el cómputo y conserva el volumen EBS. No usar `Terminate` para cerrar una sesión: puede destruir datos. No crear una segunda instancia cuando se necesite volver a trabajar.

## Restricciones AWS Academy

Los permisos IAM, tipos EC2 permitidos y duración de las sesiones dependen del laboratorio asignado. Si una acción está bloqueada, **registrar el mensaje** y solicitar orientación; no eludir restricciones ni cambiar a una cuenta AWS personal.
