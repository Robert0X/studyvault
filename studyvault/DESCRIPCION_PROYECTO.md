# StudyVault — Descripción integral del proyecto

Documento de referencia completo: qué es, qué hace hoy, qué se planea, para quién, sus ventajas, el panorama competitivo y todos los detalles técnicos necesarios para que un desarrollador profesional evalúe el proyecto y su construcción.

---

## 1. Resumen ejecutivo
**StudyVault** es una aplicación web full‑stack para **organizar material de estudio y estudiar mejor**, con foco en dos dominios: **inglés** y **programación competitiva**. A diferencia de un simple gestor de marcadores, mide **progreso real** (no checklists), aplica **repetición espaciada (SM‑2)**, integra automáticamente el **rating y los problemas de Codeforces**, y enriquece el vocabulario con **definición + colocaciones + frases reales** desde APIs públicas. Está construido con **PHP orientado a objetos (MVC), MySQL/PDO, AJAX y Bootstrap 5**, con énfasis en seguridad y código testeable.

---

## 2. Público objetivo
- **Primario:** estudiantes autodirigidos que aprenden **inglés** (con marco CEFR A1–C2) y/o practican **programación competitiva** (Codeforces).
- **Secundario:** cualquier estudiante que maneje **planes de estudio multi‑recurso** (varios libros, cursos en video, problem sets) y quiera medir avance hacia una meta con fecha.
- **Perfiles de uso:**
  - *"Quiero llegar a B2 de inglés en diciembre"* → metas con recursos ponderados + vocabulario en contexto + repaso espaciado.
  - *"Quiero subir de Pupil a Specialist en Codeforces"* → sincronización automática, repaso de problemas difíciles y sugerencias a su nivel.
  - *"Estoy leyendo un libro de 400 páginas"* → divide en unidades y ve el % real.

---

## 3. Problema que resuelve y propuesta de valor
La mayoría de estudiantes acumulan material disperso (libros, links, problemas) y lo gestionan con marcadores, hojas de cálculo y apps separadas. Marcar algo como "hecho" **no mide aprendizaje** ni combate el olvido. StudyVault unifica:
1. **Gestión** de recursos por materia.
2. **Progreso real**: granular (unidades), por tiempo (Pomodoro/racha) y hacia metas con **ritmo** (¿voy a tiempo?).
3. **Retención**: repetición espaciada SM‑2 para vocabulario y problemas.
4. **Automatización**: progreso objetivo de CP vía API de Codeforces.
5. **Aprendizaje en contexto**: vocabulario con colocaciones y ejemplos reales, no solo traducción.

---

## 4. Ventajas principales (diferenciadores)
- **Progreso medible y honesto** (unidades + tiempo + retención + rating), no auto‑declarado.
- **Aprendizaje basado en evidencia**: SM‑2, cloze/producción, colocaciones, práctica i+1.
- **Todo‑en‑uno**: reemplaza la combinación Anki + hoja de cálculo + marcadores + seguimiento manual de CP.
- **Cero costo y sin llaves**: todas las APIs usadas son públicas y gratuitas, sin registro.
- **Automático en CP**: importa lo que resolviste y tu rating sin captura manual.
- **Construcción sólida**: MVC, PDO preparado, seguridad (CSRF, hashing, cabeceras, rate limiting), pruebas de la lógica crítica.

---

## 5. Plataformas similares (análisis competitivo)
| Plataforma | Qué hace | Qué le falta vs StudyVault |
|---|---|---|
| **Anki** | Repetición espaciada (SM‑2) | No gestiona recursos, ni progreso de libros, ni CP; curva de entrada alta |
| **Quizlet** | Flashcards sencillas | SRS básico, sin contexto/colocaciones, sin CP ni metas |
| **Duolingo** | Inglés gamificado | No estudias TU material; no integra tus libros/recursos |
| **Notion / Obsidian** | Notas y organización | Sin SRS real, sin integración CP, sin medición de retención |
| **Codeforces / LeetCode / CSES** | Práctica de problemas | No gestionan tu estudio personal ni el repaso espaciado |
| **A2OJ / roadmaps CP** | Listas de problemas estáticas | Estáticos, sin progreso personal ni repaso |

**Nicho de StudyVault:** combinar *gestión de recursos + repetición espaciada + progreso real + automatización de CP + vocabulario en contexto* en una sola herramienta personal.

---

## 6. Capacidades y funciones (detalle por módulo)

