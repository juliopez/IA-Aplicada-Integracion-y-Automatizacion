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
| E. Sistemas empresariales, Ollama, Node.js y n8n | 55 | Despliegue de referencia probado; repetir y registrar en cada EC2 |
| F. Seguridad, evidencias, detención y reinicio | 35 | Lista de verificación y EC2 detenida |
| **Total** | **240** | **Entorno documentado** |

**Importante:** los tiempos son orientativos. Si una descarga o una instalación tarda más de lo previsto, continuar cuando el docente lo indique. No descargar modelos ni ampliar recursos sin autorización.

## Procedimiento completo

1. **Preparar el laboratorio:** [AWS Academy y creación de EC2](aws-ec2.md), secciones 1–2.
2. **Conectarse:** [llave SSH, terminal y VS Code](aws-ec2.md), secciones 3–5.
3. **Instalar herramientas básicas:** [Ubuntu, Git, Python y PHP](instalacion.md), apartados A–C.
4. **Desplegar la empresa simulada:** [Docker, Compose y sistemas empresariales](instalacion.md), apartado D.
5. **Instalar el modelo local:** [Ollama](instalacion.md), apartado E, solo con autorización docente.
6. **Configurar automatizaciones:** [Node.js y n8n](instalacion.md), apartado F.
7. **Comprobar los resultados:** [validación y evidencias](validacion.md); usar [túneles SSH](aws-ec2.md), sección 6, para las interfaces web.
8. **Cerrar correctamente:** [costos, seguridad y detención de EC2](costos-y-seguridad.md).

**Orden obligatorio:** AWS → SSH → actualización → Python/Git → PHP/Docker Compose → sistemas empresariales → Ollama/Node/n8n → pruebas → detener instancia. No instalar todos los servicios a la vez sin comprobar recursos.

## Entrega individual

En un repositorio **privado** del estudiante o en el medio institucional aprobado, crear `semana-00/evidencias.md` con: región, identificador EC2 **parcialmente oculto**, AMI/versión de Ubuntu, tipo de instancia cuando se autorice, tamaño EBS, versiones instaladas, comandos de verificación, estado de servicios, evidencia de túnel SSH y procedimiento de detención/reinicio. **Nunca** subir archivos `.pem`, contraseñas, tokens, `.env`, IP personal ni capturas con credenciales.

### Criterios de aprobación del laboratorio

- EC2 Ubuntu accesible mediante SSH; llave protegida.
- Git y Python funcionales; entorno virtual probado.
- PHP CLI y Docker Compose verificados; despliegue de ambos sistemas y MySQL comprobado cuando el entorno lo permita, o documentado como pendiente.
- Ollama y Node/n8n instalados y comprobados cuando los recursos autorizados lo permitan; registrar explícitamente cualquier prueba pendiente.
- Puertos internos no expuestos públicamente.
- Registro de consumo y detención de EC2 demostrados.

**Caso transversal:** [Mesa de Ayuda Inteligente](../infraestructura-empresa/README.md). Los sistemas empresariales se entregan preconstruidos en Docker; los productos de IA se desarrollarán durante el curso en Ubuntu. **Semana 0 no exige desarrollar ni integrar un agente:** únicamente dejar el entorno preparado y verificar sus componentes.

> **Para el estudiante:** seguir los ocho pasos en orden, comprobar el resultado indicado en cada sección y registrar las evidencias. Los antecedentes de pruebas e incidencias del docente están en una [bitácora separada](../docs/bitacora-tecnica-semana-00.md); no son pasos adicionales.
