# Seguridad, presupuesto y operación

## Regla de presupuesto

Cada estudiante dispone de **hasta USD 50** para el semestre según AWS Academy. No interpretar el crédito como USD 50 disponibles exclusivamente para EC2: EBS, direcciones IPv4 públicas, transferencias y otros recursos pueden generar cargos. Las tarifas varían por región, tipo y condiciones del laboratorio. **No se fijan precios ni instancia hasta comprobar la oferta real.**

Para planificación, **72 horas de clase** son el mínimo presencial de las semanas 1–18, pero el uso real incluye Semana 0 y práctica autónoma. Antes de elegir EC2, estimar:

```text
Horas previstas = (18 × 4) + horas Semana 0 + horas prácticas autónomas
Costo aproximado = horas encendida × tarifa EC2
                  + almacenamiento EBS × meses
                  + IPv4 pública y otros cargos aplicables
```

Los precios no se obtienen de esta fórmula: deben consultarse en la región y laboratorio habilitados. Reservar margen para pruebas y errores; no gastar todo el crédito en el primer bloque.

## Registro semanal de costos

| Semana | Horas EC2 | Saldo inicial | Saldo final | Cargos observados | Observaciones |
|---|---:|---:|---:|---:|---|
| 0 | | | | | |
| 1 | | | | | |
| ... | | | | | |
| 18 | | | | | |

Registrar el saldo en AWS Academy antes y después de cada sesión, teniendo presente que la contabilización puede tener retraso. Cuando el crédito sea insuficiente, detener recursos y comunicar al docente.

## Seguridad mínima obligatoria

1. **SSH:** regla TCP 22 solo desde IP del estudiante (/32). No habilitar `0.0.0.0/0` para SSH.
2. **Puertos internos:** no abrir 8081, 8082, 8083, 3307, 11434 ni 5678 en el security group. Usar túneles SSH.
3. **Llave:** `chmod 600 ~/.ssh/ia-aplicada.pem` y nunca copiar `.pem` a EC2 ni a GitHub.
4. **Credenciales:** no compartir secretos en capturas, código o `.env` versionados.
5. **Datos:** usar únicamente registros y correos ficticios.
6. **Permisos:** no usar usuario root para programar ni conectar PHP como MySQL root. Usar `sudo docker` solo para administrar contenedores; el grupo `docker` confiere privilegios equivalentes a root.
7. **Servicios:** no dejar Ollama, los contenedores Docker ni n8n ejecutándose sin necesidad; detener servicios para liberar recursos cuando no se usen; n8n puede quedar habilitado para arranque automático y detenerse manualmente durante la sesión.
8. **AWS:** no crear servicios adicionales (NAT Gateway, RDS, balanceadores, volúmenes extra, IP elástica) sin aprobación.
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

Luego en consola AWS: EC2 → Instances → seleccionar **la misma instancia** → Instance state → **Stop instance** → esperar **Stopped**. Detener EC2 interrumpe el cómputo, **no elimina automáticamente los cargos de EBS y otros recursos**. No usar `Terminate` para cerrar una sesión: puede destruir datos. No crear una segunda instancia cuando se necesite volver a trabajar.

## Restricciones AWS Academy

Los permisos IAM, tipos EC2 permitidos, duración de las sesiones y saldos dependen del laboratorio asignado. Si una acción está bloqueada, **registrar el mensaje** y solicitar orientación; no eludir restricciones ni cambiar a una cuenta AWS personal.
