# Clase 01 — Construcción del núcleo verificable de un agente de mesa de ayuda

**Bloque 1 · Semana 1 de 18 · 4 horas presenciales (240 minutos) · Nivel intermedio · 100 % práctica**

> **Alcance real:** hoy se implementa y prueba el **núcleo determinista** que utilizará el futuro agente. **Todavía no hay decisiones tomadas por un LLM**: en la clase 02 se incorporará el modelo; en la 03 se formalizarán sus herramientas; en la 04 se integrará el ciclo agéntico. No presentar un menú de comandos como si fuera un agente de IA.

## 1. Problema y producto de la sesión

Una mesa de ayuda ficticia mantiene incidencias técnicas. Queremos que, al finalizar el bloque, un agente interprete solicitudes en lenguaje natural, elija herramientas autorizadas y responda a partir de datos verificables. En esta clase se construye su base segura: **catálogo validado, dos operaciones, despachador restringido, interfaz CLI y pruebas automáticas**.

**Al finalizar las cuatro horas**, el estudiante podrá ejecutar consultas reales sobre ocho incidencias sintéticas, observar errores controlados y demostrar con pruebas que una herramienta desconocida no se ejecuta. El código producido **se reutilizará** en las clases 02–05.

**Contrato de herramientas que se conservará:**

| Herramienta | Entrada | Salida | Restricción |
|---|---|---|---|
| `listar_incidencias` | `estado`: abierta, en_proceso, cerrada o todas | Lista JSON de incidencias | Solo lectura |
| `obtener_incidencia` | `id`: entero positivo | Objeto JSON o `null` | Solo lectura |

**Criterios de aceptación:** (1) catálogo de 8 registros válidos; (2) listado filtrado correcto; (3) detalle por ID correcto; (4) rechazo de estados, argumentos y herramientas no permitidos; (5) ejecución reproducible de las pruebas sin servicios externos.

## 2. Preparación y diagnóstico — 25 minutos

**Requisitos:** Python 3.10 o superior, Git, terminal Bash (Linux, macOS o WSL) y editor de código. No se necesita Ollama, una API ni conexión a Internet para ejecutar el laboratorio. Todos los datos son ficticios.

1. Crear una carpeta local para el proyecto del bloque (no modificar el repositorio docente):
   ```bash
   mkdir -p agente-mesa-ayuda/{datos,tests,evidencias/semana-01}
   cd agente-mesa-ayuda
   python3 --version
   git --version
   git init
   ```
2. Crear `.gitignore`:
   ```gitignore
   .env
   .venv/
   __pycache__/
   *.pyc
   .pytest_cache/
   ```
3. Registrar las versiones y el sistema operativo en `evidencias/semana-01/entorno.md`. Si `python3` no existe, resolver el intérprete antes de continuar. En Windows nativo, adaptar comandos Bash o utilizar WSL.

**Punto de control A:** `python3 --version` y `git status` deben ejecutarse sin error.

## 3. Construir y validar el catálogo — 35 minutos

Crear `datos/incidencias.json` **exactamente** con el siguiente conjunto sintético. El catálogo es el dato de referencia para las pruebas y para las próximas clases.

```json
[
  {"id": 101, "titulo": "Acceso al portal", "estado": "abierta", "prioridad": "alta", "categoria": "acceso"},
  {"id": 102, "titulo": "Impresora no responde", "estado": "cerrada", "prioridad": "baja", "categoria": "hardware"},
  {"id": 103, "titulo": "VPN desconectada", "estado": "abierta", "prioridad": "alta", "categoria": "red"},
  {"id": 104, "titulo": "Correo duplicado", "estado": "en_proceso", "prioridad": "media", "categoria": "correo"},
  {"id": 105, "titulo": "Cambio de contraseña", "estado": "cerrada", "prioridad": "media", "categoria": "acceso"},
  {"id": 106, "titulo": "Wi-Fi inestable", "estado": "abierta", "prioridad": "media", "categoria": "red"},
  {"id": 107, "titulo": "Monitor sin señal", "estado": "en_proceso", "prioridad": "baja", "categoria": "hardware"},
  {"id": 108, "titulo": "Cuenta bloqueada", "estado": "abierta", "prioridad": "alta", "categoria": "acceso"}
]
```

