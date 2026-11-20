# Programa de asignatura

## Inteligencia Artificial Aplicada: Agentes, Integración y Automatización

## Programa de estudio — propuesta

**Duración:** 18 semanas · 4 horas por semana · **72 horas**  
**Modalidad:** teórico-práctica, orientada a estudiantes de informática próximos a incorporarse al mundo laboral.  
**Estado:** propuesta curricular sujeta a validación académica.

### Propósito formativo

Diseñar, implementar, integrar y evaluar soluciones de inteligencia artificial aplicadas a necesidades organizacionales, mediante agentes con herramientas, modelos de lenguaje ejecutados localmente y flujos de automatización, considerando seguridad, privacidad, trazabilidad y pruebas.

### Resultados de aprendizaje del curso

1. **RA1:** Construir y evaluar un agente de IA que seleccione herramientas, ejecute acciones acotadas y verifique resultados.
2. **RA2:** Implementar un LLM local mediante Ollama e integrarlo de forma segura con una aplicación PHP y una base de datos MySQL.
3. **RA3:** Desarrollar un flujo de automatización con n8n que procese correos, los clasifique, genere borradores de respuesta y registre operaciones en MySQL.

## Semana 0 — Laboratorio preparatorio AWS Academy

**Actividad adicional previa a las 18 semanas (4 horas, no incluidas en las 72 horas oficiales).** Cada estudiante prepara una EC2 individual con Ubuntu Server 24.04 LTS y las herramientas del semestre, con una configuración ya probada de **EC2 t2.large, 99 GiB EBS y Ollama `qwen2.5:1.5b`**.

**[Guía autónoma de Semana 0 — comenzar aquí](semana-00-laboratorio/guia-paso-a-paso.md)**: 13 pasos con comandos, resultados esperados, acceso por SSH, interfaces gráficas, pruebas y recuperación.

### Unidades y productos integradores

| Unidad | Semanas | Horas | Producto final |
|---|---|---:|---|
| 1. Construcción de agentes de IA | 1–5 | 20 | Agente funcional con herramientas, ciclo de decisión y pruebas |
| 2. LLM privado e integración empresarial | 6–13 | 32 | Aplicación PHP/MySQL integrada con Ollama |
| 3. Automatización inteligente con n8n | 14–18 | 20 | Flujo de correo → clasificación → borrador revisable → registro MySQL |
| **Total** | **18** | **72** | **Tres productos incrementales** |

### Matriz de planificación semanal (8 columnas)

