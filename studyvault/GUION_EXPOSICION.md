# StudyVault — Guion de exposición

> Documento de estudio + guion para la presentación final.
> **Duración objetivo:** 10–15 minutos. Adapta el ritmo según la audiencia.

---

## 0. Antes de empezar (checklist 2 minutos)

- [ ] **XAMPP** con Apache y MySQL en verde.
- [ ] BD reseteada con datos demo: importar `ENTREGA/studyvault_completo.sql`.
- [ ] Abrir `http://localhost/studyvault/` en el navegador.
- [ ] Iniciar sesión con **admin@studyvault.com / password** (todos los datos demo están en esta cuenta).
- [ ] Cerrar pestañas innecesarias. Tener el VS Code abierto en `studyvault/` por si te piden ver código.
- [ ] Tener `descripcion.md` (la rúbrica) abierta en otra pestaña para referenciar puntos.

---

## 1. Mini guion de apertura (≈ 1 min)

> "Buenas tardes. Mi proyecto se llama **StudyVault**, un **gestor de material de estudio** orientado a quienes aprenden por su cuenta — en mi caso, inglés y programación competitiva — y quieren ver **avance real**, no sólo una lista de PDFs guardados.
>
> El problema que resuelve es muy concreto: los estudiantes autodidactas usamos 4 o 5 apps distintas (Anki para vocabulario, Codeforces para CP, Drive para PDFs, Notion para apuntes), y nunca tenemos una visión unificada de cuánto hemos estudiado, qué tenemos pendiente, ni si vamos a tiempo de cumplir una meta. StudyVault concentra todo eso, mide el tiempo real de estudio, aplica el algoritmo SM-2 de **repetición espaciada** para memorización, y se conecta con la **API pública de Codeforces** para traer el progreso competitivo automáticamente.
>
> El proyecto cumple las 10 especificaciones obligatorias de la descripción y además tres mejoras opcionales: tema oscuro, recuperación de contraseña y gráficas estadísticas. Está construido con **PHP orientado a objetos, MySQL con PDO preparado, AJAX/fetch, Bootstrap 5 y arquitectura MVC**."

---

## 2. Recorrido funcional para la demo (≈ 8–10 min)

> **Regla de oro:** cada vez que muestres una pantalla, di en voz alta **qué requisito de la rúbrica cubre**. Eso es lo que califican.

### 2.1 Login y roles (req. 5)
1. Mostrar pantalla de login. *"Login con sesión segura: contraseña con `password_hash`, CSRF token, throttling tras varios fallos."*
2. Iniciar como **admin** → entra al Dashboard.
3. Señalar **badge "Admin"** en navbar y el ítem **"Admin"** que sólo le aparece a este rol.
   - Decir: *"Roles diferenciados (req. 5): admin tiene panel propio; estudiante no lo ve."*

### 2.2 Dashboard (req. 6 — Dashboard)
- Mostrar las 4 tarjetas de stats + tiempo de estudio + racha + metas + recursos recientes.
- *"El dashboard es el resumen de progreso real: minutos de hoy, racha de días seguidos, % de metas y materias."*

### 2.3 Materias y Recursos (req. 6 — CRUD + paginación + subida de archivos)
- Ir a **Materias**: CRUD con icono y color.
- Ir a **Recursos**: ya hay 9 cargados.
  - *"Listado con **paginación** server-side de 10 en 10."*
  - Hacer una **búsqueda en vivo** (escribe `english` o `algoritmos`) → la tabla se filtra sin recargar vía AJAX.
  - **Filtrar por materia** → mismo efecto.
  - *"Búsquedas y filtros dinámicos (req. 6 y req. 7 AJAX)."*
- Hacer click en el encabezado **"Recurso"** o **"Materia"** → la tabla se ordena.
  - *"**Ordenamiento por columna** con DataTables (req. 6)."*
- Mostrar el botón **"Nuevo recurso"** y el campo de subida de PDF/imagen.
  - *"Subida de archivos con validación de tipo y tamaño (req. 6)."*