Validar sintaxis y contar registros:

```bash
python3 -m json.tool datos/incidencias.json > /dev/null
python3 -c 'import json; d=json.load(open("datos/incidencias.json")); print(len(d))'
```

**Resultado esperado:** `8`. Identificar manualmente cuántas incidencias hay por estado y prioridad. Registrar el resultado en `evidencias/semana-01/datos.md`.

**Mini-reto:** introducir deliberadamente una coma sobrante en una **copia temporal** del JSON, ejecutar el validador, guardar el mensaje de error y restaurar la copia válida. No alterar el catálogo definitivo.

**Punto de control B:** ocho registros con IDs únicos; la copia malformada falla y el original pasa.

## 4. Implementar el núcleo determinista — 55 minutos

Crear `nucleo.py` con el siguiente código. Leer cada validación, ejecutarlo y explicar por qué **no se usa `eval`, `exec` ni SQL dinámico**.

```python
"""Núcleo determinista del laboratorio 01; no invoca un modelo de IA."""
import json
from pathlib import Path

ESTADOS = {"abierta", "en_proceso", "cerrada"}
PRIORIDADES = {"alta", "media", "baja"}
CAMPOS = {"id", "titulo", "estado", "prioridad", "categoria"}

class DatosInvalidos(ValueError):
    pass

def cargar(ruta="datos/incidencias.json"):
    try:
        registros = json.loads(Path(ruta).read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as exc:
        raise DatosInvalidos(f"No se pudo leer el catálogo: {exc}") from exc
    if not isinstance(registros, list):
        raise DatosInvalidos("El catálogo debe ser una lista")
    ids = set()
    for posicion, item in enumerate(registros, 1):
        if not isinstance(item, dict) or set(item) != CAMPOS:
            raise DatosInvalidos(f"Campos incorrectos en registro {posicion}")
        if type(item["id"]) is not int or item["id"] <= 0 or item["id"] in ids:
            raise DatosInvalidos(f"ID inválido o repetido en registro {posicion}")
        ids.add(item["id"])
        if (not isinstance(item["titulo"], str) or not item["titulo"].strip()
                or not isinstance(item["categoria"], str) or not item["categoria"].strip()
                or item["estado"] not in ESTADOS
                or item["prioridad"] not in PRIORIDADES):
            raise DatosInvalidos(f"Valores inválidos en registro {posicion}")
    return registros

def listar_incidencias(estado="todas", ruta="datos/incidencias.json"):
    if estado not in ESTADOS | {"todas"}:
        raise DatosInvalidos("Estado no permitido")
    registros = cargar(ruta)
    return [r for r in registros if estado == "todas" or r["estado"] == estado]

def obtener_incidencia(identificador, ruta="datos/incidencias.json"):
    if type(identificador) is not int or identificador <= 0:
        raise DatosInvalidos("El ID debe ser un entero positivo")
    return next((r for r in cargar(ruta) if r["id"] == identificador), None)

# El LLM no puede inventar nombres de herramientas ejecutables.
HERRAMIENTAS = {"listar_incidencias", "obtener_incidencia"}

def despachar(nombre, argumentos, ruta="datos/incidencias.json"):
    if nombre not in HERRAMIENTAS or not isinstance(argumentos, dict):
        raise DatosInvalidos("Herramienta o argumentos no permitidos")
    if nombre == "listar_incidencias":
        if set(argumentos) - {"estado"}:
            raise DatosInvalidos("Argumentos no permitidos")
        return listar_incidencias(argumentos.get("estado", "todas"), ruta)
    if set(argumentos) != {"id"}:
        raise DatosInvalidos("Se requiere únicamente el argumento id")
    return obtener_incidencia(argumentos["id"], ruta)
```

**Ejercicio guiado:** ejecutar en terminal:

```bash
python3 -c 'from nucleo import listar_incidencias; print(len(listar_incidencias("abierta")))'
python3 -c 'from nucleo import obtener_incidencia; print(obtener_incidencia(103))'
python3 -c 'from nucleo import despachar; print(despachar("listar_incidencias", {"estado":"cerrada"}))'
```

