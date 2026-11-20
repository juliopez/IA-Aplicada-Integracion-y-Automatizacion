# Semana 0 — Instalación autónoma del laboratorio AWS

**Prerrequisito práctico del curso**, fuera de las 72 horas de las clases 1–18.

## Empieza aquí

**[Abrir la guía paso a paso para estudiantes](guia-paso-a-paso.md)**

La guía contiene **13 pasos consecutivos**, desde la creación de la instancia hasta la validación y el apagado seguro. Cada paso indica dónde ejecutar los comandos, qué resultado esperar y cómo continuar. No es necesario conocer las pruebas previas del docente.

## Configuración del laboratorio

| Componente | Configuración |
|---|---|
| Plataforma | AWS Academy, instancia EC2 individual |
| Sistema operativo | Ubuntu Server 24.04 LTS, x86_64 |
| Instancia | **t2.large**, 2 vCPU, 8 GiB RAM |
| Almacenamiento | **99 GiB EBS** |
| Sistemas empresariales | Mesa de ayuda (8081), sistema académico (8082), MySQL en Docker |
| Desarrollo | Git, Python, PHP CLI |
| IA local | Ollama y modelo **qwen2.5:1.5b** |
| Automatización | Node.js 22 y n8n mediante systemd |
| Acceso gráfico | Navegador local y túneles SSH |

**No abras los puertos internos de las aplicaciones a Internet.** Las interfaces se utilizan desde el navegador personal mediante SSH.

## Qué debes entregar

Registra los resultados de las [pruebas de aceptación](validacion.md#pruebas-de-aceptación) en `semana-00/evidencias.md`: versiones, estado de servicios, capturas sin secretos de las tres interfaces y confirmación de que la instancia quedó detenida. No compartas archivos `.pem`, contraseñas, tokens, `.env` ni datos personales.

**Semana 0 prepara la infraestructura; no exige programar agentes.**

## Material complementario

- [Video: ingreso a AWS Academy](https://youtu.be/_bdFuZeAa_8)
- [Video: creación de EC2](https://youtu.be/MTgy0p7-ZGk)
- [AWS, SSH y túneles](aws-ec2.md)
- [Instalación detallada y comandos](instalacion.md)
- [Pruebas y solución de problemas](validacion.md)
- [Arquitectura](arquitectura.md)
- [Seguridad y operación](costos-y-seguridad.md)
- [Sistemas empresariales y APIs](../infraestructura-empresa/README.md)

Los antecedentes de las pruebas del docente se conservan **por separado** en la [bitácora técnica](../docs/bitacora-tecnica-semana-00.md).
