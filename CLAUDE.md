# Reglas del proyecto — StudyVault

Reglas para trabajar en este repositorio y **maximizar la calidad** de los resultados.
Este archivo lo lee Claude Code automáticamente; trátalo como instrucciones obligatorias.

---

## 0. Regla principal — Graphify al final de cada petición

> **Al terminar CADA petición que modifique el código, ejecutar `/graphify`** para
> regenerar el grafo de conocimiento del proyecto (`studyvault/graphify-out/`).

- Mantiene un mapa siempre actualizado de la arquitectura (controladores ↔ modelos ↔ vistas ↔ rutas).
- Sirve como documentación viva para el entregable y para detectar acoplamiento antes de refactorizar.
- Si una petición fue solo de lectura/consulta (sin cambios de código), no es necesario.
- Cómo iniciarlo: ver §6.

---

## 1. Flujo de trabajo Git

- **Rama de trabajo: `develop`.** Nunca commitear directo a `main`.
- `main` solo recibe merges estables desde `develop` (vía PR cuando sea posible).
- Commits pequeños y descriptivos, en español, enfocados en el "por qué".
- **No** hacer push forzado a `main`. **No** saltar hooks (`--no-verify`).
- Antes de cerrar una tarea: actualizar `studyvault/PLAN_IMPLEMENTACION.md`
  (casilla `[x]` + entrada en la bitácora §G con archivos, decisiones y cómo revertir).

---

## 2. Cuándo usar cada skill (para máxima calidad)

### Proceso / cómo abordar el trabajo
- **`superpowers:brainstorming`** → ANTES de cualquier feature nueva o cambio de comportamiento. Explorar intención y diseño antes de tocar código.
- **`superpowers:writing-plans`** → cuando hay una spec/tarea multi-paso. Escribir el plan antes del código.
- **`superpowers:executing-plans`** / **`subagent-driven-development`** → al ejecutar un plan ya escrito.
- **`superpowers:systematic-debugging`** → ante CUALQUIER bug, fallo de test o comportamiento inesperado, antes de proponer arreglos.
- **`superpowers:test-driven-development`** → al implementar lógica con reglas claras. Obligatorio para lógica pura crítica: **motor SM-2** (`Flashcard::review`) y parseo de la API de Codeforces.

### Verificación y revisión (antes de dar algo por terminado)
- **`superpowers:verification-before-completion`** → SIEMPRE antes de afirmar que algo "funciona". Correr comandos y mostrar evidencia (lint, tests, prueba HTTP/BD).
- **`/code-review`** → revisar el diff actual buscando bugs de correctitud antes de mergear a `develop`.
- **`/security-review`** → OBLIGATORIO antes de mergear a `main` y siempre que se toque autenticación, CSRF, sesiones, subida de archivos o consultas SQL. (Este proyecto se califica en seguridad.)
- **`superpowers:requesting-code-review`** / **`receiving-code-review`** → al completar features grandes; verificar con rigor, no por cumplir.
- **`/verify`** o **`/run`** → levantar la app y probar el cambio en navegador antes de cerrar (no basta con tests).

### Cierre de rama
- **`superpowers:finishing-a-development-branch`** → cuando la implementación está completa y todo pasa, para decidir merge/PR/limpieza.

### Conocimiento del código
- **`/graphify`** → mapa del código (ver §0 y §6).

---

## 3. Orden de prioridad de skills cuando varias aplican
1. Proceso primero (brainstorming, debugging) — definen *cómo* abordar.
2. Implementación después (TDD, etc.).
3. Verificación y revisión al final, antes de cerrar/mergear.

Ejemplo: "agregar X" → brainstorming → plan → TDD → verificación → code-review → (graphify) → commit en `develop`.

---

## 4. Reglas de código del proyecto

- **Bootstrap único:** todo punto de entrada incluye `config/init.php` primero (sesión, CSRF, errores, autoload, helpers).
- **CSRF obligatorio:** todo formulario lleva `csrf_field()`; todo `fetch` POST envía `CSRF_TOKEN`. Sin esto → 403.
- **PDO con consultas preparadas SIEMPRE.** Nunca concatenar entrada del usuario en SQL.
- **Autorización por dueño:** toda consulta filtra por `user_id`. Verificar pertenencia en cada endpoint.
- **Escapar salida** con `htmlspecialchars()`.
- **Credenciales solo en `.env`** (nunca hardcodear; `.env` está en `.gitignore`).
- **Cambios de esquema:** archivo `migrations_vN.sql` incremental + aplicarlo y documentarlo en la bitácora. No editar migraciones ya aplicadas.
- **Soft delete** (`deleted_at`) en datos del usuario; filtrar `deleted_at IS NULL`.
- **Repetición espaciada:** reutilizar el motor SM-2 (deck `vocab`/`cp`); no duplicar el algoritmo.
- No introducir dependencias ni abstracciones que la tarea no necesite.

---

## 5. Convenciones generales
- Idioma de la UI y comentarios útiles: español. Identificadores en código: inglés/español consistente con lo existente.
- Comentar solo el "por qué" no obvio, no el "qué".
- Mantener el estilo MVC actual (`models/`, `controllers/`, `views/`, `api/`).

---

## 6. Cómo iniciar / actualizar Graphify

1. En Claude Code, escribe el comando: **`/graphify`**
2. Apúntalo al código del proyecto (carpeta `studyvault/`).
3. Genera `studyvault/graphify-out/` con: HTML interactivo, JSON del grafo y un reporte de auditoría.
4. Para **actualizar**: vuelve a ejecutar `/graphify` (regla §0, al final de cada petición con cambios).
5. Recomendación: hazlo en una sesión/turno dedicado, ya que consume tiempo y tokens.

> El primer `/graphify` establece la línea base. A partir de ahí, cada actualización refleja los cambios nuevos.
