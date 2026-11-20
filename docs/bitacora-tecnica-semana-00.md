# Bitácora técnica del docente — Semana 0

**Documento interno de referencia.** No forma parte de las instrucciones secuenciales que debe ejecutar el estudiante. Las observaciones corresponden a una EC2 de prueba del **20 de noviembre de 2026** y no garantizan idénticos resultados en otras instancias.

## Entorno utilizado
- Ubuntu Server 24.04 LTS en AWS EC2.
- Instancia de referencia: **t2.large, 8 GiB RAM**. **No** constituye una recomendación definitiva para estudiantes.
- Docker Engine/Compose, MySQL y dos aplicaciones empresariales PHP/Apache; Ollama y n8n instalados directamente en Ubuntu.
- Ollama 0.40.16; modelo `qwen2.5:1.5b` con cuantización Q4_K_M, aproximadamente 986 MB de descarga.
- Node.js 22.23.5, npm 10.9.9, n8n 2.35.7.

## Pruebas y hallazgos

### Sistemas empresariales
Se comprobó el arranque de Docker/MySQL, las interfaces de mesa de ayuda y sistema académico a través de túneles SSH, y la ejecución satisfactoria de `bash probar.sh`. Se presentó un problema de permisos al montar `db/init.local.sql` en MySQL (`Permission denied`). Se ajustaron permisos mediante `preparar.sh`. En el primer despliegue de referencia fue necesario reinicializar un volumen descartable. **No recomendar `docker compose down -v` a estudiantes con datos existentes.**

### Ollama
El servicio respondió en `127.0.0.1:11434`; se descargó el modelo y se obtuvo una respuesta de inferencia en español. `ollama show` declaró capacidad `tools`. Una primera solicitud de herramientas devolvió contenido vacío sin `tool_calls`. Una segunda, con instrucción explícita y temperatura cero, devolvió `consultar_tickets` con argumentos vacíos. **No se ejecutó realmente la API de tickets mediante el agente**: solo se verificó selección de herramienta.

### n8n
La instalación con npm finalizó, pese a advertencias de paquetes obsoletos. Se creó la cuenta propietaria y se accedió a la interfaz por túnel SSH. Se configuró `n8n.service` con usuario `ubuntu` y escucha en `127.0.0.1:5678`.

Se detectó un conflicto: la instancia manual de n8n seguía ocupando el puerto 5678; el servicio `systemd` reiniciaba y mostraba `status=1/FAILURE`. Tras detener el proceso manual y reiniciar el servicio, se observó `active (running)` y respuesta HTTP 200. Apareció el aviso `Unknown credential name confluenceCloudOAuth2Api`, que no impidió el arranque final. También se observó una advertencia sobre Python task runner sin entorno virtual; no se verificó su funcionalidad.

## Decisiones que permanecen abiertas
1. Tipo EC2 y tamaño EBS compatibles con presupuesto máximo de USD 50 por estudiante.
2. Modelo local definitivo y pruebas de rendimiento/concurrencia.
3. Acceso y credenciales para API LLM externa y correo de prueba.
4. Prueba integral del agente con ejecución efectiva de herramientas sobre APIs empresariales.

## Separación documental
- [Guía del estudiante](../semana-00-laboratorio/README.md): pasos, comandos, resultados esperados y entrega.
- [Instalación](../semana-00-laboratorio/instalacion.md): procedimiento reproducible.
- [Validación](../semana-00-laboratorio/validacion.md): criterios y resolución de problemas, sin narración histórica.
- **Esta bitácora**: fechas, versiones observadas, incidencias y decisiones pendientes.