**Resultados esperados:** `4` abiertas; la incidencia `103` corresponde a VPN; hay `2` cerradas. Justificar por qué el ID `999` retorna `None`, pero un estado no permitido genera una excepción.

**Punto de control C:** todas las consultas válidas funcionan y el catálogo no se modifica.

## 5. Construir interfaz CLI y comprobar errores — 35 minutos

Crear `cli.py`. La CLI representa **una interfaz de prueba**; no interpreta lenguaje natural ni toma decisiones autónomas.

```python
"""Interfaz de pruebas del núcleo; la decisión aún la toma el estudiante."""
import argparse
import json
import sys
from nucleo import DatosInvalidos, despachar

def main():
    parser = argparse.ArgumentParser(description="Consulta controlada de incidencias")
    parser.add_argument("herramienta", choices=["listar_incidencias", "obtener_incidencia"])
    parser.add_argument("--estado", default="todas")
    parser.add_argument("--id", type=int)
    args = parser.parse_args()
    try:
        if args.herramienta == "listar_incidencias":
            resultado = despachar(args.herramienta, {"estado": args.estado})
        else:
            resultado = despachar(args.herramienta, {"id": args.id})
        print(json.dumps({"estado": "ok", "resultado": resultado}, ensure_ascii=False, indent=2))
    except DatosInvalidos as exc:
        print(json.dumps({"estado": "error", "detalle": str(exc)}, ensure_ascii=False), file=sys.stderr)
        return 2
    return 0

if __name__ == "__main__":
    raise SystemExit(main())
```

Ejecutar:

```bash
python3 cli.py listar_incidencias --estado abierta
python3 cli.py obtener_incidencia --id 103
python3 cli.py obtener_incidencia --id 999
python3 cli.py listar_incidencias --estado eliminada
echo "Código de salida del último comando: $?"
```

**Resultados esperados:** lista de 4; detalle de 103; `resultado: null`; error JSON y código de salida `2` para estado inválido. La herramienta desconocida no aparece en la CLI; su rechazo se verificará directamente en el despachador.

**Ejercicio individual:** crear un documento `contrato.md` con las entradas/salidas de ambas herramientas, tres riesgos de seguridad y cinco criterios de aceptación medibles. Dibujar en Markdown el recorrido `solicitud → selección futura de herramienta → despachador → catálogo → resultado`, distinguiendo qué parte **todavía no existe**.

## 6. Pruebas automáticas y retos de extensión — 55 minutos

Crear `tests/test_nucleo.py`:

```python
import json
import tempfile
import unittest
from pathlib import Path
from nucleo import DatosInvalidos, cargar, despachar, listar_incidencias, obtener_incidencia

class PruebasLaboratorio(unittest.TestCase):
    def test_catalogo_tiene_ocho(self):
        self.assertEqual(len(cargar()), 8)

    def test_abiertas(self):
        self.assertEqual([x["id"] for x in listar_incidencias("abierta")], [101, 103, 106, 108])

    def test_detalle(self):
        self.assertEqual(obtener_incidencia(103)["categoria"], "red")

    def test_id_inexistente(self):
        self.assertIsNone(obtener_incidencia(999))

    def test_estado_invalido(self):
        with self.assertRaises(DatosInvalidos):
            listar_incidencias("eliminada")

    def test_herramienta_prohibida(self):
        with self.assertRaises(DatosInvalidos):
            despachar("borrar_incidencia", {"id": 101})

    def test_argumento_extra(self):
        with self.assertRaises(DatosInvalidos):
            despachar("obtener_incidencia", {"id": 101, "admin": True})

    def test_archivo_inexistente(self):
        with self.assertRaises(DatosInvalidos):
            cargar("datos/no-existe.json")

    def test_json_corrupto(self):
        with tempfile.TemporaryDirectory() as carpeta:
            ruta = Path(carpeta) / "roto.json"
            ruta.write_text("{", encoding="utf-8")
            with self.assertRaises(DatosInvalidos):
                cargar(ruta)

    def test_id_duplicado(self):
        with tempfile.TemporaryDirectory() as carpeta:
            ruta = Path(carpeta) / "duplicados.json"
            registros = cargar()
            registros.append(dict(registros[0]))
            ruta.write_text(json.dumps(registros), encoding="utf-8")
            with self.assertRaises(DatosInvalidos):
                cargar(ruta)

if __name__ == "__main__":
    unittest.main()
```

