# Organización del repositorio y materiales de la asignatura

Este documento describe la estructura de trabajo del repositorio. **No forma parte del programa curricular** y no reemplaza las instrucciones de instalación para estudiantes.

## Distribución de documentos

| Ruta | Propósito | Destinatario |
|---|---|---|
| [README.md](../README.md) | Programa de asignatura: propósito, resultados de aprendizaje, unidades, planificación, metodología, evaluación, bibliografía y recursos | Docentes, estudiantes y revisión académica |
| [semana-00-laboratorio/](../semana-00-laboratorio/README.md) | Material preparatorio: acceso, instalación, verificación, seguridad y solución de problemas | Estudiantes |
| [semana-00-laboratorio/guia-paso-a-paso.md](../semana-00-laboratorio/guia-paso-a-paso.md) | Secuencia autónoma de instalación y pruebas, con capturas | Estudiantes |
| [semana-00-laboratorio/arquitectura.md](../semana-00-laboratorio/arquitectura.md) | Configuración EC2, puertos, servicios y decisiones técnicas | Docentes y soporte |
| [infraestructura-empresa/](../infraestructura-empresa/README.md) | Código, despliegue y contratos API de los dos sistemas empresariales ficticios | Estudiantes y docentes |
| [docs/bitacora-tecnica-semana-00.md](bitacora-tecnica-semana-00.md) | Antecedentes de pruebas y decisiones de implementación | Equipo docente |

## Materiales previstos para las unidades

La siguiente organización expresa **destinos previstos**, no garantiza que todas las carpetas estén creadas:

```text
README.md                          Programa de asignatura
semana-00-laboratorio/             Laboratorio preparatorio y guías
infraestructura-empresa/           Sistemas de referencia y APIs
bloque-01-agentes/                 Semanas 1–5: agente Python y herramientas
bloque-02-ollama-php-mysql/        Semanas 6–13: LLM local e integración
bloque-03-n8n/                     Semanas 14–18: automatización
docs/                              Documentación transversal y bitácoras
```

Los materiales de cada bloque se incorporarán a medida que se desarrollen. El programa académico define **qué se aprende y cómo se evalúa**; las carpetas técnicas describen **cómo se implementan las actividades**.

## Criterios de mantenimiento

1. Mantener en el README principal los componentes curriculares y enlaces a recursos, sin duplicar comandos ni parámetros de instalación.
2. Actualizar instrucciones de instalación, capturas y resultados esperados en Semana 0.
3. Documentar puertos, arquitectura y configuración en los archivos técnicos correspondientes.
4. Mantener los contratos API y comandos propios de los sistemas ficticios en `infraestructura-empresa/`.
5. No publicar credenciales, archivos `.pem`, tokens ni información real de estudiantes.
