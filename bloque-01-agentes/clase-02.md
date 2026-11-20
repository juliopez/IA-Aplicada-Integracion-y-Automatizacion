# Clase 02 — Cliente Python para Ollama

**Semana 2/18 · 4 horas · Nivel intermedio · 100 % práctica**  
**Bloque:** 1: agentes

## Resultado de aprendizaje verificable

Crear un cliente Python que solicita al modelo planes JSON y valida su estructura.

## Requisitos y preparación (30 minutos)

Continuar el trabajo de la clase anterior; conservar archivos y datos validados. Comprobar instalación y versiones de las herramientas; usar exclusivamente datos ficticios y cuentas de laboratorio. No publicar secretos, datos privados ni archivos .env. Registrar el estado inicial del proyecto.

## Laboratorio guiado (80 minutos)

1. Crear la estructura de archivos y una rama o commit de inicio.
2. Enviar tres prompts al modelo; validar objetivo, accion y argumentos; manejar timeout y JSON inválido.
3. Integrar el componente con los desarrollados en semanas anteriores del mismo bloque.
4. Ejecutar el caso normal y registrar la entrada y la salida reales.

### Punto de partida técnico

```bash
python -m pip install requests
ollama list
# Implementar POST a http://127.0.0.1:11434/api/chat
# JSON: {"model":"MODELO_INSTALADO","stream":false,"format":"json","messages":[{"role":"user","content":"Clasifica la incidencia"}]}
```

**Importante:** los comentarios y pseudocódigo indican componentes que el estudiante debe implementar; no son una solución terminada. Ajustar nombres de modelo, rutas y credenciales al entorno local.

## Ejercicio autónomo (90 minutos)

**Desafío:** Enviar tres prompts al modelo; validar objetivo, accion y argumentos; manejar timeout y JSON inválido.

Completar los elementos que falten, integrar la solución y ejecutar como mínimo estos ensayos:

| Caso | Acción | Resultado esperado |
|---|---|---|
| Normal | Ejecutar entrada sintética válida | Salida correcta, reproducible y registrada |
| Entrada inválida | Omitir parámetro o usar valor no permitido | Error controlado sin alterar datos |
| Dependencia no disponible | Detener temporalmente el servicio dependiente en laboratorio | Error explícito y recuperación documentada |
| Seguridad | Solicitar operación fuera de permisos | Denegación verificable y registro |

En operaciones con efectos externos, exigir confirmación y registrar el resultado. Para correos, mantener aprobación humana antes del envío.

## Validación, evidencias y entrega (40 minutos)

1. Ejecutar nuevamente los casos normal y adverso; registrar salidas reales, no simuladas.
2. Comparar resultados con el estado de referencia (JSON, SQL o bitácora, según el bloque).
3. Crear `evidencias/semana-02/` con `pruebas.md`, capturas depuradas y pasos de reproducción.
4. Documentar al menos un error encontrado, su causa y la corrección aplicada.
5. Realizar commit: `clase-02: cliente python para ollama`.

**Entregable:** código o flujo funcional de esta etapa, evidencia verificable y documentación de reproducción. En las semanas 5, 13 y 18 se entrega además el producto integrador del bloque con defensa técnica.

## Lista de comprobación

- [ ] Funcionalidad implementada y ejecutada en laboratorio.
- [ ] Prueba positiva y negativa documentadas.
- [ ] Permisos mínimos y secretos protegidos.
- [ ] Salidas verificadas contra datos de referencia.
- [ ] Código y evidencias versionados en GitHub.

## Criterios formativos

**Funcionamiento (40 %), integración incremental (20 %), seguridad y errores (20 %), evidencia reproducible (20 %).** Ponderación orientativa para retroalimentación de esta práctica; las evaluaciones sumativas institucionales permanecen pendientes de aprobación.
