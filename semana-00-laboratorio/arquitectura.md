# Arquitectura y decisiones de infraestructura

## Decisiones aprobadas

- Una **instancia EC2 individual** por estudiante, dentro de las posibilidades del laboratorio AWS Academy.
- Ubuntu Server **24.04 LTS**, arquitectura de CPU compatible con paquetes y binarios elegidos.
- Instancia activa **solo** durante clases y práctica.
- Presupuesto máximo **USD 50 por estudiante durante el semestre**.
- Herramientas en Ubuntu: Python, Git, PHP CLI, Docker Engine/Compose, Ollama, Node.js y n8n. MySQL y el PHP/Apache de los dos sistemas empresariales se ejecutan dentro de Docker.
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
   +-- localhost:8081 --túnel--> Mesa de ayuda Docker 127.0.0.1:8081
   +-- localhost:8082 --túnel--> Sistema académico Docker 127.0.0.1:8082
   +-- localhost:8083 --túnel--> PHP de desarrollo 127.0.0.1:8083
   +-- localhost:5678 --túnel--> n8n 127.0.0.1:5678
   +-- localhost:11434 -túnel-> Ollama 127.0.0.1:11434
                                           |
                                   MySQL Docker 127.0.0.1:3307
```

La IP pública puede **cambiar** tras detener/iniciar EC2. Los túneles se establecen desde el computador del estudiante, no desde EC2. Una IP pública asignada puede generar cargos aun con la instancia en marcha; revisar costos del laboratorio.

## Persistencia y límites

El volumen raíz EBS persiste al **detener** la instancia, pero puede perderse al **terminarla**, según configuración. No utilizar `Terminate` para ahorrar horas. Guardar código en Git; no depender solo del disco. El crédito AWS no equivale a acceso irrestricto a cualquier tipo de instancia. El rendimiento de Ollama sin GPU no está garantizado.

## Servicios y exposición

Los puertos Docker 8081, 8082 y 3307 se publican únicamente en 127.0.0.1 de EC2. Ollama y n8n también deben escuchar en loopback. El PHP que desarrolla el estudiante puede utilizar `php -S 127.0.0.1:8083`. Acceso web por túneles SSH. No crear reglas públicas para 80/443/8081/8082/8083/3307/5678/11434. Docker Compose es ahora requisito para los sistemas empresariales. No instalar Apache ni MySQL Server directamente en Ubuntu.

## Caso transversal

Los dos sistemas de referencia se encuentran en [`infraestructura-empresa/`](../infraestructura-empresa/README.md). Los agentes Python, PHP/Ollama y n8n se ejecutan en Ubuntu y consumen APIs HTTP locales. **Las aplicaciones Docker aún no han superado una prueba de integración en EC2**; el dimensionamiento y el modelo Ollama siguen pendientes.