| Semana | Unidad | Resultado de aprendizaje | Contenidos | Actividad práctica (incremental) | Herramientas | Evidencia de aprendizaje | Evaluación |
|---|---|---|---|---|---|---|---|
| 1 | Agentes | Distinguir LLM, automatización y agente | Arquitecturas; herramientas; autonomía; ciclo observar-actuar | Definir caso de uso, objetivo, herramientas y criterios de éxito | Python, Git, LLM | Especificación del agente y casos de prueba | Diagnóstica: alcance y viabilidad |
| 2 | Agentes | Invocar un LLM desde una aplicación | API de inferencia; prompts; respuestas estructuradas | Implementar cliente y salida JSON validada | Python, API LLM | Cliente ejecutable con pruebas | Formativa: estructura y validación |
| 3 | Agentes | Incorporar herramientas controladas | Function calling; contratos; permisos | Crear dos herramientas y un despachador con lista permitida | Python, API LLM | Herramientas funcionales y registros | Formativa: seguridad y ejecución |
| 4 | Agentes | Implementar un ciclo agéntico verificable | Planificación; estado; límites; manejo de errores | Implementar decisión → herramienta → observación → nueva decisión | Python, LLM, Git | Agente con trazas y límites de ejecución | Formativa: autonomía y trazabilidad |
| 5 | Agentes | Demostrar solución agéntica completa | Pruebas; evaluación; fallos; documentación | Ejecutar casos normales y fallidos; presentar el agente | GitHub, pruebas | Producto 1: agente documentado y demostrable | Sumativa 1: funcionamiento, pruebas y defensa |
| 6 | LLM privado | Desplegar inferencia local | Ollama; modelos; recursos; privacidad | Instalar Ollama y ejecutar un modelo local | Ollama, Linux/Windows | Servicio local y evidencia de inferencia | Formativa: instalación reproducible |
| 7 | LLM privado | Consumir inferencia local mediante API | Endpoints; prompts; JSON; latencia | Construir cliente PHP que consulte Ollama | PHP, Ollama | Endpoint PHP funcional | Formativa: integración y errores |
| 8 | LLM privado | Diseñar la aplicación empresarial | Arquitectura web; casos de uso; modelo de datos | Definir esquema MySQL y crear datos ficticios | MySQL, PHP | Esquema SQL y datos de prueba | Formativa: diseño y consistencia |
| 9 | LLM privado | Implementar una aplicación PHP/MySQL | CRUD; consultas parametrizadas; capas | Crear frontend y backend con consulta de registros | PHP, MySQL | Aplicación operativa con datos | Formativa: funcionamiento y seguridad SQL |
| 10 | LLM privado | Integrar lenguaje natural con datos privados | Herramientas backend; acceso mínimo; consultas autorizadas | Permitir que el LLM solicite consultas mediante funciones PHP | Ollama, PHP, MySQL | Consulta en lenguaje natural con resultados reales | Formativa: precisión y control de acceso |
| 11 | LLM privado | Aplicar controles de seguridad y privacidad | Autenticación; autorización; inyección de prompts; auditoría | Restringir operaciones por rol y registrar solicitudes | PHP, MySQL, Ollama | Permisos y bitácora verificables | Formativa: controles y pruebas negativas |
| 12 | LLM privado | Evaluar exactitud y robustez de respuestas | Pruebas de integración; resultados fundamentados; fallos | Crear batería de preguntas y contrastar con consultas SQL | PHP, MySQL, Ollama | Reporte de pruebas y correcciones | Formativa: evidencia y reproducibilidad |
| 13 | LLM privado | Entregar integración empresarial completa | Despliegue; documentación; demostración | Demostrar aplicación y escenarios autorizados/no autorizados | Ollama, PHP, MySQL, GitHub | Producto 2: aplicación integrada y documentada | Sumativa 2: integración, seguridad y defensa |
| 14 | n8n | Diseñar automatización de correspondencia | Disparadores; nodos; credenciales; flujo de datos | Desplegar n8n y diseñar flujo con correos de prueba | n8n, correo de pruebas | Diagrama y flujo inicial | Formativa: arquitectura y credenciales |
| 15 | n8n | Leer y clasificar mensajes | Conectores de correo; categorías; salidas estructuradas | Capturar correos y clasificarlos con IA | n8n, correo, LLM | Flujo de lectura y clasificación | Formativa: clasificación y manejo de errores |
| 16 | n8n | Generar borradores seguros | Plantillas; contexto; revisión humana | Generar respuesta y enviarla a aprobación, sin envío automático | n8n, LLM, correo | Borradores con revisión | Formativa: calidad y supervisión |
| 17 | n8n | Persistir operaciones y recuperar fallos | MySQL; idempotencia; reintentos; registros | Registrar mensajes, categorías, borradores y estado | n8n, MySQL | Flujo con persistencia y prevención de duplicados | Formativa: trazabilidad y recuperación |
| 18 | n8n | Demostrar automatización empresarial integral | Pruebas extremo a extremo; monitoreo; documentación | Ejecutar casos normales, duplicados y errores; presentar solución | n8n, correo, MySQL, GitHub | Producto 3: flujo completo y evidencias | Sumativa 3: ejecución, control y defensa |

### Metodología

Cada semana combina fundamentos breves, implementación guiada, práctica autónoma y validación. Los ejercicios son acumulativos dentro de cada unidad; se priorizan evidencias de funcionamiento frente a demostraciones exclusivamente declarativas. El trabajo se versiona en GitHub.

### Evaluación

Se contemplan **tres evaluaciones prácticas integradoras**, al cierre de las semanas 5, 13 y 18, con evidencias de ejecución, código, pruebas y defensa técnica. Las ponderaciones y rúbricas definitivas quedan pendientes de aprobación institucional. La distribución de calificaciones deberá formalizarse antes de impartir la asignatura. Las actividades intermedias aportan retroalimentación formativa.

### Recursos y condiciones para el aprendizaje

La asignatura contempla actividades prácticas con Python, Git, Ollama, PHP, MySQL y n8n, utilizando un entorno individual en AWS Academy. Los estudiantes trabajarán con datos ficticios y desarrollarán progresivamente tres productos integradores, considerando seguridad, privacidad, trazabilidad y validación de resultados.

Como actividad preparatoria adicional, se contempla una **Semana 0 de cuatro horas**, no incluida en las 72 horas de la asignatura, destinada a familiarizarse con el entorno de trabajo y comprobar el acceso a las herramientas.

Los procedimientos operativos se mantienen fuera de este programa y pueden consultarse en la [guía autónoma de Semana 0](semana-00-laboratorio/guia-paso-a-paso.md). El [caso transversal de Mesa de Ayuda Inteligente](infraestructura-empresa/README.md) sirve de contexto para las actividades prácticas y las integraciones de las tres unidades.

### Bibliografía y recursos de consulta iniciales

- [Ollama — documentación](https://docs.ollama.com/)
- [n8n — documentación](https://docs.n8n.io/)
- [PHP — manual](https://www.php.net/manual/es/)
- [MySQL — documentación](https://dev.mysql.com/doc/)
- [OWASP — seguridad de aplicaciones](https://owasp.org/)
