# Semana 0 — Laboratorio AWS Academy: entorno individual para las 18 clases

**Prerrequisito del curso · 4 horas adicionales (no incluidas en las 72 horas de las semanas 1–18).**  
**Modalidad:** trabajo práctico individual. **Presupuesto máximo de laboratorio:** USD 50 por estudiante para el semestre, sujeto a las condiciones efectivas de AWS Academy.

## Objetivo y alcance

Crear una **EC2 individual con Ubuntu Server 24.04 LTS**, acceder por SSH, instalar y verificar el software del semestre, aplicar controles de seguridad y aprender a detener/reiniciar la instancia sin perder el trabajo. **No se define todavía el tipo de EC2, su capacidad, el tamaño de EBS ni el modelo de Ollama:** requieren confirmación docente según catálogo y crédito AWS Academy.

### Videos de apoyo del docente

1. [Desde AWS Academy hacia la consola AWS](https://youtu.be/_bdFuZeAa_8). Usarlo durante el acceso al laboratorio.
2. [Creación de una instancia EC2](https://youtu.be/MTgy0p7-ZGk). Usarlo durante el aprovisionamiento.

Los videos **complementan** la guía; los nombres y controles de la consola pueden variar. Si el video y la consola actual difieren, verificar el estado real antes de continuar. Esta guía no presupone detalles internos de los videos.

## Arquitectura común

```text
Computador personal (VS Code / terminal / navegador)
          │ SSH (22) limitado a IP del estudiante
          ▼
AWS Academy Learner Lab → EC2 individual (Ubuntu 24.04)
          ├─ Bloque 1: Python + Git + cliente LLM/API
          ├─ Bloque 2: PHP CLI + Ollama (desarrollo en Ubuntu)
          ├─ Bloque 3: Node.js + n8n + correo de prueba
          └─ Docker Compose: mesa de ayuda + sistema académico + MySQL
                  │
          Volumen EBS persistente
```

No publicar puertos 8081/8082 (aplicaciones Docker), 3307 (MySQL publicado en loopback), 11434 (Ollama) ni 5678 (n8n). Para acceder a aplicaciones de laboratorio utilizar **túneles SSH**. No utilizar datos reales ni credenciales personales de producción.

## Ruta del laboratorio (240 minutos)

| Tramo | Minutos | Resultado verificable |
|---|---:|---|
| A. AWS Academy, presupuesto y decisiones pendientes | 25 | Acceso confirmado y hoja de control |
| B. EC2, llave, SSH y red | 45 | Ubuntu accesible con usuario no root |
| C. Sistema, Git y Python | 35 | Entorno Python aislado y Git operativos |
| D. PHP, Docker Engine y Compose | 45 | PHP CLI y Docker Compose disponibles |
| E. Sistemas empresariales, Ollama, Node.js y n8n | 55 | Despliegue preparado; ejecución sujeta a recursos y validación |
| F. Seguridad, evidencias, detención y reinicio | 35 | Lista de verificación y EC2 detenida |
| **Total** | **240** | **Entorno documentado** |

**Condición realista:** descargas de paquetes, instalación de modelos y resolución de incidencias pueden exceder el tiempo estimado. El tramo E admite dejar el modelo Ollama **sin descargar** y n8n **sin arrancar** hasta contar con dimensionamiento y validación docente. Esto no se considera fallo de la Semana 0.

## Procedimiento completo

1. [Arquitectura y decisiones pendientes](arquitectura.md).
2. [Acceso AWS Academy y creación segura de EC2](aws-ec2.md).
3. [Instalación paso a paso, con comandos](instalacion.md).
4. [Pruebas, evidencias y resolución de problemas](validacion.md).
5. [Costos, seguridad y operación diaria](costos-y-seguridad.md).

**Orden obligatorio:** AWS → SSH → actualización → Python/Git → PHP/Docker Compose → sistemas empresariales → Ollama/Node/n8n → pruebas → detener instancia. No instalar todos los servicios a la vez sin comprobar recursos.

## Entrega individual

En un repositorio **privado** del estudiante o en el medio institucional aprobado, crear `semana-00/evidencias.md` con: región, identificador EC2 **parcialmente oculto**, AMI/versión de Ubuntu, tipo de instancia cuando se autorice, tamaño EBS, versiones instaladas, comandos de verificación, estado de servicios, evidencia de túnel SSH y procedimiento de detención/reinicio. **Nunca** subir archivos `.pem`, contraseñas, tokens, `.env`, IP personal ni capturas con credenciales.

### Criterios de aprobación del laboratorio

- EC2 Ubuntu accesible mediante SSH; llave protegida.
- Git y Python funcionales; entorno virtual probado.
- PHP CLI y Docker Compose verificados; despliegue de ambos sistemas y MySQL comprobado cuando el entorno lo permita, o documentado como pendiente.
- Instalación de Ollama y Node/n8n documentada; ejecución de modelo y n8n **condicionada** a la capacidad autorizada.
- Puertos internos no expuestos públicamente.
- Registro de consumo y detención de EC2 demostrados.

**Caso transversal aprobado:** [Mesa de Ayuda Inteligente](../infraestructura-empresa/README.md). Los sistemas empresariales se entregan preconstruidos en Docker; los tres productos de IA se desarrollan en Ubuntu. **La configuración Docker aún requiere validación real de integración**, por lo que los resultados no deben declararse exitosos sin ejecutarlos.