- Click en **"Unidades"** de un recurso (ej. Murphy's Grammar) → 10 capítulos, 3 completados.
  - *"Progreso granular: un libro grande no es uno-o-cero, sino N capítulos. La barra de progreso se actualiza sin recargar."*

### 2.4 Diccionario y Flashcards — APIs externas + SM-2 (req. 7, 8)
- Sidebar → **Diccionario EN**: escribe `although` → consulta a **Free Dictionary API** + **Datamuse** (colocaciones) + **Tatoeba** (frases reales).
  - *"Consumo de 3 APIs públicas con proxy PHP que cachea respuestas para evitar rate limits (req. 8)."*
- Botón **"Guardar como tarjeta"** → crea flashcard sin recargar (req. 7 AJAX).
- Ir a **Flashcards** → hay ~15 tarjetas con niveles A1–C2 y estados varios.
- Click **Estudiar** → modo Clásico. Mostrar volteo + botones "Otra vez / Difícil / Bien / Fácil".
  - *"Algoritmo **SM-2** de repetición espaciada — el mismo de Anki. La calidad de la respuesta ajusta el ease factor y calcula la próxima fecha. Implementado como función pura testeada."*
- Cambiar a modo **Cloze** o **Producción** → muestra cómo se ocultan partes para reforzar el uso, no solo el reconocimiento.
- Click encabezado de columna → **ordenamiento** (DataTables aquí también).
- Botones **Exportar** e **Importar CSV** (compatible con Anki).

### 2.5 Programación Competitiva — API Codeforces (req. 8)
- Ir a **Competitiva**. Mostrar el handle `tourist` y rating `3779` (precargado).
- Mostrar la tabla con 22 problemas (algunos solved, attempted, todo, upsolved).
- *"Sincronización automática: el botón llama al endpoint `user.info` y `user.status` de Codeforces, inserta los problemas resueltos con `INSERT...ON DUPLICATE KEY UPDATE` para evitar duplicados."*
- Mostrar **"Resueltos por tema"** (panel derecho): barra horizontal con dp, graphs, math, greedy.
- Mostrar **"Practica (tu nivel +100/+300)"**: sugiere 6 problemas usando el catálogo cacheado y tu rating.
- Click **Repasar** (badge 1) → entra al modo SM-2 de problemas (mismo motor reutilizado).
- Mostrar **Plantillas** → snippets de código con botón "Copiar".

### 2.6 Temporizador y Metas (req. 6)
- Ir a **Temporizador** → muestra tiempo hoy, racha de 7 días, heatmap de los últimos 84 días, meta diaria 45 min.
- *"El Pomodoro registra minutos reales en la BD vía AJAX al completarse o al detenerlo."*
- Ir a **Metas**: 3 metas activas. Entrar a "Inglés B2 para diciembre".
- *"Progreso ponderado: cada recurso aporta según su peso. La etiqueta verde 'En camino' o roja 'Atrasado' compara el % real con el % esperado según la fecha objetivo."*
- Click **"Exportar PDF"** → muestra el diálogo de impresión con la vista limpia.
  - *"Exportar a PDF (opcional intermedio): vista imprimible con `@media print` que oculta navbar y sidebar — sin dependencias de Composer."*

### 2.7 Estadísticas con Chart.js (req. 6 + opcional)
- Ir a **Estadísticas** → 6 gráficas Chart.js: minutos por día (barras), tarjetas por estado (doughnut), CEFR (barras horizontales), CP por tag (radar), curva de rating Codeforces (línea), histograma de solved por dificultad.
- *"Refuerza el principio del proyecto: 'ver avance real'. Los datos vienen de un endpoint JSON único `?page=stats&action=feed`."*

### 2.8 Plantillas públicas + rol Invitado (req. 5)
- Ir a **Metas → Plantillas públicas** → muestra 2 plantillas compartidas.
- **Abrir una ventana de incógnito** → entrar a la misma URL **sin login** → ver que un **invitado** puede explorar (modo solo lectura) y el botón dice "Crear cuenta para clonar".
  - *"Rol invitado (req. 5): cierra el punto de roles diferenciados."*

### 2.9 Panel administrativo (req. 5 + 6)
- Volver al login admin → menú **"Admin"**.
- *"Mini-panel: 8 tarjetas de totales globales + listado paginado de usuarios con búsqueda."*
- **Desactivar** al estudiante → el badge cambia a "Inactivo".
- Cerrar sesión → intentar login con `student@studyvault.com` → mensaje "Tu cuenta está desactivada".
- Reactivar → ya entra normal.

### 2.10 Notificaciones (req. opcional)
- Click en el **icono de campana** del navbar → dropdown con avisos.
- *"Generación automática: cada vez que entras al sitio, se evalúa si tienes repasos pendientes, si vas atrasado en una meta o si tu racha está en riesgo. El sistema usa `createOncePerDay` para no inundarte."*
- Ir a página de **Notificaciones** completa → mostrar **"Activar avisos del navegador"** (Notification API).

### 2.11 Recuperación de contraseña (req. opcional)
- Cerrar sesión → en login click **"¿Olvidaste tu contraseña?"**.
- Poner `student@studyvault.com` → se muestra el enlace en pantalla (modo demo sin SMTP).
- Abrir el enlace → cambiar contraseña → login.
- *"Token criptográfico de 32 bytes hex, expiración 1h, marcado como `used` al canjearse."*

### 2.12 Modo oscuro (req. opcional)
- En cualquier momento: tocar el icono **luna** del navbar → cambia toda la UI.
- *"Persistido en localStorage."*

---

## 3. Mapa de requisitos cubiertos (memoriza esto)

| Req. de descripción | Implementación |
|---|---|
| **HTML + CSS** | Bootstrap 5 + `assets/css/app.css` (custom). |
| **JavaScript / AJAX** | `assets/js/app.js`, fetch en cada vista. |
| **PHP OOP** | `models/`, `controllers/`, autoload con `spl_autoload_register` en `config/init.php`. |
| **MySQL con PDO preparado** | `Database` singleton en `config/database.php`. Todas las consultas usan `?` o `:name`. |
| **MySQL relacional** | 16 tablas con PK/FK, `ON DELETE CASCADE`, índices. |
| **Sesiones seguras** | `session_regenerate_id` al login, cookies HttpOnly/SameSite. |
| **Roles** | `users.role` ENUM(admin, student) + `requireAdmin()` + rol invitado (browse público). |
| **Funcionalidades dinámicas** | Paginación, búsqueda, filtros, **ordenamiento (DataTables)**, CRUD, subida de archivos, dashboard. |
| **AJAX / fetch** | Búsqueda en vivo, estudio de flashcards, marcar repasos, diccionario, notificaciones. |
| **Consumo de APIs** | Free Dictionary, Datamuse, Tatoeba, Codeforces (4 APIs públicas vía proxy PHP). |
| **Seguridad** | CSRF en todo POST, `password_hash`, throttling, headers (`X-Frame`, `nosniff`, **CSP**), filtrado por `user_id` (anti-IDOR). |
| **MVC** | `config/` `models/` `controllers/` `views/` `api/`. |
| **Opcional: tema oscuro** | Toggle persistente en localStorage. |
| **Opcional: recuperación de contraseña** | Token + expiración 1h (`PasswordReset` model). |
| **Opcional: gráficas estadísticas** | Chart.js, 6 gráficas en `?page=stats`. |
| **Opcional: notificaciones** | Centro in-app + Notification API. |
| **Opcional: PDF** | `window.print()` + CSS `@media print` (sin dependencias). |
| **Opcional: bitácora** | Tabla `activity_log` + helper `log_activity()`. |

---

## 4. Cómo funciona cada cosa — explicación lógica + código

> Si te preguntan "explícame cómo está hecho X", esta es tu chuleta.

### 4.1 Arquitectura MVC + Front Controller

- **Punto de entrada único:** `index.php` actúa como **front controller**. Lee `?page=...` y `?action=...`, valida CSRF si es POST, y despacha a la combinación correcta de controller→método.
- **Bootstrap centralizado:** `config/init.php` carga `.env`, configura sesión segura, manejo de errores, **autoload** y helpers (`csrf_*`, `json_response`, `requireLogin`, `log_activity`).
- **Autoload:** `spl_autoload_register` que busca en `models/` y `controllers/`. Sin Composer.

```
Request → index.php → init.php (env, sesión, CSRF) → Controller → Model (PDO) → View → Response
```

### 4.2 Seguridad

- **CSRF:** token aleatorio en `$_SESSION['csrf_token']`. Cada formulario imprime `csrf_field()`. Cada POST se valida en `index.php` con `csrf_verify()`. Cada fetch envía `csrf_token` en el body o `X-CSRF-Token` header.
- **Anti SQL injection:** `PDO::prepare` con placeholders. Nunca concateno entrada del usuario.
- **Anti IDOR:** TODA consulta incluye `WHERE user_id = ?`. Tests automatizados en `tests/security_test.php` validan que un usuario no puede ver recursos de otro.
- **Throttling de login:** modelo `LoginThrottle` cuenta intentos por IP+email en la tabla `login_attempts`. Tras 5 fallos en 15 min, bloquea con mensaje "intenta en N minutos".
- **Headers de seguridad:** `init.php` envía `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`, y **CSP** (permite sólo los CDNs usados).
- **Subidas:** `.htaccess` en `assets/uploads/` desactiva ejecución de PHP.

### 4.3 Repetición espaciada SM-2 (Flashcards + CP)

- **Algoritmo puro:** `Flashcard::sm2($ef, $reps, $interval, $quality)` devuelve los nuevos `ease_factor`, `repetitions`, `interval_days`, `status`. No toca BD → fácil de testear.
- **Calidad 0-5:** mapeada a 4 botones — "Otra vez" (2), "Difícil" (3), "Bien" (4), "Fácil" (5). Si quality < 3, reinicia (`reps=0`, `interval=1`); si ≥ 3, aumenta el intervalo y suma una repetición.
- **Reutilización:** el motor lo usan tanto `Flashcard::review()` como `CpProblem::reviewProblem()`. Una sola implementación, dos dominios.
- **Cola diaria:** `SELECT * FROM flashcards WHERE due_date <= CURRENT_DATE ORDER BY due_date`.

### 4.4 Integración Codeforces

- **Sin API key:** consume `user.info` y `user.status` con `file_get_contents` desde el servidor.
- **Anti CORS:** todas las llamadas externas pasan por **proxy PHP** en `api/`.
- **Dedup:** `INSERT...ON DUPLICATE KEY UPDATE` evita duplicar el mismo problema en sincronizaciones repetidas.
- **Catálogo cacheado:** `problemset.problems` (~9000 problemas) se descarga una vez a `cf_problemset_cache` y de ahí se sugiere "next problem" en tu rating +100/+300.

### 4.5 Progreso ponderado de metas (función pura)

```
weighted_pct = sum(weight_i * pct_i) / sum(weight_i)
```
Implementado como `Goal::weightedProgress($items)` — función pura testeada. El "pace" compara avance vs (días transcurridos / días totales) → etiqueta "En camino" o "Atrasado".

### 4.6 DataTables (ordenamiento)

- Declarativo: marca `<table data-sv-sortable>` y `app.js::svInitDataTables()` lo detecta y lo activa.
- En Recursos uso `data-sv-sort-only` para que coexista con la paginación server-side propia.
- Tras un re-render AJAX llamamos `svInitDataTables()` de nuevo para no perder el sort.

### 4.7 Chart.js / Estadísticas

- Vista renderiza 6 `<canvas>` vacíos.
- Un único endpoint `?page=stats&action=feed` devuelve **un JSON con todos los datasets**.
- Las consultas SQL agregan en MySQL (`GROUP BY status`, `GROUP BY cefr_level`, etc.) — no traemos rows individuales.
- Histograma de CP por rating: agrupa en buckets de 200 (`floor(rating/200)*200`).

### 4.8 Notificaciones

- Tabla `notifications` (user_id, type, title, body, link, read_at).
- `NotificationController::generate($userId)` evalúa 5 reglas: repasos de flashcards, repasos de CP, metas atrasadas, meta diaria sin cubrir, racha en riesgo.
- **Idempotente por día:** `createOncePerDay($type)` evita duplicados (chequea si existe una del mismo `type` sin leer en las últimas 24h).
- El navbar tiene un dropdown que hace fetch a `?page=notifications&action=feed` cada 60s + al hacer click.
- **Notification API:** opt-in del navegador, persistido en `localStorage`.

### 4.9 Recuperación de contraseña

- Tabla `password_resets(user_id, token, expires_at, used)`.
- `PasswordReset::create($userId)` invalida tokens anteriores y genera uno nuevo (`bin2hex(random_bytes(32))`, expira en 1h).
- `findValid($token)` exige `used=0 AND expires_at > NOW()`.
- Vista `forgot.php` muestra el enlace en pantalla (porque XAMPP no tiene SMTP). En producción se enviaría por email.

### 4.10 Exportar PDF (sin Composer)

- Botón ejecuta `window.print()`.
- `assets/css/app.css` tiene una sección `@media print` que oculta navbar, sidebar, botones, y deja sólo el contenido. El navegador permite "Guardar como PDF".

### 4.11 Modo oscuro

- Variables CSS bajo `[data-theme="dark"]`. JS pone/quita ese atributo en `<html>` y guarda en localStorage.

---

## 5. Posibles preguntas (y cómo responder)

### Sobre arquitectura
- **¿Por qué MVC y no un framework como Laravel?**
  - "Para cumplir el requisito de **PHP orientado a objetos puro** sin depender de un framework, y para demostrar que entiendo la separación de responsabilidades por mi cuenta. El proyecto es escalable: ya tiene autoload, front controller, init centralizado y proxies de API."

- **¿Cómo está organizado el código?**
  - "`config/` bootstrap y BD; `models/` clases de dominio con PDO; `controllers/` reciben request y orquestan; `views/` PHP puro con `htmlspecialchars`; `api/` proxies para APIs externas; `tests/` suite simple con assert."

### Sobre seguridad
- **¿Cómo previenes SQL injection?**
  - "PDO con consultas preparadas en TODAS las queries. Nunca concateno entrada de usuario. Si me apuntas a un archivo, te muestro un ejemplo."

- **¿Y CSRF?**
  - "Token por sesión generado con `random_bytes(32)`. Todo formulario lleva `csrf_field()`. Cada AJAX manda `csrf_token` en el body. `index.php` valida antes del despacho — si falta o no coincide, 403."

- **¿Anti-IDOR?**
  - "Cada consulta filtra por `WHERE user_id = ?`. Lo tengo cubierto con tests en `tests/security_test.php` que intentan que el usuario A vea recursos del B y verifican el rechazo."

- **¿Cómo proteges las contraseñas?**
  - "`password_hash()` con bcrypt por defecto + `password_verify()` al login. Nunca guardo plaintext."

### Sobre features
- **¿Qué es SM-2 y por qué lo usan?**
  - "Es el algoritmo de Anki: mide qué tan bien recuerdas algo del 0 al 5 y calcula cuándo deberías repasar de nuevo, con un intervalo creciente. Si fallas, reinicia; si aciertas, multiplica por el ease factor. Esto fija memorias a largo plazo en menos tiempo que estudiar todo todos los días."

- **¿Por qué Codeforces y no LeetCode?**
  - "Porque Codeforces tiene una **API pública sin key**, lo que cubre el requisito de consumo de servicios web sin complicaciones. Además es la plataforma estándar de programación competitiva."

- **¿Cuántas APIs externas usan?**
  - "Cuatro: Free Dictionary (definiciones), Datamuse (colocaciones — cómo se usa una palabra con otras), Tatoeba (frases de ejemplo reales), Codeforces (rating + problemas resueltos + concursos + catálogo)."

- **¿Por qué un proxy PHP en vez de llamar las APIs desde el navegador?**
  - "Tres razones: 1) CORS — varias APIs no permiten llamadas desde browser. 2) Caché — guardamos las respuestas para no agotar rate limits. 3) Rate limiting — controlamos cuántas veces el usuario puede pegar la API por sesión."

### Sobre código específico
- **¿Cómo funciona la paginación?**
  - "El controller pasa `LIMIT` y `OFFSET` al modelo (`Resource::getByUser($u, 10, $offset)`), y otro método `countByUser` para saber el total y pintar los números de página."

- **¿Por qué `data-sv-sortable` en vez de inicializar DataTables fila por fila?**
  - "Para que sea **declarativo y reutilizable**: cualquier tabla nueva sólo necesita el atributo; el JS genérico se encarga del resto."

- **¿Las gráficas se generan en servidor o en cliente?**
  - "El servidor manda **datos agregados ya calculados** en un JSON pequeño; Chart.js los pinta. Esto es más eficiente porque MySQL hace el `GROUP BY`, no JavaScript."

### Sobre datos y BD
- **¿Cuántas tablas tienes?**
  - "16, todas con FK y `ON DELETE CASCADE` para mantener integridad. Tengo índices en columnas de búsqueda (user_id, due_date, status, cefr_level)."

- **¿Cómo manejas el borrado?**
  - "**Soft delete**: la mayoría de tablas tienen `deleted_at TIMESTAMP NULL`, y todas las queries filtran `WHERE deleted_at IS NULL`. Permite recuperar accidentes."

- **¿Migraciones?**
  - "Sí: archivos `migrations_v2.sql` ... `v8.sql` incrementales. El esquema canónico final está en `ENTREGA/studyvault_completo.sql`, listo para reset desde cero."

### Sobre escala / próximos pasos
- **¿Qué falta para producción?**
  - "API REST documentada para una PWA móvil, SMTP real (ahora muestro el enlace en pantalla), Docker para despliegue, y un panel admin más rico (auditoría por usuario, no solo activar/desactivar)."

- **¿Cómo escala MySQL?**
  - "Ya tiene índices en los joins críticos. Si crece, agregaría caché Redis para el dashboard y replicación read-only."

### Preguntas trampa típicas
- **"¿Y si te quito Internet?"**
  - "La app sigue funcionando para todo lo local: estudiar flashcards, marcar progreso, gestionar metas. Lo que falla son los 4 proxies a APIs externas; mi código devuelve mensajes claros de error, no errores 500."

- **"¿Tu CSRF es seguro contra ataques de tiempo?"**
  - "Sí, uso `hash_equals()` que es comparación en tiempo constante."

- **"¿Los tokens de reset se invalidan al usarse?"**
  - "Sí: tras un cambio exitoso, `markUsed()` pone `used=1`. Además, al crear un token nuevo, invalido los anteriores del mismo usuario, así sólo hay uno activo."

---

## 6. Datos rápidos para mencionar como métricas

- **16 tablas**, **10 modelos**, **11 controladores**, **23 vistas**, **5 proxies de API**.
- **53 tests** automatizados (tests de seguridad, SM-2, progreso ponderado, throttle de login, IDOR).
- **4 APIs externas** consumidas (Free Dictionary, Datamuse, Tatoeba, Codeforces).
- **6 gráficas** Chart.js + **1 heatmap** de constancia.
- **8 migraciones** incrementales documentadas en `PLAN_IMPLEMENTACION.md` con bitácora.

---

## 7. Si algo falla durante la demo (plan de contingencia)

| Síntoma | Qué decir / hacer |
|---|---|
| Una página no carga | "Probablemente perdí sesión, voy a iniciarla otra vez." → reiniciar XAMPP MySQL y refrescar. |
| Chart.js sale en blanco | "Es un problema de conexión al CDN, los datos están — déjame mostrarte el endpoint." → abre `?page=stats&action=feed` para mostrar el JSON. |
| Codeforces no sincroniza | "La API tiene rate limit; los datos demo ya están en la BD." → muestra la tabla precargada. |
| Notificación duplicada | "Generación idempotente: la próxima ya no se duplica." |
| Login dice incorrecto | "Recargar y usar `admin@studyvault.com` con contraseña `password` exactamente." |

---

## 8. Resumen ejecutivo en 30 segundos (por si lo cortan)

> "StudyVault es un gestor de estudio personal en PHP/MySQL con arquitectura MVC. Resuelve el problema de tener herramientas de estudio dispersas: integra recursos, flashcards con SM-2, problemas de Codeforces vía su API, metas con progreso ponderado, temporizador Pomodoro, gráficas de avance, panel administrativo y rol invitado. Cumple las 10 especificaciones obligatorias de la descripción, supera el mínimo en seguridad (CSRF, throttling, CSP, anti-IDOR con tests) y agrega siete mejoras opcionales: tema oscuro, bitácora, recuperación de contraseña, notificaciones, exportación PDF, ordenamiento con DataTables y gráficas Chart.js."

**Cierre:** "¿Alguna pregunta?"

---

### Buena suerte. Repite el guion 2 veces en voz alta antes de exponer.
