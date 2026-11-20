# Arquitectura y decisiones de infraestructura

## Decisiones aprobadas

- Una **instancia EC2 individual** por estudiante, dentro de las posibilidades del laboratorio AWS Academy.
- Ubuntu Server **24.04 LTS**, arquitectura de CPU compatible con paquetes y binarios elegidos.
- Instancia activa **solo** durante clases y práctica.
- Presupuesto máximo **USD 50 por estudiante durante el semestre**.
- Herramientas: Python, Git, Apache/PHP, MySQL, Ollama, Node.js, n8n.
- Semana 0 preparatoria, fuera de las 72 horas curriculares.

## Pendiente de autorización docente (NO adivinar)

| Parámetro | Estado |
|---|---|
| Región AWS permitida | Por confirmar en Learner Lab |
| Tipo/familia EC2 | Pendiente |
| Arquitectura x86_64 o ARM64 | Depende de la EC2 elegida |
| Volumen EBS y capacidad | Pendiente |
| Modelo Ollama y cuantización | Pendiente de prueba de RAM/CPU |
| Acceso a API LLM externa y credenciales | Pendiente |
| Cuentas de correo de prueba | Pendiente |
| Restricciones IAM, servicios y cuotas de AWS Academy | Verificar en cada laboratorio |

**Regla:** si no hay autorización para tipo EC2/volumen, el estudiante **no debe crear recursos de pago por su cuenta**. Realizar la lectura de consola y esperar confirmación.

## Diagrama de acceso y puertos

```text
[Estudiante] --SSH 22 desde su IP--> [EC2 Ubuntu]
   |                                       |
   +-- localhost:8080 --túnel--> Apache 127.0.0.1:80
   +-- localhost:5678 --túnel--> n8n 127.0.0.1:5678
   +-- localhost:11434 -túnel-> Ollama 127.0.0.1:11434
                                           |
                                   MySQL 127.0.0.1:3306
```

La IP pública puede **cambiar** tras detener/iniciar EC2. Los túneles se establecen desde el computador del estudiante, no desde EC2. Una IP pública asignada puede generar cargos aun con la instancia en marcha; revisar costos del laboratorio.

## Persistencia y límites

El volumen raíz EBS persiste al **detener** la instancia, pero puede perderse al **terminarla**, según configuración. No utilizar `Terminate` para ahorrar horas. Guardar código en Git; no depender solo del disco. El crédito AWS no equivale a acceso irrestricto a cualquier tipo de instancia. El rendimiento de Ollama sin GPU no está garantizado.

## Servicios y exposición

Apache puede probarse únicamente por túnel SSH; MySQL y Ollama deben escuchar en loopback. n8n se ejecutará por defecto en loopback y se accederá por túnel. No crear reglas públicas 80/443/3306/5678/11434 para este laboratorio. No instalar Docker como requisito: se prioriza una configuración de administración sencilla.