Ejecutar desde la raíz del proyecto:

```bash
python3 -m unittest discover -s tests -v
```

**Resultado esperado:** `Ran 10 tests` y `OK`. No copiar el resultado esperado como si fuera una ejecución real: conservar la salida de la propia terminal.

### Retos progresivos (trabajo autónomo)

1. **Nivel 1 (15 min):** agregar en `nucleo.py` una función `contar_por_estado()` que devuelva `{"abierta":4,"en_proceso":2,"cerrada":2}`, sin alterar las dos herramientas públicas. Crear un test que verifique exactamente ese resultado.
2. **Nivel 2 (15 min):** escribir dos tests nuevos: rechazo de `id=True` y rechazo de un `estado` que sea lista en vez de cadena. Si aparece un fallo inesperado, corregirlo sin debilitar las validaciones.
3. **Nivel 3 (15 min):** simular una incidencia con prioridad desconocida **en un archivo temporal**, comprobar que el cargador la rechaza y agregar el test de regresión.
4. **Cierre de retos (10 min):** explicar por escrito por qué el texto de una incidencia nunca debe convertirse directamente en una instrucción ejecutable.

**Punto de control D:** diez pruebas base exitosas y pruebas adicionales documentadas. Los retos son extensiones reales de código, no una repetición de la especificación.

## 7. Evidencias, revisión y entrega — 35 minutos

1. Crear `evidencias/semana-01/pruebas.md` con una tabla de **entrada, resultado esperado, resultado obtenido y estado** para al menos seis casos.
2. Guardar salidas reales:
   ```bash
   python3 -m unittest discover -s tests -v > evidencias/semana-01/unittest.txt 2>&1
   python3 cli.py listar_incidencias --estado abierta > evidencias/semana-01/listado.json
   ```
3. Incluir un fallo controlado y la explicación de su tratamiento; comprobar que no hay secretos ni datos reales.
4. Revisar `git diff` y registrar el avance:
   ```bash
   git add .gitignore datos/incidencias.json nucleo.py cli.py tests contrato.md evidencias/semana-01
   git commit -m "clase-01: nucleo verificable de mesa de ayuda"
   ```
5. Entregar el enlace al repositorio del estudiante y la ruta del commit. Si aún no hay remoto, conservar el commit local y documentar cómo publicarlo.

**Estructura final esperada:**

```text
agente-mesa-ayuda/
├── .gitignore
├── datos/incidencias.json
├── nucleo.py
├── cli.py
├── contrato.md
├── tests/test_nucleo.py
└── evidencias/semana-01/
    ├── entorno.md
    ├── datos.md
    ├── pruebas.md
    ├── unittest.txt
    └── listado.json
```

## 8. Verificación docente y continuidad

| Evidencia | Comprobación |
|---|---|
| Catálogo | 8 incidencias; 4 abiertas, 2 en proceso y 2 cerradas |
| Herramientas | Dos operaciones de lectura con argumentos validados |
| Seguridad | Rechazo de nombre de herramienta desconocido y argumentos extra |
| Calidad | 10 tests base y retos adicionales con resultados reales |
| Reproducibilidad | Otra persona puede clonar el proyecto y ejecutar los comandos |
| Continuidad | `nucleo.py` y `datos/incidencias.json` se reutilizan en la clase 02 |

**Distribución temporal:** preparación 25 + catálogo 35 + núcleo 55 + CLI 35 + pruebas y retos 55 + entrega 35 = **240 minutos**. Es una planificación, no una garantía de duración individual: si un grupo termina antes, ampliar la cobertura de pruebas y la revisión de casos límite; no añadir actividades irrelevantes para completar tiempo.

**Evaluación formativa sugerida:** funcionamiento 40 %, pruebas 25 %, seguridad 20 %, documentación y reproducibilidad 15 %. No se asigna nota sumativa institucional en esta semana.

**Límite explícito:** esta clase demuestra un **sistema determinista de herramientas**, no un agente IA. La clase 02 integrará un LLM para interpretar solicitudes; las clases 03 y 04 permitirán selección de herramientas y ciclo agéntico bajo controles.
