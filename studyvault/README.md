# StudyVault

Gestor personal de material de estudio enfocado en **inglés** y **programación competitiva**: organiza recursos por materia, mide progreso real, repasa con repetición espaciada (SM-2), integra la API de Codeforces y enriquece vocabulario con diccionario, colocaciones y frases reales.

Proyecto final de **Programación Web** (PHP OOP + MySQL/PDO + AJAX).

---

## Requisitos
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.2+
- Apache (XAMPP recomendado)
- Conexión a internet (para las APIs públicas y los CDNs de Bootstrap/Font Awesome)

## Instalación (XAMPP)
1. Copia la carpeta `studyvault/` a `C:\xampp\htdocs\studyvault`.
2. Inicia **Apache** y **MySQL** desde el panel de XAMPP.
3. Crea la base de datos y las tablas ejecutando, **en este orden**, en phpMyAdmin (o consola MySQL):
   1. `studyvault.sql`  (base + datos demo)
   2. `migrations_v2.sql`  (flashcards, metas, unidades, sesiones, etc.)
   3. `migrations_v3.sql`  (integración Codeforces)
   4. `migrations_v4.sql`  (repaso CP, plantillas, caché de catálogo)
4. Copia `.env.example` a `.env` y ajusta credenciales si tu MySQL tiene contraseña:
   ```
   DB_HOST=localhost
   DB_NAME=studyvault
   DB_USER=root
   DB_PASS=
   APP_DEBUG=false
   ```
5. Abre **http://localhost/studyvault/**

> Si la URL base difiere, ajústala en `config/config.php` (`BASE_URL`).

## Credenciales demo
| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@studyvault.com | `password` |
| Estudiante | student@studyvault.com | (ver `studyvault.sql`) |

---

## Funcionalidades (cobertura de la rúbrica)
| Requisito | Implementación |
|---|---|
| HTML5 + CSS3 responsivo | Bootstrap 5 + `assets/css/app.css` + modo oscuro |
| JavaScript + AJAX/fetch | Búsquedas en vivo, CRUD sin recargar, repaso, diccionario |
| PHP orientado a objetos | Modelos y controladores en `models/`, `controllers/` (MVC) |
| PDO + consultas preparadas | `config/database.php` + todos los modelos |
| MySQL relacional | 13+ tablas con llaves foráneas e índices |
| Sesiones + roles | Login/logout, admin/estudiante, `requireLogin/requireAdmin` |
| Seguridad | CSRF en todo POST, `password_hash`, `session_regenerate_id`, cabeceras de seguridad, rate limiting, `.htaccess` en uploads |
| Subida de archivos | PDFs/imágenes en recursos (validación de tipo y tamaño) |
| CRUD completo | Materias, recursos, unidades, metas, flashcards, problemas CP |
| Funcionalidades dinámicas | Filtros, búsqueda en vivo, cambio de estado inline, dashboard |
| Consumo de servicios web | Free Dictionary, Datamuse, Tatoeba, **Codeforces** (sin API key) |
| Bitácora de acciones | `activity_log` vía `log_activity()` |

### Módulos
- **Dashboard** — progreso por materia, tiempo de estudio, racha, metas con "pace".
- **Materias / Recursos** — CRUD, subida de archivos, búsqueda y filtros AJAX.
- **Unidades** — divide un recurso (libro) en capítulos y mide % real ("libro enorme").
- **Flashcards** — repetición espaciada SM-2; modos Clásico / Cloze / Producción; estudio por nivel CEFR; exportar a CSV/Anki.
- **Diccionario** (barra lateral) — definición + audio (Free Dictionary), colocaciones (Datamuse), frases reales (Tatoeba); "Guardar como tarjeta".
- **Competitiva** — sincroniza rating y problemas resueltos desde Codeforces, repaso SM-2 de problemas, sugerir-siguiente (i+1), calendario de concursos, plantillas/snippets.
- **Metas** — agrupa recursos con peso, progreso ponderado y ritmo hacia la fecha objetivo.
- **Temporizador** — Pomodoro con registro de tiempo, racha y heatmap.
- **Reporte semanal** — actividad de los últimos 7 días.

---

## Estructura del proyecto (MVC)
```
studyvault/
  config/      init.php (bootstrap), database.php (PDO), config.php
  models/      User, Subject, Resource, Unit, Goal, Flashcard, StudySession, CpProblem, CpTemplate
  controllers/ Auth, Subject, Resource, Unit, Goal, Timer, Report, Flashcard, Cp
  views/       auth, dashboard, subjects, resources, flashcards, cp, goals, timer, report, partials
  api/         search, resource_status, dictionary, datamuse, tatoeba
  assets/      css, js, img, uploads
  tests/       sm2_test, goal_test, units_test (php tests/<archivo>.php)
  *.sql        studyvault.sql + migrations_v2/v3/v4
```

## Pruebas
```
php tests/sm2_test.php
php tests/goal_test.php
php tests/units_test.php
```
Cubren la lógica pura crítica: algoritmo SM-2, progreso ponderado de metas + ritmo, y % de unidades.

## APIs públicas usadas (gratuitas, sin key)
- Free Dictionary (`dictionaryapi.dev`) — definición, fonética, audio.
- Datamuse (`api.datamuse.com`) — colocaciones / usos.
- Tatoeba (`tatoeba.org`) — frases de ejemplo reales.
- Codeforces (`codeforces.com/api`) — rating, problemas, concursos.

## Documentos del proyecto
- `MANUAL_USUARIO.md` — guía de uso.
- `PLAN_IMPLEMENTACION.md` — clasificación de mejoras, orden, seguimiento y bitácora.
- `MEJORAS.md` — análisis profundo y hoja de ruta.
- `PROPUESTAS_DISENO.md` — propuestas de rediseño (pendientes de aplicar).