### 6.1 Autenticación, sesiones y seguridad
- Registro, inicio y cierre de sesión; contraseñas con `password_hash`/`password_verify`.
- **Roles**: administrador y estudiante; rutas protegidas (`requireLogin`, `requireAdmin`).
- `session_regenerate_id(true)` al autenticar; cookies `HttpOnly` + `SameSite=Lax` (+ `Secure` en HTTPS).
- **CSRF** en todas las peticiones POST (token por sesión, helper `csrf_field()`/`csrf_verify()`).
- **Cabeceras de seguridad**: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`.
- **Rate limiting** por sesión en los proxies a APIs externas.
- **`.htaccess`** en `assets/uploads/` que desactiva la ejecución de PHP (defensa ante subidas maliciosas).
- Sanitización de salida con `htmlspecialchars`; consultas siempre preparadas (anti‑inyección).
- Credenciales fuera del código, en `.env`.

### 6.2 Materias
- CRUD completo vía AJAX (crear/editar/eliminar sin recargar), con nombre, descripción, ícono y color.
- Estadísticas por materia (totales y % completado).

### 6.3 Recursos
- CRUD de recursos por materia; tipos: **enlace, PDF/archivo, nota, video**.
- **Subida de archivos** (PDF/imagen) con validación de tipo MIME y tamaño (máx. 5 MB).
- **Búsqueda y filtros en vivo** (AJAX) por texto, materia, tipo y estado.
- Cambio de **estado** (pendiente / en progreso / completado) inline sin recargar.

### 6.4 Unidades (progreso granular — "el libro enorme")
- Divide un recurso en **unidades/capítulos**; alta individual o **generación masiva** ("Capítulo 1..N").
- Estado por unidad y **% real** del recurso calculado a partir de unidades completadas.
- Rango de páginas opcional por unidad; sincroniza `resources.total_units`.

### 6.5 Flashcards con repetición espaciada
- Tarjetas con anverso, reverso, ejemplo, "extra" (fonética/colocaciones), mazo (`vocab`/`cp`) y **nivel CEFR**.
- **Algoritmo SM‑2**: cola diaria de repaso según `due_date`, factor de facilidad, intervalos y estados (nueva/aprendiendo/madura/dominada).
- **Modos de estudio**: **Clásico**, **Cloze** (oculta la palabra en la frase) y **Producción** (escribir la palabra a partir del significado, con verificación). Atajos de teclado (Espacio voltea, 1–4 califican).
- **Estudio por nivel** CEFR (A1–C2).
- **Exportación a CSV/Anki** (compatible con la importación de Anki).
- Creación manual o **desde el diccionario** ("Guardar como tarjeta").
- Estadísticas: total, por estado, vencidas hoy.

### 6.6 Diccionario de inglés enriquecido (barra lateral)
- **Free Dictionary API**: definición, fonética y **audio** de pronunciación.
- **Datamuse API**: **colocaciones** ("se usa con…") para aprender uso real, no traducción aislada.
- **Tatoeba**: **frases de ejemplo reales** en contexto (i+1).
- **Caché** local de definiciones (`dictionary_cache`, 30 días) para velocidad y menos llamadas.
- Botón para guardar la palabra como flashcard con todo el contexto.

### 6.7 Programación competitiva (integración Codeforces)
- Vinculación de **handle**; **sincronización** automática del **rating** y de los **problemas resueltos** (API `user.info` + `user.status`, dedup por problema).
- **Tracking** de problemas con estados `todo/attempted/solved/upsolved` y **nota editorial** (idea clave).
- **Análisis de debilidades**: conteo de resueltos por **tag**.
- **Repaso espaciado de problemas**: reutiliza el motor **SM‑2** (programar repaso, cola y calificación).
- **Sugerir‑siguiente (i+1)**: descarga el catálogo (`problemset.problems`) a caché y propone problemas no resueltos en tu rating **+100/+300**.
- **Calendario** de próximos concursos (`contest.list`).
- **Biblioteca de plantillas/snippets** de código (CRUD + copiar al portapapeles).

### 6.8 Metas / planes de estudio
- Metas con descripción y **fecha objetivo**; estados activa/pausada/terminada.
- Asociación de recursos con **peso**; **progreso ponderado** del conjunto.
- **Ritmo (pace)**: compara avance real vs esperado según la fecha → "En camino / Atrasado / Vencida" y días restantes.

### 6.9 Temporizador y tiempo de estudio
- **Pomodoro 25/5** con cuenta regresiva; al completar, registra la **sesión** (minutos reales).
- Registro manual de minutos; selección del recurso estudiado.
- Métricas: minutos de hoy y de los últimos 7 días, **racha** de días consecutivos y **heatmap** de 84 días.

### 6.10 Dashboard y reporte
- **Dashboard**: progreso por materia, tiempo (hoy/semana), racha, metas con pace, recursos recientes, tarjetas por repasar.
- **Reporte semanal**: tiempo vs semana previa, tarjetas repasadas, problemas resueltos, racha y estado de metas.

### 6.11 Transversales
- **Modo oscuro** persistente (localStorage).
- **Bitácora de actividad** (`activity_log`) vía `log_activity()`.
- **Cachés** de diccionario y de catálogo de Codeforces.
- Manejo de errores centralizado con log a archivo y `APP_DEBUG` configurable.

---

## 7. Funciones planeadas (roadmap)
> Detalle, prioridad, dependencias y bitácora en `PLAN_IMPLEMENTACION.md` y `MEJORAS.md`.

- **Inglés (B5):** importar mazos Anki, **modo escucha** (audio→escribir), **banco CEFR completo** (tablas `word_bank`/`word_enrichment` ya creadas), diario de escritura, estadísticas de retención por nivel.
- **Hábitos (B6):** recordatorios (web push / email), meta diaria de estudio.
- **Compartir / comunidad (B7):** plantillas de planes **públicas** y clonables, onboarding con plantillas iniciales, perfiles públicos, gamificación (XP/insignias), leaderboards.
- **Escala y robustez (B8):** paginación, recuperación de contraseña, verificación de email, búsqueda full‑text en notas, papelera (soft‑delete con UI), **API REST** propia, **PWA/móvil**, i18n, Docker, más pruebas automatizadas.
- **Diseño:** rediseño visual (paleta, tipografía, tokens, modo foco de estudio, toasts) — propuestas en `PROPUESTAS_DISENO.md`.

---

## 8. Arquitectura técnica
- **Patrón MVC** sin framework, enrutado por front controller (`index.php`) con `match()` sobre `?page=` + `_action`.
- **Bootstrap central** (`config/init.php`): carga `.env`, configura sesión segura, manejo de errores + log, **autoloader** de `models/` y `controllers/`, helpers (`csrf_*`, `json_response`, `requireLogin/Admin`, `rate_limit`, `log_activity`) y cabeceras de seguridad.
- **Capa de datos**: `Database` (singleton PDO con `ERRMODE_EXCEPTION`, `EMULATE_PREPARES=false`).
- **Modelos**: encapsulan SQL preparado y lógica de dominio (incluida la lógica pura testeable: `Flashcard::sm2`, `Goal::weightedProgress`/`pace`, `Unit::progressPct`).
- **Controladores**: validan, aplican CSRF, orquestan modelos y responden HTML (vistas) o JSON (endpoints AJAX).
- **Vistas**: PHP + Bootstrap, con partials (`header`/`footer`) y JS embebido para AJAX.
- **Flujo de una petición**: `index.php` → `init.php` (sesión/CSRF/autoload) → `match` ruta → controlador → modelo (PDO) → vista/JSON.

---

## 9. Modelo de datos (MySQL/MariaDB)
Tablas (15) con integridad referencial e índices:
- **Núcleo:** `users`, `subjects`, `resources`, `units`.
- **Metas:** `goals`, `goal_resources` (N:M con `weight`).
- **Estudio:** `flashcards` (SM‑2, genérica vocab/cp), `study_sessions`.
- **Competitiva:** `cp_problems` (con SM‑2), `cp_templates`, `cf_problemset_cache`.
- **Inglés (preparadas):** `word_bank`, `word_enrichment`.
- **Soporte:** `activity_log`, `dictionary_cache`.

Relaciones clave: `users 1—N {subjects, resources, goals, flashcards, cp_problems, study_sessions}`; `subjects 1—N resources`; `resources 1—N units`; `resources N—M goals` (vía `goal_resources`). FKs con `ON DELETE CASCADE`; índices en FKs y en `due_date`/`due_review`. Progreso granular en `resources` (`total_units`, `unit_type`) y soft‑delete (`deleted_at`) en entidades de usuario.

Instalación: `studyvault.sql` + `migrations_v2/v3/v4.sql`, o el consolidado `ENTREGA/studyvault_completo.sql`.

---

## 10. Stack tecnológico
- **Backend:** PHP 8 (OOP, MVC), PDO con consultas preparadas.
- **Base de datos:** MySQL 5.7+ / MariaDB 10.2+.
- **Frontend:** HTML5, CSS3, **Bootstrap 5**, **Font Awesome 6**, JavaScript vanilla (`fetch`/AJAX), `localStorage` (modo oscuro). DataTables y jQuery incluidos para tablas.
- **Servidor:** Apache (XAMPP).
- **Algoritmo:** **SM‑2** (SuperMemo) para repetición espaciada.
- **Herramientas de proyecto:** Git/GitHub (repo privado, rama `develop`), **graphify** (grafo de conocimiento del código en `graphify-out/`), arnés de pruebas en PHP plano (`tests/`).

---

## 11. APIs externas integradas (gratuitas, sin API key)
| API | Uso | Endpoint |
|---|---|---|
| Free Dictionary | Definición, fonética, audio | `dictionaryapi.dev` |
| Datamuse | Colocaciones / usos | `api.datamuse.com` |
| Tatoeba | Frases de ejemplo reales | `tatoeba.org/.../api_v0/search` |
| Codeforces | Rating, problemas, catálogo, concursos | `codeforces.com/api` |

Todas se consumen vía **proxy PHP** (evita CORS, permite caché y rate limiting).

---

## 12. Seguridad (resumen para evaluación)
CSRF en todo POST · `password_hash`/`verify` · `session_regenerate_id` + cookies endurecidas · cabeceras de seguridad · rate limiting · `.htaccess` anti‑ejecución en uploads · consultas preparadas (anti‑SQLi) · escape de salida (anti‑XSS) · autorización por dueño (`user_id`) en cada consulta · credenciales en `.env` · `APP_DEBUG` controla exposición de errores.

---

## 13. Algoritmo SM‑2 (repetición espaciada)
Función pura `Flashcard::sm2($ease, $reps, $interval, $quality)`: ante una calificación (0–5, mapeada a 4 botones), recalcula intervalo (1, 6, luego `intervalo×ease`), repeticiones, factor de facilidad (piso 1.3) y estado de madurez; fija la próxima fecha de repaso. Se **reutiliza** para flashcards y para el repaso de problemas de CP. Cubierto por pruebas (`tests/sm2_test.php`).

---

## 14. Calidad y verificación
- **Pruebas** de la lógica pura crítica: `sm2_test.php` (SM‑2), `goal_test.php` (progreso ponderado + pace), `units_test.php` (% de unidades). Ejecutar con `php tests/<archivo>.php`.
- **Verificación** continua durante el desarrollo: lint (`php -l`), pruebas de integración contra BD y smoke HTTP (rutas/CSRF/sin fatales).
- **Trazabilidad:** `PLAN_IMPLEMENTACION.md` con casillas de avance y **bitácora** por cambio (qué se hizo, archivos, cómo probar, cómo revertir).
- **Grafo del código** (graphify) para inspeccionar arquitectura y acoplamiento.

---

## 15. Estructura de archivos
```
studyvault/
  config/      init.php, database.php, config.php
  models/      User, Subject, Resource, Unit, Goal, Flashcard, StudySession, CpProblem, CpTemplate
  controllers/ Auth, Subject, Resource, Unit, Goal, Timer, Report, Flashcard, Cp
  views/       auth, dashboard, subjects, resources, flashcards, cp, goals, timer, report, partials
  api/         search, resource_status, dictionary, datamuse, tatoeba
  assets/      css, js, img, uploads (.htaccess)
  tests/       sm2_test, goal_test, units_test
  *.sql        studyvault.sql + migrations_v2/v3/v4
  README.md · MANUAL_USUARIO.md · PLAN_IMPLEMENTACION.md · MEJORAS.md · PROPUESTAS_DISENO.md
```

---

## 16. Cómo instalar y evaluar
Ver `README.md` (instalación) y `../ENTREGA/GUIA_ENTREGA.md` (guía de pruebas + SQL consolidado en un solo archivo). Credenciales demo: `admin@studyvault.com` / `password`.

---

## 17. Limitaciones conocidas
- Sin paginación aún (listas cargan completas) — planeado.
- Las APIs externas requieren conexión a internet; sin red, esas funciones degradan con mensaje controlado.
- Modo escucha, import Anki y banco CEFR completo: pendientes (estructura lista).
- Sin tests de UI automatizados (la verificación visual es manual).

---

## 18. Conclusión
StudyVault es una aplicación de estudio **funcional, segura y bien estructurada** que va más allá de un gestor de recursos: mide progreso real y favorece la retención con técnicas de eficacia probada, integrando automatización (Codeforces) y aprendizaje en contexto (inglés). Su arquitectura MVC, el uso de PDO preparado, la cobertura de seguridad y las pruebas de la lógica crítica la hacen evaluable y extensible; el roadmap documentado muestra un camino claro de evolución hacia un producto multiusuario.
